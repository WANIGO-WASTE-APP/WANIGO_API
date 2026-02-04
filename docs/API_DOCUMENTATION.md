# WANIGO Bank Sampah API Documentation

## Overview

This document provides comprehensive API documentation for the WANIGO Bank Sampah (Waste Bank) management system. The API supports Firebase Authentication (Google Sign-In) and provides endpoints for managing bank sampah (waste banks), setoran sampah (waste deposits), and related features.

**Base URL:** `http://localhost:8000/api` (development)  
**Production URL:** `https://api.wanigo.com/api`

**API Version:** v1  
**Last Updated:** January 2026

## Table of Contents

1. [Authentication](#authentication)
   - [Firebase Google Sign-In](#firebase-google-sign-in)
2. [Bank Sampah Endpoints](#bank-sampah-endpoints)
   - [Get All Bank Sampah](#get-all-bank-sampah)
   - [Get Bank Sampah Detail](#get-bank-sampah-detail)
   - [Get Registered Bank Sampah](#get-registered-bank-sampah)
   - [Get Top Frequency Bank Sampah](#get-top-frequency-bank-sampah)
3. [Setoran Sampah Endpoints](#setoran-sampah-endpoints)
   - [Get Ongoing Setoran](#get-ongoing-setoran)
   - [Get Setoran History](#get-setoran-history)
   - [Create Pengajuan Setoran](#create-pengajuan-setoran)
   - [Get Setoran Detail](#get-setoran-detail)
   - [Cancel Setoran](#cancel-setoran)
   - [Get Dashboard Statistics](#get-dashboard-statistics)
4. [Detail Setoran Endpoints](#detail-setoran-endpoints)
   - [Get Single Item Detail](#get-single-item-detail)
   - [Get All Items by Setoran](#get-all-items-by-setoran)
   - [Add Item to Setoran](#add-item-to-setoran)
   - [Update Item Detail](#update-item-detail)
   - [Delete Item from Setoran](#delete-item-from-setoran)
   - [Bulk Update Items](#bulk-update-items)
5. [Response Format Standards](#response-format-standards)
5. [Error Handling](#error-handling)
6. [Deprecation Notices](#deprecation-notices)
7. [Status Values Reference](#status-values-reference)

---

## Authentication

### Firebase Google Sign-In

Authenticate a user with Firebase Google Sign-In and receive a Laravel Sanctum token for API access.

**Endpoint:** `POST /api/auth/firebase/google`

**Authentication:** None (public endpoint)

**Request Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "id_token": "eyJhbGciOiJSUzI1NiIsImtpZCI6IjE4MmU0M..."
}
```

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id_token | string | Yes | Firebase ID token obtained from Firebase Authentication |

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Login success",
  "data": {
    "token": "1|abcdef123456...",
    "token_type": "Bearer",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "avatar_url": "https://lh3.googleusercontent.com/..."
    }
  }
}
```

**Error Responses:**

**401 Unauthorized** - Invalid or expired token:
```json
{
  "success": false,
  "message": "Invalid or expired Firebase token"
}
```

**422 Unprocessable Entity** - Validation error:
```json
{
  "success": false,
  "message": "Validasi gagal",
  "errors": {
    "id_token": ["Firebase ID token is required"]
  }
}
```

**500 Internal Server Error** - Security configuration error:
```json
{
  "success": false,
  "message": "Security configuration error"
}
```

**Using the Sanctum Token:**

After successful authentication, include the token in all subsequent API requests:

```
Authorization: Bearer 1|abcdef123456...
```

**Example cURL:**
```bash
curl -X POST https://api.wanigo.com/api/auth/firebase/google \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"id_token": "eyJhbGciOiJSUzI1NiIsImtpZCI6IjE4MmU0M..."}'
```

---

## Bank Sampah Endpoints

### Get All Bank Sampah

Retrieve a paginated list of all active bank sampah with optional filtering and sorting.

**Endpoint:** `GET /api/bank-sampah`

**Authentication:** Optional (member status included if authenticated)

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| q | string | No | Search keyword for bank sampah name |
| lat | float | No | Latitude for distance calculation (-90 to 90) |
| lng | float | No | Longitude for distance calculation (-180 to 180) |
| radius_km | integer | No | Search radius in kilometers (1-100, default: 10) |
| kategori | string | No | Waste category filter: `kering`, `basah`, or `semua` |
| provinsi_id | integer | No | Filter by province ID |
| kabupaten_id | integer | No | Filter by regency/city ID |
| kecamatan_id | integer | No | Filter by district ID |
| sort | string | No | Sort order: `distance` or `name` (default: distance if lat/lng provided, otherwise name) |
| per_page | integer | No | Items per page (1-100, default: 20) |
| page | integer | No | Page number (default: 1) |

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Daftar bank sampah berhasil diambil",
  "data": [
    {
      "id": 1,
      "nama_bank_sampah": "Bank Sampah Melati",
      "alamat": "Jl. Merdeka No. 123, Jakarta",
      "latitude": -6.2088,
      "longitude": 106.8456,
      "distance_km": 2.5,
      "status_operasional": true,
      "contact_info": {
        "phone": "081234567890",
        "email": "melati@banksampah.com"
      },
      "foto_usaha_url": "https://api.wanigo.com/storage/bank_sampah/melati.jpg",
      "insight": "Bank sampah terbesar di Jakarta",
      "kategori_sampah": ["kering", "basah"],
      "jam_operasional_hari_ini": {
        "buka": true,
        "jam_buka": "08:00",
        "jam_tutup": "16:00"
      },
      "member_status": "aktif",
      "@deprecated": {
        "nomor_telepon": "081234567890",
        "nomor_telepon_publik": "081234567890"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 45,
    "last_page": 3,
    "from": 1,
    "to": 20
  }
}
```

**Response Headers:**
```
Warning: 299 - "Deprecated fields (@deprecated) will be removed in v2.0"
```

**Field Descriptions:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | Bank sampah unique identifier |
| nama_bank_sampah | string | Bank sampah name |
| alamat | string | Full address |
| latitude | float | Latitude coordinate |
| longitude | float | Longitude coordinate |
| distance_km | float | Distance from user location in kilometers (only if lat/lng provided) |
| status_operasional | boolean | Whether bank is currently operational |
| contact_info | object | **NEW:** Normalized contact information |
| contact_info.phone | string\|null | Phone number (priority: nomor_telepon_publik > nomor_telepon) |
| contact_info.email | string\|null | Email address |
| foto_usaha_url | string | Business photo URL (uses default image if null) |
| insight | string | Bank sampah description/insight |
| kategori_sampah | array | Accepted waste categories: `["kering"]`, `["basah"]`, or `["kering", "basah"]` |
| jam_operasional_hari_ini | object | Today's operating hours |
| jam_operasional_hari_ini.buka | boolean | Whether currently open |
| jam_operasional_hari_ini.jam_buka | string\|null | Opening time (HH:mm format) |
| jam_operasional_hari_ini.jam_tutup | string\|null | Closing time (HH:mm format) |
| member_status | string | User's membership status: `aktif`, `pending`, `ditolak`, or `bukan_nasabah` |
| @deprecated | object | **DEPRECATED:** Old contact fields (will be removed in v2.0) |
| @deprecated.nomor_telepon | string\|null | **DEPRECATED:** Use `contact_info.phone` instead |
| @deprecated.nomor_telepon_publik | string\|null | **DEPRECATED:** Use `contact_info.phone` instead |

**Important Notes:**

1. **tonase_sampah field is NOT included** in list endpoints for performance optimization
2. **contact_info** is the new standard for phone and email - use this instead of deprecated fields
3. **@deprecated fields** will be removed in the next major release (v2.0)
4. **distance_km** is only calculated when `lat` and `lng` parameters are provided
5. **member_status** is only included for authenticated users

**Example cURL:**
```bash
# Search nearby bank sampah
curl -X GET "https://api.wanigo.com/api/bank-sampah?lat=-6.2088&lng=106.8456&radius_km=5&kategori=kering" \
  -H "Accept: application/json"

# Search with authentication
curl -X GET "https://api.wanigo.com/api/bank-sampah?q=melati" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef123456..."
```

---

### Get Bank Sampah Detail

Retrieve detailed information about a specific bank sampah, including tonase_sampah.

**Endpoint:** `GET /api/bank-sampah/{id}`

**Authentication:** Optional (member data included if authenticated)

**Path Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Bank sampah ID |

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Detail bank sampah berhasil diambil",
  "data": {
    "id": 1,
    "nama_bank_sampah": "Bank Sampah Melati",
    "alamat": "Jl. Merdeka No. 123, Jakarta",
    "latitude": -6.2088,
    "longitude": 106.8456,
    "distance_km": 2.5,
    "status_operasional": true,
    "contact_info": {
      "phone": "081234567890",
      "email": "melati@banksampah.com"
    },
    "foto_usaha_url": "https://api.wanigo.com/storage/bank_sampah/melati.jpg",
    "insight": "Bank sampah terbesar di Jakarta",
    "deskripsi": "Bank Sampah Melati melayani masyarakat Jakarta sejak 2015...",
    "kategori_sampah": ["kering", "basah"],
    "jam_operasional_hari_ini": {
      "buka": true,
      "jam_buka": "08:00",
      "jam_tutup": "16:00"
    },
    "member_status": "aktif",
    "member_data": {
      "kode_nasabah": "NSB001",
      "tanggal_bergabung": "2024-01-15",
      "saldo": 150000.00
    },
    "tonase_sampah": 1250.50,
    "@deprecated": {
      "nomor_telepon": "081234567890",
      "nomor_telepon_publik": "081234567890"
    }
  }
}
```

**Error Response (404 Not Found):**
```json
{
  "success": false,
  "message": "Bank sampah tidak ditemukan"
}
```

**Additional Fields (compared to list endpoint):**

| Field | Type | Description |
|-------|------|-------------|
| deskripsi | string | Detailed description of the bank sampah |
| member_data | object\|null | Member information (only for authenticated members) |
| member_data.kode_nasabah | string | Member code/ID |
| member_data.tanggal_bergabung | string | Join date (YYYY-MM-DD format) |
| member_data.saldo | float | Current balance in Rupiah |
| tonase_sampah | float | **INCLUDED IN DETAIL:** Total tonnage of waste collected (in kg) |

**Important Notes:**

1. **tonase_sampah is ONLY included in detail endpoint**, not in list endpoints
2. **member_data** is only included for authenticated users who are members of this bank sampah
3. All other fields follow the same format as the list endpoint

**Example cURL:**
```bash
curl -X GET "https://api.wanigo.com/api/bank-sampah/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef123456..."
```

---

### Get Registered Bank Sampah

Retrieve list of bank sampah where the authenticated user is an active member.

**Endpoint:** `GET /api/nasabah/bank-sampah/registered`

**Authentication:** Required (Bearer token)

**Query Parameters:** None

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Daftar bank sampah terdaftar berhasil diambil",
  "data": [
    {
      "id": 1,
      "nama_bank_sampah": "Bank Sampah Melati",
      "alamat": "Jl. Merdeka No. 123, Jakarta",
      "latitude": -6.2088,
      "longitude": 106.8456,
      "status_operasional": true,
      "contact_info": {
        "phone": "081234567890",
        "email": "melati@banksampah.com"
      },
      "foto_usaha_url": "https://api.wanigo.com/storage/bank_sampah/melati.jpg",
      "insight": "Bank sampah terbesar di Jakarta",
      "kategori_sampah": ["kering", "basah"],
      "jam_operasional_hari_ini": {
        "buka": true,
        "jam_buka": "08:00",
        "jam_tutup": "16:00"
      },
      "member_status": "aktif",
      "@deprecated": {
        "nomor_telepon": "081234567890",
        "nomor_telepon_publik": "081234567890"
      }
    }
  ]
}
```

**Important Notes:**

1. Returns only bank sampah where user has **active membership** (`status_keanggotaan = 'aktif'`)
2. **tonase_sampah is NOT included** (uses BankSampahListResource)
3. All returned items will have `member_status: "aktif"`
4. Requires authentication - returns 401 if not authenticated

**Example cURL:**
```bash
curl -X GET "https://api.wanigo.com/api/nasabah/bank-sampah/registered" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef123456..."
```

---

### Get Top Frequency Bank Sampah

Retrieve top 5 bank sampah based on user's transaction history (most frequently visited).

**Endpoint:** `GET /api/nasabah/bank-sampah/top-frequency`

**Authentication:** Required (Bearer token)

**Query Parameters:** None

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Top frekuensi bank sampah berhasil diambil",
  "data": [
    {
      "bank_sampah": {
        "id": 1,
        "nama_bank_sampah": "Bank Sampah Melati",
        "alamat": "Jl. Merdeka No. 123, Jakarta",
        "contact_info": {
          "phone": "081234567890",
          "email": "melati@banksampah.com"
        },
        "foto_usaha_url": "https://api.wanigo.com/storage/bank_sampah/melati.jpg",
        "kategori_sampah": ["kering", "basah"],
        "jam_operasional_hari_ini": {
          "buka": true,
          "jam_buka": "08:00",
          "jam_tutup": "16:00"
        },
        "@deprecated": {
          "nomor_telepon": "081234567890",
          "nomor_telepon_publik": "081234567890"
        }
      },
      "visit_count": 15
    }
  ]
}
```

**Important Notes:**

1. Returns maximum **5 bank sampah** ordered by visit count (descending)
2. Visit count is based on number of setoran sampah transactions
3. Requires authentication - returns 401 if not authenticated

**Example cURL:**
```bash
curl -X GET "https://api.wanigo.com/api/nasabah/bank-sampah/top-frequency" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef123456..."
```

---

## Setoran Sampah Endpoints

### Get Ongoing Setoran

Retrieve paginated list of ongoing waste deposits (status: **pengajuan** or **diproses**).

**Endpoint:** `GET /api/nasabah/setoran-sampah/ongoing`

**Authentication:** Required (Bearer token)

**Query Parameters:** None

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 123,
        "kode_setoran_sampah": "MELA001234",
        "user_id": 1,
        "bank_sampah_id": 1,
        "tanggal_setoran": "2026-01-25",
        "waktu_setoran": "10:00",
        "status_setoran": "pengajuan",
        "total_berat": 0.00,
        "total_berat_format": "0.00 kg",
        "total_saldo": 0.00,
        "total_nilai_format": "Rp 0",
        "total_poin": 0,
        "catatan_status_setoran": null,
        "jumlah_item": 3,
        "is_cancelable": true,
        "bank_sampah": {
          "id": 1,
          "nama_bank_sampah": "Bank Sampah Melati",
          "alamat": "Jl. Merdeka No. 123, Jakarta"
        },
        "created_at": "2026-01-24T15:30:00.000000Z",
        "updated_at": "2026-01-24T15:30:00.000000Z"
      }
    ],
    "first_page_url": "https://api.wanigo.com/api/nasabah/setoran-sampah/ongoing?page=1",
    "from": 1,
    "last_page": 2,
    "last_page_url": "https://api.wanigo.com/api/nasabah/setoran-sampah/ongoing?page=2",
    "next_page_url": "https://api.wanigo.com/api/nasabah/setoran-sampah/ongoing?page=2",
    "path": "https://api.wanigo.com/api/nasabah/setoran-sampah/ongoing",
    "per_page": 10,
    "prev_page_url": null,
    "to": 10,
    "total": 15
  }
}
```

**Status Values:**

| Status | Description |
|--------|-------------|
| pengajuan | **NEW:** Submission pending review by bank sampah |
| diproses | **NEW:** Being processed by bank sampah staff |

**Important Notes:**

1. **Only returns setoran with status `pengajuan` or `diproses`** (excludes `selesai` and `dibatalkan`)
2. **New status values** replaced old values:
   - Old: `pending`, `requested` → New: `pengajuan`
   - Old: `processing`, `in_progress` → New: `diproses`
3. `is_cancelable` indicates if setoran can be cancelled (only within 24 hours of creation and status must be `pengajuan`)
4. Ordered by `tanggal_setoran` descending (most recent first)
5. Paginated with 10 items per page

**Example cURL:**
```bash
curl -X GET "https://api.wanigo.com/api/nasabah/setoran-sampah/ongoing" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef123456..."
```

---

### Get Setoran History

Retrieve paginated list of completed or cancelled waste deposits.

**Endpoint:** `GET /api/nasabah/setoran-sampah/history`

**Authentication:** Required (Bearer token)

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| status_setoran | string | No | Filter by status: `selesai` or `dibatalkan` |
| bank_sampah_id | integer | No | Filter by bank sampah ID |

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 120,
        "kode_setoran_sampah": "MELA001230",
        "user_id": 1,
        "bank_sampah_id": 1,
        "tanggal_setoran": "2026-01-20",
        "waktu_setoran": "14:00",
        "status_setoran": "selesai",
        "total_berat": 5.50,
        "total_berat_format": "5.50 kg",
        "total_saldo": 27500.00,
        "total_nilai_format": "Rp 27.500",
        "total_poin": 27,
        "catatan_status_setoran": "Setoran berhasil diproses",
        "jumlah_item": 5,
        "bank_sampah": {
          "id": 1,
          "nama_bank_sampah": "Bank Sampah Melati",
          "alamat": "Jl. Merdeka No. 123, Jakarta"
        },
        "created_at": "2026-01-20T10:00:00.000000Z",
        "updated_at": "2026-01-20T15:30:00.000000Z"
      }
    ],
    "per_page": 10,
    "total": 25
  }
}
```

**Status Values:**

| Status | Description |
|--------|-------------|
| selesai | **NEW:** Completed and balance credited to user |
| dibatalkan | **NEW:** Cancelled by user or bank sampah |

**Important Notes:**

1. **Only returns setoran with status `selesai` or `dibatalkan`** (excludes ongoing statuses)
2. If no `status_setoran` parameter provided, returns both `selesai` and `dibatalkan`
3. **New status values** replaced old values:
   - Old: `done`, `completed` → New: `selesai`
4. Ordered by `tanggal_setoran` descending (most recent first)
5. Paginated with 10 items per page

**Example cURL:**
```bash
# Get all history
curl -X GET "https://api.wanigo.com/api/nasabah/setoran-sampah/history" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef123456..."

# Get only completed setoran
curl -X GET "https://api.wanigo.com/api/nasabah/setoran-sampah/history?status_setoran=selesai" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef123456..."
```

---

### Create Pengajuan Setoran

Create a new waste deposit submission (pengajuan) without weight information.

**Endpoint:** `POST /api/nasabah/setoran-sampah/pengajuan`

**Authentication:** Required (Bearer token)

**Request Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "bank_sampah_id": 1,
  "tanggal_setoran": "2026-01-27",
  "waktu_setoran": "10:00",
  "item_ids": [5, 12, 18],
  "catatan": "Sampah plastik dan kertas"
}
```

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| bank_sampah_id | integer | Yes | Bank sampah ID where deposit will be made |
| tanggal_setoran | string | Yes | Deposit date (YYYY-MM-DD format) |
| waktu_setoran | string | Yes | Deposit time (HH:mm format) |
| item_ids | array | Yes | Array of katalog_sampah IDs to be deposited |
| catatan | string | No | Optional notes (max 255 characters) |

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Pengajuan setoran sampah berhasil dibuat dan menunggu diproses oleh bank sampah",
  "data": {
    "id": 125,
    "kode_setoran": "MELA001235",
    "status_setoran": "pengajuan",
    "tanggal_setoran": "2026-01-27",
    "waktu_setoran": "10:00",
    "jumlah_item": 3
  }
}
```

**Error Responses:**

**422 Unprocessable Entity** - Validation error:
```json
{
  "success": false,
  "message": "Anda belum terdaftar sebagai nasabah bank sampah ini atau status keanggotaan tidak aktif"
}
```

**422 Unprocessable Entity** - Invalid item:
```json
{
  "success": false,
  "message": "Gagal membuat pengajuan setoran sampah: Katalog sampah tidak valid atau tidak terdaftar di bank sampah ini"
}
```

**Important Notes:**

1. User must be an **active member** of the bank sampah
2. All `item_ids` must belong to the specified bank sampah
3. Initial status is always **`pengajuan`**
4. `total_berat` and `total_saldo` are set to 0 (will be filled by bank sampah staff)
5. Creates detail_setoran records for each item with berat=0 and saldo=0
6. Creates initial status log entry

**Example cURL:**
```bash
curl -X POST "https://api.wanigo.com/api/nasabah/setoran-sampah/pengajuan" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef123456..." \
  -d '{
    "bank_sampah_id": 1,
    "tanggal_setoran": "2026-01-27",
    "waktu_setoran": "10:00",
    "item_ids": [5, 12, 18],
    "catatan": "Sampah plastik dan kertas"
  }'
```

---

### Get Setoran Detail

Retrieve detailed information about a specific waste deposit, including items and status timeline.

**Endpoint:** `GET /api/nasabah/setoran-sampah/{id}`

**Authentication:** Required (Bearer token)

**Path Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Setoran sampah ID |

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "setoran": {
      "id": 123,
      "kode_setoran_sampah": "MELA001234",
      "user_id": 1,
      "bank_sampah_id": 1,
      "tanggal_setoran": "2026-01-25",
      "waktu_setoran": "10:00",
      "status_setoran": "diproses",
      "total_berat": 3.50,
      "total_berat_format": "3.50 kg",
      "total_saldo": 17500.00,
      "total_nilai_format": "Rp 17.500",
      "total_poin": 17,
      "catatan_status_setoran": "Sedang ditimbang",
      "bank_sampah": {
        "id": 1,
        "nama_bank_sampah": "Bank Sampah Melati",
        "alamat": "Jl. Merdeka No. 123, Jakarta"
      },
      "created_at": "2026-01-24T15:30:00.000000Z",
      "updated_at": "2026-01-25T09:15:00.000000Z"
    },
    "item_by_sub_kategori": [
      {
        "id": 1,
        "nama": "Plastik",
        "items": [
          {
            "id": 45,
            "setoran_sampah_id": 123,
            "item_sampah_id": 5,
            "berat": 2.00,
            "berat_format": "2.00 kg",
            "saldo": 10000.00,
            "saldo_format": "Rp 10.000",
            "foto": "detail_setoran/photo123.jpg",
            "foto_url": "https://api.wanigo.com/storage/detail_setoran/photo123.jpg",
            "katalog_sampah": {
              "id": 5,
              "nama_sampah": "Botol Plastik PET",
              "harga_per_kg": 5000.00,
              "kategori_sampah": 0
            }
          }
        ]
      }
    ],
    "timeline": [
      {
        "status_setoran": "pengajuan",
        "tanggal": "24 Jan 2026",
        "waktu": "15:30",
        "keterangan": "Pengajuan setoran dibuat"
      },
      {
        "status_setoran": "diproses",
        "tanggal": "25 Jan 2026",
        "waktu": "09:15",
        "keterangan": "Sedang ditimbang"
      }
    ],
    "is_editable": false,
    "is_cancelable": false
  }
}
```

**Error Response (404 Not Found):**
```json
{
  "success": false,
  "message": "Setoran sampah tidak ditemukan"
}
```

**Field Descriptions:**

| Field | Type | Description |
|-------|------|-------------|
| is_editable | boolean | Whether setoran can be edited (true only if status is `pengajuan`) |
| is_cancelable | boolean | Whether setoran can be cancelled (true only if status is `pengajuan` and within 24 hours) |
| item_by_sub_kategori | array | Items grouped by sub-category for better organization |
| timeline | array | Status change history ordered chronologically |

**Important Notes:**

1. Only returns setoran belonging to the authenticated user
2. Items are grouped by sub-category for easier viewing
3. Timeline shows complete status change history
4. `is_cancelable` is true only if:
   - Status is `pengajuan`
   - Created within last 24 hours

**Example cURL:**
```bash
curl -X GET "https://api.wanigo.com/api/nasabah/setoran-sampah/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef123456..."
```

---

### Cancel Setoran

Cancel a waste deposit submission (only allowed for status `pengajuan` within 24 hours).

**Endpoint:** `POST /api/nasabah/setoran-sampah/{id}/cancel`

**Authentication:** Required (Bearer token)

**Path Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Setoran sampah ID to cancel |

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Setoran sampah berhasil dibatalkan",
  "data": {
    "id": 123,
    "kode_setoran_sampah": "MELA001234",
    "status_setoran": "dibatalkan",
    "tanggal_setoran": "2026-01-25",
    "updated_at": "2026-01-25T10:30:00.000000Z"
  }
}
```

**Error Responses:**

**404 Not Found:**
```json
{
  "success": false,
  "message": "Setoran sampah tidak ditemukan"
}
```

**422 Unprocessable Entity** - Wrong status:
```json
{
  "success": false,
  "message": "Hanya setoran dengan status pengajuan yang dapat dibatalkan"
}
```

**422 Unprocessable Entity** - Time limit exceeded:
```json
{
  "success": false,
  "message": "Setoran tidak dapat dibatalkan karena sudah lebih dari 24 jam sejak pengajuan"
}
```

**Important Notes:**

1. Can only cancel setoran with status **`pengajuan`**
2. Must be within **24 hours** of creation
3. Status changes to **`dibatalkan`**
4. Creates status log entry with cancellation reason
5. Cannot be undone once cancelled

**Example cURL:**
```bash
curl -X POST "https://api.wanigo.com/api/nasabah/setoran-sampah/123/cancel" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef123456..."
```

---

### Get Dashboard Statistics

Retrieve summary statistics of user's waste deposits for dashboard display.

**Endpoint:** `GET /api/nasabah/setoran-sampah/dashboard-stats`

**Authentication:** Required (Bearer token)

**Query Parameters:** None

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "jumlah_setoran": {
      "pengajuan": 2,
      "diproses": 1,
      "selesai": 15,
      "batal": 1,
      "total": 19
    },
    "total_statistik": {
      "total_berat": 45.50,
      "total_berat_format": "45.50 kg",
      "total_saldo": 227500.00,
      "total_saldo_format": "Rp 227.500",
      "perkiraan_poin": 227
    },
    "setoran_terakhir": {
      "id": 125,
      "kode_setoran": "MELA001235",
      "status_setoran": "pengajuan",
      "bank_sampah": "Bank Sampah Melati",
      "tanggal": "27 Jan 2026",
      "total_nilai_format": "Rp 0"
    }
  }
}
```

**Field Descriptions:**

| Field | Type | Description |
|-------|------|-------------|
| jumlah_setoran | object | Count of setoran by status |
| jumlah_setoran.pengajuan | integer | Count with status `pengajuan` |
| jumlah_setoran.diproses | integer | Count with status `diproses` |
| jumlah_setoran.selesai | integer | Count with status `selesai` |
| jumlah_setoran.batal | integer | Count with status `dibatalkan` |
| jumlah_setoran.total | integer | Total count of all setoran |
| total_statistik | object | Cumulative statistics (only from completed setoran) |
| total_statistik.total_berat | float | Total weight in kg (only `selesai` status) |
| total_statistik.total_saldo | float | Total balance in Rupiah (only `selesai` status) |
| total_statistik.perkiraan_poin | integer | Estimated points (1 point per 1000 Rupiah) |
| setoran_terakhir | object\|null | Most recent setoran (any status) |

**Important Notes:**

1. **total_statistik** only includes setoran with status **`selesai`**
2. **jumlah_setoran** includes all statuses
3. Points calculation: 1 point = 1000 Rupiah (floor division)
4. `setoran_terakhir` can be null if user has no setoran yet

**Example cURL:**
```bash
curl -X GET "https://api.wanigo.com/api/nasabah/setoran-sampah/dashboard-stats" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef123456..."
```

---

## Detail Setoran Endpoints

The Detail Setoran endpoints manage individual waste items within a deposit transaction (setoran sampah). These endpoints allow users to view, add, update, and delete specific items in their waste deposits.

### Important Distinctions

**Two Main Retrieval Endpoints:**

1. **GET /api/nasabah/detail-setoran/{id}/detail** - Retrieves a **SINGLE** detail_setoran item by its ID
2. **POST /api/nasabah/detail-setoran/by-setoran** - Retrieves **ALL** detail_setoran items for a given setoran_sampah_id

### Get Single Item Detail

Retrieve detailed information about a **SINGLE** waste item within a deposit, including bank name and deposit code.

**Endpoint:** `GET /api/nasabah/detail-setoran/{id}/detail`

**Authentication:** Required (Bearer token)

**Path Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Detail setoran ID (individual item ID) |

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "detail_setoran": {
      "id": 45,
      "setoran_sampah_id": 123,
      "item_sampah_id": 5,
      "berat": 2.50,
      "saldo": 12500.00,
      "foto": "setoran_sampah/photo123.jpg",
      "foto_url": "https://api.wanigo.com/storage/setoran_sampah/photo123.jpg",
      "harga_format": "Rp 5.000",
      "nilai_format": "Rp 12.500",
      "berat_format": "2,50 kg",
      "created_at": "2026-01-25T10:00:00.000000Z",
      "updated_at": "2026-01-25T10:00:00.000000Z",
      "katalog_sampah": {
        "id": 5,
        "nama_item_sampah": "Botol Plastik PET",
        "harga_per_kg": 5000.00,
        "kategori_sampah": 0,
        "sub_kategori_sampah_id": 1
      },
      "setoran_sampah": {
        "id": 123,
        "kode_setoran_sampah": "MELA001234",
        "bank_sampah": {
          "id": 1,
          "nama_bank_sampah": "Bank Sampah Melati"
        }
      }
    },
    "item_sampah": {
      "id": 5,
      "nama": "Botol Plastik PET",
      "kategori_utama": "Sampah Kering",
      "sub_kategori": "Plastik",
      "deskripsi": "Botol plastik jenis PET yang bersih dan kering",
      "cara_pemilahan": "Pisahkan tutup botol, bersihkan dari sisa cairan",
      "cara_pengemasan": "Kumpulkan dalam kantong plastik besar",
      "gambar_url": "https://api.wanigo.com/storage/katalog/botol-pet.jpg"
    },
    "nama_bank_sampah": "Bank Sampah Melati",
    "kode_setoran": "MELA001234"
  }
}
```

**Error Responses:**

**404 Not Found** - Detail setoran not found:
```json
{
  "success": false,
  "message": "Detail setoran tidak ditemukan"
}
```

**403 Forbidden** - Unauthorized access:
```json
{
  "success": false,
  "message": "Anda tidak memiliki akses ke data ini"
}
```

**Field Descriptions:**

| Field | Type | Description |
|-------|------|-------------|
| detail_setoran | object | Complete detail setoran information |
| detail_setoran.id | integer | Detail setoran unique identifier |
| detail_setoran.setoran_sampah_id | integer | Parent setoran ID |
| detail_setoran.item_sampah_id | integer | Katalog sampah ID |
| detail_setoran.berat | float | Weight in kilograms |
| detail_setoran.saldo | float | Calculated value (berat × harga_per_kg) |
| detail_setoran.foto | string\|null | Photo file path |
| detail_setoran.foto_url | string\|null | Full photo URL |
| detail_setoran.harga_format | string | Formatted price per kg |
| detail_setoran.nilai_format | string | Formatted total value |
| detail_setoran.berat_format | string | Formatted weight with unit |
| item_sampah | object | Complete waste item catalog information |
| item_sampah.nama | string | Item name |
| item_sampah.kategori_utama | string | Main category (Kering/Basah) |
| item_sampah.sub_kategori | string | Sub-category name |
| item_sampah.deskripsi | string | Item description |
| item_sampah.cara_pemilahan | string | Sorting instructions |
| item_sampah.cara_pengemasan | string | Packaging instructions |
| item_sampah.gambar_url | string\|null | Item reference image URL |
| **nama_bank_sampah** | string\|null | **NEW:** Bank sampah name |
| **kode_setoran** | string\|null | **NEW:** Deposit code |

**Important Notes:**

1. **This endpoint retrieves a SINGLE item** - Use the detail_setoran ID (not setoran_sampah_id)
2. **nama_bank_sampah and kode_setoran are NEW fields** added for convenience
3. Returns complete item information including catalog details
4. Only accessible by the user who owns the parent setoran
5. Returns 403 if user tries to access another user's detail item

**Example cURL:**
```bash
curl -X GET "https://api.wanigo.com/api/nasabah/detail-setoran/45/detail" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef123456..."
```

---

### Get All Items by Setoran

Retrieve **ALL** detail_setoran items for a specific setoran_sampah, grouped by sub-category.

**Endpoint:** `POST /api/nasabah/detail-setoran/by-setoran`

**Authentication:** Required (Bearer token)

**Request Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "setoran_sampah_id": 123
}
```

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| setoran_sampah_id | integer | Yes | Setoran sampah ID to retrieve all items from |

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "detail_setoran": [
      {
        "id": 45,
        "setoran_sampah_id": 123,
        "item_sampah_id": 5,
        "berat": 2.50,
        "saldo": 12500.00,
        "foto": "setoran_sampah/photo123.jpg",
        "foto_url": "https://api.wanigo.com/storage/setoran_sampah/photo123.jpg",
        "harga_format": "Rp 5.000",
        "nilai_format": "Rp 12.500",
        "berat_format": "2,50 kg",
        "kategori_utama": "Sampah Kering",
        "sub_kategori": "Plastik",
        "katalog_sampah": {
          "id": 5,
          "nama_item_sampah": "Botol Plastik PET",
          "harga_per_kg": 5000.00,
          "kategori_sampah": 0,
          "sub_kategori_sampah_id": 1
        }
      },
      {
        "id": 46,
        "setoran_sampah_id": 123,
        "item_sampah_id": 12,
        "berat": 1.00,
        "saldo": 3000.00,
        "foto": null,
        "foto_url": null,
        "harga_format": "Rp 3.000",
        "nilai_format": "Rp 3.000",
        "berat_format": "1,00 kg",
        "kategori_utama": "Sampah Kering",
        "sub_kategori": "Kertas",
        "katalog_sampah": {
          "id": 12,
          "nama_item_sampah": "Kertas HVS",
          "harga_per_kg": 3000.00,
          "kategori_sampah": 0,
          "sub_kategori_sampah_id": 2
        }
      }
    ],
    "detail_by_sub_kategori": [
      {
        "id": 1,
        "nama": "Plastik",
        "warna": "#FF5733",
        "items": [
          {
            "id": 45,
            "setoran_sampah_id": 123,
            "item_sampah_id": 5,
            "berat": 2.50,
            "saldo": 12500.00,
            "harga_format": "Rp 5.000",
            "nilai_format": "Rp 12.500",
            "berat_format": "2,50 kg",
            "katalog_sampah": {
              "id": 5,
              "nama_item_sampah": "Botol Plastik PET"
            }
          }
        ]
      },
      {
        "id": 2,
        "nama": "Kertas",
        "warna": "#33C3FF",
        "items": [
          {
            "id": 46,
            "setoran_sampah_id": 123,
            "item_sampah_id": 12,
            "berat": 1.00,
            "saldo": 3000.00,
            "harga_format": "Rp 3.000",
            "nilai_format": "Rp 3.000",
            "berat_format": "1,00 kg",
            "katalog_sampah": {
              "id": 12,
              "nama_item_sampah": "Kertas HVS"
            }
          }
        ]
      }
    ],
    "setoran": {
      "id": 123,
      "kode_setoran": "MELA001234",
      "nama_bank_sampah": "Bank Sampah Melati",
      "status": "pengajuan",
      "tanggal_setoran": "2026-01-25",
      "waktu_setoran": "10:00",
      "total_berat": 3.50,
      "total_berat_format": "3,50 kg",
      "total_nilai": 15500.00,
      "total_nilai_format": "Rp 15.500",
      "editable": true
    }
  }
}
```

**Error Responses:**

**404 Not Found** - Setoran not found or unauthorized:
```json
{
  "success": false,
  "message": "Setoran tidak ditemukan atau Anda tidak memiliki akses"
}
```

**422 Unprocessable Entity** - Validation error:
```json
{
  "success": false,
  "message": "The setoran_sampah_id field is required."
}
```

**Field Descriptions:**

| Field | Type | Description |
|-------|------|-------------|
| detail_setoran | array | **ALL** detail items in original format |
| detail_by_sub_kategori | array | Items grouped by sub-category for organized display |
| detail_by_sub_kategori[].id | integer | Sub-category ID |
| detail_by_sub_kategori[].nama | string | Sub-category name |
| detail_by_sub_kategori[].warna | string | Sub-category color (hex code) |
| detail_by_sub_kategori[].items | array | Items belonging to this sub-category |
| setoran | object | Parent setoran summary information |
| setoran.id | integer | Setoran sampah ID |
| setoran.kode_setoran | string | Deposit code |
| **setoran.nama_bank_sampah** | string\|null | **NEW:** Bank sampah name |
| setoran.status | string | Current status (pengajuan, diproses, selesai, dibatalkan) |
| setoran.tanggal_setoran | string | Deposit date (YYYY-MM-DD) |
| setoran.waktu_setoran | string | Deposit time (HH:mm) |
| setoran.total_berat | float | Total weight of all items |
| setoran.total_berat_format | string | Formatted total weight |
| setoran.total_nilai | float | Total value of all items |
| setoran.total_nilai_format | string | Formatted total value |
| setoran.editable | boolean | Whether items can be edited (true only if status is pengajuan) |

**Important Notes:**

1. **This endpoint retrieves ALL items** for a given setoran_sampah_id
2. **nama_bank_sampah is a NEW field** in the setoran object
3. Returns items in two formats:
   - `detail_setoran`: Flat array of all items
   - `detail_by_sub_kategori`: Items grouped by sub-category for better UI organization
4. Only accessible by the user who owns the setoran
5. `editable` flag indicates if items can be modified (only when status is `pengajuan`)

**Example cURL:**
```bash
curl -X POST "https://api.wanigo.com/api/nasabah/detail-setoran/by-setoran" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef123456..." \
  -d '{"setoran_sampah_id": 123}'
```

---

### Add Item to Setoran

Add a new waste item to an existing setoran (only allowed for status `pengajuan`).

**Endpoint:** `POST /api/nasabah/detail-setoran`

**Authentication:** Required (Bearer token)

**Request Headers:**
```
Content-Type: multipart/form-data
Accept: application/json
Authorization: Bearer {token}
```

**Request Body (multipart/form-data):**
```
setoran_sampah_id: 123
item_sampah_id: 5
berat: 2.50
foto: [file upload]
```

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| setoran_sampah_id | integer | Yes | Setoran sampah ID |
| item_sampah_id | integer | Yes | Katalog sampah ID to add |
| berat | float | No | Weight in kg (can be 0 for initial submission) |
| foto | file | No | Photo of the waste item (jpeg, png, jpg, max 2MB) |

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Item berhasil ditambahkan ke setoran",
  "data": {
    "detail_setoran": {
      "id": 47,
      "setoran_sampah_id": 123,
      "item_sampah_id": 5,
      "berat": 2.50,
      "saldo": 12500.00,
      "foto": "setoran_sampah/abc123.jpg",
      "katalog_sampah": {
        "id": 5,
        "nama_item_sampah": "Botol Plastik PET",
        "harga_per_kg": 5000.00
      }
    },
    "total_berat": 5.50,
    "total_berat_format": "5,50 kg",
    "total_nilai": 27500.00,
    "total_nilai_format": "Rp 27.500"
  }
}
```

**Error Responses:**

**404 Not Found:**
```json
{
  "success": false,
  "message": "Setoran tidak ditemukan atau Anda tidak memiliki akses"
}
```

**422 Unprocessable Entity** - Wrong status:
```json
{
  "success": false,
  "message": "Hanya setoran dengan status pengajuan yang dapat dimodifikasi"
}
```

**422 Unprocessable Entity** - Invalid item:
```json
{
  "success": false,
  "message": "Item sampah tidak valid atau tidak terdaftar di bank sampah ini"
}
```

**422 Unprocessable Entity** - Duplicate item:
```json
{
  "success": false,
  "message": "Item ini sudah ditambahkan ke setoran ini"
}
```

**Important Notes:**

1. Can only add items to setoran with status **`pengajuan`**
2. Item must belong to the same bank sampah as the setoran
3. Cannot add duplicate items (same item_sampah_id)
4. Weight can be 0 for initial submission (will be filled by bank staff)
5. Photo is optional but recommended
6. Automatically updates setoran total_berat and total_saldo

**Example cURL:**
```bash
curl -X POST "https://api.wanigo.com/api/nasabah/detail-setoran" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef123456..." \
  -F "setoran_sampah_id=123" \
  -F "item_sampah_id=5" \
  -F "berat=2.50" \
  -F "foto=@/path/to/photo.jpg"
```

---

### Update Item Detail

Update weight and photo of an existing detail setoran item.

**Endpoint:** `PUT /api/nasabah/detail-setoran/{id}`

**Authentication:** Required (Bearer token)

**Path Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Detail setoran ID to update |

**Request Headers:**
```
Content-Type: multipart/form-data
Accept: application/json
Authorization: Bearer {token}
```

**Request Body (multipart/form-data):**
```
berat: 3.00
foto: [file upload]
```

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| berat | float | Yes | New weight in kg (minimum 0.01) |
| foto | file | No | New photo (replaces old photo if provided) |

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Detail setoran berhasil diupdate",
  "data": {
    "detail_setoran": {
      "id": 45,
      "setoran_sampah_id": 123,
      "item_sampah_id": 5,
      "berat": 3.00,
      "saldo": 15000.00,
      "foto": "setoran_sampah/new_photo.jpg",
      "katalog_sampah": {
        "id": 5,
        "nama_item_sampah": "Botol Plastik PET",
        "harga_per_kg": 5000.00
      }
    },
    "total_berat": 6.00,
    "total_berat_format": "6,00 kg",
    "total_nilai": 30000.00,
    "total_nilai_format": "Rp 30.000"
  }
}
```

**Error Responses:**

**404 Not Found:**
```json
{
  "success": false,
  "message": "Detail setoran tidak ditemukan"
}
```

**422 Unprocessable Entity:**
```json
{
  "success": false,
  "message": "Hanya setoran dengan status pengajuan yang dapat dimodifikasi"
}
```

**Important Notes:**

1. Can only update items in setoran with status **`pengajuan`**
2. Weight is required and must be at least 0.01 kg
3. Saldo is automatically recalculated based on new weight
4. If new photo is uploaded, old photo is automatically deleted
5. Automatically updates parent setoran totals

**Example cURL:**
```bash
curl -X PUT "https://api.wanigo.com/api/nasabah/detail-setoran/45" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef123456..." \
  -F "berat=3.00" \
  -F "foto=@/path/to/new_photo.jpg"
```

---

### Delete Item from Setoran

Remove an item from a setoran (only allowed for status `pengajuan`).

**Endpoint:** `DELETE /api/nasabah/detail-setoran/{id}`

**Authentication:** Required (Bearer token)

**Path Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Detail setoran ID to delete |

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Item berhasil dihapus dari setoran",
  "data": {
    "total_berat": 3.00,
    "total_berat_format": "3,00 kg",
    "total_nilai": 15000.00,
    "total_nilai_format": "Rp 15.000"
  }
}
```

**Error Responses:**

**404 Not Found:**
```json
{
  "success": false,
  "message": "Detail setoran tidak ditemukan"
}
```

**422 Unprocessable Entity:**
```json
{
  "success": false,
  "message": "Hanya setoran dengan status pengajuan yang dapat dimodifikasi"
}
```

**Important Notes:**

1. Can only delete items from setoran with status **`pengajuan`**
2. Photo is automatically deleted if exists
3. Automatically updates parent setoran totals
4. Cannot be undone - item is permanently deleted

**Example cURL:**
```bash
curl -X DELETE "https://api.wanigo.com/api/nasabah/detail-setoran/45" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef123456..."
```

---

### Bulk Update Items

Update multiple detail setoran items in a single request.

**Endpoint:** `POST /api/nasabah/detail-setoran/bulk-update`

**Authentication:** Required (Bearer token)

**Request Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "setoran_sampah_id": 123,
  "items": [
    {
      "id": 45,
      "berat": 3.00
    },
    {
      "id": 46,
      "berat": 1.50
    }
  ]
}
```

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| setoran_sampah_id | integer | Yes | Setoran sampah ID |
| items | array | Yes | Array of items to update |
| items[].id | integer | Yes | Detail setoran ID |
| items[].berat | float | Yes | New weight in kg (minimum 0.01) |

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Item setoran berhasil diupdate",
  "data": {
    "updated_items": [
      {
        "id": 45,
        "setoran_sampah_id": 123,
        "item_sampah_id": 5,
        "berat": 3.00,
        "saldo": 15000.00
      },
      {
        "id": 46,
        "setoran_sampah_id": 123,
        "item_sampah_id": 12,
        "berat": 1.50,
        "saldo": 4500.00
      }
    ],
    "total_berat": 4.50,
    "total_berat_format": "4,50 kg",
    "total_nilai": 19500.00,
    "total_nilai_format": "Rp 19.500"
  }
}
```

**Error Responses:**

**404 Not Found:**
```json
{
  "success": false,
  "message": "Setoran tidak ditemukan atau Anda tidak memiliki akses"
}
```

**422 Unprocessable Entity:**
```json
{
  "success": false,
  "message": "Hanya setoran dengan status pengajuan yang dapat dimodifikasi"
}
```

**Important Notes:**

1. Can only update items in setoran with status **`pengajuan`**
2. All items must belong to the specified setoran_sampah_id
3. Saldo is automatically recalculated for each item
4. Updates are performed in a transaction (all or nothing)
5. Automatically updates parent setoran totals

**Example cURL:**
```bash
curl -X POST "https://api.wanigo.com/api/nasabah/detail-setoran/bulk-update" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef123456..." \
  -d '{
    "setoran_sampah_id": 123,
    "items": [
      {"id": 45, "berat": 3.00},
      {"id": 46, "berat": 1.50}
    ]
  }'
```

---

## Response Format Standards

All API endpoints follow a consistent response structure:

### Success Response Structure

```json
{
  "success": true,
  "message": "Operation description",
  "data": {
    // Response payload
  }
}
```

### Error Response Structure

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    // Validation errors (422 only)
  }
}
```

### Pagination Structure

Paginated endpoints include Laravel's standard pagination metadata:

```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [...],
    "first_page_url": "...",
    "from": 1,
    "last_page": 5,
    "last_page_url": "...",
    "next_page_url": "...",
    "path": "...",
    "per_page": 10,
    "prev_page_url": null,
    "to": 10,
    "total": 50
  }
}
```

---

## Error Handling

### HTTP Status Codes

| Code | Description | Usage |
|------|-------------|-------|
| 200 | OK | Successful request |
| 401 | Unauthorized | Missing or invalid authentication token |
| 403 | Forbidden | Authenticated but lacks permission |
| 404 | Not Found | Resource does not exist |
| 422 | Unprocessable Entity | Validation error |
| 500 | Internal Server Error | Server-side error |
| 503 | Service Unavailable | Service temporarily unavailable |

### Common Error Scenarios

**Authentication Errors (401):**
```json
{
  "success": false,
  "message": "Unauthenticated."
}
```

**Validation Errors (422):**
```json
{
  "success": false,
  "message": "Validasi gagal",
  "errors": {
    "field_name": [
      "Error message 1",
      "Error message 2"
    ]
  }
}
```

**Resource Not Found (404):**
```json
{
  "success": false,
  "message": "Resource tidak ditemukan"
}
```

**Server Error (500):**
```json
{
  "success": false,
  "message": "Terjadi kesalahan pada server"
}
```

---

## Deprecation Notices

### Deprecated Fields

The following fields are **deprecated** and will be removed in **v2.0** (next major release):

#### Bank Sampah Endpoints

**Deprecated Fields:**
- `@deprecated.nomor_telepon`
- `@deprecated.nomor_telepon_publik`

**Migration Path:**
Use `contact_info.phone` instead, which provides normalized phone number with priority logic:
1. First checks `nomor_telepon_publik`
2. Falls back to `nomor_telepon`
3. Returns `null` if both are null

**Example Migration:**

❌ **Old (Deprecated):**
```javascript
const phone = bankSampah['@deprecated'].nomor_telepon_publik || 
              bankSampah['@deprecated'].nomor_telepon;
```

✅ **New (Recommended):**
```javascript
const phone = bankSampah.contact_info.phone;
```

### Deprecation Warning Header

All responses containing deprecated fields include a Warning header:

```
Warning: 299 - "Deprecated fields (@deprecated) will be removed in v2.0"
```

**Timeline:**
- **Current Release (v1.x):** Deprecated fields included with warning
- **Next Release (v2.0):** Deprecated fields removed completely

**Action Required:**
Update your client applications to use `contact_info.phone` and `contact_info.email` before upgrading to v2.0.

---

## Status Values Reference

### Setoran Sampah Status Values

The status system was updated to use clearer, user-friendly Indonesian terms.

#### Current Status Values (v1.x)

| Status | Description | User Action | Bank Action |
|--------|-------------|-------------|-------------|
| **pengajuan** | Submission pending review | Can cancel within 24h | Review submission |
| **diproses** | Being processed by staff | Wait for completion | Weigh and calculate |
| **selesai** | Completed and credited | View history | - |
| **dibatalkan** | Cancelled | View history | - |

#### Status Migration Mapping

Old status values were automatically migrated to new values:

| Old Status | New Status | Migration Date |
|------------|------------|----------------|
| pending | pengajuan | January 2026 |
| requested | pengajuan | January 2026 |
| processing | diproses | January 2026 |
| in_progress | diproses | January 2026 |
| done | selesai | January 2026 |
| completed | selesai | January 2026 |

**Important Notes:**

1. **Old status values are no longer accepted** in API requests
2. All existing data was migrated automatically
3. API validation only accepts new status values
4. Database migration is reversible for rollback safety

#### Status Workflow

```
pengajuan → diproses → selesai
    ↓
dibatalkan (only from pengajuan, within 24h)
```

**Status Transitions:**

1. **pengajuan** (Initial)
   - Created by nasabah via `/pengajuan` endpoint
   - Can be cancelled within 24 hours
   - Visible in "ongoing" endpoint

2. **diproses** (Processing)
   - Updated by bank sampah staff
   - Cannot be cancelled by nasabah
   - Visible in "ongoing" endpoint

3. **selesai** (Completed)
   - Updated by bank sampah staff
   - Balance credited to nasabah account
   - Visible in "history" endpoint
   - Included in statistics

4. **dibatalkan** (Cancelled)
   - Can be set by nasabah (from pengajuan only)
   - Can be set by bank sampah staff (any status)
   - Visible in "history" endpoint
   - Not included in statistics

### Endpoint Status Filtering

| Endpoint | Included Statuses |
|----------|-------------------|
| `/ongoing` | pengajuan, diproses |
| `/history` | selesai, dibatalkan |
| `/dashboard-stats` (statistics) | selesai only |
| `/dashboard-stats` (counts) | All statuses |

---

## Best Practices

### Authentication

1. **Store tokens securely** - Use secure storage (Keychain/Keystore on mobile)
2. **Include token in all requests** - Use `Authorization: Bearer {token}` header
3. **Handle 401 errors** - Redirect to login when token expires
4. **Refresh tokens** - Implement token refresh logic if needed

### Error Handling

1. **Check `success` field** - Always check before accessing `data`
2. **Display user-friendly messages** - Use `message` field for user feedback
3. **Log errors** - Log full error response for debugging
4. **Handle network errors** - Implement retry logic for network failures

### Pagination

1. **Use pagination parameters** - Don't fetch all data at once
2. **Implement infinite scroll** - Load more data as user scrolls
3. **Cache responses** - Cache paginated data to reduce API calls
4. **Show loading states** - Display loading indicators during fetch

### Deprecated Fields

1. **Migrate immediately** - Update code to use new fields
2. **Monitor Warning headers** - Check for deprecation warnings
3. **Test thoroughly** - Ensure migration doesn't break functionality
4. **Plan for v2.0** - Prepare for removal of deprecated fields

### Performance

1. **Use location filtering** - Provide lat/lng for distance calculation
2. **Filter by category** - Reduce payload size with category filters
3. **Request only needed data** - Use detail endpoint only when needed
4. **Implement caching** - Cache bank sampah list and details

---

## Postman Collection

### Environment Variables

Set up these variables in your Postman environment:

| Variable | Description | Example |
|----------|-------------|---------|
| base_url | API base URL | `http://localhost:8000/api` |
| firebase_id_token | Firebase ID token | `eyJhbGciOiJSUzI1NiIsImtpZCI6...` |
| sanctum_token | Sanctum bearer token | `1\|abcdef123456...` |
| user_id | Authenticated user ID | `1` |
| bank_sampah_id | Test bank sampah ID | `1` |
| setoran_id | Test setoran ID | `123` |

### Collection Structure

```
WANIGO API
├── Authentication
│   └── Firebase Google Sign-In
├── Bank Sampah
│   ├── Get All Bank Sampah
│   ├── Get Bank Sampah Detail
│   ├── Get Registered Bank Sampah
│   └── Get Top Frequency
├── Setoran Sampah
│   ├── Get Ongoing Setoran
│   ├── Get Setoran History
│   ├── Create Pengajuan
│   ├── Get Setoran Detail
│   ├── Cancel Setoran
│   └── Get Dashboard Stats
└── Detail Setoran
    ├── Get Single Item Detail
    ├── Get All Items by Setoran
    ├── Add Item to Setoran
    ├── Update Item Detail
    ├── Delete Item from Setoran
    └── Bulk Update Items
```

### Auto-Save Token Script

Add this test script to the Firebase authentication request:

```javascript
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    pm.environment.set("sanctum_token", jsonData.data.token);
    pm.environment.set("user_id", jsonData.data.user.id);
    console.log("Token saved:", jsonData.data.token);
}
```

---

## Changelog

### Version 1.1 (January 2026)

**Added:**
- Detail Setoran endpoints documentation
- `nama_bank_sampah` field in GET /api/nasabah/detail-setoran/{id}/detail response
- `kode_setoran` field in GET /api/nasabah/detail-setoran/{id}/detail response
- `nama_bank_sampah` field in POST /api/nasabah/detail-setoran/by-setoran response (setoran object)
- Comprehensive documentation for all Detail Setoran CRUD operations

**Clarified:**
- GET /api/nasabah/detail-setoran/{id}/detail retrieves a **SINGLE** item by detail_setoran ID
- POST /api/nasabah/detail-setoran/by-setoran retrieves **ALL** items for a given setoran_sampah_id
- Distinction between the two main retrieval endpoints

### Version 1.0 (January 2026)

**Added:**
- Firebase Authentication integration with Google Sign-In
- Normalized `contact_info` object in Bank Sampah responses
- New Setoran Sampah status values (pengajuan, diproses, selesai, dibatalkan)
- Deprecation warning system with HTTP Warning headers
- Comprehensive API documentation

**Changed:**
- Bank Sampah list endpoints now exclude `tonase_sampah` field
- Bank Sampah detail endpoint includes `tonase_sampah` field
- Setoran Sampah status values updated to Indonesian terms
- Phone number resolution uses priority logic (nomor_telepon_publik > nomor_telepon)

**Deprecated:**
- `@deprecated.nomor_telepon` - Use `contact_info.phone` instead
- `@deprecated.nomor_telepon_publik` - Use `contact_info.phone` instead

**Migration:**
- Automatic migration of old status values to new values
- Backward compatibility maintained for one release cycle

---

## Support

For API support and questions:

- **Email:** support@wanigo.com
- **Documentation:** https://docs.wanigo.com
- **Issue Tracker:** https://github.com/wanigo/api/issues

---

## License

© 2026 WANIGO. All rights reserved.
