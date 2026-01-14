# WANIGO API

<p align="center">
<img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php" alt="PHP Version">
<img src="https://img.shields.io/badge/Laravel-12.0-FF2D20?style=flat&logo=laravel" alt="Laravel Version">
<img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License">
</p>

API Backend untuk aplikasi WANIGO - Platform manajemen Bank Sampah berbasis Laravel dengan Jetstream, Sanctum, dan Livewire.

## 📋 Features

- 🔐 Authentication & Authorization (Laravel Sanctum)
- 👥 User Management (Laravel Jetstream)
- 🏦 Bank Sampah Management
- 👤 Nasabah Management
- 📦 Katalog Sampah (Kategori & Sub-kategori)
- 💰 Setoran Sampah & Point System
- 📚 Educational Content (Modul & Konten)
- 📍 Location Data (Provinsi, Kabupaten, Kecamatan, Kelurahan)
- 📊 DataTables Integration
- 🔄 RESTful API Endpoints

## 🚀 Quick Start

### Requirements

- PHP 8.2 or higher
- Composer
- MySQL/MariaDB
- Node.js & NPM

### Installation

```bash
# Clone repository
git clone [repository-url]
cd WANIGO_API

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Setup database
php artisan migrate
php artisan db:seed

# Build assets
npm run build

# Run development server
php artisan serve
```

## 📦 Deployment

Untuk deploy aplikasi ini ke production (IDCloudHost atau hosting lainnya), ikuti panduan lengkap di:

**📖 [DEPLOYMENT.md](DEPLOYMENT.md)** - Panduan lengkap deployment ke IDCloudHost

### Quick Links:
- [Deployment Checklist](DEPLOYMENT_CHECKLIST.md) - Checklist step-by-step
- [Artisan Commands](ARTISAN_COMMANDS.md) - Reference artisan commands penting
- [.env Production Template](.env.production.example) - Template environment production
- [.htaccess Production](.htaccess.production) - .htaccess dengan security headers

## 📚 API Documentation

API documentation tersedia di Postman Collection:
- `WANIGO_API_Fixed.postman_collection.json`
- `WANIGO_API.postman_environment.json`

Import files tersebut ke Postman untuk testing API endpoints.

## 🛠️ Tech Stack

- **Framework:** Laravel 12
- **Authentication:** Laravel Sanctum
- **Frontend:** Livewire 3, Jetstream 5
- **Database:** MySQL/MariaDB
- **UI:** Tailwind CSS
- **DataTables:** Yajra Laravel DataTables

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

-   [Simple, fast routing engine](https://laravel.com/docs/routing).
-   [Powerful dependency injection container](https://laravel.com/docs/container).
-   Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
-   Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
-   Database agnostic [schema migrations](https://laravel.com/docs/migrations).
-   [Robust background job processing](https://laravel.com/docs/queues).
-   [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

-   **[Vehikl](https://vehikl.com/)**
-   **[Tighten Co.](https://tighten.co)**
-   **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
-   **[64 Robots](https://64robots.com)**
-   **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
-   **[DevSquad](https://devsquad.com/hire-laravel-developers)**
-   **[Redberry](https://redberry.international/laravel-development/)**
-   **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
