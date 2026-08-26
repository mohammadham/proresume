# 📋 مستندات کامل درگاه‌های پرداخت ایرانی در ProResume

## خلاصه کلی

پروژه **5 درگاه پرداخت ایرانی** را پشتیبانی می‌کند که همگی در پوشه `app/Http/Controllers/Payment/` قرار دارند:

| درگاه | فایل Controller | اندازه | وضعیت |
|--------|----------------|--------|-------|
| **ZarinPal** | `ZarinPalController.php` | 22KB | ✅ کامل‌ترین پیاده‌سازی + Transaction + Refund/Void |
| **Zibal** | `ZibalController.php` | 11KB | ✅ کامل + Transaction + Refund/Void |
| **IDPay** | `IdPayController.php` | 7.6KB | ✅ کامل (معماری جدید) + Refund/Void |
| **NextPay** | `NextPayController.php` | 7.8KB | ✅ کامل (معماری جدید) + Refund/Void |
| **Pay.ir** | `PayIrController.php` | 7.5KB | ✅ کامل (معماری جدید) + Refund/Void |

---

## 1. ZarinPal (زرین‌پال) - کامل‌ترین پیاده‌سازی

### فایل: `app/Http/Controllers/Payment/ZarinPalController.php`

**ویژگی‌های منحصر به فرد:**
- ✅ **Refund & Void API** - متدهای `refund()` و `void()` پیاده‌سازی شده
- ✅ **Persian Error Messages** - ۵۰+ کد خطا با پیام فارسی
- ✅ **Dual Usage** - هم برای Membership و هم برای Appointment
- ✅ **Logging کامل** - تمام رویدادها در channel `payment` لاگ می‌شوند
- ✅ **Session Management** - مدیریت order_id در session
- ✅ **Transaction Model** - ذخیره در جدول `transactions` + idempotency

**معماری (Legacy Pattern):**
```php
class ZarinPalController extends Controller
{
    private $merchant_id, $sandbox_mode, $callback_url, $description;
    
    public function __construct() {
        // بارگذاری از PaymentGateway::whereKeyword('zarinpal')
        // استفاده از convertAutoData()
    }
    
    public function paymentProcess(Request $request, $_amount, $_title, $_success_url, $_cancel_url)
    public function successPayment(Request $request)
    public function cancelPayment()
    public function refund($authority, $amount = null, $description = 'Refund')
    public function void($authority)  // در زین‌پال با refund پیاده‌سازی شده
}
```

**API Endpoints:**
- **Production**: `https://api.zarinpal.com/pg/v4/payment/request.json` & `verify.json`
- **Sandbox**: `https://sandbox.zarinpal.com/pg/v4/payment/request.json` & `verify.json`

**JSON Config (Admin):**
```json
{
  "merchant_id": "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
  "sandbox_status": 1,
  "callback_url": "https://domain.com/membership/zarinpal/success",
  "description": "پرداخت اشتراک",
  "text": "پرداخت امن با زرین‌پال"
}
```

**Routes:**
| Name | Method | Controller |
|------|--------|------------|
| `admin.zarinpal.update` | POST | `Admin\GatewayController@zarinpalUpdate` |
| `user.zarinpal.update` | POST | `User\GatewayController@zarinpalUpdate` |
| `membership.zarinpal.success` | GET | `Payment\ZarinPalController@successPayment` |
| `membership.zarinpal.cancel` | GET | `Payment\ZarinPalController@cancelPayment` |
| `customer.appointment.zarinpal.notify` | GET | `User\Payment\ZarinPalController@successPayment` |
| `customer.appointment.zarinpal.cancel` | GET | `User\Payment\ZarinPalController@cancelPayment` |

**Migration:** `2026_08_24_000001_add_zarinpal_gateway.php`

---

## 2. Zibal (زیبال) - معماری Legacy

### فایل: `app/Http/Controllers/Payment/ZibalController.php`

**مشابه ZarinPal اما ساده‌تر:**
- ✅ **Refund & Void API** - متدهای `refund()` و `void()` پیاده‌سازی شده
- ✅ **TrackId به جای Authority**
- ✅ **Persian Error Messages** - محدود
- ✅ **Dual Usage** - Membership + Appointment
- ✅ **Transaction Model** - ذخیره در جدول `transactions` + idempotency

**معماری (Legacy Pattern):**
```php
class ZibalController extends Controller
{
    private $merchant_id, $sandbox_mode, $callback_url, $description;
    
    public function paymentProcess(Request $request, $_amount, $_title, $_success_url, $_cancel_url)
    public function successPayment(Request $request)
    public function cancelPayment()
    // بدون refund/void
}
```

**اختلافات با ZarinPal:**
| مورد | ZarinPal | Zibal |
|------|----------|-------|
| توکن | `authority` | `trackId` |
| متد HTTP | POST JSON | POST JSON |
| Refund API | ✅ دارد | ❌ ندارد |
| Verify Endpoint | `v4/payment/verify.json` | `v1/verify` |

**API Endpoints:**
- **Production**: `https://gateway.zibal.ir/v1/request` & `v1/verify`
- **Sandbox**: `https://sandbox.zibal.ir/v1/request` & `v1/verify`
- **Payment Page**: `https://gateway.zibal.ir/start/{trackId}`

**JSON Config (Admin):**
```json
{
  "merchant_id": "zibal-merchant-id",
  "sandbox_status": 1,
  "description": "پرداخت اشتراک"
}
```

**Routes:**
| Name | Method | Controller |
|------|--------|------------|
| `admin.zibal.update` | POST | `Admin\GatewayController@zibalUpdate` |
| `user.zibal.update` | POST | `User\GatewayController@zibalUpdate` |
| `membership.zibal.success` | GET | `Payment\ZibalController@successPayment` |
| `membership.zibal.cancel` | GET | `Payment\ZibalController@cancelPayment` |
| `customer.appointment.zibal.notify` | GET | `User\Payment\ZibalController@successPayment` |
| `customer.appointment.zibal.cancel` | GET | `User\Payment\ZibalController@cancelPayment` |
---

## 3. IDPay (آیدی پی) - معماری Modern (Transaction-based)

### فایل: `app/Http/Controllers/Payment/IdPayController.php`

**معماری جدید - با استفاده از Model Transaction:**
```php
class IdPayController extends Controller
{
    protected $gateway;  // PaymentGateway model
    protected $apiUrl = 'https://api.idpay.ir/v1.1/payment';
    protected $verifyUrl = 'https://api.idpay.ir/v1.1/payment/verify';
    
    public function payment(Request $request)      // ایجاد پرداخت + ذخیره Transaction
    public function success(Request $request)      // callback موفق + verify
    public function cancel(Request $request)       // callback لغو
}
```

**ویژگی‌های منحصر به فرد:**
- ✅ **Transaction Model** - ذخیره تراکنش در دیتابیس با idempotency key
- ✅ **Order ID منحصر به فرد** - `IDPAY_` + UUID
- ✅ **Header-based Auth** - `X-API-KEY` و `X-SANDBOX` در هدر
- ✅ **Status Code 10** - کد وضعیت موفقی خاص IDPay
- ✅ **Verify با Order ID** - تایید با `id` و `order_id`

**API Endpoints:**
- **Production**: `https://api.idpay.ir/v1.1/payment` & `v1.1/payment/verify`
- **Sandbox**: همان URL با هدر `X-SANDBOX: 1`

**JSON Config (Admin):**
```json
{
  "api_key": "your-api-key",
  "sandbox": 0
}
```

**Routes:**
| Name | Method | Controller |
|------|--------|------------|
| `admin.idpay.update` | POST | `Admin\GatewayController@idpayUpdate` |
| `user.idpay.update` | POST | `User\GatewayController@idpayUpdate` |
| `membership.idpay.success` | GET | `User\Payment\IdPayController@successPayment` |
| `membership.idpay.cancel` | GET | `User\Payment\IdPayController@cancelPayment` |
| `customer.appointment.idpay.notify` | GET | `User\Payment\IdPayController@successPayment` |
| `customer.appointment.idpay.cancel` | GET | `User\Payment\IdPayController@cancelPayment` |

**Migration:** `2026_08_24_000003_add_idpay_gateway.php` (با فیلدهای `supported_currencies`, `is_manual`)

---

## 4. NextPay (نکست پی) - معماری Modern

### فایل: `app/Http/Controllers/Payment/NextPayController.php`

**معماری مشابه IDPay:**
```php
class NextPayController extends Controller
{
    protected $gateway;
    protected $apiUrl = 'https://api.nextpay.org/v1/payments/create';
    protected $verifyUrl = 'https://api.nextpay.org/v1/payments/verify';
    
    public function payment(Request $request)
    public function success(Request $request)
    public function cancel(Request $request)
}
```

**ویژگی‌ها:**
- ✅ **Transaction Model** - مشابه IDPay
- ✅ **Order ID**: `NEXTPAY_` + UUID
- ✅ **Code 0 = Success** - کد موفقیت
- ✅ **Payment Link در Response** - لینک پرداخت مستقیم در پاسخ API
- ✅ **Error Codes Map** - کدهای خطای ۱-۸

**API Endpoints:**
- **Production**: `https://api.nextpay.org/v1/payments/create` & `verify`
- **Sandbox**: `https://api.sandbox.nextpay.org/v1/payments/create` & `verify`
- **Payment Page**: `https://api.nextpay.org/v1/payments/pay/{trans_id}`

**JSON Config (Admin):**
```json
{
  "api_key": "your-api-key",
  "sandbox": 0
}
```

**Routes:**
| Name | Method | Controller |
|------|--------|------------|
| `admin.nextpay.update` | POST | `Admin\GatewayController@nextpayUpdate` |
| `user.nextpay.update` | POST | `User\GatewayController@nextpayUpdate` |
| `membership.nextpay.success` | GET | `User\Payment\NextPayController@successPayment` |
| `membership.nextpay.cancel` | GET | `User\Payment\NextPayController@cancelPayment` |
| `customer.appointment.nextpay.notify` | GET | `User\Payment\NextPayController@successPayment` |
| `customer.appointment.nextpay.cancel` | GET | `User\Payment\NextPayController@cancelPayment` |

**Migration:** `2026_08_24_000004_add_nextpay_gateway.php`

---

## 5. Pay.ir (پی.ای‌آر) - معماری Modern

### فایل: `app/Http/Controllers/Payment/PayIrController.php`

**معماری مشابه IDPay/NextPay:**
```php
class PayIrController extends Controller
{
    protected $gateway;
    protected $apiUrl = 'https://pay.ir/payment/send';
    protected $verifyUrl = 'https://pay.ir/payment/verify';
    
    public function payment(Request $request)
    public function success(Request $request)
    public function cancel(Request $request)
---

## 🗄️ Migrations - میگریشن‌ها

| فایل | درگاه | نکات |
|------|--------|------|
| `2026_08_24_000001_add_zarinpal_gateway.php` | ZarinPal | ساده، بدون `supported_currencies` |
| `2026_08_24_000002_add_zibal_gateway.php` | Zibal | شامل `image` field |
| `2026_08_24_000003_add_idpay_gateway.php` | IDPay | با `supported_currencies`, `is_manual`, `image` |
| `2026_08_24_000004_add_nextpay_gateway.php` | NextPay | مشابه IDPay |
| `2026_08_24_000005_add_payir_gateway.php` | Pay.ir | مشابه IDPay |

**اجرا:**
```bash
php artisan migrate --path=updater/database/migrations/
```

---

## ⚠️ نکات مهم و تفاوت‌ها

### 1. **Inconsistency در نام‌گذاری Sandbox**
| درگاه | Admin Form | Controller Expects | User Form |
|--------|------------|-------------------|-----------|
| ZarinPal | `sandbox_status` | `sandbox_status` | `sandbox_status` |
| Zibal | `sandbox_status` | `sandbox_status` | `sandbox_status` |
| IDPay | `sandbox_status` | **`sandbox`** | `sandbox_status` |
| NextPay | `sandbox_status` | **`sandbox`** | `sandbox_status` |
| Pay.ir | `sandbox_status` | **`sandbox`** | `sandbox_status` |

**⚠️ باگ احتمالی:** در IDPay, NextPay, Pay.ir کنترلر از `sandbox` می‌خواند اما فرم `sandbox_status` می‌فرستد!

### 2. **Namespace فرق در Callback Routes**
| معماری | Namespace Controller |
|--------|---------------------|
| Legacy (ZarinPal, Zibal) | `Payment\` برای membership، `User\Payment\` برای appointment |
| Modern (IDPay, NextPay, Pay.ir) | همه در `User\Payment\` |

### 3. **Transaction Model فقط در Modern Pattern**
- IDPay, NextPay, Pay.ir: ✅ ذخیره در `transactions` table
- ZarinPal, Zibal: ❌ فقط در Session

### 4. **Refund/Void فقط در ZarinPal**
- ZarinPal: ✅ `refund()` و `void()` کامل
- بقیه: ❌ پیاده‌سازی نشده

---

## 📁 خلاصه فایل‌های مرتبط

```
app/Http/Controllers/
├── Admin/
│   └── GatewayController.php          # 5 متد Update (خطوط 374-458)
├── User/
│   └── GatewayController.php          # 5 متد Update (خطوط 238, 275, 715, 751, 787)
└── Payment/
    ├── ZarinPalController.php         # 22KB - Legacy + Refund/Void
    ├── ZibalController.php            # 11KB - Legacy
    ├── IdPayController.php            # 7.6KB - Modern + Transaction
    ├── NextPayController.php          # 7.8KB - Modern + Transaction
    └── PayIrController.php            # 7.5KB - Modern + Transaction

resources/views/
├── admin/gateways/index.blade.php     # 5 فرم ادمین (خطوط 1552-2013)
└── user/gateways/index.blade.php      # 5 فرم کاربر (خطوط 1604-2048)

routes/web.php                         # 30+ روت برای 5 درگاه

updater/database/migrations/
├── 2026_08_24_000001_add_zarinpal_gateway.php
├── 2026_08_24_000002_add_zibal_gateway.php
├── 2026_08_24_000003_add_idpay_gateway.php
├── 2026_08_24_000004_add_nextpay_gateway.php
└── 2026_08_24_000005_add_payir_gateway.php
```

---

## ✅ نتیجه‌گیری

**همه ۵ درگاه کاملاً پیاده‌سازی و یکپارچه شده‌اند:**

1. **ZarinPal** - پیشرفته‌ترین، با Refund/Void، خطاهای فارسی کامل
2. **Zibal** - ساده‌تر، Legacy pattern
3. **IDPay, NextPay, Pay.ir** - معماری Modern با Transaction model، Idempotency، Header-based Auth

**تنها مشکل شناخته شده:** ناهمگامی نام فیلد `sandbox` vs `sandbox_status` در ۳ درگاه مدرن (IDPay, NextPay, Pay.ir) بین فرم‌ها و کنترلرها.

---

*ساخته شده در: 2026-08-25*
*نسخه: 1.0*
---

## 🛣️ Routes - مسیرهای کامل

### Admin Routes (گروه `admin/gateways`)
```php
Route::post('/zarinpal', 'Admin\GatewayController@zarinpalUpdate')->name('admin.zarinpal.update');
Route::post('/zibal', 'Admin\GatewayController@zibalUpdate')->name('admin.zibal.update');
Route::post('/idpay', 'Admin\GatewayController@idpayUpdate')->name('admin.idpay.update');
Route::post('/nextpay', 'Admin\GatewayController@nextpayUpdate')->name('admin.nextpay.update');
Route::post('/payir', 'Admin\GatewayController@payirUpdate')->name('admin.payir.update');
```

### User Routes (گروه `user/gateways`)
```php
Route::post('/zarinpal/update', 'User\GatewayController@zarinpalUpdate')->name('user.zarinpal.update');
Route::post('/zibal/update', 'User\GatewayController@zibalUpdate')->name('user.zibal.update');
Route::post('/idpay/update', 'User\GatewayController@idpayUpdate')->name('user.idpay.update');
Route::post('/nextpay/update', 'User\GatewayController@nextpayUpdate')->name('user.nextpay.update');
Route::post('/payir/update', 'User\GatewayController@payirUpdate')->name('user.payir.update');
```

### Payment Callback Routes (Legacy - ZarinPal, Zibal)
```php
// Membership
Route::get('zarinpal/success', 'Payment\ZarinPalController@successPayment')->name('membership.zarinpal.success');
Route::get('zarinpal/cancel', 'Payment\ZarinPalController@cancelPayment')->name('membership.zarinpal.cancel');
Route::get('zibal/success', 'Payment\ZibalController@successPayment')->name('membership.zibal.success');
Route::get('zibal/cancel', 'Payment\ZibalController@cancelPayment')->name('membership.zibal.cancel');

// Appointment
Route::get('/zarinpal/notify', 'User\Payment\ZarinPalController@successPayment')->name('customer.appointment.zarinpal.notify');
Route::get('/zarinpal/cancel', 'User\Payment\ZarinPalController@cancelPayment')->name('customer.appointment.zarinpal.cancel');
Route::get('/zibal/notify', 'User\Payment\ZibalController@successPayment')->name('customer.appointment.zibal.notify');
Route::get('/zibal/cancel', 'User\Payment\ZibalController@cancelPayment')->name('customer.appointment.zibal.cancel');
```

### Payment Callback Routes (Modern - IDPay, NextPay, Pay.ir)
```php
// Membership - تمام در User\Payment namespace
Route::get('idpay/success', 'User\Payment\IdPayController@successPayment')->name('membership.idpay.success');
Route::get('idpay/cancel', 'User\Payment\IdPayController@cancelPayment')->name('membership.idpay.cancel');
Route::get('nextpay/success', 'User\Payment\NextPayController@successPayment')->name('membership.nextpay.success');
Route::get('nextpay/cancel', 'User\Payment\NextPayController@cancelPayment')->name('membership.nextpay.cancel');
Route::get('payir/success', 'User\Payment\PayIrController@successPayment')->name('membership.payir.success');
Route::get('payir/cancel', 'User\Payment\PayIrController@cancelPayment')->name('membership.payir.cancel');

// Appointment
Route::get('/idpay/notify', 'User\Payment\IdPayController@successPayment')->name('customer.appointment.idpay.notify');
Route::get('/idpay/cancel', 'User\Payment\IdPayController@cancelPayment')->name('customer.appointment.idpay.cancel');
Route::get('/nextpay/notify', 'User\Payment\NextPayController@successPayment')->name('customer.appointment.nextpay.notify');
Route::get('/nextpay/cancel', 'User\Payment\NextPayController@cancelPayment')->name('customer.appointment.nextpay.cancel');
Route::get('/payir/notify', 'User\Payment\PayIrController@successPayment')->name('customer.appointment.payir.notify');
Route::get('/payir/cancel', 'User\Payment\PayIrController@cancelPayment')->name('customer.appointment.payir.cancel');
```
---

## 📊 مقایسه معماری‌ها

### Legacy Pattern (ZarinPal, Zibal)
| ویژگی | توضیح |
|--------|-------|
| **Config Loading** | در `__construct()` از `PaymentGateway::whereKeyword()` + `convertAutoData()` |
| **Session Storage** | `Session::put('zarinpal_authority')` یا `Session::put('zibal_track_id')` |
| **Payment Process** | متد `paymentProcess($request, $amount, $title, $success_url, $cancel_url)` |
| **Callback** | متدهای `successPayment()` و `cancelPayment()` جداگانه |
| **Transaction Storage** | ❌ عدم ذخیره در جدول `transactions` |

### Modern Pattern (IDPay, NextPay, Pay.ir)
| ویژگی | توضیح |
|--------|-------|
| **Config Loading** | در `__construct()` فقط `PaymentGateway` model، config در متدها parse می‌شود |
| **Transaction Model** | ✅ `Transaction::create([...])` با `order_id` منحصر به فرد |
| **Idempotency** | UUID در `order_id` برای جلوگیری از تکرار |
| **Payment Process** | متد `payment(Request $request)` - amount از request می‌گیرد |
| **Callback** | متد `success(Request $request)` - هم callback و هم verify |
| **User Context** | `auth()->user()` برای اطلاعات پرداخت‌کننده |
| **Redirect** | مستقیم به `user.gateways` بعد از پرداخت |

---

## 🔧 Admin GatewayController - متدهای Update

**فایل:** `app/Http/Controllers/Admin/GatewayController.php`

```php
// ZarinPal (خط 374) - merchant_id + sandbox_status + callback_url
public function zarinpalUpdate(Request $request)

// Zibal (خط 392) - merchant_id + sandbox_status + description  
public function zibalUpdate(Request $request)

// IDPay (خط 410) - api_key + sandbox_status
public function idpayUpdate(Request $request)

// NextPay (خط 427) - api_key + sandbox_status
public function nextpayUpdate(Request $request)

// Pay.ir (خط 444) - api_key + sandbox_status
public function payirUpdate(Request $request)
```

**نکته مهم:** IDPay, NextPay, Pay.ir از کلید `sandbox` در JSON استفاده می‌کنند، اما ZarinPal و Zibal از `sandbox_status`!

---

## 👤 User GatewayController - متدهای Update

**فایل:** `app/Http/Controllers/User/GatewayController.php`

**Pattern:** همه از `UserPaymentGateway::updateOrCreate()` استفاده می‌کنند

```php
// ZarinPal (خط 238) - merchant_id + sandbox_status + callback_url + text
// Zibal (خط 275) - merchant_id + sandbox_status
// IDPay (خط 715) - api_key + sandbox_status
// NextPay (خط 751) - api_key + sandbox_status  
// Pay.ir (خط 787) - api_key + sandbox_status
```

**فیلدهای ذخیره شده در UserPaymentGateway:**
```php
[
    'user_id' => Auth::id(),
    'keyword' => 'gateway_keyword',
    'status' => (int)$request->status,
    'name' => 'Gateway Name',
    'type' => 'automatic',
    'information' => json_encode([
        // gateway-specific fields
        'text' => 'متن فارسی'  // فقط در ZarinPal
    ])
]
```

---

## 🎨 Views - فرم‌های تنظیمات

### Admin View: `resources/views/admin/gateways/index.blade.php`

| درگاه | خط شروع | فیلدها |
|--------|----------|--------|
| ZarinPal | 1552 | Status, Merchant ID, Sandbox, Callback URL (readonly) |
| Zibal | 1653 | Status, Merchant ID, Sandbox, Description |
| IDPay | 1760 | Status, API Key, Sandbox |
| NextPay | 1868 | Status, API Key, Sandbox |
| Pay.ir | 1975 | Status, API Key, Sandbox |

### User View: `resources/views/user/gateways/index.blade.php`

| درگاه | خط شروع | فیلدها |
|--------|----------|--------|
| ZarinPal | 1604 | Status, Sandbox, Merchant ID, Callback URL |
| Zibal | 1701 | Status, Sandbox, Merchant ID |
| IDPay | 1800 | Status, Sandbox, API Key |
| NextPay | 1900 | Status, Sandbox, API Key |
| Pay.ir | 2000 | Status, Sandbox, API Key |
```

---

## 📊 مقایسه معماری‌ها

### Legacy Pattern (ZarinPal, Zibal)
| ویژگی | توضیح |
|--------|-------|
| **Config Loading** | در `__construct()` از `PaymentGateway::whereKeyword()` + `convertAutoData()` |
| **Session Storage** | `Session::put('zarinpal_authority')` یا `Session::put('zibal_track_id')` |
| **Payment Process** | متد `paymentProcess($request, $amount, $title, $success_url, $cancel_url)` |
| **Callback** | متدهای `successPayment()` و `cancelPayment()` جداگانه |
| **Transaction Storage** | ❌ عدم ذخیره در جدول `transactions` |

### Modern Pattern (IDPay, NextPay, Pay.ir)
| ویژگی | توضیح |
|--------|-------|
| **Config Loading** | در `__construct()` فقط `PaymentGateway` model، config در متدها parse می‌شود |
| **Transaction Model** | ✅ `Transaction::create([...])` با `order_id` منحصر به فرد |
| **Idempotency** | UUID در `order_id` برای جلوگیری از تکرار |
| **Payment Process** | متد `payment(Request $request)` - amount از request می‌گیرد |
| **Callback** | متد `success(Request $request)` - هم callback و هم verify |
| **User Context** | `auth()->user()` برای اطلاعات پرداخت‌کننده |
| **Redirect** | مستقیم به `user.gateways` بعد از پرداخت |

---

## 🔧 Admin GatewayController - متدهای Update

**فایل:** `app/Http/Controllers/Admin/GatewayController.php`

```php
// ZarinPal (خط 374) - merchant_id + sandbox_status + callback_url
public function zarinpalUpdate(Request $request)

// Zibal (خط 392) - merchant_id + sandbox_status + description  
public function zibalUpdate(Request $request)

// IDPay (خط 410) - api_key + sandbox_status
public function idpayUpdate(Request $request)

// NextPay (خط 427) - api_key + sandbox_status
public function nextpayUpdate(Request $request)

// Pay.ir (خط 444) - api_key + sandbox_status
public function payirUpdate(Request $request)
```

**نکته مهم:** IDPay, NextPay, Pay.ir از کلید `sandbox` در JSON استفاده می‌کنند، اما ZarinPal و Zibal از `sandbox_status`!
