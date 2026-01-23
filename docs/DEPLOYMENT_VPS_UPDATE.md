# Panduan Update Kode ke VPS

## Persiapan di Local

### 1. Commit Perubahan ke Git

```bash
# Cek status file yang berubah
git status

# Add semua perubahan
git add .

# Atau add file spesifik
git add app/Http/Controllers/API/Nasabah/KatalogSampahController.php
git add TESTING_KATALOG_ENDPOINT.md
git add check-database.sql
git add test-endpoint.php

# Commit dengan pesan yang jelas
git commit -m "Fix: Perbaiki error endpoint katalog API - mengganti leftJoin dengan eager loading"

# Push ke repository
git push origin main
# atau
git push origin master
```

**Catatan:** Ganti `main` dengan nama branch kamu (bisa `master`, `main`, atau `development`)

---

## Deployment ke VPS

### 2. Login ke VPS via SSH

```bash
ssh username@your-vps-ip
# Contoh: ssh root@103.xxx.xxx.xxx
```

Masukkan password VPS kamu.

---

### 3. Navigasi ke Direktori Project

```bash
cd /path/to/your/project
# Contoh: cd /var/www/wanigo-api
# atau: cd /home/username/wanigo-api
```

**Cara cek lokasi project:**
```bash
# Jika lupa lokasi project
find /var/www -name "artisan" 2>/dev/null
find /home -name "artisan" 2>/dev/null
```

---

### 4. Backup Database (PENTING!)

```bash
# Backup database sebelum update
php artisan db:backup
# atau manual:
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql
```

**Contoh:**
```bash
mysqldump -u wanigo_user -p wanigo_db > backup_20260123_150000.sql
```

---

### 5. Pull Perubahan dari Git

```bash
# Cek branch saat ini
git branch

# Pull perubahan terbaru
git pull origin main
# atau
git pull origin master
```

**Jika ada conflict:**
```bash
# Lihat file yang conflict
git status

# Jika ingin overwrite dengan versi remote (HATI-HATI!)
git fetch --all
git reset --hard origin/main
```

---

### 6. Update Dependencies (Jika Perlu)

```bash
# Update Composer dependencies
composer install --no-dev --optimize-autoloader

# Atau jika ada perubahan di composer.json
composer update --no-dev --optimize-autoloader
```

---

### 7. Clear Cache Laravel

```bash
# Clear semua cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild cache untuk production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

### 8. Set Permissions (Jika Perlu)

```bash
# Set ownership
sudo chown -R www-data:www-data storage bootstrap/cache

# Set permissions
sudo chmod -R 775 storage bootstrap/cache
```

**Untuk Nginx:**
```bash
sudo chown -R nginx:nginx storage bootstrap/cache
```

---

### 9. Restart Services

```bash
# Restart PHP-FPM
sudo systemctl restart php8.1-fpm
# atau
sudo systemctl restart php8.2-fpm

# Restart Nginx
sudo systemctl restart nginx

# Atau restart Apache
sudo systemctl restart apache2
```

**Cek status service:**
```bash
sudo systemctl status php8.1-fpm
sudo systemctl status nginx
```

---

### 10. Verifikasi Update

```bash
# Cek versi commit terbaru
git log -1 --oneline

# Test endpoint debug (jika ada)
curl http://your-domain.com/test-katalog

# Cek Laravel log
tail -f storage/logs/laravel.log
```

---

## Testing di VPS

### 11. Test Endpoint dengan Curl

**Test tanpa auth (debug endpoint):**
```bash
curl http://your-domain.com/test-katalog
```

**Test dengan auth:**
```bash
# Login dulu untuk dapat token
curl -X POST http://your-domain.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'

# Copy token dari response, lalu test endpoint
curl -X GET "http://your-domain.com/api/bank-sampah/1/katalog?kategori=kering&per_page=20" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

---

## Troubleshooting

### Error: "Permission Denied"

```bash
# Fix permissions
sudo chown -R www-data:www-data /path/to/project
sudo chmod -R 775 storage bootstrap/cache
```

---

### Error: "Git Pull Failed"

```bash
# Stash perubahan lokal
git stash

# Pull lagi
git pull origin main

# Apply stash jika perlu
git stash pop
```

---

### Error: "Composer Install Failed"

```bash
# Update Composer
composer self-update

# Install ulang
rm -rf vendor
composer install --no-dev --optimize-autoloader
```

---

### Error: "500 Internal Server Error"

```bash
# Cek Laravel log
tail -50 storage/logs/laravel.log

# Cek Nginx/Apache error log
sudo tail -50 /var/log/nginx/error.log
# atau
sudo tail -50 /var/log/apache2/error.log

# Cek PHP-FPM log
sudo tail -50 /var/log/php8.1-fpm.log
```

---

### Database Tidak Sync

```bash
# Jalankan migration (jika ada)
php artisan migrate

# Jalankan seeder (jika perlu)
php artisan db:seed --class=SubKategoriSampahSeeder
```

---

## Checklist Deployment

- [ ] Backup database
- [ ] Commit & push perubahan ke Git
- [ ] Login ke VPS via SSH
- [ ] Navigasi ke direktori project
- [ ] Pull perubahan dari Git
- [ ] Update Composer dependencies (jika perlu)
- [ ] Clear semua cache Laravel
- [ ] Set permissions storage & bootstrap/cache
- [ ] Restart PHP-FPM & Nginx/Apache
- [ ] Test endpoint dengan curl atau Postman
- [ ] Cek Laravel log untuk error
- [ ] Verifikasi response API sesuai ekspektasi

---

## Rollback (Jika Ada Masalah)

### Rollback Git

```bash
# Lihat commit history
git log --oneline

# Rollback ke commit sebelumnya
git reset --hard COMMIT_HASH

# Contoh:
git reset --hard abc1234
```

### Restore Database

```bash
# Restore dari backup
mysql -u username -p database_name < backup_20260123_150000.sql
```

---

## Monitoring Setelah Deployment

```bash
# Monitor Laravel log real-time
tail -f storage/logs/laravel.log

# Monitor Nginx access log
sudo tail -f /var/log/nginx/access.log

# Monitor Nginx error log
sudo tail -f /var/log/nginx/error.log
```

---

## Catatan Penting

1. **Selalu backup database** sebelum deployment
2. **Test di local** terlebih dahulu sebelum push ke VPS
3. **Gunakan environment production** di VPS (`.env` dengan `APP_ENV=production`)
4. **Jangan lupa clear cache** setelah update kode
5. **Monitor log** setelah deployment untuk deteksi error cepat
6. **Hapus file test** (`routes/test.php`, `test-endpoint.php`) setelah testing berhasil

---

## Kontak Darurat

Jika ada masalah serius:
1. Rollback ke commit sebelumnya
2. Restore database dari backup
3. Restart semua services
4. Hubungi tim support VPS jika server down
