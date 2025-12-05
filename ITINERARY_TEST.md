# Panduan Testing Itinerary Generator

## Setup Database

Jalankan migration dan seeder:

```bash
php artisan migrate
php artisan db:seed
```

Ini akan membuat:
- 12 kategori (Alam, Kuliner, Sejarah, dll)
- 11 tempat: 6 wisata, 3 restoran, 2 hotel
- 3 user dengan preferensi berbeda

## Data Dummy yang Dibuat

### Wisata (Attraction)
1. Istana Maimun (Sejarah, Budaya) - Medan
2. Masjid Raya Al-Mashun (Religi, Sejarah) - Medan
3. Museum Sumatera Utara (Museum, Sejarah, Budaya) - Medan
4. Danau Toba View Point (Danau, Alam) - Parapat
5. Pulau Samosir Tour (Alam, Budaya, Danau) - Samosir
6. Pantai Parbaba (Pantai, Alam) - Parapat

### Restoran
1. Restoran Tip Top - Medan
2. Resto Tabo Cottage - Parapat
3. Resto Pantai Pasir Putih - Parapat

### Hotel
1. Hotel Santika Medan - Medan
2. Hotel Inna Parapat - Parapat

### User dengan Preferensi
- **User 1 (ahmad@example.com)**: Suka Alam, Kuliner, Sejarah
- **User 2 (siti@example.com)**: Suka Kuliner, Budaya
- **User 3 (budi@example.com)**: Suka Alam, Pantai

## Testing API

### Generate Itinerary

**Endpoint:** `POST /api/itinerary/generate`

**Request Body:**
```json
{
  "user_id": 1,
  "selected_place_ids": [1, 2, 3, 4, 5, 6],
  "duration_days": 3,
  "start_date": "2024-01-15"
}
```

**Contoh cURL:**
```bash
curl -X POST http://localhost:8000/api/itinerary/generate \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "selected_place_ids": [1, 2, 3, 4, 5, 6],
    "duration_days": 3,
    "start_date": "2024-01-15"
  }'
```

**Response JSON:**
```json
{
  "success": true,
  "data": {
    "duration": {
      "days": 3,
      "nights": 2,
      "label": "3 Hari 2 Malam"
    },
    "preferences": ["Alam", "Kuliner", "Sejarah"],
    "days": [
      {
        "day": 1,
        "date": "2024-01-15",
        "activities": [
          {
            "time": "07:30",
            "activity": "Berangkat dari Hotel",
            "location": "-",
            "duration": null,
            "type": "departure"
          },
          {
            "time": "08:00",
            "activity": "Kunjungan 1",
            "location": "Istana Maimun (Sejarah, Budaya)",
            "duration": "1.5 jam",
            "type": "attraction",
            "place_id": 1
          },
          ...
        ]
      },
      ...
    ]
  }
}
```

## Fitur Itinerary Generator

1. **Preferensi User**: Otomatis diambil dari rating tinggi user (rating >= 4)
2. **Distribusi Wisata**: Membagi wisata secara merata per hari
3. **Rekomendasi Restoran**: Otomatis menambahkan rekomendasi makan siang berdasarkan lokasi
4. **Rekomendasi Hotel**: Otomatis menambahkan hotel untuk menginap (jika durasi > 1 hari)
5. **Estimasi Waktu**: 
   - Durasi kunjungan: 1-3 jam per tempat
   - Waktu perjalanan: Dihitung berdasarkan jarak (Haversine formula)
6. **Check-in/Check-out**: Otomatis ditambahkan untuk perjalanan multi-hari

## Catatan

- Sistem akan otomatis memilih restoran dan hotel terdekat berdasarkan lokasi wisata
- Waktu perjalanan > 1 jam akan ditampilkan sebagai aktivitas terpisah
- Makan siang otomatis ditambahkan sekitar jam 12:00-14:00




