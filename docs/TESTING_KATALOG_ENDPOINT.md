# Testing Katalog Endpoint - Panduan Lengkap

## Status Perbaikan
✅ **Perbaikan telah diterapkan** pada `KatalogSampahController::getKatalogByBank()`

### Perubahan yang Dilakukan:
1. **Menghapus konflik query builder**: Tidak lagi menggunakan `leftJoin` + `load()` yang menyebabkan konflik
2. **Menggunakan eager loading**: Relasi `subKategoriSampah` di-load langsung dengan `with()`
3. **Sorting yang lebih aman**: Sorting berdasarkan `urutan` dilakukan setelah data ter-load
4. **Error handling yang lebih baik**: Log error lebih detail untuk debugging

---

## Langkah Testing

### 1. Jalankan Laravel Server

```bash
php artisan serve
```

Server akan berjalan di `http://localhost:8000`

---

### 2. Test Debug Endpoint (Tanpa Auth)

**Endpoint:**
```
GET http://localhost:8000/test-katalog
```

**Cara Test:**
- Buka di browser: `http://localhost:8000/test-katalog`
- Atau gunakan curl:
  ```bash
  curl http://localhost:8000/test-katalog
  ```

**Expected Response:**
```json
{
  "test": "success",
  "katalog_count": 10,
  "katalog_found": true,
  "katalog_data": { ... },
  "resource_data": { ... },
  "sub_kategori_loaded": true
}
```

**Jika Gagal:**
- Cek apakah ada data di tabel `katalog_sampah` untuk `bank_sampah_id = 1`
- Cek apakah relasi `sub_kategori_sampah_id` valid
- Lihat error di response JSON

---

### 3. Test Endpoint Asli (Dengan Auth)

**Endpoint:**
```
GET http://localhost:8000/api/bank-sampah/{bank_sampah_id}/katalog
```

**Query Parameters:**
- `kategori` (optional): `kering` | `basah` | `semua` (default: `semua`)
- `sub_kategori_id` (optional): integer
- `per_page` (optional): integer (default: 20, max: 100)
- `page` (optional): integer (default: 1)

**Headers Required:**
```
Authorization: Bearer {your_token}
Accept: application/json
```

**Contoh Request di Postman:**

1. **URL:**
   ```
   GET http://localhost:8000/api/bank-sampah/1/katalog?kategori=kering&per_page=20&page=1
   ```

2. **Headers:**
   - Key: `Authorization`, Value: `Bearer eyJ0eXAiOiJKV1QiLCJhbGc...` (token dari login)
   - Key: `Accept`, Value: `application/json`

3. **Expected Response (Success):**
   ```json
   {
     "success": true,
     "message": "Katalog sampah berhasil diambil",
     "data": [
       {
         "id": 1,
         "nama": "Botol Plastik",
         "harga": 3000,
         "deskripsi": "Botol plastik bekas minuman",
         "gambar_url": "http://localhost:8000/storage/...",
         "kategori_sampah": "kering",
         "sub_kategori": {
           "id": 1,
           "nama": "Plastik PET",
           "slug": "plastik-pet",
           "icon": "♻️",
           "warna": "#3B82F6"
         }
       }
     ],
     "meta": {
       "current_page": 1,
       "per_page": 20,
       "total": 50,
       "last_page": 3,
       "from": 1,
       "to": 20
     }
   }
   ```

4. **Expected Response (Error - Unauthorized):**
   ```json
   {
     "message": "Unauthenticated."
   }
   ```
   → Ini normal jika token tidak valid atau tidak ada

---

### 4. Test Berbagai Skenario

#### Skenario 1: Semua Kategori
```
GET /api/bank-sampah/1/katalog?kategori=semua&per_page=20
```

#### Skenario 2: Hanya Kategori Kering
```
GET /api/bank-sampah/1/katalog?kategori=kering&per_page=20
```

#### Skenario 3: Hanya Kategori Basah
```
GET /api/bank-sampah/1/katalog?kategori=basah&per_page=20
```

#### Skenario 4: Filter by Sub-Kategori
```
GET /api/bank-sampah/1/katalog?kategori=kering&sub_kategori_id=1&per_page=20
```

#### Skenario 5: Pagination
```
GET /api/bank-sampah/1/katalog?kategori=kering&per_page=10&page=2
```

---

### 5. Cek Laravel Logs (Jika Error)

Jika endpoint masih error, cek log Laravel:

```bash
type storage\logs\laravel.log
```

Atau lihat 50 baris terakhir:
```bash
Get-Content storage\logs\laravel.log -Tail 50
```

**Yang Perlu Dicari:**
- `Error in getKatalogByBank:` → Error message dari controller
- `SQLSTATE` → Error database
- `Call to undefined method` → Error method tidak ditemukan
- `Trying to get property of non-object` → Error relasi null

---

## Troubleshooting

### Error: "Server Error" atau 500
**Kemungkinan Penyebab:**
1. Relasi `subKategoriSampah` tidak ditemukan
2. Data `sub_kategori_sampah_id` di tabel `katalog_sampah` NULL atau invalid
3. Accessor `gambar_item_sampah_url` error

**Solusi:**
1. Cek data di database:
   ```sql
   SELECT id, nama_item_sampah, sub_kategori_sampah_id, bank_sampah_id 
   FROM katalog_sampah 
   WHERE bank_sampah_id = 1 
   LIMIT 5;
   ```

2. Cek apakah `sub_kategori_sampah_id` valid:
   ```sql
   SELECT ks.id, ks.nama_item_sampah, ks.sub_kategori_sampah_id, sks.nama_sub_kategori
   FROM katalog_sampah ks
   LEFT JOIN sub_kategori_sampah sks ON ks.sub_kategori_sampah_id = sks.id
   WHERE ks.bank_sampah_id = 1
   LIMIT 5;
   ```

3. Lihat Laravel log untuk detail error

---

### Error: "Unauthenticated"
**Penyebab:** Token Bearer tidak ada atau tidak valid

**Solusi:**
1. Login terlebih dahulu untuk mendapatkan token:
   ```
   POST /api/login
   Body: {
     "email": "user@example.com",
     "password": "password"
   }
   ```

2. Copy token dari response
3. Tambahkan ke header: `Authorization: Bearer {token}`

---

### Error: Tidak Ada Data
**Penyebab:** Tidak ada data katalog untuk `bank_sampah_id` yang diminta

**Solusi:**
1. Cek data di database:
   ```sql
   SELECT COUNT(*) FROM katalog_sampah WHERE bank_sampah_id = 1;
   ```

2. Jika kosong, jalankan seeder:
   ```bash
   php artisan db:seed --class=KatalogSampahSeeder
   ```

---

## Checklist Testing

- [ ] Server Laravel berjalan (`php artisan serve`)
- [ ] Test debug endpoint berhasil (`/test-katalog`)
- [ ] Login dan dapatkan Bearer token
- [ ] Test endpoint asli dengan token
- [ ] Test filter kategori: `kering`, `basah`, `semua`
- [ ] Test filter sub_kategori_id
- [ ] Test pagination (page 1, 2, 3)
- [ ] Verifikasi response sesuai format yang diharapkan
- [ ] Cek Laravel log tidak ada error

---

## Setelah Testing Berhasil

Jika semua test berhasil, **hapus file test route**:

```bash
del routes\test.php
```

Dan pastikan route test tidak ter-load di `routes/web.php` atau `routes/api.php`.

---

## Kontak & Support

Jika masih ada masalah, berikan informasi berikut:
1. Response dari `/test-katalog`
2. Response dari endpoint asli
3. Log error dari `storage/logs/laravel.log`
4. Screenshot Postman request & response
