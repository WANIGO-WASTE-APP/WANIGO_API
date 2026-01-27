# Firebase Setup Guide - Panduan Lengkap dari Awal

Panduan ini akan membantu kamu setup Firebase Authentication untuk WANIGO API dari nol sampai jalan.

## 📋 Daftar Isi

1. [Buat Project Firebase](#1-buat-project-firebase)
2. [Enable Google Sign-In](#2-enable-google-sign-in)
3. [Download Service Account Key](#3-download-service-account-key)
4. [Setup Laravel Backend](#4-setup-laravel-backend)
5. [Testing](#5-testing)
6. [Troubleshooting](#6-troubleshooting)

---

## 1. Buat Project Firebase

### Step 1.1: Buka Firebase Console
1. Buka browser, ke [https://console.firebase.google.com](https://console.firebase.google.com)
2. Login dengan akun Google kamu
3. Klik **"Add project"** atau **"Tambah project"**

### Step 1.2: Isi Detail Project
1. **Project name**: Ketik nama project (contoh: `wanigo-app`)
2. Klik **Continue**
3. **Google Analytics**: Bisa di-disable (tidak wajib untuk auth)
4. Klik **Create project**
5. Tunggu beberapa detik sampai project selesai dibuat
6. Klik **Continue**

✅ **Project Firebase sudah jadi!**

---

## 2. Enable Google Sign-In

### Step 2.1: Masuk ke Authentication
1. Di sidebar kiri, klik **"Build"** → **"Authentication"**
2. Klik tombol **"Get started"**

### Step 2.2: Enable Google Provider
1. Klik tab **"Sign-in method"**
2. Cari **"Google"** di daftar providers
3. Klik **"Google"**
4. Toggle **"Enable"** jadi ON (warna biru)
5. **Project support email**: Pilih email kamu dari dropdown
6. Klik **"Save"**

✅ **Google Sign-In sudah aktif!**

---

## 3. Download Service Account Key

### Step 3.1: Buka Project Settings
1. Klik icon **⚙️ (gear/roda gigi)** di sidebar kiri atas
2. Klik **"Project settings"**

### Step 3.2: Buka Service Accounts
1. Klik tab **"Service accounts"** (tab ke-4)
2. Scroll ke bawah sampai ketemu tombol **"Generate new private key"**

### Step 3.3: Download File JSON
1. Klik **"Generate new private key"**
2. Popup muncul, klik **"Generate key"**
3. File JSON akan otomatis terdownload (nama: `wanigo-app-xxxxx.json`)

⚠️ **PENTING**: File ini berisi private key, jangan share ke siapapun!

### Step 3.4: Simpan File ke Project Laravel
1. Buka folder project Laravel kamu
2. Masuk ke folder `storage/app/`
3. Rename file JSON yang tadi didownload jadi: **`firebase-credentials.json`**
4. Copy file tersebut ke `storage/app/firebase-credentials.json`

Struktur folder:
```
your-laravel-project/
├── storage/
│   ├── app/
│   │   ├── firebase-credentials.json  ← File JSON kamu di sini
│   │   └── ...
```

✅ **Service Account Key sudah tersimpan!**

---

## 4. Setup Laravel Backend

### Step 4.1: Update File .env

Buka file `.env` di root project Laravel, tambahkan baris ini di bagian bawah:

```env
# Firebase Configuration
FIREBASE_CREDENTIALS=storage/app/firebase-credentials.json
FIREBASE_PROJECT_ID=wanigo-app
```

⚠️ Ganti `wanigo-app` dengan **Project ID** kamu yang sebenarnya!

**Cara cek Project ID:**
1. Buka Firebase Console
2. Klik ⚙️ → Project settings
3. Lihat di bagian **"Project ID"** (bukan Project name!)

### Step 4.2: Update .env.example (Opsional)

Buka `.env.example`, tambahkan contoh variable (tanpa value asli):

```env
# Firebase Configuration
FIREBASE_CREDENTIALS=storage/app/firebase-credentials.json
FIREBASE_PROJECT_ID=your-project-id
```

### Step 4.3: Pastikan .gitignore Sudah Benar

Buka `.gitignore`, pastikan ada baris ini:

```
/storage/*.key
/storage/app/firebase-credentials.json
```

Kalau belum ada, tambahkan baris tersebut.

### Step 4.4: Clear Config Cache

Jalankan command ini di terminal:

```bash
php artisan config:clear
php artisan cache:clear
```

✅ **Laravel backend sudah siap!**

---

## 5. Testing

### Step 5.1: Test dengan Postman

1. **Import Postman Collection**
   - Buka Postman
   - Import file: `postman/WANIGO_API_Complete.postman_collection.json`
   - Import environment: `postman/WANIGO_API_Development.postman_environment.json`

2. **Pilih Environment**
   - Klik dropdown environment (kanan atas)
   - Pilih **"WANIGO API - Development"**

3. **Dapatkan Firebase ID Token**
   
   Untuk testing, kamu perlu ID token dari Firebase. Ada 2 cara:

   **Cara A: Dari Mobile App (Recommended)**
   - Jika sudah ada mobile app dengan Firebase SDK
   - Login dengan Google di app
   - Print/log ID token yang didapat
   - Copy token tersebut

   **Cara B: Dari Firebase Auth Emulator (Development)**
   - Install Firebase CLI: `npm install -g firebase-tools`
   - Setup emulator: `firebase init emulators`
   - Jalankan: `firebase emulators:start`
   - Buka UI emulator untuk generate test token

4. **Test Endpoint**
   - Buka request: **"1. Authentication"** → **"Firebase Google Sign-In (NEW)"**
   - Paste ID token di request body:
     ```json
     {
       "id_token": "eyJhbGciOiJSUzI1NiIsImtpZCI6..."
     }
     ```
   - Klik **Send**
   - Jika berhasil, response:
     ```json
     {
       "success": true,
       "message": "Login success",
       "data": {
         "token": "1|abcdef...",
         "token_type": "Bearer",
         "user": {...}
       }
     }
     ```

✅ **Firebase Authentication berhasil!**

---

## 6. Troubleshooting

### Error: "Firebase credentials file not found"

**Penyebab**: File `firebase-credentials.json` tidak ditemukan

**Solusi**:
1. Cek apakah file ada di `storage/app/firebase-credentials.json`
2. Cek path di `.env` sudah benar: `FIREBASE_CREDENTIALS=storage/app/firebase-credentials.json`
3. Jalankan: `php artisan config:clear`

---

### Error: "Invalid or expired Firebase token"

**Penyebab**: ID token tidak valid atau sudah expired

**Solusi**:
1. Firebase ID token hanya valid 1 jam
2. Generate token baru dari mobile app
3. Pastikan token yang dikirim lengkap (tidak terpotong)

---

### Error: "Security configuration error"

**Penyebab**: APP_DEBUG=true di production

**Solusi**:
1. Buka `.env`
2. Set `APP_DEBUG=false` untuk production
3. Set `APP_ENV=production`
4. Jalankan: `php artisan config:clear`

---

### Error: "Project ID mismatch"

**Penyebab**: Project ID di `.env` tidak sesuai dengan Firebase

**Solusi**:
1. Buka Firebase Console → ⚙️ → Project settings
2. Copy **Project ID** yang benar
3. Update `FIREBASE_PROJECT_ID` di `.env`
4. Jalankan: `php artisan config:clear`

---

### Error: "Permission denied" saat akses file JSON

**Penyebab**: File permissions tidak benar

**Solusi** (Linux/Mac):
```bash
chmod 644 storage/app/firebase-credentials.json
```

**Solusi** (Windows):
- Klik kanan file → Properties → Security
- Pastikan user kamu punya Read permission

---

## 📚 Referensi Tambahan

- **Firebase Console**: https://console.firebase.google.com
- **Firebase Admin SDK Docs**: https://firebase.google.com/docs/admin/setup
- **API Documentation**: `docs/API_DOCUMENTATION.md`
- **Postman Guide**: `postman/README_POSTMAN.md`

---

## 🎯 Checklist Setup

Gunakan checklist ini untuk memastikan semua sudah benar:

- [ ] Project Firebase sudah dibuat
- [ ] Google Sign-In sudah di-enable di Firebase Console
- [ ] Service Account Key sudah didownload
- [ ] File `firebase-credentials.json` ada di `storage/app/`
- [ ] `.env` sudah diupdate dengan `FIREBASE_CREDENTIALS` dan `FIREBASE_PROJECT_ID`
- [ ] `.gitignore` sudah include `firebase-credentials.json`
- [ ] Config cache sudah di-clear
- [ ] Test endpoint berhasil dengan Postman

---

**Butuh bantuan?** Cek section Troubleshooting atau baca dokumentasi lengkap di `docs/API_DOCUMENTATION.md`

**Last Updated**: January 27, 2026
