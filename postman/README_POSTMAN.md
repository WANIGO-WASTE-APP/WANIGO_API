# WANIGO API - Postman Collection Guide

## 📦 Files

- **WANIGO_API_Complete.postman_collection.json** - Complete API collection with all endpoints
- **WANIGO_API_Development.postman_environment.json** - Development environment (localhost)
- **WANIGO_API_Production.postman_environment.json** - Production environment

## 🚀 Quick Start

### 1. Import Collection & Environment

1. Open Postman
2. Click **Import** button
3. Import these files:
   - `WANIGO_API_Complete.postman_collection.json`
   - `WANIGO_API_Development.postman_environment.json`
   - `WANIGO_API_Production.postman_environment.json`

### 2. Select Environment

- Click environment dropdown (top right)
- Select **WANIGO API - Development** for local testing
- Select **WANIGO API - Production** for production testing

### 3. Configure Environment Variables

Click the eye icon (👁️) next to environment dropdown to edit variables:

**Development Environment:**
- `base_url`: `http://localhost:8000` (default)
- `test_email`: Your test email
- `test_password`: Your test password
- `test_phone`: Your test phone number
- `token`: Auto-filled after login
- `firebase_id_token`: Firebase ID token (for Google Sign-In)

**Production Environment:**
- `base_url`: `https://api.wanigo.com`
- Other variables: Fill with your production credentials

## 🔐 Authentication

### Method 1: Email/Password Login

1. Go to **1. Authentication** → **Login (Email/Password)**
2. Update request body with your credentials:
   ```json
   {
     "email": "your-email@example.com",
     "password": "your-password"
   }
   ```
3. Click **Send**
4. ✅ Token automatically saved to `{{token}}` variable
5. All authenticated endpoints will now work!

### Method 2: Firebase Google Sign-In (NEW)

1. Get Firebase ID token from your mobile app
2. Go to **1. Authentication** → **Firebase Google Sign-In (NEW)**
3. Update request body:
   ```json
   {
     "id_token": "your-firebase-id-token"
   }
   ```
4. Click **Send**
5. ✅ Token automatically saved to `{{token}}` variable

**How it works:**
- New users: Account created automatically
- Existing users: Profile updated with Firebase data
- Returns Sanctum token for API access

## 📋 Collection Structure

### 1. Authentication
- Check Email
- Register
- **Login (Email/Password)** - Auto-saves token ✅
- **Firebase Google Sign-In (NEW)** - Auto-saves token ✅
- Logout
- Forgot Password
- Reset Password
- Get Profile
- Update Profile
- Update Password
- Check Profile Status

### 2. Bank Sampah (Public)
- **Get All Bank Sampah (UPDATED)** - New response format
- **Get Bank Sampah Detail (UPDATED)** - Includes tonase_sampah

### 3. Bank Sampah (Authenticated)
- Get Top Frequency
- Get Bank Sampah List (Old)
- Find Nearby
- **Get Registered Bank Sampah (UPDATED)** - New response format
- Get User's Bank Sampah
- Map Filter

### 4. Katalog Sampah
- Get Katalog by Bank (NEW)
- Get Katalog by Bank (Old)
- Get Katalog Detail
- Search Katalog
- Get Katalog for Setoran

### 5. Sub-Kategori Sampah (NEW)
- Get Sub-Kategori by Bank (NEW)
- Get Sub-Kategori List (Old)
- Get Katalog by Sub-Kategori

### 6. Dashboard
- Get Dashboard Statistics

### 7. Penarikan Saldo (Withdrawal)
- Get Withdrawal History
- Create Withdrawal
- Get Withdrawal Detail
- Approve Withdrawal (Petugas)
- Complete Withdrawal (Nasabah)

### 8. Nasabah Profile
- Get Nasabah Profile
- Update Profile Step 1
- Update Profile Step 2
- Update Profile Step 3

### 9. Education (Edukasi)
- Get Modul List
- Get Modul Detail
- Get Video Detail
- Get Article Detail
- Mark Content Complete

### 10. Setoran Sampah (NEW STATUS) ⭐
- **Get Ongoing Setoran (UPDATED)** - New status values
- **Get Setoran History (UPDATED)** - New status values
- Create Pengajuan Setoran
- Get Setoran Detail
- Cancel Setoran
- Get Dashboard Stats

### 11. Jadwal Sampah (NEW) ⭐
- Get All Jadwal Sampah
- Check Bank Sampah Registration
- Get Calendar View
- Get Nasabah Bank Sampah List
- Get Jadwal By Tanggal
- Get Jadwal Detail
- Create Jadwal Pemilahan
- Create Jadwal Setoran
- Update Jadwal
- Delete Jadwal
- Mark Jadwal Complete

### 12. Bank Sampah Profile (NEW) ⭐
- Get Bank Sampah Profile
- Get Operating Hours
- Get Katalog Sampah
- Get Member List
- Get Statistics

### 13. Bank Sampah Membership (NEW) ⭐
- Register as Member
- Get Membership Status
- Update Member Profile
- Leave Bank Sampah

### 14. Detail Setoran (NEW) ⭐
- Get Detail Setoran List
- Get Detail Setoran by ID
- Create Detail Setoran
- Update Detail Setoran
- Delete Detail Setoran

## 🆕 What's New in This Update

### 1. Complete API Coverage (NEW) ⭐
- ✅ Added **Jadwal Sampah** section - Complete schedule management
- ✅ Added **Bank Sampah Profile** section - View bank profiles and details
- ✅ Added **Bank Sampah Membership** section - Membership management
- ✅ Added **Detail Setoran** section - Detailed waste deposit records
- ✅ Merged all missing endpoints from previous collections

### 2. Firebase Authentication
- ✅ New endpoint: `POST /api/auth/firebase/google`
- ✅ Auto-save token to environment
- ✅ Supports new and existing users

### 3. Bank Sampah API Improvements
- ✅ New `contact_info` object (normalized phone/email)
- ✅ `@deprecated` object for backward compatibility
- ✅ Warning header for deprecated fields
- ✅ `tonase_sampah` removed from list endpoints (performance)
- ✅ `tonase_sampah` included in detail endpoint only

**Example Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "nama_bank_sampah": "Bank Sampah Melati",
    "contact_info": {
      "phone": "081234567890",
      "email": "melati@example.com"
    },
    "@deprecated": {
      "nomor_telepon": "081234567890",
      "nomor_telepon_publik": "081234567890"
    }
  }
}
```

**Response Headers:**
```
Warning: 299 - "Deprecated fields (@deprecated) will be removed in v2.0"
```

### 4. Setoran Sampah Status Updates

**Old Status → New Status:**
- `pending` / `requested` → `pengajuan`
- `processing` / `in_progress` → `diproses`
- `done` / `completed` → `selesai`
- New: `dibatalkan`

**Endpoints Updated:**
- Get Ongoing Setoran - Returns only `pengajuan` and `diproses`
- Get Setoran History - Returns only `selesai` and `dibatalkan`
- All responses use new status values

## 🔧 Tips & Tricks

### Auto-Save Token
Both login methods automatically save the token to `{{token}}` variable. You don't need to copy-paste manually!

### Check Console
After login, check Postman Console (View → Show Postman Console) to see:
- ✅ Login successful messages
- ❌ Error messages
- Token save confirmation

### Environment Variables
Use `{{variable_name}}` in requests to reference environment variables:
- `{{base_url}}` - API base URL
- `{{token}}` - Authentication token
- `{{test_email}}` - Test email
- `{{test_password}}` - Test password

### Testing Flow
1. **Register** new user (or use existing)
2. **Login** with email/password OR Firebase
3. Token auto-saved ✅
4. Test authenticated endpoints
5. **Logout** when done

## 📝 Notes

### Deprecated Fields
Some Bank Sampah endpoints include `@deprecated` object with old field names. These will be removed in v2.0. Update your app to use `contact_info` instead.

### Status Migration
If you're using old status values in your database, run the migration:
```bash
php artisan migrate
```

This will update all existing status values to the new format.

### Backward Compatibility
All changes maintain backward compatibility for one release cycle:
- Old fields still present in `@deprecated` object
- Warning headers indicate deprecation
- Mobile apps have time to migrate

## 🐛 Troubleshooting

### Token Not Saved
1. Check Postman Console for errors
2. Verify response format matches expected structure
3. Make sure environment is selected (top right dropdown)

### 401 Unauthorized
1. Check if token is set in environment variables
2. Try logging in again
3. Verify token hasn't expired

### 404 Not Found
1. Check `base_url` in environment
2. Verify endpoint path is correct
3. Make sure Laravel server is running

## 📚 Additional Resources

- **API Documentation**: `docs/API_DOCUMENTATION.md`
- **Firebase Auth Guide**: `docs/FIREBASE_AUTH_API.md`
- **Deployment Guide**: `docs/DEPLOYMENT_VPS_UPDATE.md`

## 🎯 Quick Test Checklist

- [ ] Import collection and environment
- [ ] Select Development environment
- [ ] Update test credentials in environment
- [ ] Test Email/Password login
- [ ] Verify token auto-saved
- [ ] Test Firebase Google Sign-In (if available)
- [ ] Test Bank Sampah endpoints (check new response format)
- [ ] Test Setoran Sampah endpoints (check new status values)
- [ ] Check Warning headers in responses
- [ ] Test authenticated endpoints

---

**Last Updated**: January 27, 2026  
**Version**: 2.1 (Complete API Coverage + Firebase Auth + API Improvements)
