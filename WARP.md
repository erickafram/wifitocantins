# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Project Overview

This is a Laravel 12 application for a WiFi captive portal system with MikroTik router integration. The application manages WiFi access control, user authentication, payment processing (PIX/card), voucher systems, and provides an administrative dashboard for monitoring connections and revenue.

## Tech Stack

- **Backend**: PHP 8.2 + Laravel 12
- **Frontend**: TailwindCSS 4.0 + Vite + TypeScript
- **Database**: SQLite (configurable via .env)
- **Router Integration**: MikroTik RouterOS API
- **Testing**: PHPUnit
- **Code Quality**: Laravel Pint (code formatting), StyleCI

## Development Commands

### Environment Setup
```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Copy environment file and configure
cp .env.example .env

# Generate application key
php artisan key:generate

# Create database and run migrations
php artisan migrate

# Create admin user (first-time setup)
php artisan route:list | findstr create-admin
```

### Development Server
```bash
# Start all development services (Laravel server, queue worker, logs, Vite)
composer run dev

# Or start services individually:
php artisan serve                    # Laravel development server
php artisan queue:listen --tries=1   # Queue worker
php artisan pail --timeout=0        # Real-time logs
npm run dev                         # Vite development server
```

### Testing
```bash
# Run all tests
composer run test

# Run tests with verbose output
php artisan test --verbose

# Run specific test file
php artisan test tests/Feature/PortalControllerTest.php

# Run specific test method
php artisan test --filter test_user_registration
```

### Code Quality
```bash
# Format PHP code with Laravel Pint
./vendor/bin/pint

# Check code style without making changes
./vendor/bin/pint --test

# Build frontend assets for production
npm run build
```

### Database Operations
```bash
# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Fresh migration with seeding
php artisan migrate:fresh --seed

# Create new migration
php artisan make:migration create_table_name
```

### Common Artisan Commands
```bash
# Create controller
php artisan make:controller ControllerName

# Create model with migration
php artisan make:model ModelName -m

# Create middleware
php artisan make:middleware MiddlewareName

# Clear various caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# List all routes
php artisan route:list
```

## Architecture Overview

### Core Components

**Portal System**: Captive portal interface for WiFi users with device detection, user registration, and payment flows.

**MikroTik Integration**: Direct socket-based communication with MikroTik RouterOS API for device access control, status monitoring, and bandwidth management.

**Payment System**: Multi-provider payment processing supporting PIX and card payments with webhook handling for payment confirmations.

**Administrative Dashboard**: Real-time monitoring of connections, revenue analytics, user management, and system configuration.

**Voucher System**: Digital voucher creation and redemption with time-based access control.

### Key Controllers

- `PortalController`: Handles captive portal interface, device detection, and Instagram free access
- `MikrotikController`: Manages router integration for device allow/block operations and status monitoring  
- `PaymentController`: Processes PIX and card payments with provider integration
- `AdminController`: Provides administrative dashboard functionality and reporting
- `VoucherController`: Manages voucher creation, validation, and redemption
- `AuthController`: Handles admin authentication and authorization

### Database Schema

**Users**: Stores user information with WiFi-specific fields (mac_address, ip_address, data_used, expires_at)

**Devices**: Tracks connected devices with MAC addresses and connection metadata

**Sessions**: Records connection sessions with start/end times and data usage

**Payments**: Payment transaction records with provider details and status

**Vouchers**: Digital voucher codes with usage limits and expiration dates

### MikroTik Integration

The application communicates with MikroTik routers using raw socket connections to the RouterOS API (port 8728). Configuration is managed through `config/wifi.php` with connection parameters (host, username, password, port).

### Frontend Architecture  

Built with TailwindCSS 4.0 and TypeScript, using Vite for asset compilation. Views are organized into:
- `portal/`: Captive portal user interface
- `admin/`: Administrative dashboard  
- `auth/`: Authentication forms

### Environment Configuration

Key environment variables:
- Database configuration (DB_*)
- MikroTik router settings (WIFI_MIKROTIK_*)
- Payment provider credentials
- Application URL and debugging settings

## Development Guidelines

### Testing Strategy
- Feature tests for API endpoints and user workflows
- Unit tests for business logic and MikroTik integration
- Database testing uses in-memory SQLite for speed

### Code Organization
- Controllers follow single responsibility principle
- Models contain WiFi-specific business logic (connection status, data usage)
- Middleware handles admin access control and device detection
- Services encapsulate external integrations (MikroTik, payment providers)

### Security Considerations
- Admin routes protected by authentication middleware
- MAC address validation for device operations  
- Payment webhooks with signature verification
- SQL injection prevention through Eloquent ORM

### Performance Notes
- MikroTik API calls are synchronous and may impact response times
- Consider queueing heavy operations like device bulk operations
- Database queries optimized for connection monitoring dashboard
- Frontend assets optimized with Vite's tree-shaking and code splitting