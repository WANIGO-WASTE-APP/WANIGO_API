# Deployment: Detail Setoran API Improvements ke VPS

## Overview Update

Update ini menambahkan field baru pada endpoint Detail Setoran:
- ✅ `nama_bank_sampah` dan `kode_setoran` pada GET /api/nasabah/detail-setoran/{id}/detail
- ✅ `nama_bank_sampah` pada POST /api/nasabah/detail-setoran/by-setoran

**Catatan**: Update ini **TIDAK memerlukan migration database** karena hanya menambahkan field di response API.

---

## Pre-Deployment Checklist

- [ ] Semua test lokal sudah passing
- [ ] Perubahan sudah di-commit ke Git
- [ ] Backup VPS sudah disiapkan
- [ ] Akses SSH ke VPS tersedia

---

## Step 1: Commit dan Push ke Git Repository

```bash
# Di local machine
cd /path/to/wanigo-api

# Cek status perubahan
git status

# Add semua file yang berubah
git add app/Http/Controllers/API/Nasabah/DetailSetoranController.php
git add tests/Feature/DetailSetoranGetItemDetailTest.php
git add tests/Feature/DetailSetoranGetBySetoranTest.php
git add docs/API_DOCUMENTATION.md
git add postman/WANIGO_API_Complete.postman_collection.json
git add postman/WANIGO_API\ \(Full\ Access\).postman_collection.json
git add postman/DETAIL_SETORAN_API_UPDATES.md

# Commit dengan pesan yang jelas
git commit -m "feat: Add nama_bank_sampah and kode_setoran to Detail Setoran API endpoints

- Add nama_bank_sampah and kode_setoran to GET /api/nasabah/detail-setoran/{id}/detail
- Add nama_bank_sampah to POST /api/nasabah/detail-setoran/by-setoran
- Add eager loading for bankSampah relationship
- Update API documentation
- Update Postman collections
- Add comprehensive tests"

# Push ke repository
git push origin main
# atau sesuaikan dengan branch Anda: git push origin master
```

---

## Step 2: Login ke VPS

```bash
# Login via SSH
ssh username@your-vps-ip

# Contoh:
# ssh root@103.xxx.xxx.xxx
# atau
# ssh wanigo@your-domain.com
```

Masukkan password VPS Anda.

---

## Step 3: Backup Database (PENTING!)

Meskipun update ini tidak mengubah database, backup tetap penting untuk keamanan.

```bash
# Navigasi ke direktori project
cd /var/www/wanigo-api
# atau sesuaikan dengan lokasi project Anda

# Backup database
mysqldump -u DB_USERNAME -p DB_NAME > backup_detail_setoran_$(date +%Y%m%d_%H%M%S).sql

# Contoh:
# mysqldump -u wanigo_user -p wanigo_db > backup_detail_setoran_20260204_100000.sql
```

**Verifikasi backup berhasil:**
```bash
ls -lh backup_detail_setoran_*.sql
```

---

## Step 4: Pull Perubahan dari Git

```bash
# Pastikan di direktori project
pwd
# Output harus: /var/www/wanigo-api (atau lokasi project Anda)

# Cek branch saat ini
git branch

# Stash perubahan lokal jika ada (opsional)
git stash

# Pull perubahan terbaru
git pull origin main
# atau: git pull origin master

# Verifikasi perubahan berhasil di-pull
git log -1 --oneline
# Harus menampilkan commit terbaru tentang Detail Setoran
```

**Jika ada conflict:**
```bash
# Lihat file yang conflict
git status

# Jika yakin ingin overwrite dengan versi remote
git fetch --all
git reset --hard origin/main
```

---

## Step 5: Update Dependencies (Opsional)

Karena tidak ada perubahan di `composer.json`, step ini bisa dilewati. Tapi untuk memastikan:

```bash
# Update autoloader saja
composer dump-autoload --optimize
```

---

## Step 6: Clear All Cache

**PENTING**: Cache harus di-clear agar perubahan controller terdeteksi.

```bash
# Clear semua cache Laravel
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild cache untuk production
php artisan config:cache
php artisan route:cache

# Verifikasi cache berhasil di-clear
php artisan cache:clear
echo "Cache cleared successfully"
```

---

## Step 7: Set Permissions (Jika Perlu)

```bash
# Set ownership untuk web server
sudo chown -R www-data:www-data storage bootstrap/cache

# Set permissions
sudo chmod -R 775 storage bootstrap/cache

# Untuk Nginx, gunakan:
# sudo chown -R nginx:nginx storage bootstrap/cache
```

---

## Step 8: Restart Services

```bash
# Restart PHP-FPM (sesuaikan versi PHP Anda)
sudo systemctl restart php8.1-fpm
# atau: sudo systemctl restart php8.2-fpm

# Restart Nginx
sudo systemctl restart nginx

# Atau jika menggunakan Apache:
# sudo systemctl restart apache2

# Verifikasi service berjalan
sudo systemctl status php8.1-fpm
sudo systemctl status nginx
```

---

## Step 9: Verifikasi Deployment

### 9.1 Cek Git Commit

```bash
# Verifikasi commit terbaru
git log -1 --pretty=format:"%h - %s (%cr)" --abbrev-commit
```

### 9.2 Cek File Controller

```bash
# Verifikasi file controller sudah terupdate
grep -n "nama_bank_sampah" app/Http/Controllers/API/Nasabah/DetailSetoranController.php

# Harus menampilkan baris yang mengandung:
# - $namaBankSampah = ...
# - 'nama_bank_sampah' => $namaBankSampah
```

### 9.3 Test Endpoint dengan Curl

**Test 1: Get Single Item Detail**

```bash
# Login dulu untuk dapat token
curl -X POST https://your-domain.com/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password"
  }'

# Copy token dari response, lalu test endpoint
curl -X GET "https://your-domain.com/api/nasabah/detail-setoran/1/detail" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json" | jq

# Verifikasi response memiliki:
# - data.nama_bank_sampah
# - data.kode_setoran
```

**Test 2: Get All Items by Setoran**

```bash
curl -X POST "https://your-domain.com/api/nasabah/detail-setoran/by-setoran" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "setoran_sampah_id": 1
  }' | jq

# Verifikasi response memiliki:
# - data.setoran.nama_bank_sampah
```

---

## Step 10: Monitor Logs

```bash
# Monitor Laravel log real-time
tail -f storage/logs/laravel.log

# Di terminal lain, test endpoint
# Pastikan tidak ada error di log

# Monitor Nginx access log
sudo tail -f /var/log/nginx/access.log

# Monitor Nginx error log
sudo tail -f /var/log/nginx/error.log
```

---

## Testing dengan Postman

### Import Updated Collection

1. Download file collection terbaru dari repository:
   - `postman/WANIGO_API_Complete.postman_collection.json`

2. Import ke Postman:
   - Buka Postman
   - Klik **Import**
   - Pilih file collection
   - Replace collection yang lama

3. Set Environment:
   - Pilih environment Production
   - Set `base_url` ke URL VPS Anda

4. Test Endpoints:
   - Login untuk dapat token
   - Test "Get Item Detail" - verifikasi ada `nama_bank_sampah` dan `kode_setoran`
   - Test "Get Detail By Setoran" - verifikasi ada `nama_bank_sampah` di object `setoran`

---

## Troubleshooting

### Error: "Class not found" atau "Method not found"

```bash
# Clear autoloader dan rebuild
composer dump-autoload --optimize
php artisan clear-compiled
php artisan cache:clear
php artisan config:clear
```

### Error: "500 Internal Server Error"

```bash
# Cek Laravel log
tail -50 storage/logs/laravel.log

# Cek PHP error log
sudo tail -50 /var/log/php8.1-fpm.log

# Cek Nginx error log
sudo tail -50 /var/log/nginx/error.log
```

### Response Tidak Berubah (Masih Response Lama)

```bash
# Clear semua cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Restart PHP-FPM
sudo systemctl restart php8.1-fpm

# Clear browser cache atau test dengan curl
```

### Git Pull Failed

```bash
# Cek status
git status

# Jika ada perubahan lokal yang conflict
git stash
git pull origin main
git stash pop

# Atau reset ke remote (HATI-HATI!)
git fetch --all
git reset --hard origin/main
```

---

## Rollback (Jika Diperlukan)

### Rollback Git

```bash
# Lihat commit history
git log --oneline -5

# Rollback ke commit sebelumnya
git reset --hard COMMIT_HASH_SEBELUMNYA

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Restart services
sudo systemctl restart php8.1-fpm
sudo systemctl restart nginx
```

### Restore Database (Jika Perlu)

```bash
# Restore dari backup
mysql -u DB_USERNAME -p DB_NAME < backup_detail_setoran_20260204_100000.sql
```

---

## Post-Deployment Checklist

- [ ] Git pull berhasil tanpa error
- [ ] Cache Laravel sudah di-clear
- [ ] PHP-FPM dan Nginx sudah di-restart
- [ ] Endpoint GET /api/nasabah/detail-setoran/{id}/detail mengembalikan `nama_bank_sampah` dan `kode_setoran`
- [ ] Endpoint POST /api/nasabah/detail-setoran/by-setoran mengembalikan `nama_bank_sampah` di object `setoran`
- [ ] Tidak ada error di Laravel log
- [ ] Tidak ada error di Nginx/PHP-FPM log
- [ ] Response time endpoint masih normal (tidak ada performance issue)
- [ ] Postman collection sudah diupdate dan ditest
- [ ] Tim development sudah diinformasikan tentang field baru

---

## Performance Monitoring

### Cek Query Performance

```bash
# Enable query log sementara di .env (HANYA UNTUK TESTING)
# DB_LOG_QUERIES=true

# Atau cek dengan Laravel Telescope jika terinstall
php artisan telescope:install
```

### Monitor Response Time

```bash
# Test response time dengan curl
time curl -X GET "https://your-domain.com/api/nasabah/detail-setoran/1/detail" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -o /dev/null -s -w "Time: %{time_total}s\n"

# Response time harus < 1 detik untuk endpoint ini
```

---

## Dokumentasi untuk Tim

Setelah deployment berhasil, informasikan ke tim:

1. **Field Baru yang Tersedia:**
   - `nama_bank_sampah` (string|null) - Nama bank sampah
   - `kode_setoran` (string|null) - Kode setoran sampah

2. **Endpoint yang Terpengaruh:**
   - GET /api/nasabah/detail-setoran/{id}/detail
   - POST /api/nasabah/detail-setoran/by-setoran

3. **Backward Compatibility:**
   - Semua field lama tetap ada
   - Tidak ada breaking changes
   - Aplikasi mobile lama tetap berfungsi

4. **Dokumentasi:**
   - API Documentation: `docs/API_DOCUMENTATION.md`
   - Postman Collection: `postman/WANIGO_API_Complete.postman_collection.json`
   - Update Notes: `postman/DETAIL_SETORAN_API_UPDATES.md`

---

## Kontak Darurat

Jika ada masalah serius:
1. ✅ Rollback ke commit sebelumnya
2. ✅ Restore database dari backup (jika perlu)
3. ✅ Restart semua services
4. ✅ Hubungi tim DevOps/Infrastructure
5. ✅ Dokumentasikan issue di issue tracker

---

## Summary

Update ini menambahkan informasi bank sampah dan kode setoran pada response API Detail Setoran untuk memberikan informasi yang lebih lengkap kepada aplikasi mobile tanpa perlu melakukan request tambahan.

**Deployment Time Estimate:** 10-15 menit
**Downtime:** Tidak ada (zero downtime deployment)
**Risk Level:** Low (hanya menambah field, tidak mengubah struktur existing)

---

**Deployment Date:** _________________
**Deployed By:** _________________
**Status:** [ ] Success [ ] Failed [ ] Rolled Back
**Notes:** _________________
