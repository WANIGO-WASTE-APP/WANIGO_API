# 🚀 DEPLOY SEKARANG - Detail Setoran API Update

## Ikuti Langkah Ini Satu Per Satu

---

## ✅ STEP 1: Commit & Push (Di Local)

Buka terminal di folder project lokal Anda, lalu jalankan:

```bash
# Add semua perubahan
git add .

# Commit
git commit -m "feat: Add nama_bank_sampah and kode_setoran to Detail Setoran API endpoints"

# Push ke repository
git push origin main
```

> **Catatan:** Ganti `main` dengan `master` jika branch Anda bernama master

**✓ Berhasil jika:** Tidak ada error dan muncul pesan "Everything up-to-date" atau commit berhasil di-push

---

## ✅ STEP 2: Login ke VPS

```bash
ssh username@your-vps-ip
```

Masukkan password VPS Anda.

**Contoh:**
```bash
ssh root@103.xxx.xxx.xxx
# atau
ssh wanigo@yourdomain.com
```

**✓ Berhasil jika:** Anda masuk ke terminal VPS

---

## ✅ STEP 3: Navigasi ke Folder Project

```bash
cd /var/www/wanigo-api
```

> **Catatan:** Sesuaikan path dengan lokasi project Anda. Jika lupa, cari dengan:
> ```bash
> find /var/www -name "artisan" 2>/dev/null
> ```

**✓ Berhasil jika:** Command `ls` menampilkan file `artisan`

---

## ✅ STEP 4: Backup Database

```bash
# Ganti DB_USER dan DB_NAME dengan kredensial database Anda
mysqldump -u DB_USER -p DB_NAME > backup_detail_setoran_$(date +%Y%m%d_%H%M%S).sql
```

Masukkan password database ketika diminta.

**Contoh:**
```bash
mysqldump -u wanigo_user -p wanigo_db > backup_detail_setoran_$(date +%Y%m%d_%H%M%S).sql
```

**✓ Berhasil jika:** File backup muncul saat Anda jalankan `ls backup_*.sql`

---

## ✅ STEP 5: Pull Perubahan dari Git

```bash
# Cek branch saat ini
git branch

# Pull perubahan terbaru
git pull origin main
```

> **Catatan:** Ganti `main` dengan `master` jika branch Anda bernama master

**✓ Berhasil jika:** Muncul pesan "Already up to date" atau file-file ter-update

**Jika ada error "local changes":**
```bash
git stash
git pull origin main
```

---

## ✅ STEP 6: Clear Cache Laravel

```bash
# Clear semua cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild cache
php artisan config:cache
php artisan route:cache
```

**✓ Berhasil jika:** Setiap command menampilkan pesan sukses

---

## ✅ STEP 7: Restart Services

```bash
# Restart PHP-FPM (sesuaikan versi PHP Anda)
sudo systemctl restart php8.1-fpm

# Restart Nginx
sudo systemctl restart nginx
```

> **Catatan:** 
> - Ganti `php8.1-fpm` dengan versi PHP Anda (bisa `php8.2-fpm`, dll)
> - Ganti `nginx` dengan `apache2` jika menggunakan Apache

**✓ Berhasil jika:** Tidak ada error dan services running

**Cek status:**
```bash
sudo systemctl status php8.1-fpm
sudo systemctl status nginx
```

---

## ✅ STEP 8: Verifikasi Deployment

### 8.1 Cek File Controller

```bash
grep -n "nama_bank_sampah" app/Http/Controllers/API/Nasabah/DetailSetoranController.php
```

**✓ Berhasil jika:** Menampilkan beberapa baris yang mengandung `nama_bank_sampah`

### 8.2 Cek Laravel Log

```bash
tail -20 storage/logs/laravel.log
```

**✓ Berhasil jika:** Tidak ada error baru

---

## ✅ STEP 9: Test Endpoint

### Option A: Test dengan Curl

```bash
# Login dulu untuk dapat token
curl -X POST https://your-domain.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'
```

Copy token dari response, lalu:

```bash
# Test endpoint (ganti YOUR_TOKEN dan your-domain.com)
curl -X GET "https://your-domain.com/api/nasabah/detail-setoran/1/detail" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

**✓ Berhasil jika:** Response JSON memiliki field `nama_bank_sampah` dan `kode_setoran`

### Option B: Test dengan Postman

1. Buka Postman
2. Import collection terbaru: `postman/WANIGO_API_Complete.postman_collection.json`
3. Set environment ke Production
4. Login untuk dapat token
5. Test endpoint "Get Item Detail"
6. Verifikasi response memiliki field baru

---

## ✅ STEP 10: Monitor (Opsional)

Buka terminal baru dan monitor log real-time:

```bash
ssh username@your-vps-ip
cd /var/www/wanigo-api
tail -f storage/logs/laravel.log
```

Biarkan terminal ini terbuka sambil Anda test endpoint di Postman.

**✓ Berhasil jika:** Tidak ada error muncul saat test endpoint

---

## 🎉 DEPLOYMENT SELESAI!

Jika semua step di atas berhasil, deployment Anda sudah selesai!

### Field Baru yang Tersedia:

1. **GET /api/nasabah/detail-setoran/{id}/detail**
   - ✅ `nama_bank_sampah` (string|null)
   - ✅ `kode_setoran` (string|null)

2. **POST /api/nasabah/detail-setoran/by-setoran**
   - ✅ `nama_bank_sampah` di object `setoran` (string|null)

---

## ⚠️ Jika Ada Masalah

### Response Tidak Berubah?

```bash
# Clear cache lagi
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Restart services
sudo systemctl restart php8.1-fpm nginx
```

### Error 500?

```bash
# Cek log
tail -50 storage/logs/laravel.log
```

### Ingin Rollback?

```bash
# Lihat commit sebelumnya
git log --oneline -5

# Rollback (ganti COMMIT_HASH dengan hash commit sebelumnya)
git reset --hard COMMIT_HASH

# Clear cache & restart
php artisan cache:clear
sudo systemctl restart php8.1-fpm nginx
```

---

## 📞 Butuh Bantuan?

Lihat dokumentasi lengkap:
- `docs/QUICK_DEPLOY_DETAIL_SETORAN.md` - Quick reference
- `docs/DEPLOYMENT_DETAIL_SETORAN_UPDATE.md` - Full guide
- `postman/DETAIL_SETORAN_API_UPDATES.md` - API changes

---

## ✅ Deployment Checklist

Centang setelah selesai:

- [ ] Git push berhasil (local)
- [ ] Login ke VPS berhasil
- [ ] Backup database berhasil
- [ ] Git pull berhasil
- [ ] Cache di-clear
- [ ] Services di-restart
- [ ] Controller file terupdate
- [ ] Test endpoint berhasil
- [ ] Field baru muncul di response
- [ ] Tidak ada error di log
- [ ] Tim sudah diinformasikan

---

**Deployment Date:** _______________  
**Deployed By:** _______________  
**Status:** [ ] Success [ ] Failed  
**Notes:** _______________
