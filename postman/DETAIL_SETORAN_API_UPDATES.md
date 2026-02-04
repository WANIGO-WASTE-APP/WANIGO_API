# Detail Setoran API Updates - Postman Collection

## Overview

Dokumen ini menjelaskan perubahan yang dilakukan pada Postman collection untuk endpoint Detail Setoran API, sesuai dengan enhancement yang telah diimplementasikan.

## Perubahan yang Dilakukan

### 1. Endpoint: GET /api/nasabah/detail-setoran/{id}/detail

**Tujuan**: Mendapatkan detail **SATU** item setoran berdasarkan ID detail_setoran

**Perubahan Response**:
- ✅ Menambahkan field `nama_bank_sampah` di root level data
- ✅ Menambahkan field `kode_setoran` di root level data

**Response Baru**:
```json
{
  "success": true,
  "data": {
    "detail_setoran": {
      "id": 1,
      "setoran_sampah_id": 1,
      "katalog_sampah_id": 1,
      "berat": 3,
      "harga_per_kg": 2500,
      "nilai": 7500,
      "harga_format": "Rp 2.500",
      "nilai_format": "Rp 7.500",
      "berat_format": "3,00 kg"
    },
    "item_sampah": {
      "id": 1,
      "nama": "Kertas HVS",
      "kategori_utama": "Sampah Kering",
      "sub_kategori": "Kertas",
      "deskripsi": "Kertas HVS putih tanpa noda",
      "cara_pemilahan": "Pisahkan dari kertas berwarna",
      "cara_pengemasan": "Ikat dengan tali",
      "gambar_url": "https://example.com/storage/katalog_sampah/kertas_hvs.jpg"
    },
    "nama_bank_sampah": "Bank Sampah Melati",  // ← FIELD BARU
    "kode_setoran": "KWNA000001"                // ← FIELD BARU
  }
}
```

**Deskripsi yang Diperbarui**:
> Mendapatkan detail SATU item setoran berdasarkan ID detail_setoran. Endpoint ini mengembalikan informasi lengkap untuk satu item termasuk nama bank sampah dan kode setoran.

---

### 2. Endpoint: POST /api/nasabah/detail-setoran/by-setoran

**Tujuan**: Mendapatkan **SEMUA** item setoran untuk setoran_sampah_id tertentu

**Perubahan Response**:
- ✅ Menambahkan field `nama_bank_sampah` di dalam object `setoran`

**Response Baru**:
```json
{
  "success": true,
  "data": {
    "detail_setoran": [
      // ... array of all detail items
    ],
    "detail_by_sub_kategori": [
      // ... items grouped by sub-category
    ],
    "setoran": {
      "id": 1,
      "kode_setoran": "KWNA000001",
      "nama_bank_sampah": "Bank Sampah Melati",  // ← FIELD BARU
      "status": "pengajuan",
      "tanggal_setoran": "2025-05-31",
      "waktu_setoran": "10:00",
      "total_berat": 0,
      "total_berat_format": "0,00 kg",
      "total_nilai": 0,
      "total_nilai_format": "Rp 0",
      "editable": true
    }
  }
}
```

**Deskripsi yang Diperbarui**:
> Mendapatkan SEMUA item setoran berdasarkan ID setoran. Endpoint ini mengembalikan semua detail_setoran untuk setoran_sampah_id tertentu.

---

## Perbedaan Kedua Endpoint

### GET /api/nasabah/detail-setoran/{id}/detail
- **Mengambil**: SATU item detail_setoran
- **Parameter**: `id` (detail_setoran ID) di URL path
- **Use Case**: Melihat detail lengkap satu item sampah tertentu
- **Field Tambahan**: `nama_bank_sampah` dan `kode_setoran` di root level

### POST /api/nasabah/detail-setoran/by-setoran
- **Mengambil**: SEMUA item detail_setoran dalam satu setoran
- **Parameter**: `setoran_sampah_id` di request body
- **Use Case**: Melihat semua item dalam satu transaksi setoran
- **Field Tambahan**: `nama_bank_sampah` di dalam object `setoran`

---

## File yang Diperbarui

1. ✅ `postman/WANIGO_API_Complete.postman_collection.json`
2. ✅ `postman/WANIGO_API (Full Access).postman_collection.json`

---

## Cara Menggunakan

### Import Collection ke Postman

1. Buka Postman
2. Klik **Import** di pojok kiri atas
3. Pilih file collection yang telah diperbarui:
   - `WANIGO_API_Complete.postman_collection.json` (recommended)
   - atau `WANIGO_API (Full Access).postman_collection.json`
4. Import environment file:
   - `WANIGO_API_Development.postman_environment.json` untuk development
   - `WANIGO_API_Production.postman_environment.json` untuk production

### Testing Endpoints

1. **Set Environment**: Pilih environment yang sesuai (Development/Production)
2. **Login**: Jalankan endpoint login untuk mendapatkan token
3. **Test Single Item**:
   - Buka folder "14. Detail Setoran"
   - Pilih "Get Item Detail"
   - Ganti ID di URL dengan ID detail_setoran yang valid
   - Klik Send
   - Verifikasi response memiliki `nama_bank_sampah` dan `kode_setoran`

4. **Test All Items**:
   - Buka folder "14. Detail Setoran"
   - Pilih "Get Detail By Setoran"
   - Ganti `setoran_sampah_id` di body dengan ID yang valid
   - Klik Send
   - Verifikasi response memiliki `nama_bank_sampah` di object `setoran`

---

## Backward Compatibility

✅ **Semua field lama tetap ada** - Tidak ada breaking changes
✅ **Field baru bersifat additive** - Hanya menambah, tidak menghapus atau mengubah
✅ **Aplikasi lama tetap berfungsi** - Field baru bersifat opsional

---

## Changelog

### Version 1.1 (February 2026)
- ✅ Added `nama_bank_sampah` field to GET /api/nasabah/detail-setoran/{id}/detail response
- ✅ Added `kode_setoran` field to GET /api/nasabah/detail-setoran/{id}/detail response
- ✅ Added `nama_bank_sampah` field to POST /api/nasabah/detail-setoran/by-setoran response (in setoran object)
- ✅ Updated endpoint descriptions to clarify single vs bulk retrieval
- ✅ Updated example responses in Postman collection

---

## Support

Jika ada pertanyaan atau masalah terkait perubahan ini, silakan hubungi tim development atau buka issue di repository.
