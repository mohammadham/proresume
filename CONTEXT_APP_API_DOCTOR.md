# دانش پروژه ProResume - API Doctor

> **تاریخ ایجاد:** 2026-08-29
> **نسخه:** 1.0.0

---

## فهرست مطالب

1. [نمای کلی پروژه](#نمای-کلی-پروژه)
2. [معماری سیستم](#معماری-سیستم)
3. [اجزای اصلی](#اجزای-اصلی)
4. [جریان‌های داده](#جریانهای-داده)
5. [وابستگی‌ها](#وابستگیها)
6. [نکات فنی](#نکات-فنی)

---

## نمای کلی پروژه

ProResume یک پلتفرم وب برای ساخت رزومه و پروفایل آنلاین است که در حال حاضر در حال اضافه کردن قابلیت‌های API برای یکپارچگی با اپلیکیشن‌های موبایل پزشکی/ارایشی است.

### بازیگران اصلی

1. **صاحب پلتفرم** - مدیر کل سیستم
2. **ارائه‌دهندگان** - پزشکان/ارایشگران که از پنل کاربر سایت خودشان را مدیریت می‌کنند
3. **مراجعین/بیماران** - کاربران نهایی که از اپ موبایل برای رزرو نوبت استفاده می‌کنند

---

## معماری سیستم

### لایه‌بندی

```
┌─────────────────────────────────────────┐
│         اپلیکیشن موبایل (Mobile App)      │
│         (React Native / Flutter)         │
└─────────────────────────────────────────┘
                    │
                    │ HTTP/JSON
                    ▼
┌─────────────────────────────────────────┐
│           Laravel API Layer              │
│  ┌─────────────────────────────────┐    │
│  │  routes/api.php                 │    │
│  │  - AuthController               │    │
│  │  - ProviderController           │    │
│  │  - AppointmentController        │    │
│  └─────────────────────────────────┘    │
└─────────────────────────────────────────┘
                    │
                    ▼
┌─────────────────────────────────────────┐
│         Business Logic Layer             │
│  ┌─────────────────────────────────┐    │
│  │  Controllers/API/               │    │
│  │  Controllers/User/              │    │
│  │  Services/LocationService.php   │    │
│  └─────────────────────────────────┘    │
└─────────────────────────────────────────┘
                    │
                    ▼
┌─────────────────────────────────────────┐
│         Data Access Layer                │
│  ┌─────────────────────────────────┐    │
│  │  Models/                        │    │
│  │  - User                         │    │
│  │  - ApiIntegration               │    │
│  │  - Province                     │    │
│  │  - City                         │    │
│  │  - AppointmentBooking           │    │
│  └─────────────────────────────────┘    │
└─────────────────────────────────────────┘
                    │
                    ▼
┌─────────────────────────────────────────┐
│         Database Layer                   │
│  ┌─────────────────────────────────┐    │
│  │  MySQL/MariaDB                   │    │
│  │  - users                         │    │
│  │  - api_integrations              │    │
│  │  - provinces                     │    │
│  │  - cities                        │    │
│  │  - appointment_bookings          │    │
│  └─────────────────────────────────┘    │
└─────────────────────────────────────────┘
                    │
                    ▼
┌─────────────────────────────────────────┐
│         پنل کاربر (Web)                  │
│  ┌─────────────────────────────────┐    │
│  │  User/ApiIntegrationController  │    │
│  │  views/user/settings/           │    │
│  │  api-integration.blade.php      │    │
│  └─────────────────────────────────┘    │
└─────────────────────────────────────────┘
```

---

## اجزای اصلی

### 1. لایه API

#### AuthController
- **مسیر:** `app/Http/Controllers/API/AuthController.php`
- **وظایف:**
  - ثبت‌نام کاربران جدید
  - ورود با ایمیل/موبایل
  - خروج و حذف token
  - دریافت پروفایل کاربر

#### ProviderController
- **مسیر:** `app/Http/Controllers/API/ProviderController.php`
- **وظایف:**
  - لیست ارائه‌دهندگان با فیلترها
  - جزئیات ارائه‌دهنده
  - لیست ارائه‌دهندگان برای نقشه

#### AppointmentController
- **مسیر:** `app/Http/Controllers/API/AppointmentController.php`
- **وظایف:**
  - دریافت بازه‌های زمانی آزاد
  - ثبت نوبت جدید
  - لیست نوبت‌های کاربر

### 2. لایه مدل‌ها

#### User
- **مسیر:** `app/Models/User.php`
- **ویژگی‌ها:**
  - کاربران سیستم (ارائه‌دهندگان و مراجعین)
  - فیلدهای اضافه شده: `service_type`, `specialty`, `district`, `lat`, `lng`
  - رابطه `apiIntegration()` با مدل ApiIntegration

#### ApiIntegration
- **مسیر:** `app/Models/ApiIntegration.php`
- **ویژگی‌ها:**
  - تنظیمات یکپارچگی API برای هر کاربر
  - تولید API Key با `generateApiKey()`
  - فیلدها: `api_key`, `is_active`, `app_type`, `settings`

#### Province
- **مسیر:** `app/Models/Province.php`
- **ویژگی‌ها:**
  - لیست استان‌ها
  - رابطه `cities()` با مدل City

#### City
- **مسیر:** `app/Models/City.php`
- **ویژگی‌ها:**
  - لیست شهرها
  - رابطه `province()` با مدل Province

### 3. لایه سرویس

#### LocationService
- **مسیر:** `app/Services/LocationService.php`
- **وظایف:**
  - جستجوی ارائه‌دهندگان در شعاع مشخص
  - محاسبه فاصله با فرمول Haversine
  - فیلتر بر اساس استان/شهر/منطقه

### 4. لایه پنل کاربر

#### ApiIntegrationController
- **مسیر:** `app/Http/Controllers/User/ApiIntegrationController.php`
- **وظایف:**
  - نمایش صفحه تنظیمات یکپارچگی
  - به‌روزرسانی تنظیمات
  - بازنشانی API Key
  - به‌روزرسانی پروفایل
  - دریافت شهرهای یک استان

#### View
- **مسیر:** `resources/views/user/settings/api-integration.blade.php`
- **ویژگی‌ها:**
  - فرم انتخاب نوع فعالیت
  - نمایش/کپی API Key
  - فرم اطلاعات مکانی
  -dropdown استان/شهر

---

## جریان‌های داده

### جریان ۱: ثبت‌نام و ورود

```
Mobile App → POST /api/v1/register → AuthController → User::create() → Sanctum Token
Mobile App ← JSON Response (token + user)
```

### جریان ۲: جستجوی ارائه‌دهنده

```
Mobile App → GET /api/v1/providers?service_type=doctor&province=تهران
         → ProviderController::index()
         → User::where('service_type', 'doctor')->where('state', 'تهران')
         → with('apiIntegration')
         → paginate(20)
Mobile App ← JSON Response (لیست ارائه‌دهندگان)
```

### جریان ۳: رزرو نوبت

```
Mobile App → GET /api/v1/appointments/slots/1?date=2026-09-01
         → AppointmentController::slots()
         → UserTimeSlot::where('user_id', 1)->where('day', 'Monday')
         → AppointmentBooking::where('user_id', 1)->where('date', '2026-09-01')
         → Mark slots as available/booked
Mobile App ← JSON Response (slots)

Mobile App → POST /api/v1/appointments
         → AppointmentController::store()
         → Check slot availability
         → AppointmentBooking::create()
Mobile App ← JSON Response (appointment created)
```

### جریان ۴: فعال‌سازی API در پنل کاربر

```
Doctor → GET /api-integration
      → ApiIntegrationController::index()
      → View: api-integration.blade.php

Doctor → POST /api-integration/update
      → ApiIntegrationController::update()
      → Create/Update ApiIntegration
      → Generate API Key

Doctor → POST /api-integration/profile
      → ApiIntegrationController::updateProfile()
      → Update User fields (service_type, specialty, lat, lng, etc.)
```

---

## وابستگی‌ها

### پکیج‌های Composer

| پکیج | نسخه | کاربرد |
|------|------|--------|
| laravel/sanctum | ^3.3 | احراز هویت Token-based |
| laravel/framework | ^9.0 | فریم‌ورک اصلی |
| guzzlehttp/guzzle | ^7.5 | درخواست HTTP |

### کلاس‌های داخلی استفاده شده

| کلاس | مسیر | کاربرد |
|------|------|--------|
| User | `App\Models\User` | مدل کاربران |
| ApiIntegration | `App\Models\ApiIntegration` | مدل یکپارچگی |
| Province | `App\Models\Province` | مدل استان‌ها |
| City | `App\Models\City` | مدل شهرها |
| UserTimeSlot | `App\Models\User\UserTimeSlot` | بازه‌های زمانی |
| AppointmentBooking | `App\Models\User\AppointmentBooking` | رزروهای نوبت |

---

## نکات فنی

### احراز هویت
- استفاده از Laravel Sanctum برای Token-based auth
- Token در هدر `Authorization: Bearer {token}` ارسال می‌شود
- توکن‌ها در جدول `personal_access_tokens` ذخیره می‌شوند

### فیلتر مکان
- استفاده از فرمول Haversine برای محاسبه فاصله
- فیلترها: `service_type`, `province` (state), `city`, `district`
- فقط ارائه‌دهندگان با `apiIntegration.is_active = true` نمایش داده می‌شوند

### محدودیت نرخ
- `throttle:60,1` در روت‌های API (۶۰ درخواست در دقیقه)

### Pagination
- لیست‌ها با pagination ۲۰ آیتم در صفحه برگشت داده می‌شوند

---

## وضعیت پیاده‌سازی

| فاز | وضعیت | توضیحات |
|-----|-------|---------|
| فاز ۰: Migrations | ✅ | ۵ فایل migration ایجاد شده |
| فاز ۱: Sanctum | ✅ | نصب و تنظیم شد |
| فاز ۲: مدل‌ها | ✅ | ApiIntegration, Province, City ایجاد شد |
| فاز ۳: AuthController | ✅ | register, login, logout, profile |
| فاز ۴: ProviderController | ✅ | index, show, map |
| فاز ۵: AppointmentController | ✅ | slots, store, myAppointments |
| فاز ۶: روت‌های API | ✅ | در routes/api.php تعریف شد |
| فاز ۷: پلاگین پنل | ✅ | Controller + View + Routes |
| فاز ۸: LocationService | ✅ | سرویس جستجوی مکان |
| فاز ۹: تنظیمات نهایی | ✅ | logging channel اضافه شد |

---

## فایل‌های ایجاد شده

### مدل‌ها
- `app/Models/ApiIntegration.php`
- `app/Models/Province.php`
- `app/Models/City.php`
- `app/Models/User.php` (تغییر داده شده)

### کنترلرهای API
- `app/Http/Controllers/API/AuthController.php`
- `app/Http/Controllers/API/ProviderController.php`
- `app/Http/Controllers/API/AppointmentController.php`

### کنترلر پنل کاربر
- `app/Http/Controllers/User/ApiIntegrationController.php`

### ویو
- `resources/views/user/settings/api-integration.blade.php`

### سرویس
- `app/Services/LocationService.php`

### تنظیمات
- `config/sanctum.php`
- `config/logging.php` (کانال api اضافه شد)
- `config/app.php` (SanctumServiceProvider اضافه شد)
- `.env` (تنظیمات Sanctum)
- `app/Http/Kernel.php` (Middleware Sanctum)

### روت‌ها
- `routes/api.php` (روت‌های API)
- `routes/web.php` (روت‌های پلاگین)

### Migrationها
- `database/migrations/2026_01_15_000002_add_service_fields_to_users_table.php`
- `database/migrations/2026_01_15_000003_create_provinces_table.php`
- `database/migrations/2026_01_15_000004_create_cities_table.php`
- `database/migrations/2026_01_15_000005_create_api_integrations_table.php`
- `database/migrations/2026_01_15_000006_add_api_fields_to_basic_settings_table.php`

### Migrationهای Updater
- `updater/database/migrations/2026_08_24_000006_create_api_integrations_table.php`
- `updater/database/migrations/2026_08_24_000007_add_api_fields_to_basic_settings_table.php`
- `updater/database/migrations/2026_08_24_000008_add_service_fields_to_users_table.php`
- `updater/database/migrations/2026_08_24_000009_create_provinces_table.php`
- `updater/database/migrations/2026_08_24_000010_create_cities_table.php`

---

## مستندات مرتبط

- `API_DOCUMENTATION.md` - مستندات کامل API
- `TODO_API.md` - لیست کارهای remaining
