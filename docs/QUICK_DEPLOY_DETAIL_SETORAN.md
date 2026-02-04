# Quick Deploy: Detail Setoran API Update

## 🚀 Quick Commands (Copy & Paste)

### 1️⃣ Local: Commit & Push

```bash
git add .
git commit -m "feat: Add nama_bank_sampah and kode_setoran to Detail Setoran API"
git push origin main
```

### 2️⃣ VPS: Login

```bash
ssh username@your-vps-ip
```

### 3️⃣ VPS: Backup Database

```bash
cd /var/www/wanigo-api
mysqldump -u DB_USER -p DB_NAME > backup_$(date +%Y%m%d_%H%M%S).sql
```

### 4️⃣ VPS: Deploy

```bash
# Pull changes
git pull origin main

# Clear cache
php artisan cache:clear && php artisan config:clear && php artisan route:clear

# Rebuild cache
php artisan config:cache && php artisan route:cache

# Restart services
sudo systemctl restart php8.1-fpm nginx
```

### 5️⃣ VPS: Verify

```bash
# Check controller
grep -n "nama_bank_sampah" app/Http/Controllers/API/Nasabah/DetailSetoranController.php

# Monitor log
tail -f storage/logs/laravel.log
```

---

## 🤖 Automated Deploy (Recommended)

```bash
# Di VPS, jalankan script deployment
cd /var/www/wanigo-api
bash scripts/deploy-detail-setoran-update.sh
```

Script akan otomatis:
- ✅ Backup database
- ✅ Pull changes dari Git
- ✅ Clear & rebuild cache
- ✅ Restart services
- ✅ Verify deployment

---

## 🧪 Quick Test

```bash
# Test dengan curl (ganti TOKEN dan URL)
curl -X GET "https://your-domain.com/api/nasabah/detail-setoran/1/detail" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" | jq '.data | keys'

# Harus menampilkan: nama_bank_sampah, kode_setoran
```

---

## ⚠️ Troubleshooting

### Cache tidak clear?
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
sudo systemctl restart php8.1-fpm
```

### Response masih lama?
```bash
# Clear semua cache
php artisan optimize:clear
sudo systemctl restart php8.1-fpm nginx
```

### Git pull error?
```bash
git stash
git pull origin main
```

---

## 🔄 Rollback

```bash
# Lihat commit sebelumnya
git log --oneline -5

# Rollback
git reset --hard COMMIT_HASH

# Clear cache & restart
php artisan cache:clear && sudo systemctl restart php8.1-fpm nginx
```

---

## ✅ Checklist

- [ ] Backup database
- [ ] Git pull berhasil
- [ ] Cache di-clear
- [ ] Services di-restart
- [ ] Test endpoint berhasil
- [ ] Field baru muncul di response
- [ ] Tidak ada error di log

---

## 📞 Need Help?

Lihat dokumentasi lengkap:
- `docs/DEPLOYMENT_DETAIL_SETORAN_UPDATE.md` - Full deployment guide
- `docs/DEPLOYMENT_VPS_UPDATE.md` - General VPS deployment guide
- `postman/DETAIL_SETORAN_API_UPDATES.md` - API changes documentation

---

**Estimated Time:** 5-10 minutes  
**Downtime:** None (zero downtime)  
**Risk:** Low
