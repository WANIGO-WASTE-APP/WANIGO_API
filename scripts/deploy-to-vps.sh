#!/bin/bash

# Script Deployment Otomatis ke VPS
# Cara pakai: bash deploy-to-vps.sh

echo "=========================================="
echo "  DEPLOYMENT SCRIPT - WANIGO API"
echo "=========================================="
echo ""

# Warna untuk output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Fungsi untuk print dengan warna
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
    echo -e "${YELLOW}ℹ $1${NC}"
}

# 1. Cek apakah ada perubahan yang belum di-commit
echo "1. Checking for uncommitted changes..."
if [[ -n $(git status -s) ]]; then
    print_warning "Ada perubahan yang belum di-commit!"
    git status -s
    echo ""
    read -p "Apakah ingin commit sekarang? (y/n): " commit_now
    
    if [[ $commit_now == "y" || $commit_now == "Y" ]]; then
        read -p "Masukkan commit message: " commit_msg
        git add .
        git commit -m "$commit_msg"
        print_success "Changes committed"
    else
        print_error "Deployment dibatalkan. Commit perubahan terlebih dahulu."
        exit 1
    fi
else
    print_success "No uncommitted changes"
fi

echo ""

# 2. Push ke repository
echo "2. Pushing to remote repository..."
read -p "Branch name (default: main): " branch_name
branch_name=${branch_name:-main}

git push origin $branch_name
if [ $? -eq 0 ]; then
    print_success "Pushed to origin/$branch_name"
else
    print_error "Failed to push. Check your git configuration."
    exit 1
fi

echo ""

# 3. Informasi VPS
echo "3. VPS Connection Information"
print_info "Masukkan informasi VPS kamu:"
read -p "VPS IP/Domain: " vps_host
read -p "SSH Username: " ssh_user
read -p "Project Path (e.g., /var/www/wanigo-api): " project_path

echo ""

# 4. Konfirmasi deployment
echo "=========================================="
echo "  DEPLOYMENT SUMMARY"
echo "=========================================="
echo "VPS Host: $vps_host"
echo "SSH User: $ssh_user"
echo "Project Path: $project_path"
echo "Branch: $branch_name"
echo "=========================================="
echo ""
read -p "Lanjutkan deployment? (y/n): " confirm

if [[ $confirm != "y" && $confirm != "Y" ]]; then
    print_error "Deployment dibatalkan"
    exit 1
fi

echo ""

# 5. SSH ke VPS dan jalankan deployment commands
echo "4. Connecting to VPS and deploying..."
echo ""

ssh $ssh_user@$vps_host << EOF
    echo "=========================================="
    echo "  DEPLOYMENT ON VPS"
    echo "=========================================="
    echo ""
    
    # Navigate to project directory
    echo "→ Navigating to project directory..."
    cd $project_path || exit 1
    
    # Backup database
    echo "→ Creating database backup..."
    php artisan db:backup 2>/dev/null || echo "⚠ Database backup command not found (skipping)"
    
    # Pull latest changes
    echo "→ Pulling latest changes from Git..."
    git pull origin $branch_name
    
    # Install/Update dependencies
    echo "→ Updating Composer dependencies..."
    composer install --no-dev --optimize-autoloader --no-interaction
    
    # Clear all caches
    echo "→ Clearing Laravel caches..."
    php artisan cache:clear
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    
    # Rebuild caches for production
    echo "→ Rebuilding caches..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    # Set permissions
    echo "→ Setting permissions..."
    sudo chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || sudo chown -R nginx:nginx storage bootstrap/cache 2>/dev/null
    sudo chmod -R 775 storage bootstrap/cache
    
    # Restart services
    echo "→ Restarting services..."
    sudo systemctl restart php8.1-fpm 2>/dev/null || sudo systemctl restart php8.2-fpm 2>/dev/null || sudo systemctl restart php-fpm
    sudo systemctl restart nginx 2>/dev/null || sudo systemctl restart apache2 2>/dev/null
    
    echo ""
    echo "=========================================="
    echo "  DEPLOYMENT COMPLETED"
    echo "=========================================="
    echo ""
    echo "→ Latest commit:"
    git log -1 --oneline
    echo ""
    echo "→ PHP-FPM status:"
    sudo systemctl status php8.1-fpm --no-pager -l 2>/dev/null || sudo systemctl status php8.2-fpm --no-pager -l 2>/dev/null || echo "Could not check PHP-FPM status"
    echo ""
    echo "→ Web server status:"
    sudo systemctl status nginx --no-pager -l 2>/dev/null || sudo systemctl status apache2 --no-pager -l 2>/dev/null || echo "Could not check web server status"
    echo ""
EOF

if [ $? -eq 0 ]; then
    echo ""
    print_success "=========================================="
    print_success "  DEPLOYMENT SUCCESSFUL!"
    print_success "=========================================="
    echo ""
    print_info "Next steps:"
    echo "  1. Test endpoint: http://$vps_host/test-katalog"
    echo "  2. Test API: http://$vps_host/api/bank-sampah/1/katalog"
    echo "  3. Monitor logs: ssh $ssh_user@$vps_host 'tail -f $project_path/storage/logs/laravel.log'"
    echo ""
else
    echo ""
    print_error "=========================================="
    print_error "  DEPLOYMENT FAILED!"
    print_error "=========================================="
    echo ""
    print_info "Troubleshooting:"
    echo "  1. Check SSH connection: ssh $ssh_user@$vps_host"
    echo "  2. Check project path: $project_path"
    echo "  3. Check Laravel logs on VPS"
    echo ""
fi
