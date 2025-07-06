# Xboard VPN Management Panel - Comprehensive Documentation

## 📋 Table of Contents
- [Overview](#overview)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Core Architecture](#core-architecture)
- [Database Models](#database-models)
- [Services Layer](#services-layer)
- [Controllers & API](#controllers--api)
- [Theme System](#theme-system)
- [Payment System](#payment-system)
- [Configuration Files](#configuration-files)
- [Development Guide](#development-guide)
- [Feature Implementation Guide](#feature-implementation-guide)

## 🎯 Overview

Xboard is a modern VPN management panel built on Laravel 11 + Octane, designed for high-performance proxy service management. It supports multiple VPN protocols including v2ray, shadowsocks, trojan, hysteria, and more.

**Key Features:**
- Multi-protocol VPN server management
- User subscription management
- Payment processing integration
- Theme system for customization
- Admin dashboard (React-based)
- User frontend (Vue3-based)
- Traffic monitoring and statistics
- Invitation system with commissions

## 🚀 Tech Stack

### Backend
- **Framework**: Laravel 11 with Octane
- **Database**: MySQL 5.7+
- **Caching**: Redis + Octane Cache
- **Queue**: Laravel Horizon
- **Authentication**: Laravel Sanctum

### Frontend
- **Admin Panel**: React + Shadcn UI + TailwindCSS
- **User Interface**: Vue3 + TypeScript + NaiveUI
- **Build Tool**: Vite
- **Styling**: TailwindCSS

### Infrastructure
- **Deployment**: Docker + Docker Compose
- **Web Server**: Nginx (via Docker)
- **Process Management**: Supervisord
- **Protocols**: V2Ray, Shadowsocks, Trojan, Hysteria, VLESS, etc.

## 📁 Project Structure

```
/app/
├── 📁 app/                          # Laravel application core
│   ├── 📁 Console/                  # Artisan commands
│   ├── 📁 Contracts/                # Interface contracts
│   ├── 📁 Exceptions/               # Custom exceptions
│   ├── 📁 Helpers/                  # Helper functions
│   ├── 📁 Http/                     # HTTP layer (controllers, middleware)
│   ├── 📁 Jobs/                     # Background jobs
│   ├── 📁 Logging/                  # Custom logging
│   ├── 📁 Models/                   # Eloquent models
│   ├── 📁 Observers/                # Model observers
│   ├── 📁 Payments/                 # Payment gateways
│   ├── 📁 Plugins/                  # Plugin system
│   ├── 📁 Protocols/                # VPN protocol handlers
│   ├── 📁 Providers/                # Service providers
│   ├── 📁 Scope/                    # Query scopes
│   ├── 📁 Services/                 # Business logic services
│   ├── 📁 Support/                  # Support utilities
│   ├── 📁 Traits/                   # Reusable traits
│   └── 📁 Utils/                    # Utility classes
├── 📁 bootstrap/                    # Laravel bootstrap
├── 📁 config/                       # Configuration files
├── 📁 database/                     # Database migrations, seeders
├── 📁 library/                      # Third-party libraries
├── 📁 plugins/                      # Plugin implementations
├── 📁 public/                       # Public assets
├── 📁 resources/                    # Views, assets, language files
├── 📁 routes/                       # Route definitions
├── 📁 storage/                      # Storage directories
├── 📁 theme/                        # Theme system
└── 📁 vendor/                       # Composer dependencies
```

## 🏗️ Core Architecture

### Application Entry Points
- **📄 /public/index.php**: Main Laravel entry point
- **📄 /artisan**: Laravel CLI tool
- **📄 /routes/web.php**: Web routes (frontend + admin)

### Core Configuration
- **📄 /config/app.php**: Main application configuration
- **📄 /bootstrap/app.php**: Application bootstrap
- **📄 /composer.json**: PHP dependencies and autoloading

## 🗄️ Database Models

### Primary Models

#### 📄 /app/Models/User.php
**Purpose**: Central user management model
```php
// Key fields: id, email, plan_id, transfer_enable, expired_at, balance
// Relationships: plan(), orders(), tickets(), trafficResetLogs()
// Methods: isActive(), getTotalUsedTraffic(), getSubscribeUrlAttribute()
```
**Edit this file when**: Adding user features, subscription logic, traffic management

#### 📄 /app/Models/Server.php
**Purpose**: VPN server management
```php
// Key fields: name, type, host, port, protocol_settings, group_ids
// Supports: hysteria, vless, trojan, vmess, shadowsocks, etc.
// Methods: generateShadowsocksPassword(), getAvailableStatusAttribute()
```
**Edit this file when**: Adding new VPN protocols, server management features

#### 📄 /app/Models/Plan.php
**Purpose**: Subscription plan management
```php
// Key fields: name, transfer_enable, prices, reset_traffic_method
// Supports: monthly, quarterly, yearly, onetime subscriptions
// Methods: getPriceByPeriod(), getActivePeriods(), canResetTraffic()
```
**Edit this file when**: Adding new subscription types, pricing models

#### 📄 /app/Models/Order.php
**Purpose**: Order and payment tracking
```php
// Key fields: user_id, plan_id, total_amount, status, type
// Types: new_purchase, renewal, upgrade, reset_traffic
// Status: pending, processing, completed, cancelled
```
**Edit this file when**: Adding payment features, order processing logic

#### 📄 /app/Models/Setting.php
**Purpose**: System configuration storage
```php
// Dynamic configuration management
// Methods: createOrUpdate(), getContentValue()
```
**Edit this file when**: Adding new system settings

### Supporting Models
- **📄 /app/Models/ServerGroup.php**: Server access groups
- **📄 /app/Models/ServerRoute.php**: Traffic routing rules
- **📄 /app/Models/Ticket.php**: Support ticket system
- **📄 /app/Models/InviteCode.php**: Invitation system
- **📄 /app/Models/CommissionLog.php**: Commission tracking
- **📄 /app/Models/StatUser.php**: User statistics
- **📄 /app/Models/StatServer.php**: Server statistics

## 🔧 Services Layer

### Core Services

#### 📄 /app/Services/UserService.php
**Purpose**: User management business logic
```php
// Methods: createUser(), getUserTrafficInfo(), isAvailable()
// Handles: user creation, traffic management, subscription checks
```
**Edit this file when**: Adding user management features, subscription logic

#### 📄 /app/Services/ServerService.php
**Purpose**: Server management and user access
```php
// Methods: getAvailableServers(), getAvailableUsers(), getRoutes()
// Handles: server filtering, user access control, load balancing
```
**Edit this file when**: Adding server management features, load balancing

#### 📄 /app/Services/OrderService.php
**Purpose**: Order processing and subscription management
```php
// Methods: createFromRequest(), open(), setOrderType(), paid()
// Handles: order creation, payment processing, subscription activation
```
**Edit this file when**: Adding payment methods, subscription features

#### 📄 /app/Services/PaymentService.php
**Purpose**: Payment gateway integration
```php
// Methods: pay(), notify(), form()
// Supports: Multiple payment providers via /app/Payments/
```
**Edit this file when**: Adding new payment gateways

#### 📄 /app/Services/ThemeService.php
**Purpose**: Theme system management
```php
// Methods: getList(), switch(), upload(), delete(), getConfig()
// Handles: theme switching, configuration, file management
```
**Edit this file when**: Enhancing theme system, adding theme features

### Specialized Services
- **📄 /app/Services/CouponService.php**: Discount code management
- **📄 /app/Services/TicketService.php**: Support ticket handling
- **📄 /app/Services/StatisticalService.php**: Analytics and reporting
- **📄 /app/Services/TelegramService.php**: Telegram bot integration
- **📄 /app/Services/MailService.php**: Email notifications
- **📄 /app/Services/TrafficResetService.php**: Traffic reset automation

## 🎮 Controllers & API

### API Structure
```
/app/Http/Controllers/
├── 📁 V1/                          # API Version 1
│   ├── 📁 Client/                  # Server-side API (for VPN servers)
│   ├── 📁 Guest/                   # Public API (registration, payments)
│   ├── 📁 Passport/                # Authentication API
│   ├── 📁 Server/                  # Server management API
│   └── 📁 User/                    # User dashboard API
├── 📁 V2/                          # API Version 2
│   └── 📁 Admin/                   # Admin dashboard API
└── 📄 Controller.php               # Base controller
```

### Key Controllers

#### Client API (/app/Http/Controllers/V1/Client/)
**Purpose**: Server-to-panel communication
- **📄 ClientController.php**: Server synchronization, user data
**Edit when**: Adding server integration features

#### User API (/app/Http/Controllers/V1/User/)
**Purpose**: User dashboard functionality
- **📄 UserController.php**: User profile management
- **📄 OrderController.php**: Order management
- **📄 PlanController.php**: Subscription plans
- **📄 ServerController.php**: Server access
**Edit when**: Adding user features, dashboard enhancements

#### Admin API (/app/Http/Controllers/V2/Admin/)
**Purpose**: Admin dashboard functionality
- **📄 UserController.php**: User management
- **📄 ServerController.php**: Server administration
- **📄 OrderController.php**: Order management
- **📄 PlanController.php**: Plan management
**Edit when**: Adding admin features, management tools

## 🎨 Theme System

### Theme Structure
```
/theme/
├── 📁 Xboard/                      # Default theme
│   ├── 📄 config.json              # Theme configuration
│   ├── 📄 dashboard.blade.php      # Main template
│   ├── 📄 env.js                   # Environment settings
│   └── 📁 assets/                  # Static assets
└── 📁 v2board/                     # Legacy theme
    ├── 📄 config.json              # Theme configuration
    ├── 📄 dashboard.blade.php      # Main template
    └── 📁 assets/                  # Static assets
```

### Theme Files

#### 📄 /theme/Xboard/config.json
**Purpose**: Theme configuration and customization options
```json
{
  "name": "Xboard",
  "configs": [
    {
      "field_name": "theme_color",
      "field_type": "select",
      "select_options": {...}
    }
  ]
}
```
**Edit when**: Adding theme customization options

#### 📄 /theme/Xboard/dashboard.blade.php
**Purpose**: Main theme template
```html
<!-- Vue3 application mount point -->
<div id="app"></div>
<script>
  window.settings = {
    theme: { color: '{{ $theme_config['theme_color'] }}' }
  }
</script>
```
**Edit when**: Modifying theme structure, adding global settings

### Theme Management
- **📄 /app/Services/ThemeService.php**: Theme system backend
- **📄 /routes/web.php**: Theme routing and loading logic

## 💳 Payment System

### Payment Gateway Structure
```
/app/Payments/
├── 📄 AlipayF2F.php               # Alipay Face-to-Face
├── 📄 PayPal.php                  # PayPal integration
├── 📄 Coinbase.php                # Cryptocurrency
├── 📄 EPay.php                    # Generic e-payment
├── 📄 BTCPay.php                  # Bitcoin payment
└── ... (more payment providers)
```

### Payment Integration
- **📄 /app/Contracts/PaymentInterface.php**: Payment contract
- **📄 /app/Services/PaymentService.php**: Payment orchestration
- **📄 /app/Models/Payment.php**: Payment configuration model

**Edit when**: Adding new payment methods, modifying payment flow

## ⚙️ Configuration Files

### Core Configuration
- **📄 /config/app.php**: Main application settings
- **📄 /config/database.php**: Database connections
- **📄 /config/cache.php**: Cache configuration
- **📄 /config/queue.php**: Queue configuration
- **📄 /config/octane.php**: Octane settings

### Custom Configuration
- **📄 /config/theme/**: Theme system configuration
- **📄 /config/hidden_features.php**: Feature flags
- **📄 /config/cloud_storage.php**: File storage settings

## 🔨 Development Guide

### Adding New Features

#### 1. User Management Features
**Files to edit:**
- **📄 /app/Models/User.php**: Add new user fields/methods
- **📄 /app/Services/UserService.php**: Add business logic
- **📄 /app/Http/Controllers/V1/User/UserController.php**: Add API endpoints
- **📄 /database/migrations/**: Add database changes

#### 2. Server Management Features
**Files to edit:**
- **📄 /app/Models/Server.php**: Add server functionality
- **📄 /app/Services/ServerService.php**: Add server logic
- **📄 /app/Http/Controllers/V2/Admin/ServerController.php**: Add admin API
- **📄 /app/Protocols/**: Add new protocol support

#### 3. Payment Features
**Files to edit:**
- **📄 /app/Payments/**: Add new payment gateway
- **📄 /app/Services/PaymentService.php**: Update payment logic
- **📄 /app/Models/Payment.php**: Update payment model
- **📄 /app/Http/Controllers/V1/Guest/PaymentController.php**: Add endpoints

#### 4. Theme Customization
**Files to edit:**
- **📄 /theme/[theme_name]/config.json**: Add theme options
- **📄 /theme/[theme_name]/dashboard.blade.php**: Update template
- **📄 /app/Services/ThemeService.php**: Add theme functionality

#### 5. Admin Dashboard Features
**Files to edit:**
- **📄 /app/Http/Controllers/V2/Admin/**: Add admin controllers
- **📄 /resources/views/admin.blade.php**: Update admin template
- **📄 /public/assets/admin/**: Update admin assets

## 📋 Feature Implementation Guide

### 🎯 Want to Add User Analytics?
**Edit these files:**
1. **📄 /app/Models/StatUser.php**: Add new statistics fields
2. **📄 /app/Services/StatisticalService.php**: Add analytics logic
3. **📄 /app/Http/Controllers/V1/User/StatController.php**: Create API endpoints
4. **📄 /database/migrations/**: Add analytics tables

### 🎯 Want to Add New VPN Protocol?
**Edit these files:**
1. **📄 /app/Protocols/**: Create new protocol class
2. **📄 /app/Models/Server.php**: Add protocol configuration
3. **📄 /app/Services/ServerService.php**: Add protocol handling
4. **📄 /app/Http/Controllers/V1/Client/ClientController.php**: Add protocol endpoints

### 🎯 Want to Add Subscription Tiers?
**Edit these files:**
1. **📄 /app/Models/Plan.php**: Add tier functionality
2. **📄 /app/Services/PlanService.php**: Add tier logic
3. **📄 /app/Services/OrderService.php**: Update order processing
4. **📄 /app/Http/Controllers/V1/User/PlanController.php**: Add tier endpoints

### 🎯 Want to Add Multi-Language Support?
**Edit these files:**
1. **📄 /resources/lang/**: Add language files
2. **📄 /theme/[theme]/dashboard.blade.php**: Update i18n settings
3. **📄 /app/Http/Middleware/**: Add language middleware
4. **📄 /config/app.php**: Update locale settings

### 🎯 Want to Add Advanced Admin Features?
**Edit these files:**
1. **📄 /app/Http/Controllers/V2/Admin/**: Add admin controllers
2. **📄 /app/Services/**: Add business logic services
3. **📄 /resources/views/admin.blade.php**: Update admin template
4. **📄 /public/assets/admin/**: Update admin frontend

### 🎯 Want to Add Plugin System?
**Edit these files:**
1. **📄 /app/Plugins/**: Create plugin structure
2. **📄 /app/Providers/PluginServiceProvider.php**: Register plugins
3. **📄 /app/Services/Plugin/**: Add plugin management
4. **📄 /plugins/**: Add plugin implementations

### 🎯 Want to Add API Rate Limiting?
**Edit these files:**
1. **📄 /app/Http/Middleware/**: Add rate limiting middleware
2. **📄 /app/Http/Kernel.php**: Register middleware
3. **📄 /config/cache.php**: Configure rate limiting cache
4. **📄 /routes/**: Apply middleware to routes

### 🎯 Want to Add Email Templates?
**Edit these files:**
1. **📄 /resources/views/mail/**: Add email templates
2. **📄 /app/Services/MailService.php**: Add email logic
3. **📄 /app/Jobs/SendEmailJob.php**: Add email jobs
4. **📄 /config/mail.php**: Configure email settings

### 🎯 Want to Add Real-time Notifications?
**Edit these files:**
1. **📄 /app/Events/**: Create notification events
2. **📄 /app/Listeners/**: Add event listeners
3. **📄 /config/broadcasting.php**: Configure broadcasting
4. **📄 /resources/js/**: Add frontend WebSocket handling

## 🔐 Security Considerations

### Authentication & Authorization
- **📄 /app/Http/Middleware/**: Authentication middleware
- **📄 /config/auth.php**: Authentication configuration
- **📄 /config/sanctum.php**: API token management

### Data Validation
- **📄 /app/Http/Requests/**: Form request validation
- **📄 /app/Rules/**: Custom validation rules

### Rate Limiting & Security
- **📄 /config/cors.php**: CORS configuration
- **📄 /app/Http/Middleware/**: Security middleware

## 🚀 Performance Optimization

### Caching Strategy
- **📄 /config/cache.php**: Cache configuration
- **📄 /app/Utils/CacheKey.php**: Cache key management

### Queue Management
- **📄 /config/queue.php**: Queue configuration
- **📄 /app/Jobs/**: Background job processing

### Database Optimization
- **📄 /database/migrations/**: Database schema
- **📄 /app/Models/**: Eloquent model optimization

## 📚 Additional Resources

### Logs & Debugging
- **📄 /storage/logs/**: Application logs
- **📄 /config/logging.php**: Logging configuration
- **📄 /config/debugbar.php**: Debug bar configuration

### Testing
- **📄 /tests/**: Test cases
- **📄 /phpunit.xml**: PHPUnit configuration

### Deployment
- **📄 /Dockerfile**: Docker configuration
- **📄 /compose.sample.yaml**: Docker Compose template
- **📄 /init.sh**: Initialization script

---

## 🎉 Conclusion

This documentation provides a comprehensive overview of the Xboard codebase structure. Each section includes specific file references and guidance on what to edit when implementing new features. Use this as your roadmap for understanding and extending the Xboard VPN management panel.

**Remember**: Always backup your database and test changes in a development environment before applying them to production!