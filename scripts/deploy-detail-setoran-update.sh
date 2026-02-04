#!/bin/bash

###############################################################################
# Deployment Script: Detail Setoran API Improvements
# 
# Script ini akan melakukan deployment update Detail Setoran API ke VPS
# dengan langkah-langkah yang aman dan terstruktur.
#
# Usage: bash deploy-detail-setoran-update.sh
###############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PROJECT_DIR="/var/www/wanigo-api"  # Sesuaikan dengan lokasi project Anda
PHP_VERSION="8.1"  # Sesuaikan dengan versi PHP Anda
WEB_SERVER="nginx"  # nginx atau apache2
GIT_BRANCH="main"  # main atau master

###############################################################################
# Helper Functions
###############################################################################

print_header() {
    echo -e "${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

confirm() {
    read -p "$(echo -e ${YELLOW}$1 [y/N]: ${NC})" -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_error "Deployment dibatalkan"
        exit 1
    fi
}

###############################################################################
# Pre-Deployment Checks
###############################################################################

print_header "PRE-DEPLOYMENT CHECKS"

# Check if running as root or with sudo
if [ "$EUID" -ne 0 ]; then 
    print_warning "Script ini memerlukan sudo privileges untuk restart services"
    confirm "Lanjutkan?"
fi

# Check if project directory exists
if [ ! -d "$PROJECT_DIR" ]; then
    print_error "Project directory tidak ditemukan: $PROJECT_DIR"
    exit 1
fi

print_success "Project directory ditemukan: $PROJECT_DIR"

# Navigate to project directory
cd "$PROJECT_DIR" || exit 1
print_success "Navigasi ke project directory"

# Check if git repository
if [ ! -d ".git" ]; then
    print_error "Bukan git repository"
    exit 1
fi

print_success "Git repository terdeteksi"

###############################################################################
# Backup Database
###############################################################################

print_header "BACKUP DATABASE"

confirm "Apakah Anda ingin backup database?"

# Read database credentials from .env
if [ -f ".env" ]; then
    DB_DATABASE=$(grep DB_DATABASE .env | cut -d '=' -f2)
    DB_USERNAME=$(grep DB_USERNAME .env | cut -d '=' -f2)
    
    BACKUP_FILE="backup_detail_setoran_$(date +%Y%m%d_%H%M%S).sql"
    
    print_info "Backing up database: $DB_DATABASE"
    
    mysqldump -u "$DB_USERNAME" -p "$DB_DATABASE" > "$BACKUP_FILE"
    
    if [ -f "$BACKUP_FILE" ]; then
        BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
        print_success "Database backup berhasil: $BACKUP_FILE ($BACKUP_SIZE)"
    else
        print_error "Database backup gagal"
        exit 1
    fi
else
    print_error "File .env tidak ditemukan"
    exit 1
fi

###############################################################################
# Git Operations
###############################################################################

print_header "GIT OPERATIONS"

# Show current branch
CURRENT_BRANCH=$(git branch --show-current)
print_info "Current branch: $CURRENT_BRANCH"

# Show current commit
CURRENT_COMMIT=$(git log -1 --oneline)
print_info "Current commit: $CURRENT_COMMIT"

# Stash local changes if any
if [[ -n $(git status -s) ]]; then
    print_warning "Ada perubahan lokal yang belum di-commit"
    confirm "Stash perubahan lokal?"
    git stash
    print_success "Perubahan lokal di-stash"
fi

# Pull latest changes
print_info "Pulling latest changes from $GIT_BRANCH..."
git pull origin "$GIT_BRANCH"

# Show new commit
NEW_COMMIT=$(git log -1 --oneline)
print_success "Git pull berhasil"
print_info "New commit: $NEW_COMMIT"

# Verify DetailSetoranController updated
if grep -q "nama_bank_sampah" app/Http/Controllers/API/Nasabah/DetailSetoranController.php; then
    print_success "DetailSetoranController berhasil diupdate"
else
    print_error "DetailSetoranController tidak terupdate dengan benar"
    exit 1
fi

###############################################################################
# Update Dependencies
###############################################################################

print_header "UPDATE DEPENDENCIES"

print_info "Updating Composer autoloader..."
composer dump-autoload --optimize

print_success "Composer autoloader updated"

###############################################################################
# Clear Cache
###############################################################################

print_header "CLEAR CACHE"

print_info "Clearing Laravel cache..."

php artisan cache:clear
print_success "Cache cleared"

php artisan config:clear
print_success "Config cache cleared"

php artisan route:clear
print_success "Route cache cleared"

php artisan view:clear
print_success "View cache cleared"

print_info "Rebuilding cache for production..."

php artisan config:cache
print_success "Config cached"

php artisan route:cache
print_success "Route cached"

###############################################################################
# Set Permissions
###############################################################################

print_header "SET PERMISSIONS"

print_info "Setting permissions for storage and bootstrap/cache..."

if [ "$WEB_SERVER" = "nginx" ]; then
    WEB_USER="nginx"
else
    WEB_USER="www-data"
fi

sudo chown -R $WEB_USER:$WEB_USER storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

print_success "Permissions set"

###############################################################################
# Restart Services
###############################################################################

print_header "RESTART SERVICES"

print_info "Restarting PHP-FPM..."
sudo systemctl restart php${PHP_VERSION}-fpm

if sudo systemctl is-active --quiet php${PHP_VERSION}-fpm; then
    print_success "PHP-FPM restarted successfully"
else
    print_error "PHP-FPM restart failed"
    exit 1
fi

print_info "Restarting $WEB_SERVER..."
sudo systemctl restart $WEB_SERVER

if sudo systemctl is-active --quiet $WEB_SERVER; then
    print_success "$WEB_SERVER restarted successfully"
else
    print_error "$WEB_SERVER restart failed"
    exit 1
fi

###############################################################################
# Verification
###############################################################################

print_header "VERIFICATION"

# Check if controller file has the new fields
print_info "Verifying controller changes..."

if grep -q "nama_bank_sampah" app/Http/Controllers/API/Nasabah/DetailSetoranController.php && \
   grep -q "kode_setoran" app/Http/Controllers/API/Nasabah/DetailSetoranController.php; then
    print_success "Controller verification passed"
else
    print_error "Controller verification failed"
    exit 1
fi

# Check Laravel log for errors
if [ -f "storage/logs/laravel.log" ]; then
    RECENT_ERRORS=$(tail -50 storage/logs/laravel.log | grep -i "error" | wc -l)
    if [ "$RECENT_ERRORS" -gt 0 ]; then
        print_warning "Ditemukan $RECENT_ERRORS error di Laravel log"
        print_info "Cek: tail -50 storage/logs/laravel.log"
    else
        print_success "Tidak ada error di Laravel log"
    fi
fi

###############################################################################
# Summary
###############################################################################

print_header "DEPLOYMENT SUMMARY"

echo -e "${GREEN}✓ Deployment berhasil!${NC}"
echo ""
echo "Details:"
echo "  - Project: $PROJECT_DIR"
echo "  - Branch: $GIT_BRANCH"
echo "  - Commit: $NEW_COMMIT"
echo "  - Backup: $BACKUP_FILE"
echo "  - PHP Version: $PHP_VERSION"
echo "  - Web Server: $WEB_SERVER"
echo ""
echo "Next Steps:"
echo "  1. Test endpoint dengan Postman atau curl"
echo "  2. Monitor Laravel log: tail -f storage/logs/laravel.log"
echo "  3. Verifikasi response memiliki field baru:"
echo "     - nama_bank_sampah"
echo "     - kode_setoran"
echo ""
echo "Rollback (jika diperlukan):"
echo "  git reset --hard $CURRENT_COMMIT"
echo "  mysql -u $DB_USERNAME -p $DB_DATABASE < $BACKUP_FILE"
echo ""

print_success "Deployment selesai!"

###############################################################################
# Post-Deployment Test (Optional)
###############################################################################

print_header "POST-DEPLOYMENT TEST (OPTIONAL)"

confirm "Apakah Anda ingin menjalankan test endpoint?"

# Read APP_URL from .env
APP_URL=$(grep APP_URL .env | cut -d '=' -f2)

print_info "Testing endpoint: $APP_URL/api/nasabah/detail-setoran/1/detail"
print_warning "Anda perlu token untuk test ini"

read -p "Masukkan Bearer Token (atau tekan Enter untuk skip): " TOKEN

if [ -n "$TOKEN" ]; then
    print_info "Testing GET /api/nasabah/detail-setoran/1/detail..."
    
    RESPONSE=$(curl -s -X GET "$APP_URL/api/nasabah/detail-setoran/1/detail" \
        -H "Authorization: Bearer $TOKEN" \
        -H "Accept: application/json")
    
    if echo "$RESPONSE" | grep -q "nama_bank_sampah"; then
        print_success "Field 'nama_bank_sampah' ditemukan di response"
    else
        print_error "Field 'nama_bank_sampah' TIDAK ditemukan di response"
    fi
    
    if echo "$RESPONSE" | grep -q "kode_setoran"; then
        print_success "Field 'kode_setoran' ditemukan di response"
    else
        print_error "Field 'kode_setoran' TIDAK ditemukan di response"
    fi
    
    echo ""
    print_info "Full response:"
    echo "$RESPONSE" | jq '.' 2>/dev/null || echo "$RESPONSE"
else
    print_info "Test endpoint di-skip"
fi

print_header "DEPLOYMENT COMPLETE"
