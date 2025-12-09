# Setup Mapbox untuk Routing

## Langkah Setup

### 1. Daftar Mapbox Account

1. Kunjungi [mapbox.com](https://mapbox.com)
2. Buat akun gratis
3. Dapatkan **Access Token** dari dashboard

### 2. Setup Environment Variable

Tambahkan ke file `.env`:

```bash
# Mapbox API (untuk routing)
MAPBOX_ACCESS_TOKEN=your_mapbox_access_token_here
```

### 3. Fitur Mapbox

-   ✅ **50,000 requests gratis per bulan**
-   ✅ Routing akurat untuk Indonesia
-   ✅ Mendukung traffic real-time
-   ✅ Tidak perlu server sendiri

### 4. Testing

Setelah setup token, routing akan menggunakan Mapbox yang lebih akurat daripada OSRM untuk wilayah Indonesia.

### 5. Status Implementasi

✅ **Mapbox routing sudah terintegrasi** - Menggunakan Mapbox Directions API melalui Leaflet Routing Machine
✅ **Konfigurasi selesai** - Token Mapbox dapat dikonfigurasi melalui environment variable
✅ **Tidak perlu server OSRM** - Routing langsung dari client menggunakan Mapbox
✅ **Stabil & production-ready** - Tidak ada lagi warning "NOT SUITABLE FOR PRODUCTION"

## Troubleshooting

-   Jika routing tidak muncul, cek apakah token Mapbox valid
-   Pastikan koneksi internet stabil
-   Mapbox memiliki limit 50rb requests/bulan untuk free tier
