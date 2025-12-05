## Itinerary Sumut — Dokumentasi Sementara

### Ringkasan Arsitektur
- Backend: Laravel 12 (API + CRUD + GeoJSON + integrasi rekomendasi)
- Rekomendasi: FastAPI (Python) di folder `reco_service/`
- Database: PostgreSQL
- Peta: Leaflet (data disajikan via endpoint GeoJSON)

### Prasyarat
- PHP 8.2+, Composer
- PostgreSQL
- Python 3.10+ (untuk service rekomendasi)

### Konfigurasi `.env`
Wajib diisi:
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=itinerary_sumut
DB_USERNAME=root
DB_PASSWORD=root

GOOGLE_PLACES_KEY=PASTE_API_KEY
RECO_SERVICE_URL=http://localhost:8001
```
Refresh konfigurasi setelah mengubah `.env`:
```
php artisan config:clear
```

### Migrasi Database
```
php artisan migrate --force
```

### Service Rekomendasi (Python)
Lokasi: `reco_service/`

Instal & jalankan:
```
cd reco_service
python3 -m venv .venv && source .venv/bin/activate
pip install -r requirements.txt
export DB_HOST=127.0.0.1 DB_PORT=5432 DB_DATABASE=itinerary_sumut DB_USERNAME=root DB_PASSWORD=root
uvicorn main:app --host 0.0.0.0 --port 8001 --reload
```

Endpoint FastAPI:
- `GET /health` → status
- `POST /train` → latih Content-Based Filtering (CBF)
- `POST /recommend/content` body: `{ "user_id": 1, "top_k": 20 }`
- `POST /recommend/collab` body: sama; rekomendasi item-item sederhana dari co-visit
- `POST /recommend/mixed` body: kombinasi CBF+CF

Contoh uji:
```
curl -X POST http://localhost:8001/train
curl -X POST http://localhost:8001/recommend/mixed -H 'Content-Type: application/json' -d '{"user_id":1,"top_k":10}'
```

### Endpoint Laravel yang Tersedia
- `GET /api/places` — daftar tempat (paginate)
- `GET /api/places/{place}` — detail tempat (ID numerik)
- `GET /api/places/map` — GeoJSON untuk peta
- `POST /api/recommend` — panggil service Python; body: `{ "user_id": 1, "top_k": 10 }`

Contoh uji cepat:
```
curl http://localhost:8000/api/places
curl http://localhost:8000/api/places/map
curl -X POST http://localhost:8000/api/recommend -H 'Content-Type: application/json' -d '{"user_id":1,"top_k":10}'
```

### Struktur Data Utama
- `places` (memuat semua jenis tempat) — kolom penting: `id`, `name`, `kind`, `description`, `open_time`, `close_time`, `entry_price`, `latitude`, `longitude`, `visit_count`, `provinces`, `city`, `subdistrict`, `street_name`, `postal_code`, `google_place_id`, `rating_avg`, `rating_count`.
- `categories`, `facilities`, pivot: `place_category`, `place_facility`.
- Interaksi user: `user_ratings(rating 1–5)`, `user_visits`.
- Itinerary: `itineraries`, `itinerary_items`.

### Yang Sudah Berjalan
- Skema DB + migrasi
- Model & relasi Eloquent
- CRUD minimal `places`
- Endpoint GeoJSON peta
- Importer Google Places (Nearby + Details) + pengisian `kind`
- Service rekomendasi FastAPI (CBF, CF sederhana, blend) + integrasi dari Laravel

### Next Steps (disarankan)
- Auth API (Sanctum/Breeze) + endpoint rating/visit user
- Filter `kind` di `/api/places` (+ parameter pencarian lokasi)
- Penyusunan itinerary otomatis berdasarkan waktu, jarak, dan budget
- Scheduler: retrain rekomendasi + impor pembaruan berkala

### Catatan Kuota & TOS Google Places
- Hanya command importer yang memanggil API Google.
- Hormati `next_page_token` (delay 2–3 detik) — sudah diterapkan.
- Simpan `place_id` dan data yang diizinkan; gunakan peta OSM/Leaflet untuk menampilkan supaya hemat biaya.


