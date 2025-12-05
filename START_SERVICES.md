# Panduan Menjalankan Semua Service

## Service yang Perlu Dijalankan

### 1. ✅ PostgreSQL Database (WAJIB)
Pastikan PostgreSQL sudah running:
```bash
# Ubuntu/Debian
sudo systemctl status postgresql

# Atau start jika belum running
sudo systemctl start postgresql
```

### 2. ✅ Recommendation Service - Python FastAPI (WAJIB)
**Port: 8001**

Ini yang menyebabkan error di log jika tidak running. Jalankan di terminal terpisah:

```bash
cd reco_service

# Jika belum setup virtual environment
python3 -m venv .venv
source .venv/bin/activate  # atau `.venv\Scripts\activate` di Windows
pip install -r requirements.txt

# Set environment variables untuk database
export DB_HOST=127.0.0.1
export DB_PORT=5432
export DB_DATABASE=itinerary_sumut
export DB_USERNAME=root
export DB_PASSWORD=root

# Jalankan service
uvicorn main:app --host 0.0.0.0 --port 8001 --reload
```

**Verifikasi:**
```bash
curl http://localhost:8001/health
```

### 3. ✅ Laravel Server (WAJIB)
**Port: 8000 (default)**

```bash
php artisan serve
# atau custom port
php artisan serve --port=8000
```

### 4. ⚠️ Queue Worker (Opsional - hanya jika ada queue jobs)
Jika ada job yang di-queue, jalankan:

```bash
php artisan queue:work
# atau dengan tries
php artisan queue:listen --tries=1
```

### 5. ⚠️ Vite Dev Server (Opsional - untuk development frontend)
**Port: 5173 (default)**

Hanya perlu jika melakukan perubahan pada assets frontend (JS/CSS):

```bash
npm run dev
```

Jika sudah production atau assets sudah di-build:
```bash
npm run build
# Tidak perlu `npm run dev` lagi
```

---

## Cara Mudah: Jalankan Semua Sekaligus

Dari `composer.json`, sudah ada script yang menjalankan semua service sekaligus:

```bash
composer run dev
```

Ini akan menjalankan:
- ✅ `php artisan serve` (Laravel)
- ✅ `php artisan queue:listen` (Queue)
- ✅ `php artisan pail` (Log viewer)
- ✅ `npm run dev` (Vite)

**TAPI**, ini TIDAK termasuk Recommendation Service (Python), jadi tetap harus jalankan manual di terminal terpisah!

---

## Checklist Quick Start

1. ✅ PostgreSQL running
2. ✅ Recommendation Service (Python) di port 8001
3. ✅ Laravel Server (`php artisan serve`)
4. ⚠️ Queue Worker (jika diperlukan)
5. ⚠️ Vite Dev Server (jika development frontend)

---

## Troubleshooting

### Error: "Connection refused to localhost:8001"
➡️ **Solusi:** Recommendation Service tidak running. Jalankan langkah #2 di atas.

### Error: "relation user_visits does not exist"
➡️ **Solusi:** Table `user_visits` belum dibuat. Jalankan migration:
```bash
php artisan migrate
```

### Error: "500 Internal Server Error" di Python service
➡️ **Solusi:** 
- Pastikan database sudah di-migrate dengan benar
- Python service sekarang punya fallback ke `user_ratings` jika `user_visits` kosong
- Pastikan environment variables database sudah di-set di terminal yang menjalankan Python service

### Error: "No places found matching categories"
➡️ **Solusi:** Bisa jadi:
- Recommendation service tidak running (akan fallback ke query biasa)
- Data places tidak memiliki tags yang valid
- Filter kategori terlalu ketat

### Assets CSS/JS tidak load
➡️ **Solusi:** Jalankan `npm run dev` atau `npm run build`

### Queue jobs tidak diproses
➡️ **Solusi:** Jalankan `php artisan queue:work`

---

## Port yang Digunakan

- **8000**: Laravel Server
- **8001**: Recommendation Service (Python FastAPI)
- **5173**: Vite Dev Server (development)
- **5432**: PostgreSQL (default)

