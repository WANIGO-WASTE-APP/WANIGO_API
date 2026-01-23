# Quick Deployment Guide - Cheat Sheet

## 🚀 Cara Tercepat (Otomatis)

### Windows (PowerShell)
```powershell
.\deploy-to-vps.ps1
```

### Linux/Mac (Bash)
```bash
bash deploy-to-vps.sh
```

Script akan otomatis:
- ✅ Commit & push perubahan
- ✅ SSH ke VPS
- ✅ Pull latest code
- ✅ Update dependencies
- ✅ Clear cache
- ✅ Restart services

---

## 📝 Cara Manual (Step by Step)

### Di Local (Windows)

```bash
# 1. Commit & Push
git add .
git commit -m "Fix: Perbaiki endpoint katalog API"
git push origin main

# 2. SSH ke VPS
ssh username@vps-ip
```

### Di VPS (Setelah SSH)

```bash
# 3. Masuk ke folder project
cd /var/www/wanigo-api

# 4. Pull perubahan
git pull origin main

# 5. Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 6. Rebuild cache
php artisan config:cache
php artisan route:cache

# 7. Restart services
sudo systemctl restart php8.1-fpm
sudo systemctl restart nginx

# 8. Test
curl http://localhost/test-katalog
```

---

## 🔧 Troubleshooting Cepat

### Error: Permission Denied
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Error: Git Pull Failed
```bash
git stash
git pull origin main
```

### Error: 500 Internal Server Error
```bash
# Cek log
tail -50 storage/logs/laravel.log
```

### Error: Composer Failed
```bash
composer install --no-dev --optimize-autoloader
```

---

## ✅ Checklist Deployment

```
[ ] Commit perubahan di local
[ ] Push ke Git repository
[ ] SSH ke VPS
[ ] Backup database (opsional tapi recommended)
[ ] Pull perubahan dari Git
[ ] Clear semua cache Laravel
[ ] Restart PHP-FPM & Nginx
[ ] Test endpoint
[ ] Cek Laravel log
```

---

## 🎯 Testing Setelah Deployment

### Test Debug Endpoint
```bash
curl http://your-domain.com/test-katalog
```

### Test API Endpoint (dengan token)
```bash
# Login dulu
curl -X POST http://your-domain.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'

# Test endpoint (ganti YOUR_TOKEN)
curl -X GET "http://your-domain.com/api/bank-sampah/1/katalog?kategori=kering" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

---

## 📞 Informasi VPS (Isi Sendiri)

```
VPS IP/Domain: _______________________
SSH Username: _______________________
SSH Password: _______________________
Project Path: _______________________
Database Name: _______________________
Database User: _______________________
Git Branch: _______________________
```

---

## 🆘 Emergency Rollback

```bash
# Rollback Git
git log --oneline
git reset --hard COMMIT_HASH

# Restore Database
mysql -u username -p database_name < backup.sql
```

---

## 📚 File Penting

- `DEPLOYMENT_VPS_UPDATE.md` - Panduan lengkap deployment
- `deploy-to-vps.ps1` - Script otomatis (Windows)
- `deploy-to-vps.sh` - Script otomatis (Linux/Mac)
- `TESTING_KATALOG_ENDPOINT.md` - Panduan testing endpoint
- `check-database.sql` - Query untuk cek database

---

## 💡 Tips

1. **Selalu backup database** sebelum deployment
2. **Test di local** dulu sebelum push ke VPS
3. **Monitor log** setelah deployment
4. **Hapus file test** setelah testing berhasil
5. **Gunakan script otomatis** untuk menghindari human error
