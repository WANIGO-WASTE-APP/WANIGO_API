# Postman Collection - Complete Setup Guide

## ✅ Yang Sudah Dibuat

### 1. **WANIGO_API_Complete.postman_collection.json** ⭐
Collection lengkap dengan **50+ endpoints** yang mencakup:

#### Authentication (10 endpoints)
- Check Email, Register, Login, Logout
- Forgot Password, Reset Password
- Get/Update Profile, Update Password
- Check Profile Status

#### Bank Sampah (7 endpoints)
- **Public**: Get All (with filtering), Get Detail
- **Authenticated**: Top Frequency, List, Find Nearby, User's Banks, Map Filter

#### Katalog Sampah (5 endpoints)
- **NEW**: Get Katalog by Bank (with sub-kategori info, filtering, pagination)
- Get by Bank (Old), Detail, Search, For Setoran

#### Sub-Kategori Sampah (3 endpoints) ⭐ NEW
- **Get Sub-Kategori by Bank** - Grouped by kering/basah
- Get List (Old), Get Katalog by Sub-Kategori

#### Dashboard (1 endpoint)
- Get Statistics (saldo, tonase, setoran, bank count)

#### Penarikan Saldo (5 endpoints)
- History, Create, Detail, Approve (Petugas), Complete (Nasabah)

#### Nasabah Profile (4 endpoints)
- Get Profile, Update Step 1-3

#### Education (5 endpoints)
- Modul List/Detail, Video/Article Detail, Mark Complete

---

### 2. **WANIGO_API_Complete.postman_environment.json** ⭐
Environment file dengan variables:
- `base_url`: http://localhost:8000
- `token`: Auto-saved after login
- `test_email`, `test_password`, `test_phone`
- `bank_sampah_id`, `katalog_id`, `sub_kategori_id`

---

### 3. **Dokumentasi Lengkap**

#### `/docs/API_ENDPOINTS_COMPLETE.md`
- Daftar lengkap semua endpoints
- Request/response format
- Query parameters
- Authentication requirements
- Error codes

#### `/postman/README.md`
- Quick start guide
- Collection structure
- Testing workflow
- Troubleshooting
- Environment variables

#### `/postman/generate_complete_collection.py`
- Script Python untuk regenerate collection
- Bisa dijalankan kapan saja untuk update

---

## 🚀 Cara Menggunakan

### Step 1: Import ke Postman

1. Buka Postman
2. Klik **Import**
3. Pilih 2 files ini:
   ```
   postman/WANIGO_API_Complete.postman_collection.json
   postman/WANIGO_API_Complete.postman_environment.json
   ```

### Step 2: Set Environment

1. Pilih **"WANIGO API - Complete Environment"** dari dropdown
2. Update `base_url` jika perlu (default: http://localhost:8000)

### Step 3: Test!

1. Buka folder **"1. Authentication"**
2. Run **"Login"** request
3. Token akan otomatis tersimpan
4. Sekarang bisa test endpoint lain!

---

## 🆕 Endpoint Baru (Sub-Kategori Refactoring)

### 1. Get Katalog by Bank
```
GET /api/bank-sampah/{bank_sampah_id}/katalog
```

**Query Parameters:**
- `kategori`: kering|basah|semua
- `sub_kategori_id`: Filter by sub-category
- `per_page`: 20 (default)
- `page`: 1 (default)

**Response:**
```json
{
  "success": true,
  "message": "Katalog sampah berhasil diambil",
  "data": [
    {
      "id": 1,
      "nama": "Botol Plastik PET",
      "harga": 3000,
      "kategori_sampah": "kering",
      "sub_kategori": {
        "id": 2,
        "nama": "Botol Plastik",
        "slug": "botol-plastik",
        "icon": "bottle",
        "warna": "#2196F3"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 45,
    "last_page": 3
  }
}
```

---

### 2. Get Sub-Kategori by Bank
```
GET /api/bank-sampah/{bank_sampah_id}/sub-kategori
```

**Query Parameters:**
- `kategori`: kering|basah|semua (default: semua)

**Response:**
```json
{
  "success": true,
  "message": "Sub kategori sampah berhasil diambil",
  "data": {
    "kering": [
      {
        "id": 1,
        "nama_sub_kategori": "Kertas",
        "slug": "kertas",
        "icon": "paper",
        "warna": "#8BC34A",
        "urutan": 1,
        "kategori_sampah": "kering"
      },
      {
        "id": 2,
        "nama_sub_kategori": "Botol Plastik",
        "slug": "botol-plastik",
        "icon": "bottle",
        "warna": "#2196F3",
        "urutan": 2,
        "kategori_sampah": "kering"
      }
    ],
    "basah": [
      {
        "id": 8,
        "nama_sub_kategori": "Organik",
        "slug": "organik",
        "icon": "organic",
        "warna": "#4CAF50",
        "urutan": 1,
        "kategori_sampah": "basah"
      }
    ]
  }
}
```

---

## 📊 Fitur Utama

### ✅ Auto Token Management
- Login otomatis save token ke environment
- Semua authenticated request pakai `{{token}}`
- Tidak perlu copy-paste token manual!

### ✅ Pre-configured Examples
- Semua request sudah ada contoh values
- Query parameters ada deskripsi
- Path variables sudah diisi

### ✅ Organized Structure
- 9 folder logis
- Naming jelas
- NEW endpoints ditandai

### ✅ Complete Coverage
- Semua authentication flows
- Semua CRUD operations
- Semua filtering options
- Semua endpoint refactored

---

## 🎯 Testing Workflow

### Untuk User Baru:
1. **Register** → Buat akun baru
2. **Login** → Dapat token (auto-saved)
3. **Update Profile Step 1-3** → Lengkapi profil
4. **Get Dashboard Stats** → Lihat statistik
5. **Get Bank Sampah List** → Browse banks
6. **Get Sub-Kategori** → Lihat kategori
7. **Get Katalog** → Browse items

### Untuk User Existing:
1. **Login** → Dapat token (auto-saved)
2. Test endpoint apapun

### Untuk Test Fitur Baru:
1. **Get Sub-Kategori by Bank** → Lihat kategori grouped
2. **Get Katalog by Bank** → Lihat items dengan sub-kategori info
3. **Filter by kategori** → Test kering/basah
4. **Test pagination** → Ubah per_page dan page

---

## 📁 File Structure

```
postman/
├── WANIGO_API_Complete.postman_collection.json ⭐ MAIN
├── WANIGO_API_Complete.postman_environment.json ⭐ MAIN
├── generate_complete_collection.py (script generator)
├── README.md (panduan lengkap)
├── Bank_Sampah_API_v2.postman_collection.json (legacy)
├── WANIGO_API.postman_environment.json (legacy)
└── WANIGO_API (Full Access).postman_collection.json (legacy)

docs/
├── API_ENDPOINTS_COMPLETE.md ⭐ (dokumentasi API lengkap)
├── POSTMAN_COLLECTION_COMPLETE.md (file ini)
├── KATALOG_SAMPAH_VALIDATION_IMPLEMENTATION.md
├── PHASE4_MIGRATION_README.md
└── ... (dokumentasi lainnya)
```

---

## 🔄 Regenerate Collection

Jika perlu update collection:

```bash
python postman/generate_complete_collection.py
```

Akan generate:
- `WANIGO_API_Complete.postman_collection.json`
- `WANIGO_API_Complete.postman_environment.json`

---

## 🐛 Troubleshooting

### Token tidak tersimpan setelah login
✅ **Solusi:**
1. Pastikan environment sudah dipilih
2. Cek Login request punya test script
3. Cek response ada `data.access_token`

### Error 401 Unauthorized
✅ **Solusi:**
1. Run Login request dulu
2. Cek token tersimpan di environment
3. Cek token belum expired

### Error 404 Not Found
✅ **Solusi:**
1. Cek `base_url` sudah benar
2. Cek endpoint path sudah benar
3. Cek resource ID ada

### Error 422 Validation
✅ **Solusi:**
1. Cek format request body
2. Cek required fields ada semua
3. Cek value constraints

---

## 📚 Resources

- **API Docs**: `/docs/API_ENDPOINTS_COMPLETE.md`
- **Postman Guide**: `/postman/README.md`
- **Validation Guide**: `/docs/KATALOG_SAMPAH_VALIDATION_IMPLEMENTATION.md`
- **Migration Guide**: `/docs/PHASE4_MIGRATION_README.md`

---

## ✨ Summary

**Yang Sudah Dibuat:**
✅ Complete Postman collection (50+ endpoints)
✅ Environment file dengan auto-token
✅ Dokumentasi API lengkap
✅ Postman README guide
✅ Python script untuk regenerate
✅ Semua endpoint baru (Sub-Kategori Refactoring)

**Cara Pakai:**
1. Import 2 files ke Postman
2. Pilih environment
3. Login (token auto-saved)
4. Test endpoints!

**Endpoint Baru:**
- `GET /api/bank-sampah/{id}/katalog` - Katalog dengan sub-kategori info
- `GET /api/bank-sampah/{id}/sub-kategori` - Sub-kategori grouped

**Ready to use!** 🚀

---

**Last Updated:** 2026-01-22  
**Version:** 2.0 Complete  
**Total Endpoints:** 50+
