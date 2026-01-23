# 🚀 Deployment Guide - Update Endpoint Katalog API ke VPS

## 📋 Overview

Panduan ini berisi langkah-langkah untuk deploy perbaikan endpoint katalog API dari local ke VPS.

**Perubahan yang akan di-deploy:**
- ✅ Fix error pada endpoint `GET /api/bank-sampah/{bank_sampah_id}/katalog`
- ✅ Mengganti query builder `leftJoin` dengan eager loading
- ✅ Improve error handling dan logging
- ✅ File testing dan dokumentasi

---

## 🎯 Pilihan Deployment

### Option 1: Otomatis (Recommended) ⭐

**Windows:**
```powershell
.\deploy-to-vps.ps1
```

**Linux/Mac:**
```bash
bash deploy-to-vps.sh
```

Script akan otomatis handle semua proses deployment.

---

### Option 2: Manual

Ikuti panduan di `DEPLOYMENT_VPS_UPDATE.md` untuk step-by-step manual deployment.

**Quick Steps:**
1. Commit & push ke Git
2. SSH ke VPS
3. Pull perubahan
4. Clear cache
5. Restart services
6. Test endpoint

---

## 📁 File yang Tersedia

| File | Deskripsi |
|------|-----------|
| `DEPLOYMENT_VPS_UPDATE.md` | Panduan lengkap deployment manual |
| `QUICK_DEPLOYMENT_GUIDE.md` | Cheat sheet deployment cepat |
| `deploy-to-vps.ps1` | Script otomatis untuk Windows |
| `deploy-to-vps.sh` | Script otomatis untuk Linux/Mac |
| `TESTING_KATALOG_ENDPOINT.md` | Panduan testing endpoint |
| `check-database.sql` | Query untuk verifikasi database |
| `test-endpoint.php` | Script PHP untuk test cepat |

---

## ⚡ Quick Start

### 1. Persiapan di Local

```bash
# Pastikan semua perubahan sudah di-commit
git status

# Jika ada perubahan, commit dulu
git add .
git commit -m "Fix: Perbaiki endpoint katalog API"
git push origin main
```

### 2. Deploy ke VPS

**Cara Otomatis:**
```powershell
# Windows
.\deploy-to-vps.ps1
```

**Cara Manual:**
```bash
# SSH ke VPS
ssh username@vps-ip

# Masuk ke folder project
cd /var/www/wanigo-api

# Pull perubahan
git pull origin main

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Rebuild cache
php artisan config:cache
php artisan route:cache

# Restart services
sudo systemctl restart php8.1-fpm
sudo systemctl restart nginx
```

### 3. Testing

```bash
# Test debug endpoint
curl http://your-domain.com/test-katalog

# Test API endpoint (perlu token)
curl -X GET "http://your-domain.com/api/bank-sampah/1/katalog?kategori=kering" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

---

## 🔍 Verifikasi Deployment

### Cek di VPS

```bash
# Cek commit terbaru
git log -1 --oneline

# Cek Laravel log
tail -50 storage/logs/laravel.log

# Cek service status
sudo systemctl status php8.1-fpm
sudo systemctl status nginx
```

### Expected Results

✅ Git pull berhasil tanpa conflict  
✅ Cache cleared successfully  
✅ Services restarted successfully  
✅ Endpoint `/test-katalog` return success  
✅ API endpoint return data dengan format benar  
✅ Tidak ada error di Laravel log  

---

## 🐛 Troubleshooting

### Problem: Git Pull Failed

```bash
git stash
git pull origin main
git stash pop
```

### Problem: Permission Denied

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Problem: 500 Internal Server Error

```bash
# Cek Laravel log
tail -50 storage/logs/laravel.log

# Cek Nginx error log
sudo tail -50 /var/log/nginx/error.log

# Cek PHP-FPM log
sudo tail -50 /var/log/php8.1-fpm.log
```

### Problem: Endpoint Masih Error

1. Cek apakah ada data di database:
   ```bash
   mysql -u username -p database_name
   ```
   ```sql
   SELECT COUNT(*) FROM katalog_sampah WHERE bank_sampah_id = 1;
   ```

2. Jalankan query di `check-database.sql` untuk verifikasi data

3. Cek Laravel log untuk detail error

---

## 📊 Monitoring

### Real-time Log Monitoring

```bash
# Monitor Laravel log
tail -f storage/logs/laravel.log

# Monitor Nginx access log
sudo tail -f /var/log/nginx/access.log

# Monitor Nginx error log
sudo tail -f /var/log/nginx/error.log
```

---

## 🔄 Rollback (Jika Diperlukan)

### Rollback Git

```bash
# Lihat commit history
git log --oneline

# Rollback ke commit sebelumnya
git reset --hard COMMIT_HASH

# Clear cache lagi
php artisan cache:clear
php artisan config:cache
php artisan route:cache

# Restart services
sudo systemctl restart php8.1-fpm
sudo systemctl restart nginx
```

### Restore Database

```bash
# Restore dari backup
mysql -u username -p database_name < backup_YYYYMMDD_HHMMSS.sql
```

---

## ✅ Post-Deployment Checklist

- [ ] Git pull berhasil
- [ ] Composer dependencies updated
- [ ] Cache cleared & rebuilt
- [ ] Permissions set correctly
- [ ] Services restarted
- [ ] Debug endpoint tested
- [ ] API endpoint tested dengan Postman
- [ ] Laravel log checked (no errors)
- [ ] Response format sesuai ekspektasi
- [ ] File test dihapus (jika sudah selesai testing)

---

## 🧹 Cleanup (Setelah Testing Berhasil)

```bash
# Hapus file test di VPS
rm routes/test.php
rm test-endpoint.php

# Clear route cache lagi
php artisan route:clear
php artisan route:cache
```

---

## 📞 Support

Jika ada masalah:

1. **Cek dokumentasi:**
   - `DEPLOYMENT_VPS_UPDATE.md` - Panduan lengkap
   - `QUICK_DEPLOYMENT_GUIDE.md` - Cheat sheet
   - `TESTING_KATALOG_ENDPOINT.md` - Panduan testing

2. **Cek logs:**
   - Laravel: `storage/logs/laravel.log`
   - Nginx: `/var/log/nginx/error.log`
   - PHP-FPM: `/var/log/php8.1-fpm.log`

3. **Verifikasi database:**
   - Jalankan query di `check-database.sql`

4. **Rollback jika perlu:**
   - Git: `git reset --hard COMMIT_HASH`
   - Database: Restore dari backup

---

## 🎉 Success Indicators

Deployment berhasil jika:

✅ Endpoint `/test-katalog` return:
```json
{
  "test": "success",
  "katalog_count": 10,
  "katalog_found": true,
  "sub_kategori_loaded": true
}
```

✅ API endpoint return:
```json
{
  "success": true,
  "message": "Katalog sampah berhasil diambil",
  "data": [...],
  "meta": {...}
}
```

✅ Tidak ada error di Laravel log

✅ Response time < 1 detik

---

## 📝 Notes

- Selalu backup database sebelum deployment
- Test di local terlebih dahulu
- Monitor log setelah deployment
- Hapus file test setelah selesai
- Dokumentasikan setiap perubahan

---

**Last Updated:** 2026-01-23  
**Version:** 1.0.0  
**Author:** Kiro AI Assistant
