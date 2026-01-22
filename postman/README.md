# WANIGO API - Postman Collections

## 📦 Available Collections

### 1. **WANIGO_API_Complete.postman_collection.json** ⭐ RECOMMENDED
**Complete and up-to-date collection with all endpoints**

**Includes:**
- ✅ All Authentication endpoints
- ✅ Bank Sampah (Public & Authenticated)
- ✅ **NEW: Katalog Sampah with Sub-Kategori filtering**
- ✅ **NEW: Sub-Kategori Sampah endpoints**
- ✅ Dashboard Statistics
- ✅ Penarikan Saldo (Withdrawal)
- ✅ Nasabah Profile (3-step onboarding)
- ✅ Education (Edukasi) modules

**Total Endpoints:** 50+

**Features:**
- Auto-save token after login
- Organized in 9 logical folders
- Query parameters with descriptions
- Example request bodies
- Path variables pre-configured

---

### 2. **WANIGO_API_Complete.postman_environment.json** ⭐ REQUIRED
**Environment variables for the complete collection**

**Variables:**
- `base_url`: API base URL (default: http://localhost:8000)
- `token`: Auth token (auto-set after login)
- `test_email`: Test user email
- `test_password`: Test user password
- `test_phone`: Test phone number
- `bank_sampah_id`: Default bank sampah ID
- `katalog_id`: Default katalog ID
- `sub_kategori_id`: Default sub-kategori ID

---

### 3. Bank_Sampah_API_v2.postman_collection.json
Legacy collection for Bank Sampah improvements (partial)

### 4. WANIGO_API (Full Access).postman_collection.json
Old full collection (may be outdated)

### 5. WANIGO_API.postman_environment.json
Old environment file (basic variables only)

---

## 🚀 Quick Start

### Step 1: Import Collection & Environment

1. Open Postman
2. Click **Import** button
3. Select these 2 files:
   - `WANIGO_API_Complete.postman_collection.json`
   - `WANIGO_API_Complete.postman_environment.json`

### Step 2: Configure Environment

1. Select **"WANIGO API - Complete Environment"** from environment dropdown
2. Update variables if needed:
   - `base_url`: Change to your API URL
   - `test_email`, `test_password`, `test_phone`: Your test credentials

### Step 3: Test Authentication

1. Go to **"1. Authentication"** folder
2. Run **"Register"** (if new user) or **"Login"**
3. Token will be automatically saved to environment
4. Now you can test other endpoints!

---

## 📋 Collection Structure

```
WANIGO API - Complete Collection
├── 1. Authentication (10 endpoints)
│   ├── Check Email
│   ├── Register
│   ├── Login ⭐ (auto-saves token)
│   ├── Logout
│   ├── Forgot Password
│   ├── Reset Password
│   ├── Get Profile
│   ├── Update Profile
│   ├── Update Password
│   └── Check Profile Status
│
├── 2. Bank Sampah (Public) (2 endpoints)
│   ├── Get All Bank Sampah (with filtering)
│   └── Get Bank Sampah Detail
│
├── 3. Bank Sampah (Authenticated) (5 endpoints)
│   ├── Get Top Frequency
│   ├── Get Bank Sampah List (Old)
│   ├── Find Nearby
│   ├── Get User's Bank Sampah
│   └── Map Filter
│
├── 4. Katalog Sampah (5 endpoints)
│   ├── Get Katalog by Bank (NEW) ⭐
│   ├── Get Katalog by Bank (Old)
│   ├── Get Katalog Detail
│   ├── Search Katalog
│   └── Get Katalog for Setoran
│
├── 5. Sub-Kategori Sampah (NEW) (3 endpoints) ⭐
│   ├── Get Sub-Kategori by Bank (NEW)
│   ├── Get Sub-Kategori List (Old)
│   └── Get Katalog by Sub-Kategori
│
├── 6. Dashboard (1 endpoint)
│   └── Get Dashboard Statistics
│
├── 7. Penarikan Saldo (5 endpoints)
│   ├── Get Withdrawal History
│   ├── Create Withdrawal
│   ├── Get Withdrawal Detail
│   ├── Approve Withdrawal (Petugas)
│   └── Complete Withdrawal (Nasabah)
│
├── 8. Nasabah Profile (4 endpoints)
│   ├── Get Nasabah Profile
│   ├── Update Profile Step 1
│   ├── Update Profile Step 2
│   └── Update Profile Step 3
│
└── 9. Education (5 endpoints)
    ├── Get Modul List
    ├── Get Modul Detail
    ├── Get Video Detail
    ├── Get Article Detail
    └── Mark Content Complete
```

---

## 🆕 New Endpoints (Sub-Kategori Refactoring)

### 1. Get Katalog by Bank (NEW)
```
GET /api/bank-sampah/{bank_sampah_id}/katalog
```

**Query Parameters:**
- `kategori`: kering|basah|semua (default: semua)
- `sub_kategori_id`: Filter by sub-category
- `per_page`: Items per page (default: 20)
- `page`: Page number

**Response includes:**
- Sub-kategori info (nama, slug, icon, warna)
- Pagination metadata
- Ordered by sub_kategori urutan

**Example:**
```
GET /api/bank-sampah/1/katalog?kategori=kering&per_page=20&page=1
```

---

### 2. Get Sub-Kategori by Bank (NEW)
```
GET /api/bank-sampah/{bank_sampah_id}/sub-kategori
```

**Query Parameters:**
- `kategori`: kering|basah|semua (default: semua)

**Response:**
- Grouped by kering/basah
- Includes: id, nama, slug, icon, warna, urutan, kategori
- Ordered by urutan

**Example:**
```
GET /api/bank-sampah/1/sub-kategori?kategori=semua
```

**Response Structure:**
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

## 🔧 Environment Variables

| Variable | Description | Example |
|----------|-------------|---------|
| `base_url` | API base URL | `http://localhost:8000` |
| `token` | Auth token (auto-set) | `Bearer abc123...` |
| `test_email` | Test user email | `test@example.com` |
| `test_password` | Test user password | `Password123!` |
| `test_phone` | Test phone number | `08123456789` |
| `bank_sampah_id` | Default bank ID | `1` |
| `katalog_id` | Default katalog ID | `1` |
| `sub_kategori_id` | Default sub-kategori ID | `1` |

---

## 📝 Testing Workflow

### For New Users:
1. **Register** → Creates new account
2. **Login** → Gets token (auto-saved)
3. **Update Profile Step 1-3** → Complete profile
4. **Get Dashboard Stats** → View statistics
5. **Get Bank Sampah List** → Browse banks
6. **Get Sub-Kategori** → View categories
7. **Get Katalog** → Browse items

### For Existing Users:
1. **Login** → Gets token (auto-saved)
2. Test any authenticated endpoint

### For Testing New Features:
1. **Get Sub-Kategori by Bank** → See grouped categories
2. **Get Katalog by Bank** → See items with sub-kategori info
3. **Filter by kategori** → Test kering/basah filtering
4. **Test pagination** → Change per_page and page params

---

## 🎯 Key Features

### Auto Token Management
- Login request automatically saves token to environment
- All authenticated requests use `{{token}}` variable
- No manual token copying needed!

### Pre-configured Examples
- All requests have example values
- Query parameters include descriptions
- Path variables are pre-filled

### Organized Structure
- 9 logical folders
- Clear naming conventions
- NEW endpoints clearly marked

### Complete Coverage
- All authentication flows
- All CRUD operations
- All filtering options
- All new refactored endpoints

---

## 🐛 Troubleshooting

### Token Not Saved After Login
1. Check if environment is selected
2. Verify Login request has test script
3. Check response has `data.access_token`

### 401 Unauthorized Error
1. Run Login request first
2. Check token is saved in environment
3. Verify token hasn't expired

### 404 Not Found
1. Check `base_url` is correct
2. Verify endpoint path is correct
3. Check if resource ID exists

### Validation Errors (422)
1. Check request body format
2. Verify required fields are present
3. Check field value constraints

---

## 📚 Additional Resources

- **API Documentation**: `/docs/API_ENDPOINTS_COMPLETE.md`
- **Validation Guide**: `/docs/KATALOG_SAMPAH_VALIDATION_IMPLEMENTATION.md`
- **Migration Guide**: `/docs/PHASE4_MIGRATION_README.md`
- **Implementation Summary**: `/docs/IMPLEMENTATION_SUMMARY.md`

---

## 🔄 Regenerating Collection

If you need to regenerate the collection:

```bash
python postman/generate_complete_collection.py
```

This will create:
- `WANIGO_API_Complete.postman_collection.json`
- `WANIGO_API_Complete.postman_environment.json`

---

## 📞 Support

For issues or questions:
- Check documentation in `/docs` folder
- Review Postman collection structure
- Test with provided examples

---

**Last Updated:** 2026-01-22  
**Version:** 2.0 (Complete with Sub-Kategori Refactoring)  
**Total Endpoints:** 50+
