# TODO - پیاده‌سازی API ProResume

> **تاریخ ایجاد:** 2026-08-29
> **وضعیت کلی:** در حال پیاده‌سازی

---

## تودو لیست اصلی

```
[ROOT] پیاده‌سازی کامل سرویس API ProResume
│
├── [PHASE ۰] مهاجرت‌های دیتابیس
│   ├── ۰-۱. Migration افزودن فیلدها به users
│   │   └── فیلدها: service_type, specialty, district, lat, lng
│   ├── ۰-۲. Migration ایجاد جدول provinces
│   │   └── فیلدها: id, name, timestamps
│   ├── ۰-۳. Migration ایجاد جدول cities
│   │   └── فیلدها: id, province_id (FK), name, timestamps
│   ├── ۰-۴. Migration ایجاد جدول api_integrations
│   │   └── فیلدها: id, user_id (FK), api_key (unique), is_active, app_type, settings (json), timestamps
│   └── ۰-۵. Migration افزودن فیلدها به basic_settings
│       └── فیلدها: api_integration_status, api_key
│
├── [PHASE ۱] نصب و تنظیم Laravel Sanctum
│   ├── ۱-۱. نصب Sanctum
│   │   └── composer require laravel/sanctum
│   ├── ۱-۲. تنظیم config/sanctum.php
│   │   └── stateful domains, guard, expiration
│   ├── ۱-۳. تنظیم .env
│   │   └── SESSION_DRIVER=cookie, SANCTUM_STATEFUL_DOMAINS
│   ├── ۱-۴. تنظیم app/Http/Kernel.php
│   │   └── EnsureFrontendRequestsAreStateful middleware
│   └── ۱-۵. افزودن Provider در config/app.php
│       └── Laravel\Sanctum\SanctumServiceProvider::class
│
├── [PHASE ۲] ساخت مدل‌ها
│   ├── ۲-۱. مدل ApiIntegration
│   │   └── app/Models/ApiIntegration.php
│   ├── ۲-۲. مدل Province
│   │   └── app/Models/Province.php
│   ├── ۲-۳. مدل City
│   │   └── app/Models/City.php
│   └── ۲-۴. به‌روزرسانی مدل User
│       └── افزودن fillable fields + رابطه apiIntegration()
│
├── [PHASE ۳] ساخت AuthController API
│   ├── ۳-۱. ساخت فایل AuthController.php
│   │   └── app/Http/Controllers/API/AuthController.php
│   └── ۳-۲. پیاده‌سازی متدها
│       ├── register() - ثبت‌نام
│       ├── login() - ورود
│       ├── logout() - خروج
│       └── profile() - دریافت پروفایل
│
├── [PHASE ۴] ساخت ProviderController API
│   ├── ۴-۱. ساخت فایل ProviderController.php
│   │   └── app/Http/Controllers/API/ProviderController.php
│   └── ۴-۲. پیاده‌سازی متدها
│       ├── index() - لیست ارائه‌دهندگان
│       ├── show($id) - جزئیات ارائه‌دهنده
│       └── map() - لیست برای نقشه
│
├── [PHASE ۵] ساخت AppointmentController API
│   ├── ۵-۱. ساخت فایل AppointmentController.php
│   │   └── app/Http/Controllers/API/AppointmentController.php
│   └── ۵-۲. پیاده‌سازی متدها
│       ├── slots($providerId) - بازه‌های زمانی
│       ├── store() - ثبت نوبت
│       └── myAppointments() - نوبت‌های من
│
├── [PHASE ۶] تعریف روت‌های API
│   ├── ۶-۱. به‌روزرسانی routes/api.php
│   │   └── تعریف روت‌های v1
│   └── ۶-۲. تست روت‌ها
│       └── php artisan route:list
│
├── [PHASE ۷] ساخت پلاگین پنل کاربر
│   ├── ۷-۱. ساخت ApiIntegrationController
│   │   └── app/Http/Controllers/User/ApiIntegrationController.php
│   ├── ۷-۲. ساخت View
│   │   └── resources/views/user/settings/api-integration.blade.php
│   ├── ۷-۳. افزودن مسیرها در routes/web.php
│   └── ۷-۴. افزودن لینک در منوی پنل کاربر
│       └── resources/views/user/partials/side-navbar.blade.php
│
├── [PHASE ۸] ساخت LocationService
│   └── ۸-۱. ساخت فایل LocationService.php
│       └── app/Services/LocationService.php
│
└── [PHASE ۹] تنظیمات نهایی
    ├── ۹-۱. افزودن کانال لاگ api
    │   └── config/logging.php
    └── ۹-۲. تست نهایی
        └── Postman/Insomnia tests
```

---

## چک‌لیست execution

### فاز ۰: مهاجرت‌های دیتابیس
- [x] ۰-۱. Migration افزودن فیلدها به users
- [x] ۰-۲. Migration ایجاد جدول provinces
- [x] ۰-۳. Migration ایجاد جدول cities
- [x] ۰-۴. Migration ایجاد جدول api_integrations
- [x] ۰-۵. Migration افزودن فیلدها به basic_settings

### فاز ۱: نصب و تنظیم Laravel Sanctum
- [x] ۱-۱. نصب Sanctum
- [x] ۱-۲. تنظیم config/sanctum.php
- [x] ۱-۳. تنظیم .env
- [x] ۱-۴. تنظیم app/Http/Kernel.php
- [x] ۱-۵. افزودن Provider در config/app.php

### فاز ۲: ساخت مدل‌ها
- [x] ۲-۱. مدل ApiIntegration
- [x] ۲-۲. مدل Province
- [x] ۲-۳. مدل City
- [x] ۲-۴. به‌روزرسانی مدل User

### فاز ۳: ساخت AuthController API
- [x] ۳-۱. ساخت فایل AuthController.php
- [x] ۳-۲. پیاده‌سازی متدها

### فاز ۴: ساخت ProviderController API
- [x] ۴-۱. ساخت فایل ProviderController.php
- [x] ۴-۲. پیاده‌سازی متدها

### فاز ۵: ساخت AppointmentController API
- [x] ۵-۱. ساخت فایل AppointmentController.php
- [x] ۵-۲. پیاده‌سازی متدها

### فاز ۶: تعریف روت‌های API
- [x] ۶-۱. به‌روزرسانی routes/api.php
- [ ] ۶-۲. تست روت‌ها

### فاز ۷: ساخت پلاگین پنل کاربر
- [x] ۷-۱. ساخت ApiIntegrationController
- [x] ۷-۲. ساخت View
- [x] ۷-۳. افزودن مسیرها در routes/web.php
- [x] ۷-۴. افزودن لینک در منوی پنل کاربر

### فاز ۸: ساخت LocationService
- [x] ۸-۱. ساخت فایل LocationService.php

### فاز ۹: تنظیمات نهایی
- [x] ۹-۱. افزودن کانال لاگ api
- [ ] ۹-۲. تست نهایی

---

## کارهای remaining

### قبل از اجرای migrations
1. اطمینان از راه‌اندازی MySQL/MariaDB
2. تنظیمات دیتابیس در `.env`:
   - `DB_HOST=127.0.0.1`
   - `DB_PORT=3306`
   - `DB_DATABASE=proresume`
   - `DB_USERNAME=root`
   - `DB_PASSWORD=` (خالی)

### برای تست
1. اجرای migrations:
   ```bash
   php artisan migrate --path=database/migrations
   php artisan migrate --path=updater/database/migrations
   ```

2. تست روت‌ها:
   ```bash
   php artisan route:list | findstr api/v1
   ```

3. تست با Postman:
   - تست ثبت‌نام
   - تست ورود
   - تست دریافت لیست ارائه‌دهندگان
   - تست رزرو نوبت

### برای production
1. تغییر `APP_DEBUG=false`
2. تنظیم `APP_URL` صحیح
3. تنظیم `SANCTUM_STATEFUL_DOMAINS` برای دامنه production
4. غیرفعال کردن CORS عمومی
5. تست SSL certificate

---

## مشکلات شناخته شده

| مشکل | راه‌حل |
|------|--------|
| MySQL/MariaDB راه‌اندازی نشده | اجرای `mysqld` با تنظیمات صحیح |
| Imagick version warning | warning غیرمهم، عملکرد راaffe نمی‌کند |
| Sanctum ServiceProvider not found | composer dump-autoload |

---

## یادداشت‌ها

- دیتابیس هدف: `proresume` روی `127.0.0.1:3306` با کاربر `root` و بدون پسورد
- migrations در دو مسیر وجود دارند: `database/migrations` و `updater/database/migrations`
- فایل‌های migration باید به ترتیب اجرا شوند
- پس از نصب، حتماً `php artisan config:clear` اجرا شود
