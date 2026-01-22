# WANIGO API - Complete Endpoints Documentation

## Base URL
- **Local**: `http://localhost:8000`
- **Staging**: `https://staging-api.wanigo.com`
- **Production**: `https://api.wanigo.com`

## Authentication
All authenticated endpoints require Bearer token in Authorization header:
```
Authorization: Bearer {token}
```

---

## 1. Authentication Endpoints

### 1.1 Check Email
**POST** `/api/check-email`
- Check if email is registered
- **Body**: `{ "email": "test@example.com" }`

### 1.2 Register
**POST** `/api/register`
- Register new user
- **Body**: `{ "name", "email", "password", "password_confirmation", "phone_number", "role" }`

### 1.3 Login
**POST** `/api/login`
- Login and get access token
- **Body**: `{ "email", "password" }`
- **Response**: Returns `access_token`

### 1.4 Logout
**POST** `/api/logout`
- Logout current session
- **Auth**: Required

### 1.5 Forgot Password
**POST** `/api/forgot-password`
- Request password reset
- **Body**: `{ "email" }`

### 1.6 Reset Password
**POST** `/api/reset-password`
- Reset password with token
- **Body**: `{ "token", "email", "password", "password_confirmation" }`

### 1.7 Get Profile
**GET** `/api/profile`
- Get current user profile
- **Auth**: Required

### 1.8 Update Profile
**POST** `/api/update-profile`
- Update basic profile
- **Auth**: Required
- **Body**: `{ "name", "phone_number" }`

### 1.9 Update Password
**POST** `/api/update-password`
- Change password
- **Auth**: Required
- **Body**: `{ "current_password", "password", "password_confirmation" }`

### 1.10 Check Profile Status
**GET** `/api/profile-status`
- Check profile completion status
- **Auth**: Required

---

## 2. Bank Sampah Endpoints (Public)

### 2.1 Get All Bank Sampah
**GET** `/api/bank-sampah`
- Get list of bank sampah with filtering
- **Query Params**:
  - `q`: Keyword search
  - `lat`, `lng`: User location
  - `radius_km`: Search radius (default: 10)
  - `kategori`: kering|basah|semua
  - `provinsi_id`, `kabupaten_id`, `kecamatan_id`: Location filters
  - `sort`: distance|name|newest
  - `per_page`: Items per page (default: 20)
  - `page`: Page number

### 2.2 Get Bank Sampah Detail
**GET** `/api/bank-sampah/{id}`
- Get detailed bank sampah information
- **Path**: `id` - Bank Sampah ID

---

## 3. Bank Sampah Endpoints (Authenticated)

### 3.1 Get Top Frequency
**GET** `/api/nasabah/bank-sampah/top-frequency`
- Get top 5 most visited bank sampah
- **Auth**: Required

### 3.2 Get Bank Sampah List (Old)
**GET** `/api/nasabah/bank-sampah`
- Legacy endpoint with standardized response
- **Auth**: Required
- **Query**: `keyword`, `status_operasional`, `kategori_sampah`

### 3.3 Find Nearby
**POST** `/api/nasabah/bank-sampah/find-nearby`
- Find bank sampah near location
- **Auth**: Required
- **Body**: `{ "latitude", "longitude", "radius", "kategori_sampah" }`

### 3.4 Get User's Bank Sampah
**GET** `/api/nasabah/bank-sampah/list`
- Get bank sampah where user is member
- **Auth**: Required

### 3.5 Map Filter
**POST** `/api/nasabah/bank-sampah/map-filter`
- Filter bank sampah for map view
- **Auth**: Required
- **Body**: `{ "latitude", "longitude", "radius", "kategori_sampah" }`

---

## 4. Katalog Sampah Endpoints (NEW)

### 4.1 Get Katalog by Bank (NEW)
**GET** `/api/bank-sampah/{bank_sampah_id}/katalog`
- Get katalog items with filtering and pagination
- **Path**: `bank_sampah_id`
- **Query Params**:
  - `kategori`: kering|basah|semua (default: semua)
  - `sub_kategori_id`: Filter by sub-category
  - `per_page`: Items per page (default: 20)
  - `page`: Page number
- **Response**: Includes sub-kategori info (nama, slug, icon, warna)
- **Ordering**: By sub_kategori urutan, then nama_item_sampah

### 4.2 Get Katalog by Bank (Old)
**GET** `/api/nasabah/katalog-sampah/by-bank`
- Legacy endpoint
- **Auth**: Required
- **Query**: `bank_sampah_id`, `kode_kategori`, `sub_kategori_id`

### 4.3 Get Katalog Detail
**GET** `/api/nasabah/katalog-sampah/{id}`
- Get katalog item detail
- **Auth**: Required

### 4.4 Search Katalog
**GET** `/api/nasabah/katalog-sampah/search`
- Search katalog items
- **Auth**: Required
- **Query**: `bank_sampah_id`, `keyword`

### 4.5 Get Katalog for Setoran
**GET** `/api/nasabah/katalog-sampah/for-setoran`
- Get katalog for deposit selection
- **Auth**: Required
- **Query**: `bank_sampah_id`, `kode_kategori`, `sub_kategori_id`, `selected_items`, `setoran_id`

---

## 5. Sub-Kategori Sampah Endpoints (NEW)

### 5.1 Get Sub-Kategori by Bank (NEW)
**GET** `/api/bank-sampah/{bank_sampah_id}/sub-kategori`
- Get active sub-categories grouped by kategori
- **Path**: `bank_sampah_id`
- **Query Params**:
  - `kategori`: kering|basah|semua (default: semua)
- **Response**: Grouped by kering/basah with fields (id, nama, slug, icon, warna, urutan, kategori)
- **Ordering**: By kategori_sampah, then urutan

### 5.2 Get Sub-Kategori List (Old)
**GET** `/api/nasabah/sub-kategori-sampah`
- Legacy endpoint
- **Auth**: Required
- **Query**: `bank_sampah_id`, `kategori_sampah_id`, `kode_kategori`

### 5.3 Get Katalog by Sub-Kategori
**GET** `/api/nasabah/sub-kategori-sampah/katalog`
- Get katalog items in sub-category
- **Auth**: Required
- **Query**: `sub_kategori_id`

---

## 6. Dashboard Endpoints

### 6.1 Get Dashboard Statistics
**GET** `/api/nasabah/dashboard/stats`
- Get user dashboard statistics
- **Auth**: Required
- **Response**: `{ total_saldo, total_tonase, total_setoran, total_bank_sampah }`

---

## 7. Penarikan Saldo (Withdrawal) Endpoints

### 7.1 Get Withdrawal History
**GET** `/api/nasabah/penarikan-saldo`
- Get withdrawal history with pagination
- **Auth**: Required
- **Query**: `page`, `per_page`

### 7.2 Create Withdrawal
**POST** `/api/nasabah/penarikan-saldo`
- Create new withdrawal request
- **Auth**: Required
- **Body** (multipart/form-data):
  - `bank_sampah_id`: Bank Sampah ID
  - `jumlah_penarikan`: Withdrawal amount
  - `foto_buku_tabungan`: Photo file (required)
- **Response**: Returns verification code

### 7.3 Get Withdrawal Detail
**GET** `/api/nasabah/penarikan-saldo/{id}`
- Get withdrawal detail
- **Auth**: Required

### 7.4 Approve Withdrawal (Petugas)
**POST** `/api/nasabah/penarikan-saldo/{id}/approve`
- Approve withdrawal request
- **Auth**: Required (Petugas only)
- **Status**: pending → approved

### 7.5 Complete Withdrawal (Nasabah)
**POST** `/api/nasabah/penarikan-saldo/{id}/complete`
- Complete withdrawal with verification code
- **Auth**: Required
- **Body**: `{ "kode_verifikasi" }`
- **Status**: approved → completed

---

## 8. Nasabah Profile Endpoints

### 8.1 Get Nasabah Profile
**GET** `/api/nasabah/profile`
- Get nasabah profile details
- **Auth**: Required

### 8.2 Update Profile Step 1
**POST** `/api/nasabah/profile/step1`
- Update personal data
- **Auth**: Required
- **Body**: `{ "jenis_kelamin", "usia", "profesi" }`

### 8.3 Update Profile Step 2
**POST** `/api/nasabah/profile/step2`
- Update waste sorting knowledge
- **Auth**: Required
- **Body**: `{ "tahu_memilah_sampah", "motivasi_memilah_sampah", "nasabah_bank_sampah", "kode_bank_sampah" }`

### 8.4 Update Profile Step 3
**POST** `/api/nasabah/profile/step3`
- Update waste sorting habits
- **Auth**: Required
- **Body**: `{ "frekuensi_memilah_sampah", "jenis_sampah_dikelola" }`

---

## 9. Education (Edukasi) Endpoints

### 9.1 Get Modul List
**GET** `/api/nasabah/edukasi/moduls`
- Get all education modules with progress
- **Auth**: Required

### 9.2 Get Modul Detail
**GET** `/api/nasabah/edukasi/modul/{id}`
- Get module detail with content list
- **Auth**: Required

### 9.3 Get Video Detail
**GET** `/api/nasabah/edukasi/video/{id}`
- Get video content detail
- **Auth**: Required

### 9.4 Get Article Detail
**GET** `/api/nasabah/edukasi/artikel/{id}`
- Get article content detail
- **Auth**: Required

### 9.5 Mark Content Complete
**POST** `/api/nasabah/edukasi/konten/{id}/complete`
- Mark content as completed
- **Auth**: Required

---

## Response Format

All endpoints follow standardized response structure:

### Success Response
```json
{
    "success": true,
    "message": "Operation successful",
    "data": {},
    "meta": {}
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error message",
    "errors": {}
}
```

### HTTP Status Codes
- `200 OK`: Successful request
- `201 Created`: Resource created
- `400 Bad Request`: Invalid request
- `401 Unauthorized`: Authentication required
- `403 Forbidden`: Access denied
- `404 Not Found`: Resource not found
- `422 Unprocessable Entity`: Validation error
- `500 Internal Server Error`: Server error

---

## New Features (Sub-Kategori Refactoring)

### Key Improvements
1. **URL-friendly slugs**: Sub-categories now have slugs for clean URLs
2. **Grouped responses**: Sub-categories grouped by kering/basah
3. **Rich metadata**: Includes icon, warna (color), urutan (order)
4. **Better filtering**: Filter by kategori with single parameter
5. **Pagination**: Katalog endpoint supports pagination
6. **Ordering**: Results ordered by sub_kategori urutan

### Migration Notes
- Old endpoints still work (backward compatible)
- New endpoints recommended for new implementations
- `kategori_sampah` field maintained in katalog for compatibility

---

## Testing with Postman

1. Import collection: `WANIGO_API_Complete.postman_collection.json`
2. Import environment: `WANIGO_API.postman_environment.json`
3. Set environment variables:
   - `base_url`: Your API URL
   - `token`: Will be set automatically after login
   - `test_email`, `test_password`, `test_phone`: Test credentials

4. Run requests in order:
   - Register → Login (saves token) → Test other endpoints

---

## API Versioning

Use `X-API-Version` header for version control:
- `X-API-Version: 1.0` - Legacy version
- `X-API-Version: 2.0` - Current version (default)

---

## Rate Limiting

- **Public endpoints**: 60 requests/minute
- **Authenticated endpoints**: 120 requests/minute

---

## Support

For API issues or questions:
- Documentation: `/docs/API_ENDPOINTS_COMPLETE.md`
- Postman Collection: `/postman/WANIGO_API_Complete.postman_collection.json`
- Environment: `/postman/WANIGO_API.postman_environment.json`
