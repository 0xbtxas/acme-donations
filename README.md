<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## ACME Donations

Enterprise-grade CSR donation platform with Laravel 12 and Vue 3.

### Tech

-   Laravel 12 (PHP 8.2), MySQL
-   Sanctum auth, Spatie Roles/Permissions
-   Queued external payment integration (jobs), HTTP client
-   Vue 3 + Vite + @nuxt/ui minimal SPA
-   Pest tests, PHPStan

### Getting started

1. Copy `.env.example` to `.env`, set MySQL and queue connection
2. `composer install && npm install`
3. `php artisan key:generate`
4. `php artisan migrate --seed`
5. `composer run dev`

Seeds:

-   `admin@example.com` / `password`
-   `employee@example.com` / `password`

### API

-   `POST /api/auth/register|login|logout|me`
-   `GET /api/campaigns` list/search; `POST /api/campaigns`; `GET /api/campaigns/{id}`; `PUT|PATCH /api/campaigns/{id}`; `DELETE /api/campaigns/{id}`; `POST /api/campaigns/{id}/publish`
-   `POST /api/donations`; `GET /api/donations` (admin)

### Architecture

-   Requests for validation, Resources for JSON
-   Policies + Spatie permissions
-   `PaymentGateway` abstraction; `NullPaymentGateway` enqueues `SendDonationToExternalPaymentJob`
-   External endpoint: `config/services.php` > `services.payments.base_url`
