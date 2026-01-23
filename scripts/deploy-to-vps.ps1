# Script Deployment Otomatis ke VPS (PowerShell)
# Cara pakai: .\deploy-to-vps.ps1

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "  DEPLOYMENT SCRIPT - WANIGO API" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# Fungsi untuk print dengan warna
function Print-Success {
    param($message)
    Write-Host "✓ $message" -ForegroundColor Green
}

function Print-Error {
    param($message)
    Write-Host "✗ $message" -ForegroundColor Red
}

function Print-Warning {
    param($message)
    Write-Host "⚠ $message" -ForegroundColor Yellow
}

function Print-Info {
    param($message)
    Write-Host "ℹ $message" -ForegroundColor Yellow
}

# 1. Cek apakah ada perubahan yang belum di-commit
Write-Host "1. Checking for uncommitted changes..."
$gitStatus = git status -s
if ($gitStatus) {
    Print-Warning "Ada perubahan yang belum di-commit!"
    git status -s
    Write-Host ""
    $commitNow = Read-Host "Apakah ingin commit sekarang? (y/n)"
    
    if ($commitNow -eq "y" -or $commitNow -eq "Y") {
        $commitMsg = Read-Host "Masukkan commit message"
        git add .
        git commit -m "$commitMsg"
        Print-Success "Changes committed"
    } else {
        Print-Error "Deployment dibatalkan. Commit perubahan terlebih dahulu."
        exit 1
    }
} else {
    Print-Success "No uncommitted changes"
}

Write-Host ""

# 2. Push ke repository
Write-Host "2. Pushing to remote repository..."
$branchName = Read-Host "Branch name (default: main)"
if ([string]::IsNullOrWhiteSpace($branchName)) {
    $branchName = "main"
}

git push origin $branchName
if ($LASTEXITCODE -eq 0) {
    Print-Success "Pushed to origin/$branchName"
} else {
    Print-Error "Failed to push. Check your git configuration."
    exit 1
}

Write-Host ""

# 3. Informasi VPS
Write-Host "3. VPS Connection Information"
Print-Info "Masukkan informasi VPS kamu:"
$vpsHost = Read-Host "VPS IP/Domain"
$sshUser = Read-Host "SSH Username"
$projectPath = Read-Host "Project Path (e.g., /var/www/wanigo-api)"

Write-Host ""

# 4. Konfirmasi deployment
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "  DEPLOYMENT SUMMARY" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "VPS Host: $vpsHost"
Write-Host "SSH User: $sshUser"
Write-Host "Project Path: $projectPath"
Write-Host "Branch: $branchName"
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""
$confirm = Read-Host "Lanjutkan deployment? (y/n)"

if ($confirm -ne "y" -and $confirm -ne "Y") {
    Print-Error "Deployment dibatalkan"
    exit 1
}

Write-Host ""

# 5. SSH ke VPS dan jalankan deployment commands
Write-Host "4. Connecting to VPS and deploying..."
Write-Host ""

# Buat script untuk dijalankan di VPS
$deployScript = @"
echo '=========================================='
echo '  DEPLOYMENT ON VPS'
echo '=========================================='
echo ''

# Navigate to project directory
echo '→ Navigating to project directory...'
cd $projectPath || exit 1

# Backup database
echo '→ Creating database backup...'
php artisan db:backup 2>/dev/null || echo '⚠ Database backup command not found (skipping)'

# Pull latest changes
echo '→ Pulling latest changes from Git...'
git pull origin $branchName

# Install/Update dependencies
echo '→ Updating Composer dependencies...'
composer install --no-dev --optimize-autoloader --no-interaction

# Clear all caches
echo '→ Clearing Laravel caches...'
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches for production
echo '→ Rebuilding caches...'
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
echo '→ Setting permissions...'
sudo chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || sudo chown -R nginx:nginx storage bootstrap/cache 2>/dev/null
sudo chmod -R 775 storage bootstrap/cache

# Restart services
echo '→ Restarting services...'
sudo systemctl restart php8.1-fpm 2>/dev/null || sudo systemctl restart php8.2-fpm 2>/dev/null || sudo systemctl restart php-fpm
sudo systemctl restart nginx 2>/dev/null || sudo systemctl restart apache2 2>/dev/null

echo ''
echo '=========================================='
echo '  DEPLOYMENT COMPLETED'
echo '=========================================='
echo ''
echo '→ Latest commit:'
git log -1 --oneline
echo ''
"@

# Jalankan script via SSH
ssh "$sshUser@$vpsHost" $deployScript

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Print-Success "=========================================="
    Print-Success "  DEPLOYMENT SUCCESSFUL!"
    Print-Success "=========================================="
    Write-Host ""
    Print-Info "Next steps:"
    Write-Host "  1. Test endpoint: http://$vpsHost/test-katalog"
    Write-Host "  2. Test API: http://$vpsHost/api/bank-sampah/1/katalog"
    Write-Host "  3. Monitor logs: ssh $sshUser@$vpsHost 'tail -f $projectPath/storage/logs/laravel.log'"
    Write-Host ""
} else {
    Write-Host ""
    Print-Error "=========================================="
    Print-Error "  DEPLOYMENT FAILED!"
    Print-Error "=========================================="
    Write-Host ""
    Print-Info "Troubleshooting:"
    Write-Host "  1. Check SSH connection: ssh $sshUser@$vpsHost"
    Write-Host "  2. Check project path: $projectPath"
    Write-Host "  3. Check Laravel logs on VPS"
    Write-Host ""
}
