from fastapi import FastAPI, Body
from pydantic import BaseModel
import os
import psycopg2
from psycopg2.extras import RealDictCursor
from psycopg2 import pool
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
from sklearn.preprocessing import normalize
import numpy as np
import logging
from typing import List, Optional
from uuid import UUID

# Setup logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = FastAPI(title="Itinerary Reco Service")

# Database config
DB_HOST = os.getenv("DB_HOST", "127.0.0.1")
DB_PORT = int(os.getenv("DB_PORT", "5432"))
DB_NAME = os.getenv("DB_DATABASE", "itinerary")
DB_USER = os.getenv("DB_USERNAME", "postgres")
DB_PASS = os.getenv("DB_PASSWORD", "postgres")

# Connection pool
connection_pool = None

def init_pool():
    global connection_pool
    try:
        connection_pool = psycopg2.pool.SimpleConnectionPool(
            1, 20,
            host=DB_HOST, 
            port=DB_PORT, 
            dbname=DB_NAME, 
            user=DB_USER, 
            password=DB_PASS
        )
        logger.info("Database connection pool created")
    except Exception as e:
        logger.error(f"Failed to create connection pool: {e}")
        raise

def get_conn():
    if connection_pool is None:
        init_pool()
    return connection_pool.getconn()

def return_conn(conn):
    if connection_pool:
        connection_pool.putconn(conn)

class RecommendRequest(BaseModel):
    user_id: int
    top_k: int = 20

# In-memory CBF state
tfidf_vectorizer: Optional[TfidfVectorizer] = None
place_matrix = None
place_ids: List[str] = []  # Store UUID as string
place_metadata: dict = {}  # Store additional metadata for debugging

@app.on_event("startup")
async def startup_event():
    init_pool()
    logger.info("Application started")

@app.on_event("shutdown")
async def shutdown_event():
    if connection_pool:
        connection_pool.closeall()
    logger.info("Application shutdown")

@app.get("/health")
def health():
    return {"status": "ok"}

@app.post("/train")
def train():
    """
    Train TF-IDF model using enriched place data:
    - name (weight: 3x) - most important identifier
    - kind (weight: 3x) - category/type of place (wisata, kuliner, etc)
    - description (weight: 2x) - if exists
    """
    global tfidf_vectorizer, place_matrix, place_ids, place_metadata
    
    conn = get_conn()
    try:
        with conn.cursor(cursor_factory=RealDictCursor) as cur:
            # Query dengan enrichment dari kind saja
            cur.execute(
                """
                SELECT 
                    p.id,
                    COALESCE(p.name, '') AS name,
                    COALESCE(p.kind, '') AS kind,
                    COALESCE(p.description, '') AS description
                FROM places p
                WHERE p.kind = 'wisata'
                ORDER BY p.id
                """
            )
            rows = cur.fetchall()
    except psycopg2.Error as e:
        logger.error(f"Database error during training: {e}")
        return_conn(conn)
        return {"error": f"Database error: {str(e)}"}
    finally:
        return_conn(conn)

    if not rows:
        logger.warning("No places found for training")
        return {"trained_places": 0, "error": "No places found"}
    
    # Build enriched corpus with weighted repetition
    place_ids = [str(r["id"]) for r in rows]
    corpus = []
    place_metadata = {}
    
    for r in rows:
        # Weighted text: repeat important fields for emphasis
        text_parts = []
        
        # Name (weight: 3x) - most important
        # e.g., "Pantai Kuta" will appear 3 times
        if r["name"]:
            text_parts.extend([r["name"]] * 3)
        
        # Kind (weight: 3x) - category is very important for similarity
        # e.g., "wisata" akan muncul 3 kali
        # Ini akan membuat semua tempat wisata punya base similarity
        if r["kind"]:
            text_parts.extend([r["kind"]] * 3)
        
        # Description (weight: 2x) - if exists, gives more context
        if r["description"]:
            text_parts.extend([r["description"]] * 2)
        
        combined_text = " ".join(text_parts)
        corpus.append(combined_text)
        
        # Store metadata for debugging
        place_metadata[str(r["id"])] = {
            "name": r["name"],
            "kind": r["kind"],
            "has_description": bool(r["description"]),
            "text_length": len(combined_text)
        }
    
    # Train TF-IDF with optimized parameters
    try:
        tfidf_vectorizer = TfidfVectorizer(
            max_features=5000,  # Limit features
            min_df=1,           # Keep all terms (small dataset likely)
            max_df=0.8,         # Remove too common terms
            ngram_range=(1, 2), # Unigrams and bigrams (e.g., "Pantai Kuta")
            strip_accents='unicode',
            lowercase=True
        )
        place_matrix = tfidf_vectorizer.fit_transform(corpus)
        
        logger.info(f"Training completed: {len(place_ids)} places, "
                   f"matrix shape: {place_matrix.shape}")
        
        return {
            "trained_places": len(place_ids),
            "features": place_matrix.shape[1],
            "sparsity": f"{(1.0 - place_matrix.nnz / (place_matrix.shape[0] * place_matrix.shape[1])) * 100:.2f}%"
        }
    except Exception as e:
        logger.error(f"Error during TF-IDF training: {e}")
        return {"error": f"Training failed: {str(e)}"}

def get_user_liked_place_ids(user_id: int) -> List[str]:
    """Get place IDs that user rated >= 4"""
    conn = get_conn()
    try:
        with conn.cursor() as cur:
            cur.execute(
                "SELECT place_id FROM user_ratings WHERE user_id=%s AND rating>=4",
                (user_id,),
            )
            liked = [str(r[0]) for r in cur.fetchall()]
            logger.info(f"User {user_id} has {len(liked)} liked places")
            return liked
    except psycopg2.Error as e:
        logger.error(f"Database error getting user likes: {e}")
        return []
    finally:
        return_conn(conn)

@app.post("/recommend/content")
def recommend_content(req: RecommendRequest):
    """Content-Based Filtering using TF-IDF + Cosine Similarity"""
    
    # Fallback if not trained
    if place_matrix is None or tfidf_vectorizer is None:
        logger.warning("Model not trained, using popularity fallback")
        conn = get_conn()
        try:
            with conn.cursor() as cur:
                cur.execute(
                    """SELECT id FROM places 
                       WHERE kind='wisata' 
                       ORDER BY rating_count DESC, rating_avg DESC 
                       LIMIT %s""",
                    (req.top_k,),
                )
                return {"place_ids": [str(r[0]) for r in cur.fetchall()]}
        finally:
            return_conn(conn)
    
    # Get user's liked places
    liked = get_user_liked_place_ids(req.user_id)
    
    # Fallback if user has no likes
    if not liked:
        logger.info(f"User {req.user_id} has no likes, using popularity")
        conn = get_conn()
        try:
            with conn.cursor() as cur:
                cur.execute(
                    """SELECT id FROM places 
                       WHERE kind='wisata' 
                       ORDER BY rating_count DESC, rating_avg DESC 
                       LIMIT %s""",
                    (req.top_k,),
                )
                return {"place_ids": [str(r[0]) for r in cur.fetchall()]}
        finally:
            return_conn(conn)

    # Find indices of liked places
    liked_idx = [place_ids.index(pid) for pid in liked if pid in place_ids]
    
    if not liked_idx:
        logger.warning(f"No liked places found in trained data for user {req.user_id}")
        return {"place_ids": []}
    
    # Compute user profile as mean of liked places (with normalization)
    user_vec = place_matrix[liked_idx].mean(axis=0)
    user_vec = normalize(user_vec)  # Normalize for better similarity
    
    # Calculate similarities
    sims = cosine_similarity(user_vec, place_matrix).ravel()
    
    # Sort by similarity (descending)
    order = np.argsort(-sims)
    
    # Build recommendations (exclude already liked)
    recs: List[str] = []
    for i in order:
        pid = str(place_ids[i])
        if pid not in liked:
            recs.append(pid)
        if len(recs) >= req.top_k:
            break
    
    logger.info(f"CBF recommended {len(recs)} places for user {req.user_id}")
    return {"place_ids": recs}

@app.post("/recommend/collab")
def recommend_collab(req: RecommendRequest):
    """Collaborative Filtering using co-occurrence"""
    
    conn = get_conn()
    try:
        with conn.cursor() as cur:
            # Check if user_visits table exists
            cur.execute(
                """
                SELECT EXISTS (
                    SELECT FROM information_schema.tables 
                    WHERE table_schema = 'public' 
                    AND table_name = 'user_visits'
                )
                """
            )
            has_visits_table = cur.fetchone()[0]
            
            if has_visits_table:
                # Check if user has visits
                cur.execute(
                    "SELECT COUNT(*) FROM user_visits WHERE user_id=%s", 
                    (req.user_id,)
                )
                visit_count = cur.fetchone()[0]
                
                if visit_count > 0:
                    # Use user_visits for CF
                    cur.execute(
                        """
                        WITH user_items AS (
                            SELECT DISTINCT place_id FROM user_visits WHERE user_id=%s
                        ),
                        co AS (
                            SELECT v2.place_id AS other, COUNT(DISTINCT v.user_id) as cnt
                            FROM user_visits v
                            JOIN user_visits v2 ON v.user_id = v2.user_id 
                            WHERE v.place_id IN (SELECT place_id FROM user_items)
                              AND v2.place_id NOT IN (SELECT place_id FROM user_items)
                            GROUP BY v2.place_id
                        )
                        SELECT other FROM co ORDER BY cnt DESC LIMIT %s
                        """,
                        (req.user_id, req.top_k),
                    )
                    rows = cur.fetchall()
                    if rows:
                        logger.info(f"CF (visits) recommended {len(rows)} places")
                        return {"place_ids": [str(r[0]) for r in rows]}
            
            # Fallback: use user_ratings for CF
            cur.execute(
                """
                WITH user_items AS (
                    SELECT DISTINCT place_id FROM user_ratings 
                    WHERE user_id=%s AND rating>=4
                ),
                co AS (
                    SELECT r2.place_id AS other, COUNT(DISTINCT r.user_id) as cnt
                    FROM user_ratings r
                    JOIN user_ratings r2 ON r.user_id = r2.user_id 
                    WHERE r.place_id IN (SELECT place_id FROM user_items)
                      AND r2.place_id NOT IN (SELECT place_id FROM user_items)
                      AND r.rating >= 4 
                      AND r2.rating >= 4
                    GROUP BY r2.place_id
                )
                SELECT other FROM co ORDER BY cnt DESC LIMIT %s
                """,
                (req.user_id, req.top_k),
            )
            rows = cur.fetchall()
            logger.info(f"CF (ratings) recommended {len(rows)} places")
            return {"place_ids": [str(r[0]) for r in rows]}
            
    except psycopg2.Error as e:
        logger.error(f"Database error in collab filtering: {e}")
        # Final fallback: popularity
        try:
            with conn.cursor() as cur:
                cur.execute(
                    """SELECT id FROM places 
                       WHERE kind='wisata' 
                       ORDER BY rating_count DESC, rating_avg DESC 
                       LIMIT %s""",
                    (req.top_k,),
                )
                return {"place_ids": [str(r[0]) for r in cur.fetchall()]}
        except Exception as e2:
            logger.error(f"Fallback also failed: {e2}")
            return {"place_ids": []}
    finally:
        return_conn(conn)

@app.post("/recommend/mixed")
def recommend_mixed(req: RecommendRequest):
    """
    Hybrid recommendation using rank aggregation
    Dynamic weighting based on user history
    """
    
    # Get user history to determine weights
    liked = get_user_liked_place_ids(req.user_id)
    
    # Dynamic weighting: more likes = trust CBF more
    if len(liked) >= 10:
        weight_cbf, weight_cf = 0.7, 0.3
    elif len(liked) >= 5:
        weight_cbf, weight_cf = 0.6, 0.4
    else:
        weight_cbf, weight_cf = 0.4, 0.6  # New users: trust CF more
    
    logger.info(f"Mixed weights for user {req.user_id}: "
                f"CBF={weight_cbf}, CF={weight_cf}")
    
    # Get recommendations from both methods
    cbf = recommend_content(req)["place_ids"]
    cf = recommend_collab(req)["place_ids"]
    
    # Rank aggregation with reciprocal rank
    score: dict = {}
    
    def add_scores(lst: List[str], weight: float):
        for rank, pid in enumerate(lst):
            score[pid] = score.get(pid, 0.0) + weight * (1.0 / (1 + rank))
    
    add_scores(cbf, weight_cbf)
    add_scores(cf, weight_cf)
    
    # Sort by aggregated score
    ranked = sorted(score.items(), key=lambda x: -x[1])
    result = [str(pid) for pid, _ in ranked[:req.top_k]]
    
    logger.info(f"Mixed recommended {len(result)} places")
    return {"place_ids": result}

@app.get("/debug/place/{place_id}")
def debug_place(place_id: str):
    """Debug endpoint to see place features"""
    if place_id in place_metadata:
        return place_metadata[place_id]
    return {"error": "Place not found in trained data"}

@app.get("/stats")
def stats():
    """Get training statistics"""
    return {
        "trained": place_matrix is not None,
        "total_places": len(place_ids) if place_ids else 0,
        "features": place_matrix.shape[1] if place_matrix is not None else 0
    }

if __name__ == "__main__":
    import uvicorn
    uvicorn.run("main:app", host="0.0.0.0", port=8001, reload=True)