# Context Document: ZarinPal Payment Gateway Integration
# تاریخ ایجاد: 2026-08-24

## 1. تحلیل ساختار پروژه

### 1.1 ساختار کلی
- **Framework**: Laravel
- **نوع**: سیستم چند کاربره (Multi-tenant) برای ساخت رزومه آنلاین
- **پوشه اصلی**: `d:/Program Files/XAMPP/htdocs/proresume`

### 1.2 ساختار دیتابیس
- **دو سیستم میگریشن جدا دارد**:
  - `database/migrations/` - میگریشن‌های اصلی پروژه
  - `updater/database/migrations/` - میگریشن‌های آپدیت‌ها
- **نکته مهم**: آپدیت‌ها پس از نصب اولیه از طریق پوشه `updater/` اجرا می‌شوند

### 1.3 مدل PaymentGateway
- **فایل**: `app/Models/PaymentGateway.php`
- **فیلدهای اصلی**:
  - `title`: عنوان درگاه
  - `details`: جزئیات
  - `subtitle`: زیرعنوان
  - `name`: نام
  - `type`: نوع
  - `information`: JSON شامل تنظیمات خاص هر درگاه
  - `keyword`: کلید یکتا برای شناسایی درگاه (مثلاً 'paypal', 'stripe')
  - `status`: وضعیت فعال/غیرفعال

## 2. معماری درگاه‌های پرداخت موجود

### 2.1 ساختار Controller
- **مسیر**: `app/Http/Controllers/Payment/`
- **مثال‌های موجود**: 
  - `PaypalController.php`
  - `StripeController.php`
  - `MidtransController.php`
  - `IyzicoController.php`
  - `MyFatoorahController.php`

### 2.2 الگوی کلی Controller
هر درگاه پرداخت شامل متدهای زیر است:
```php
- __construct(): بارگذاری تنظیمات از دیتابیس
- paymentProcess(): ارسال کاربر به درگاه پرداخت
- successPayment(): بازگشت از درگاه بعد از پرداخت موفق
- cancelPayment(): بازگشت از درگاه بعد از پرداخت ناموفق
```

### 2.3 ساختار Admin Controller
- **فایل**: `app/Http/Controllers/Admin/GatewayController.php`
- **متدهای به‌روزرسانی**: هر درگاه یک متد به‌روزرسانی جدا دارد
  - مثال: `paypalUpdate()`, `stripeUpdate()`, `midtransUpdate()`
- **ویو**: `admin/gateways/index.blade.php`

### 2.4 ساختار User Controller
- **فایل**: `app/Http/Controllers/User/GatewayController.php`
- **متدهای به‌روزرسانی**: مشابه Admin برای تنظیمات توسط کاربر

### 2.5 مسیرهای Route
- **مسیر اصلی**: `routes/web.php`
- **دسته‌بندی**:
  - مسیرهای Admin: `admin/gateways/*`
  - مسیرهای User: `user/gateways/*`
  - مسیرهای پرداخت: `membership/{gateway}/*`
  - مسیرهای Appointment: `appointment/{gateway}/*`

## 3. ویژگی‌های خاص درگاه‌های ایرانی

### 3.1 تفاوت‌های کلیدی با درگاه‌های بین‌المللی
1. **واحد پول**: تومان (IRT) به جای USD/EUR
2. **متد احراز هویت**: معمولاً API Key + Merchant ID
3. **فرآیند پرداخت**:
   - ارسال درخواست به درگاه با مبلغ و توکن
   - دریافت لینک پرداخت
   - هدایت کاربر به لینک
   - بازگشت به URL تنظیم شده
   - **تایید نهایی**: بررسی وضعیت پرداخت از طریق API (VERIFY)
4. **تست‌های محیط**: محیط تست (Sandbox/Test) جداگانه
5. **Webhook**: برخی درگاه‌ها از Webhook پشتیبانی می‌کنند

### 3.2 ویژگی‌های موردنیاز ZarinPal
1. **Merchant ID**: کد merchant از زرین‌پال
2. **Sandbox Mode**: حالت تستی با API جداگانه
3. **Callback URL**: آدرس بازگشت بعد از پرداخت
4. **Verify API**: بررسی نهایی پرداخت
5. **لیست درگاه‌های بانکی**: انتخاب بانک توسط کاربر (اختیاری)

## 4. معماری پیشنهادی ZarinPal

### 4.1 فایل‌های موردنیاز

#### 4.1.1 Controller
- **مسیر**: `app/Http/Controllers/Payment/ZarinPalController.php`
- **وظایف**:
  - اتصال به API زرین‌پال
  - ایجاد پرداخت
  - تایید پرداخت
  - مدیریت خطاها

#### 4.1.2 Admin Controller Update
- **فایل**: `app/Http/Controllers/Admin/GatewayController.php`
- **متد جدید**: `zarinpalUpdate(Request $request)`

#### 4.1.3 User Controller Update
- **فایل**: `app/Http/Controllers/User/GatewayController.php`
- **متد جدید**: `zarinpalUpdate(Request $request)`

#### 4.1.4 Migration
- **مسیر**: `updater/database/migrations/`
- **نام**: `2026_08_24_000001_add_zarinpal_gateway.php`
- **وظایف**: اضافه کردن رکورد درگاه زرین‌پال به جدول payment_gateways

#### 4.1.5 Routes
- **فایل**: `routes/web.php`
- **مسیرهای جدید**:
  - `membership/zarinpal/success`
  - `membership/zarinpal/cancel`
  - `appointment/zarinpal/notify` (اختیاری)

### 4.2 ساختار دیتابیس

#### 4.2.1 جدول payment_gateways
```php
Schema::create('payment_gateways', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('details')->nullable();
    $table->string('subtitle')->nullable();
    $table->string('name')->nullable();
    $table->string('type')->nullable();
    $table->text('information')->nullable(); // JSON
    $table->string('keyword')->nullable(); // 'zarinpal'
    $table->integer('status')->default(1);
});
```

#### 4.2.2 اطلاعات JSON برای ZarinPal
```json
{
    "merchant_id": "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
    "sandbox_mode": 1,
    "callback_url": "https://domain.com/membership/zarinpal/success",
    "description": "پرداخت اشتراک",
    "text": "پرداخت امن با زرین‌پال"
}
```

## 5. مراحل پیاده‌سازی

### گام 1: ایجاد Migration
- ایجاد فایل میگریشن در `updater/database/migrations/`
- اضافه کردن رکورد زرین‌پال به payment_gateways

### گام 2: ایجاد Controller
- ایجاد `ZarinPalController.php` در `app/Http/Controllers/Payment/`
- پیاده‌سازی متدهای:
  - `__construct()`: بارگذاری تنظیمات
  - `paymentProcess()`: ارسال به درگاه
  - `successPayment()`: تایید پرداخت
  - `cancelPayment()`: پرداخت ناموفق

### گام 3: به‌روزرسانی Admin Controller
- اضافه کردن متد `zarinpalUpdate()` به `GatewayController.php`

### گام 4: به‌روزرسانی User Controller
- اضافه کردن متد `zarinpalUpdate()` به `User/GatewayController.php`

### گام 5: اضافه کردن Routes
- اضافه کردن مسیرهای زرین‌پال به `routes/web.php`

### گام 6: به‌روزرسانی View
- اضافه کردن بخش زرین‌پال به `admin/gateways/index.blade.php`
- اضافه کردن بخش زرین‌پال به `user/gateways/index.blade.php`

### گام 7: تست
- تست در محیط Sandbox
- تست در محیط Live
- تست پرداخت موفق
- تست پرداخت ناموفق
- تست تایید پرداخت

## 6. API ZarinPal

### 6.1 درخواست پرداخت (Payment Request)
```
Endpoint: https://api.zarinpal.com/pg/v4/payment/request.json
Method: POST
Headers: Content-Type: application/json
Body: {
    "merchant_id": "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
    "amount": 10000,
    "callback_url": "https://domain.com/callback",
    "description": "توضیحات پرداخت",
    "metadata": {
        "mobile": "09123456789",
        "email": "user@example.com"
    }
}
```

### 6.2 تایید پرداخت (Payment Verification)
```
Endpoint: https://api.zarinpal.com/pg/v4/payment/verify.json
Method: POST
Headers: Content-Type: application/json
Body: {
    "merchant_id": "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
    "amount": 10000,
    "authority": "A00000000000000000000000000"
}
```

### 6.3 درخواست پرداخت (Sandbox)
```
Endpoint: https://sandbox.zarinpal.com/pg/v4/payment/request.json
```

### 6.4 تایید پرداخت (Sandbox)
```
Endpoint: https://sandbox.zarinpal.com/pg/v4/payment/verify.json
```

## 7. ساختار قابل توسعه برای درگاه‌های ایرانی دیگر

### 7.1 الگوی طراحی
تمام درگاه‌های ایرانی باید از الگوی زیر پیروی کنند:
1. **API-based**: استفاده از REST API
2. **Redirect-based**: هدایت کاربر به درگاه و بازگشت
3. **Verify-based**: تایید نهایی پرداخت از طریق API
4. **JSON Configuration**: ذخیره تنظیمات در فیلد JSON
5. **Sandbox Support**: پشتیبانی از محیط تست

### 7.2 درگاه‌های قابل اضافه شدن
- PayPing (پایینگ)
- IDPay (آیدی پی)
- NextPay (نکست پی)
- ParsPal (پارس‌پال)
- Zibal (زیبال)
- Sepidar (سپیدار)

### 7.3 ویژگی‌های مشترک
```php
trait IranianGatewayTrait
{
    protected $merchant_id;
    protected $sandbox_mode;
    protected $callback_url;
    
    abstract protected function requestPayment($amount, $authority);
    abstract protected function verifyPayment($amount, $authority);
    abstract protected function getApiEndpoint();
}
```

## 8. نکات مهم و هشدارها

### 8.1 امنیت
- حتماً از HTTPS استفاده کنید
- کلیدهای API را در .env نگهداری کنید (نه در دیتابیس)
- تایید پرداخت حتماً از طریق API انجام شود (نه فقط بر اساس پارامترهای URL)

### 8.2 مدیریت خطا
- تمام خطاهای API را لاگ کنید
- پیام‌های خطای مناسب به کاربر نمایش دهید
- Retry mechanism برای درخواست‌های ناموفق

### 8.3 تست
- حتماً در محیط Sandbox زرین‌پال تست کنید
- تمام سناریوها را تست کنید:
  - پرداخت موفق
  - پرداخت ناموفق
  - لغو پرداخت
  - تایید تکراری

### 8.4 به‌روزرسانی
- از آپدیت‌های API زرین‌پال آگاه باشید
- نسخه‌های مختلف API را پشتیبانی کنید

## 9. فایل‌های موجود که نیاز به تغییر دارند

### 9.1 فایل‌های Controller
- [ ] `app/Http/Controllers/Admin/GatewayController.php` - اضافه کردن متد zarinpalUpdate
- [ ] `app/Http/Controllers/User/GatewayController.php` - اضافه کردن متد zarinpalUpdate
- [ ] `app/Http/Controllers/Payment/ZarinPalController.php` - ایجاد جدید

### 9.2 فایل‌های Route
- [ ] `routes/web.php` - اضافه کردن مسیرهای زرین‌پال

### 9.3 فایل‌های View
- [ ] `resources/views/admin/gateways/index.blade.php` - اضافه کردن فرم زرین‌پال
- [ ] `resources/views/user/gateways/index.blade.php` - اضافه کردن فرم زرین‌پال

### 9.4 فایل‌های Migration
- [ ] `updater/database/migrations/2026_08_24_000001_add_zarinpal_gateway.php` - ایجاد جدید

## 10. مراحل بعدی

1. [ ] ایجاد Migration برای اضافه کردن درگاه زرین‌پال
2. [ ] ایجاد ZarinPalController
3. [ ] به‌روزرسانی Admin GatewayController
4. [ ] به‌روزرسانی User GatewayController
5. [ ] اضافه کردن Routes
6. [ ] به‌روزرسانی Viewهای Admin
7. [ ] به‌روزرسانی Viewهای User
8. [ ] تست کامل در محیط Sandbox
9. [ ] مستندسازی برای اضافه کردن درگاه‌های ایرانی دیگر

## 11. اطلاعات تکمیلی

### 11.1 مستندات ZarinPal
- سایت: https://www.zarinpal.com
- مستندات: https://github.com/zarinpal/php-sdk
- پنل تست: https://sandbox.zarinpal.com

### 11.2 اطلاعات تماس
- پشتیبانی زرین‌پال: support@zarinpal.com
- تلفن: 021-8888-3838

### 11.3 نکات فنی
- API Version: v4
- Encoding: UTF-8
- Timeout: 30 seconds
- Currency: IRR (تومان)

---
**توجه**: این فایل Context به طور مداوم به روز می‌شود و باید قبل از هر تغییر در کد به روز رسانی شود.