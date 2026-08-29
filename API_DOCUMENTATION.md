# مستندات API پروژه ProResume

> **تاریخ ایجاد:** 2026-08-29
> **نسخه:** 1.0.0
> **وضعیت:** در حال پیاده‌سازی

---

## فهرست مطالب

1. [معرفی](#معرفی)
2. [نیازمندی‌ها](#نیازمندیها)
3. [ساختار دیتابیس](#ساختار-دیتابیس)
4. [مدل‌ها](#مدلها)
5. [API Endpoints](#api-endpoints)
6. [پلاگین پنل کاربر](#پلاگین-پنل-کاربر)
7. [امنیت](#امنیت)
8. [نصب و پیاده‌سازی](#نصب-و-پیادهسازی)
9. [تست](#تست)

---

## معرفی

این سرویس API برای یکپارچگی اپلیکیشن‌های موبایل با پلتفرم ProResume طراحی شده است. هدف اصلی ارائه سرویس‌های مربوط به نوبت‌دهی پزشکی/ارایشی از طریق API است.

### قابلیت‌های اصلی

- ثبت‌نام و ورود کاربران از طریق API
- جستجوی ارائه‌دهندگان خدمات (پزشک/ارایشگر)
- مشاهده لیست ارائه‌دهندگان روی نقشه
- ثبت و مدیریت نوبت‌های appointments
- پنل مدیریت یکپارچگی در پنل کاربر

---

## نیازمندی‌ها

- PHP 8.0+
- Laravel 9.x
- MySQL/MariaDB
- Composer
- Laravel Sanctum

---

## ساختار دیتابیس

### جداول اصلی

| جدول | توضیحات |
|------|---------|
| `users` | کاربران سیستم (ارائه‌دهندگان و مراجعین) |
| `provinces` | لیست استان‌ها |
| `cities` | لیست شهرها |
| `api_integrations` | تنظیمات یکپارچگی API برای هر کاربر |
| `appointment_bookings` | رزروهای نوبت |
| `categories` | دسته‌بندی خدمات/تخصص‌ها |
| `user_time_slots` | بازه‌های زمانی قابل رزرو |
| `basic_settings` | تنظیمات پایه کاربر |
| `personal_access_tokens` | توکن‌های Sanctum |

---

## مدل‌ها

### User

فایل: `app/Models/User.php`

فیلدهای اضافه شده:
- `service_type`: نوع فعالیت (doctor, clinic, barber)
- `specialty`: تخصص پزشکی
- `district`: محله/منطقه
- `lat`: عرض جغرافیایی
- `lng`: طول جغرافیایی

رابطه‌ها:
- `apiIntegration()`: ارتباط یک به یک با ApiIntegration

### ApiIntegration

فایل: `app/Models/ApiIntegration.php`

فیلدها:
- `user_id`: شناسه کاربر
- `api_key`: کلید API منحصر به فرد
- `is_active`: وضعیت فعال بودن
- `app_type`: نوع اپ (doctor, clinic, barber)
- `settings`: تنظیمات اضافی (JSON)

### Province

فایل: `app/Models/Province.php`

فیلدها:
- `name`: نام استان

### City

فایل: `app/Models/City.php`

فیلدها:
- `province_id`: شناسه استان
- `name`: نام شهر

---

## API Endpoints

### احراز هویت

| Method | Endpoint | Auth | توضیحات |
|--------|----------|------|---------|
| POST | `/api/v1/register` | No | ثبت‌نام کاربر |
| POST | `/api/v1/login` | No | ورود کاربر |
| POST | `/api/v1/logout` | Yes | خروج |
| GET | `/api/v1/profile` | Yes | دریافت پروفایل |

### ارائه‌دهندگان

| Method | Endpoint | Auth | توضیحات |
|--------|----------|------|---------|
| GET | `/api/v1/providers` | No | لیست ارائه‌دهندگان |
| GET | `/api/v1/providers/{id}` | No | جزئیات ارائه‌دهنده |
| GET | `/api/v1/providers/map` | No | لیست برای نقشه |

### نوبت‌دهی

| Method | Endpoint | Auth | توضیحات |
|--------|----------|------|---------|
| GET | `/api/v1/appointments/slots/{provider}` | Yes | بازه‌های زمانی آزاد |
| POST | `/api/v1/appointments` | Yes | ثبت نوبت |
| GET | `/api/v1/appointments` | Yes | نوبت‌های من |

---

## پلاگین پنل کاربر

### مسیرهای پنل کاربر

| Method | Endpoint | توضیحات |
|--------|----------|---------|
| GET | `/api-integration` | صفحه اصلی پلاگین |
| POST | `/api-integration/update` | به‌روزرسانی تنظیمات |
| POST | `/api-integration/profile` | به‌روزرسانی پروفایل |
| POST | `/api-integration/regenerate-key` | بازنشانی API Key |
| GET | `/api-integration/cities/{provinceId}` | دریافت شهرهای یک استان |

### تنظیمات قابل تغییر

- `app_type`: doctor | clinic | barber
- `is_active`: فعال/غیرفعال بودن
- `state`: استان
- `city`: شهر
- `district`: منطقه
- `address`: آدرس
- `lat`: عرض جغرافیایی
- `lng`: طول جغرافیایی
- `specialty`: تخصص پزشکی

---

## امنیت

| مسئله | راه‌حل |
|-------|--------|
| احراز هویت | Laravel Sanctum Token |
| تولید API Key | `hash('sha256', Str::random(60))` |
| محدودیت نرخ | `throttle:60,1` |
| Validation | Form Request Validation |
| SQL Injection | Eloquent ORM |

---

## نصب و پیاده‌سازی

### مراحل نصب

1. **نصب migrations:**
   ```bash
   php artisan migrate --path=database/migrations
   php artisan migrate --path=updater/database/migrations
   ```

2. **نصب Sanctum:**
   ```bash
   composer require laravel/sanctum
   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
   php artisan migrate
   ```

3. **تنظیمات `.env`:**
   ```env
   SESSION_DRIVER=cookie
   SESSION_DOMAIN=localhost
   SANCTUM_STATEFUL_DOMAINS=localhost
   ```

4. **تنظیمات `app/Http/Kernel.php`:**
   ```php
   'api' => [
       \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
       'throttle:60,1',
       'bindings',
   ],
   ```

5. **افزودن Provider در `config/app.php`:**
   ```php
   Laravel\Sanctum\SanctumServiceProvider::class,
   ```

6. **افزودن کانال لاگ در `config/logging.php`:**
   ```php
   'api' => [
       'driver' => 'daily',
       'path' => storage_path('logs/api.log'),
       'level' => 'debug',
       'days' => 30,
   ],
   ```

---

## تست

### تست با Postman

#### ۱. ثبت‌نام

```http
POST http://localhost/proresume/public/api/v1/register
Content-Type: application/json

{
    "name": "test user",
    "email": "test@example.com",
    "password": "password",
    "password_confirmation": "password",
    "phone": "09123456789"
}
```

#### ۲. ورود

```http
POST http://localhost/proresume/public/api/v1/login
Content-Type: application/json

{
    "login": "test@example.com",
    "password": "password"
}
```

#### ۳. دریافت لیست پزشکان

```http
GET http://localhost/proresume/public/api/v1/providers?service_type=doctor&province=تهران
```

#### ۴. دریافت جزئیات پزشک

```http
GET http://localhost/proresume/public/api/v1/providers/1
```

#### ۵. دریافت بازه‌های زمانی

```http
GET http://localhost/proresume/public/api/v1/appointments/slots/1?date=2026-09-01
Authorization: Bearer {token}
```

#### ۶. ثبت نوبت

```http
POST http://localhost/proresume/public/api/v1/appointments
Authorization: Bearer {token}
Content-Type: application/json

{
    "provider_id": 1,
    "category_id": 2,
    "booking_date": "2026-09-01",
    "time_slot_id": 5,
    "notes": "مطمئن شوید وقت خالی هست"
}
```

---

## نکات مهم

- تمام درخواست‌های API JSON برمی‌گردانند
- خطاها با status code مناسب (401, 403, 404, 422, 500) برمی‌گردانند
- Pagination برای لیست‌ها اعمال شده
- Auth با Sanctum Token (Bearer)
- دیتابیس: `127.0.0.1`, کاربر: `root`, پسورد: `(خالی)`, نام دیتابیس: `proresume`

---

## وضعیت پیاده‌سازی

| فاز | وضعیت |
|-----|-------|
| فاز ۰: Migrations | ✅ تکمیل شده |
| فاز ۱: Sanctum | ✅ تکمیل شده |
| فاز ۲: مدل ApiIntegration | ✅ تکمیل شده |
| فاز ۳: AuthController | ✅ تکمیل شده |
| فاز ۴: ProviderController | ✅ تکمیل شده |
| فاز ۵: AppointmentController | ✅ تکمیل شده |
| فاز ۶: روت‌های API | ✅ تکمیل شده |
| فاز ۷: پلاگین پنل کاربر | ✅ تکمیل شده |
| فاز ۸: LocationService | ✅ تکمیل شده |
| فاز ۹: تنظیمات نهایی | ✅ تکمیل شده |
