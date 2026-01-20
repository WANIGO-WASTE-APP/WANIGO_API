# Bank Sampah API Documentation

## Overview
Dokumentasi API untuk Bank Sampah endpoints yang telah diupdate dengan response structure yang konsisten.

## Base URL
```
https://api.wanigo.com
```

## Authentication
Semua endpoint yang memerlukan autentikasi menggunakan Laravel Sanctum dengan Bearer token:
```
Authorization: Bearer {your-token}
```

## API Versioning
API mendukung versioning melalui header `X-API-Version`. Default version adalah `2.0`.

### Version Headers
```
X-API-Version: 2.0  (default - new response structure)
X-API-Version: 1.0  (legacy - old response structure)
```

### Version Differences
- **Version 2.0**: Menggunakan standard response structure dengan `{success, message, data, meta}`
- **Version 1.0**: Response langsung tanpa wrapper (backward compatibility)

## Deprecated Endpoints
Beberapa endpoint telah deprecated dan akan dihapus pada **21 Maret 2026**. Deprecated endpoints akan mengembalikan header dan field tambahan:

### Deprecation Headers
```
X-API-Deprecated: true
Sunset: 2026-03-21
X-API-New-Endpoint: /api/bank-sampah
```

### Deprecation Response Fields
```json
{
  "deprecated": true,
  "deprecation_message": "This endpoint is deprecated and will be removed in a future version.",
  "new_endpoint": "/api/bank-sampah",
  "sunset_date": "2026-03-21"
}
```

### Deprecated Endpoints List
- `/api/nasabah/bank-sampah-profil/{id}` → Use `/api/bank-sampah/{id}`
- `/api/nasabah/bank-sampah-profil/{id}/katalog` → Use `/api/bank-sampah/{id}` (includes katalog in response)
- `/api/nasabah/bank-sampah-profil/{id}/jam-operasional` → Use `/api/bank-sampah/{id}` (includes jam operasional in response)

---

## Standard Response Structure

### Success Response
```json
{
  "success": true,
  "message": "Operation successful message",
  "data": {
    // Response data object or array
  },
  "meta": {
    // Pagination metadata (for list endpoints only)
    "current_page": 1,
    "per_page": 20,
    "total": 100,
    "last_page": 5,
    "from": 1,
    "to": 20
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

---

## Public Endpoints (No Auth Required)

### 1. Get All Bank Sampah (List)
**Endpoint:** `GET /api/bank-sampah`

**Query Parameters:**
- `q` (string, optional): Keyword search untuk nama bank sampah
- `lat` (float, optional): Latitude user untuk kalkulasi jarak
- `lng` (float, optional): Longitude user untuk kalkulasi jarak
- `radius_km` (integer, optional, default: 10): Radius pencarian dalam kilometer
- `kategori` (string, optional): Filter kategori sampah ('kering', 'basah', 'semua')
- `provinsi_id` (integer, optional): Filter berdasarkan provinsi
- `kabupaten_id` (integer, optional): Filter berdasarkan kabupaten/kota
- `kecamatan_id` (integer, optional): Filter berdasarkan kecamatan
- `sort` (string, optional, default: 'distance'): Sort by 'distance' atau 'name'
- `per_page` (integer, optional, default: 20): Items per page
- `page` (integer, optional, default: 1): Current page number

**Example Request:**
```
GET /api/bank-sampah?lat=-7.2575&lng=112.7521&radius_km=5&kategori=kering&per_page=10
```

**Response:**
```json
{
  "success": true,
  "message": "Daftar bank sampah berhasil diambil",
  "data": [
    {
      "id": 1,
      "nama_bank_sampah": "Bank Sampah Mojo",
      "alamat": "Jl. Mojo No. 123",
      "latitude": -7.2575,
      "longitude": 112.7521,
      "distance_km": 2.5,
      "status_operasional": true,
      "nomor_telepon": "081234567890",
      "email": "mojo@banksampah.com",
      "foto_usaha_url": "https://api.wanigo.com/storage/bank_sampah/mojo.jpg",
      "insight": "Hanya menerima sampah kering. Minimal setoran 5kg.",
      "kategori_sampah": ["kering"],
      "jam_operasional_hari_ini": {
        "buka": true,
        "jam_buka": "08:00",
        "jam_tutup": "16:00"
      },
      "member_status": "aktif"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 45,
    "last_page": 5,
    "from": 1,
    "to": 10
  }
}
```

### 2. Get Bank Sampah Detail
**Endpoint:** `GET /api/bank-sampah/{id}`

**Response:**
```json
{
  "success": true,
  "message": "Detail bank sampah berhasil diambil",
  "data": {
    "id": 1,
    "nama_bank_sampah": "Bank Sampah Mojo",
    "alamat": "Jl. Mojo No. 123",
    "alamat_lengkap": "Jl. Mojo No. 123, Kelurahan Mojo, Kecamatan Gubeng, Surabaya, Jawa Timur",
    "deskripsi": "Bank sampah terbesar di Surabaya",
    "insight": "Hanya menerima sampah kering. Minimal setoran 5kg.",
    "latitude": -7.2575,
    "longitude": 112.7521,
    "status_operasional": true,
    "nomor_telepon": "081234567890",
    "email": "mojo@banksampah.com",
    "foto_usaha_url": "https://api.wanigo.com/storage/bank_sampah/mojo.jpg",
    "jumlah_nasabah": 150,
    "tonase_sampah": 1250.50,
    "kategori_sampah": ["kering"],
    "lokasi": {
      "provinsi": "Jawa Timur",
      "kabupaten_kota": "Surabaya",
      "kecamatan": "Gubeng",
      "kelurahan_desa": "Mojo"
    },
    "jam_operasional": [
      {
        "hari": "Senin",
        "buka": true,
        "jam_buka": "08:00",
        "jam_tutup": "16:00"
      }
    ],
    "member_status": "aktif",
    "member_data": {
      "kode_nasabah": "NS001000012ABC",
      "tanggal_bergabung": "2024-01-15",
      "saldo": 150000.00
    }
  }
}
```

---

## Authenticated Endpoints (Require Auth)

### 3. Get Dashboard Statistics
**Endpoint:** `GET /api/nasabah/dashboard/stats`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "message": "Statistik dashboard berhasil diambil",
  "data": {
    "total_saldo": 250000.00,
    "total_tonase_sampah": 125.50,
    "total_setoran": 45,
    "total_bank_sampah": 3
  }
}
```

### 4. Get Top Frequency Bank Sampah
**Endpoint:** `GET /api/nasabah/bank-sampah/top-frequency`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "message": "Top frekuensi bank sampah berhasil diambil",
  "data": [
    {
      "bank_sampah": {
        "id": 1,
        "nama_bank_sampah": "Bank Sampah Mojo",
        "alamat_bank_sampah": "Jl. Mojo No. 123",
        "latitude": -7.2575,
        "longitude": 112.7521
      },
      "visit_count": 25
    }
  ]
}
```

### 5. Create Withdrawal Request
**Endpoint:** `POST /api/nasabah/penarikan-saldo`

**Headers:** `Authorization: Bearer {token}`

**Request Body (multipart/form-data):**
```
bank_sampah_id: 1
jumlah_penarikan: 50000
foto_buku_tabungan: [file]
```

**Response:**
```json
{
  "success": true,
  "message": "Pengajuan penarikan berhasil dibuat",
  "data": {
    "id": 1,
    "jumlah_penarikan": 50000.00,
    "kode_verifikasi": "ABC123",
    "status": "pending",
    "created_at": "2026-01-20 10:30:00"
  }
}
```

### 6. Complete Withdrawal (Nasabah Confirmation)
**Endpoint:** `POST /api/nasabah/penarikan-saldo/{id}/complete`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "kode_verifikasi": "ABC123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Penarikan berhasil diselesaikan",
  "data": {
    "id": 1,
    "status": "completed",
    "completed_at": "2026-01-20 11:00:00"
  }
}
```

### 7. Get Withdrawal History
**Endpoint:** `GET /api/nasabah/penarikan-saldo`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "message": "Riwayat penarikan berhasil diambil",
  "data": [
    {
      "id": 1,
      "jumlah_penarikan": 50000.00,
      "status": "completed",
      "kode_verifikasi": "ABC123",
      "created_at": "2026-01-20 10:30:00",
      "completed_at": "2026-01-20 11:00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 10,
    "last_page": 1
  }
}
```

---

## Field Naming Conventions

Semua API response menggunakan naming convention yang konsisten:

| Field Purpose | Standard Name | Type | Example |
|--------------|---------------|------|---------|
| Phone Number | `nomor_telepon` | string | "081234567890" |
| Email Address | `email` | string | "user@example.com" |
| Address | `alamat` | string | "Jl. Example No. 123" |
| Full Address | `alamat_lengkap` | string | "Jl. Example No. 123, Kelurahan, Kecamatan, Kota, Provinsi" |
| Operational Status | `status_operasional` | boolean | true |
| Latitude | `latitude` | float | -7.2575 |
| Longitude | `longitude` | float | 112.7521 |
| Distance | `distance_km` | float | 2.5 |
| Photo URL | `foto_usaha_url` | string | "https://..." |
| Member Status | `member_status` | string | "aktif" / "tidak_aktif" / "bukan_nasabah" |
| Waste Category | `kategori_sampah` | array | ["kering", "basah"] |

---

## HTTP Status Codes

- `200 OK`: Request berhasil
- `201 Created`: Resource berhasil dibuat
- `400 Bad Request`: Request format tidak valid
- `401 Unauthorized`: Authentication required
- `403 Forbidden`: Insufficient permissions
- `404 Not Found`: Resource tidak ditemukan
- `422 Unprocessable Entity`: Validation errors
- `500 Internal Server Error`: Server error

---

## Notes for Flutter Team

1. **Consistent Response Structure**: Semua endpoint sekarang menggunakan struktur `{success, message, data, meta}` yang konsisten
2. **Field Naming**: Gunakan field names yang sudah distandarisasi (lihat tabel di atas)
3. **Distance Calculation**: Field `distance_km` hanya muncul jika query menggunakan parameter `lat` dan `lng`
4. **Member Status**: Field `member_status` menunjukkan status keanggotaan user di bank sampah tersebut
5. **Pagination**: Gunakan field `meta` untuk pagination info
6. **Error Handling**: Selalu cek field `success` untuk menentukan apakah request berhasil atau gagal

---

## Migration dari Endpoint Lama

Jika masih menggunakan endpoint lama, berikut mapping ke endpoint baru:

| Old Endpoint | New Endpoint | Notes |
|-------------|--------------|-------|
| `POST /api/nasabah/bank-sampah/find-nearby` | `GET /api/bank-sampah?lat=&lng=&radius_km=` | Gunakan query parameters |
| `GET /api/nasabah/bank-sampah-profil/{id}` | `GET /api/bank-sampah/{id}` | Response structure sudah distandarisasi |

---

## Contact

Untuk pertanyaan atau issue terkait API, hubungi tim backend.
