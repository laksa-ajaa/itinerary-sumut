from fastapi import FastAPI, Body
from pydantic import BaseModel
import os
import psycopg2
from psycopg2.extras import RealDictCursor
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
import numpy as np


app = FastAPI(title="Itinerary Reco Service")


DB_HOST = os.getenv("DB_HOST", "127.0.0.1")
DB_PORT = int(os.getenv("DB_PORT", "5432"))
DB_NAME = os.getenv("DB_DATABASE", "itinerary")
DB_USER = os.getenv("DB_USERNAME", "postgres")
DB_PASS = os.getenv("DB_PASSWORD", "postgres")


def get_conn():
    return psycopg2.connect(
        host=DB_HOST, port=DB_PORT, dbname=DB_NAME, user=DB_USER, password=DB_PASS
    )


class RecommendRequest(BaseModel):
    user_id: int
    top_k: int = 20


# In-memory CBF state
tfidf_vectorizer: TfidfVectorizer | None = None
place_matrix = None
place_ids: list[int] = []


@app.get("/health")
def health():
    return {"status": "ok"}


@app.post("/train")
def train():
    global tfidf_vectorizer, place_matrix, place_ids
    with get_conn() as conn:
        with conn.cursor(cursor_factory=RealDictCursor) as cur:
            cur.execute(
                """
                SELECT p.id,
                       COALESCE(p.description,'') || ' ' ||
                       COALESCE(string_agg(c.name,' '), '') AS text
                FROM places p
                LEFT JOIN place_category pc ON pc.place_id = p.id
                LEFT JOIN categories c ON c.id = pc.category_id
                GROUP BY p.id
                ORDER BY p.id
                """
            )
            rows = cur.fetchall()

    place_ids = [int(r["id"]) for r in rows]
    corpus = [r["text"] or "" for r in rows]
    tfidf_vectorizer = TfidfVectorizer(max_features=8000)
    place_matrix = tfidf_vectorizer.fit_transform(corpus)
    return {"trained_places": len(place_ids)}


def get_user_liked_place_ids(user_id: int) -> list[int]:
    with get_conn() as conn:
        with conn.cursor() as cur:
            cur.execute(
                "SELECT place_id FROM user_ratings WHERE user_id=%s AND rating>=4",
                (user_id,),
            )
            liked = [int(r[0]) for r in cur.fetchall()]
    return liked


@app.post("/recommend/content")
def recommend_content(req: RecommendRequest):
    if place_matrix is None:
        return {"place_ids": []}
    liked = get_user_liked_place_ids(req.user_id)
    if not liked:
        # fallback: ranking by rating_count then rating_avg
        with get_conn() as conn:
            with conn.cursor() as cur:
                cur.execute(
                    "SELECT id FROM places ORDER BY rating_count DESC, rating_avg DESC LIMIT %s",
                    (req.top_k,),
                )
                return {"place_ids": [int(r[0]) for r in cur.fetchall()]}

    liked_idx = [place_ids.index(pid) for pid in liked if pid in place_ids]
    if not liked_idx:
        return {"place_ids": []}
    user_vec = place_matrix[liked_idx].mean(axis=0)
    sims = cosine_similarity(user_vec, place_matrix).ravel()
    order = np.argsort(-sims)
    recs: list[int] = []
    for i in order:
        pid = int(place_ids[i])
        if pid not in liked:
            recs.append(pid)
        if len(recs) >= req.top_k:
            break
    return {"place_ids": recs}


@app.post("/recommend/collab")
def recommend_collab(req: RecommendRequest):
    # Baseline sederhana: item co-visit dari user_visits
    with get_conn() as conn:
        with conn.cursor() as cur:
            cur.execute(
                """
                WITH user_items AS (
                    SELECT DISTINCT place_id FROM user_visits WHERE user_id=%s
                ),
                co AS (
                    SELECT v.place_id AS target, v2.place_id AS other, COUNT(*) as cnt
                    FROM user_visits v
                    JOIN user_visits v2 ON v.user_id = v2.user_id AND v.place_id <> v2.place_id
                    WHERE v.place_id IN (SELECT place_id FROM user_items)
                    GROUP BY v.place_id, v2.place_id
                )
                SELECT other FROM co GROUP BY other ORDER BY SUM(cnt) DESC LIMIT %s
                """,
                (req.user_id, req.top_k),
            )
            rows = cur.fetchall()
    return {"place_ids": [int(r[0]) for r in rows]}


@app.post("/recommend/mixed")
def recommend_mixed(req: RecommendRequest):
    # simple blend 0.6 CBF + 0.4 CF via rank aggregation
    cbf = recommend_content(req)["place_ids"]
    cf = recommend_collab(req)["place_ids"]
    score: dict[int, float] = {}
    def add(lst: list[int], w: float):
        for rank, pid in enumerate(lst):
            score[pid] = score.get(pid, 0.0) + w * (1.0 / (1 + rank))
    add(cbf, 0.6)
    add(cf, 0.4)
    ranked = sorted(score.items(), key=lambda x: -x[1])
    return {"place_ids": [pid for pid, _ in ranked[: req.top_k]]}


if __name__ == "__main__":
    import uvicorn

    uvicorn.run("main:app", host="0.0.0.0", port=8001, reload=True)


