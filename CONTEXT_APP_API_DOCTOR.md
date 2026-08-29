<tool_call>
<function=write>
<parameter=content>
# پلن کامل پیاده‌سازی سرویس API ProResume

> **تاریخ شروع:** 2026-08-29
> **وضعیت:** در حال پیاده‌سازی
> **هدف:** ساخت API یکپارچه برای دسترسی از طریق اپ موبایل + پلاگین در پنل کاربر

---

## فهرست مطالب

1. [تحلیل سناریو](#۱-تحلیل-سناریو)
2. [ساختار پروژه](#۲-ساختار-پروژه)
3. [فازهای پیاده‌سازی](#۳-فازهای-پیاده‌سازی)
4. [API Endpoints](#۴-api-endpoints)
5. [پلاگین پنل کاربر](#۵-پلاگین-پنل-کاربر)
6. [امنیت](#۶-امنیت)
7. [تست و پیاده‌سازی](#۷-تست-و-پیاده‌سازی)
8. [تودو لیست کامل](#۸-تودو-لیست-کامل)

---

## ۱. تحلیل سناریو

### بازیگران (Actors)

| بازیگر | توضیحات | نقش در سیستم |
|--------|---------|--------------|
| **صاحب پلتفرم (شما)** | مالک سایت اصلی ProResume | مدیریت کلان، API، اپ‌های موبایل |
| **صاحب سایت/ارائه‌دهنده** | کاربران پنل که سایت خودشون رو می‌سازن (ارایشگر، دکتر) | فعال‌سازی پلاگین API، مدیریت پروفایل، خدمات، نوبت‌دهی |
| **کاربر نهایی (App User)** | کاربر اپ موبایل | جستجو روی نقشه/لیست، رزرو نوبت |

### جریان‌های کاری (Workflows)

#### جریان ۱: فعال‌سازی API توسط ارائه‌دهنده
1. ارائه‌دهنده وارد پنل کاربر می‌شود
2. به تنظیمات پلاگین «یکپارچگی اپ» می‌رود
3. نوع اپ را انتخاب می‌کند (ارایشگر/دکتر)
4. API Integration را فعال می‌کند
5. API Key تولید می‌شود (نمایش داده می‌شود)
6. اطلاعات مکانی (استان، شهر، منطقه، آدرس، مختصات) را وارد می‌کند
7. تخصص (برای دکتر) را انتخاب می‌کند

#### جریان ۲: استفاده از API توسط اپ موبایل
1. کاربر اپ را باز می‌کند
2. موقعیت مکانی خود را به اپ می‌دهد (یا استان/شهر را انتخاب می‌کند)
3. اپ از API لیست ارائه‌دهندگان nearby را دریافت می‌کند
4. کاربر ارائه‌دهنده را انتخاب می‌کند
5. لیست خدمات/تخصص‌ها نمایش داده می‌شود
6. بازه زمانی مناسب را انتخاب می‌کند
7. نوبت رزرو می‌شود

#### جریان ۳: احراز هویت در اپ
1. کاربر در اپ ثبت‌نام/لاگین می‌کند
2. API token Sanctum دریافت می‌شود
3. درخواست‌های بعدی با token ارسال می‌شود

---

## ۲. ساختار پروژه

```
proresume/
├─ app/
│  ├─ Models/
│  │  ├─ ApiIntegration.php           # NEW
│  │  ├─ User.php                     # MODIFIED (fillable)
│  │  └─ [models موجود...]
│  ├─ Http/
│  │  ├─ Controllers/
│  │  │  ├─ API/
│  │  │  │  ├─ AuthController.php     # NEW
│  │  │  │  ├─ ProviderController.php # NEW
│  │  │  │  └─ AppointmentController.php # NEW
│  │  │  └─ User/
│  │  │     ├─ ApiIntegrationController.php # NEW
│  │  │     └─ [controllers موجود...]
│  │  └─ Middleware/
│  │     └─ Cors.php                  # NEW (if needed)
│  └─ Services/
│     └─ LocationService.php          # NEW
├─ database/
│  └─ migrations/
│     ├─ YYYY_MM_DD_HHMMSS_add_service_fields_to_users.php
│     ├─ YYYY_MM_DD_HHMMSS_create_provinces_table.php
│     ├─ YYYY_MM_DD_HHMMSS_create_cities_table.php
│     ├─ YYYY_MM_DD_HHMMSS_create_api_integrations_table.php
│     └─ YYYY_MM_DD_HHMMSS_add_api_fields_to_basic_settings.php
├─ routes/
│  └─ api.php                         # MODIFIED
├─ config/
│  └─ logging.php                     # MODIFIED (add api channel)
├─ resources/
│  └─ views/
│     └─ user/
│        └─ settings/
│           └─ api-integration.blade.php # NEW
└─ API_DOCUMENTATION.md               # THIS FILE
```

---

## ۳. فازهای پیاده‌سازی

### فاز ۰: مهاجرت‌های دیتابیس (Database Migrations)

#### ۰-۱: Migration افزودن فیلدها به جدول `users`

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('service_type')->nullable()->after('username'); // barber, doctor
    $table->string('specialty')->nullable()->after('service_type'); // فقط برای دکتر
    $table->string('district')->nullable()->after('city');
    $table->decimal('lat', 10, 7)->nullable()->after('district');
    $table->decimal('lng', 10, 7)->nullable()->after('lat');
});
```

**نکات:**
- `service_type`: نوع فعالیت کاربر (barber, doctor,或其他)
- `specialty`: فقط برای دکتر (مثلاً: دندانپزشک، پوست، قلب)
- `lat`, `lng`: مختصات GPS (برای نمایش روی نقشه)
- `district`: محله/منطقه

#### ۰-۲: Migration ایجاد جدول `provinces`

```php
Schema::create('provinces', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->timestamps();
});
```

**داده‌های اولیه:**
```php
Province::insert([
    ['name' => 'تهران'],
    ['name' => 'مشهد'],
    ['name' => 'اصفهان'],
    // ... سایر استان‌ها
]);
```

#### ۰-۳: Migration ایجاد جدول `cities`

```php
Schema::create('cities', function (Blueprint $table) {
    $table->id();
    $table->foreignId('province_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->timestamps();
});
```

**داده‌های اولیه:**
```php
City::insert([
    ['province_id' => 1, 'name' => 'تهران'],
    ['province_id' => 1, 'name' => 'کرج'],
    // ... سایر شهرها
]);
```

#### ۰-۴: Migration ایجاد جدول `api_integrations`

```php
Schema::create('api_integrations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('api_key')->unique();
    $table->boolean('is_active')->default(false);
    $table->string('app_type')->default('barber'); // barber | doctor
    $table->json('settings')->nullable(); // radius, unit, map_provider, etc.
    $table->timestamps();
});
```

**نکات:**
- `api_key`: کلید منحصر به فرد برای هر سایت/ارائه‌دهنده
- `is_active`: وضعیت فعال بودن پلاگین
- `app_type`: نوع اپ (barber یا doctor)
- `settings`: تنظیمات اضافی به صورت JSON

#### ۰-۵: Migration افزودن فیلدها به `basic_settings`

```php
Schema::table('basic_settings', function (Blueprint $table) {
    $table->boolean('api_integration_status')->default(false)->after('tawkto_status');
    $table->string('api_key')->nullable()->after('api_integration_status');
});
```

**توضیحات:**
- `api_integration_status`: فعال/غیرفعال بودن پلاگین در سایت
- `api_key`: API Key سطح سایت (برای احراز هویت اپ)

---

### فاز ۱: نصب و تنظیم Laravel Sanctum

#### ۱-۱: نصب Sanctum

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

#### ۱-۲: تنظیمات

**فایل `.env`:**
```env
SESSION_DRIVER=cookie
SESSION_DOMAIN=localhost
SANCTUM_STATEFUL_DOMAINS=localhost
```

**فایل `config/sanctum.php`:**
```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost,127.0.0.1')),
```

**فایل `app/Http/Kernel.php`:**
```php
protected $middlewareGroups = [
    'api' => [
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        'throttle:60,1',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],
];
```

---

### فاز ۲: مدل ApiIntegration

**فایل: `app/Models/ApiIntegration.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApiIntegration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'api_key',
        'is_active',
        'app_type',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function generateApiKey()
    {
        return hash('sha256', \Illuminate\Support\Str::random(60));
    }
}
```

---

### فاز ۳: AuthController API

**فایل: `app/Http/Controllers/API/AuthController.php`**

```php
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ApiIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'status' => 1, // فعال
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'ثبت‌نام با موفقیت انجام شد',
            'data' => [
                'user' => $user,
                'token' => $token,
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string', // email or phone
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->login)
            ->orWhere('phone', $request->login)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['اطلاعات ورود نادرست است'],
            ]);
        }

        // Check if user is active
        if ($user->status != 1) {
            return response()->json([
                'success' => false,
                'message' => 'حساب کاربری شما غیرفعال است'
            ], 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'ورود با موفقیت انجام شد',
            'data' => [
                'user' => $user,
                'token' => $token,
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'خروج با موفقیت انجام شد'
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    }
}
```

---

### فاز ۴: ProviderController API

**فایل: `app/Http/Controllers/API/ProviderController.php`**

```php
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ApiIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProviderController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->where('status', 1)
            ->whereNotNull('service_type')
            ->with(['apiIntegration']);

        // Filter by service_type (barber, doctor)
        if ($request->has('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        // Filter by province (state)
        if ($request->has('province')) {
            $query->where('state', $request->province);
        }

        // Filter by city
        if ($request->has('city')) {
            $query->where('city', $request->city);
        }

        // Filter by district
        if ($request->has('district')) {
            $query->where('district', $request->district);
        }

        // Search by name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        // Only providers with API integration active
        $query->whereHas('apiIntegration', function ($q) {
            $q->where('is_active', true);
        });

        $providers = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $providers
        ]);
    }

    public function show($id)
    {
        $provider = User::where('id', $id)
            ->where('status', 1)
            ->whereNotNull('service_type')
            ->with(['apiIntegration', 'services', 'categories'])
            ->firstOrFail();

        // Check if API integration is active
        if (!$provider->apiIntegration || !$provider->apiIntegration->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'ارائه‌دهنده یافت نشد'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $provider
        ]);
    }

    public function map(Request $request)
    {
        $query = User::query()
            ->where('status', 1)
            ->whereNotNull('service_type')
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->with(['apiIntegration']);

        // Filter by service_type
        if ($request->has('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        // Filter by province (state)
        if ($request->has('province')) {
            $query->where('state', $request->province);
        }

        // Filter by city
        if ($request->has('city')) {
            $query->where('city', $request->city);
        }

        // Only providers with API integration active
        $query->whereHas('apiIntegration', function ($q) {
            $q->where('is_active', true);
        });

        $providers = $query->get(['id', 'first_name', 'last_name', 'lat', 'lng', 'city', 'address', 'photo']);

        return response()->json([
            'success' => true,
            'data' => $providers
        ]);
    }
}
```

---

### فاز ۵: AppointmentController API

**فایل: `app/Http/Controllers/API/AppointmentController.php`**

```php
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\User\UserTimeSlot;
use App\Models\User\AppointmentBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function slots(Request $request, $providerId)
    {
        $provider = User::where('id', $providerId)
            ->where('status', 1)
            ->whereNotNull('service_type')
            ->firstOrFail();

        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $date = $request->date;
        $dayOfWeek = \Carbon\Carbon::parse($date)->format('l'); // Monday, Tuesday, etc.

        // Get time slots for this provider on this day
        $slots = UserTimeSlot::where('user_id', $providerId)
            ->where('day', $dayOfWeek)
            ->where('is_active', 1)
            ->orderBy('start_time')
            ->get();

        // Check which slots are already booked
        $bookedSlots = AppointmentBooking::where('user_id', $providerId)
            ->where('booking_date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->pluck('time_slot_id')
            ->toArray();

        // Mark slots as available/booked
        $slots->each(function ($slot) use ($bookedSlots) {
            $slot->is_available = !in_array($slot->id, $bookedSlots);
        });

        return response()->json([
            'success' => true,
            'data' => [
                'provider' => $provider,
                'date' => $date,
                'slots' => $slots
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'provider_id' => 'required|exists:users,id',
            'category_id' => 'nullable|exists:categories,id',
            'booking_date' => 'required|date_format:Y-m-d',
            'time_slot_id' => 'nullable|exists:user_time_slots,id',
            'notes' => 'nullable|string',
        ]);

        $provider = User::where('id', $request->provider_id)
            ->where('status', 1)
            ->whereNotNull('service_type')
            ->firstOrFail();

        // Check if slot is available
        if ($request->time_slot_id) {
            $existingBooking = AppointmentBooking::where('user_id', $request->provider_id)
                ->where('booking_date', $request->booking_date)
                ->where('time_slot_id', $request->time_slot_id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->first();

            if ($existingBooking) {
                return response()->json([
                    'success' => false,
                    'message' => 'این بازه زمانی قبلاً رزرو شده است'
                ], 409);
            }
        }

        // Get category price if exists
        $price = null;
        if ($request->category_id) {
            $category = \App\Models\User\Category::where('id', $request->category_id)
                ->where('user_id', $request->provider_id)
                ->first();
            if ($category) {
                $price = $category->appointment_price;
            }
        }

        $appointment = AppointmentBooking::create([
            'user_id' => $request->provider_id,
            'customer_id' => $request->user()->id,
            'category_id' => $request->category_id,
            'booking_date' => $request->booking_date,
            'time_slot_id' => $request->time_slot_id,
            'price' => $price,
            'status' => 'pending',
            'payment_status' => 0,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'نوبت شما با موفقیت ثبت شد',
            'data' => $appointment
        ], 201);
    }

    public function myAppointments(Request $request)
    {
        $user = $request->user();

        $appointments = AppointmentBooking::where('customer_id', $user->id)
            ->with(['provider', 'category'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $appointments
        ]);
    }
}
```

---

### فاز ۶: روت‌های API

**فایل: `routes/api.php`**

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProviderController;
use App\Http\Controllers\API\AppointmentController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {

    // Auth routes (public)
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);

        // Appointments (for app users)
        Route::get('/appointments/slots/{provider}', [AppointmentController::class, 'slots']);
        Route::post('/appointments', [AppointmentController::class, 'store']);
        Route::get('/appointments', [AppointmentController::class, 'myAppointments']);
    });

    // Public: Provider Discovery
    Route::get('/providers', [ProviderController::class, 'index']);
    Route::get('/providers/map', [ProviderController::class, 'map']);
    Route::get('/providers/{id}', [ProviderController::class, 'show']);
});
```

---

### فاز ۷: پلاگین پنل کاربر

#### ۷-۱: Controller

**فایل: `app/Http/Controllers/User/ApiIntegrationController.php`**

```php
<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ApiIntegration;
use App\Models\User;
use App\Models\Province;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiIntegrationController extends Controller
{
    public function index()
    {
        $user = Auth::guard('web')->user();
        $data = $user->apiIntegration;
        $provinces = Province::all();
        $cities = collect();
        
        if ($user->state) {
            $province = Province::where('name', $user->state)->first();
            if ($province) {
                $cities = City::where('province_id', $province->id)->get();
            }
        }

        return view('user.settings.api-integration', compact('data', 'provinces', 'cities'));
    }

    public function update(Request $request)
    {
        $user = Auth::guard('web')->user();
        
        $request->validate([
            'app_type' => 'required|in:barber,doctor',
            'is_active' => 'nullable|boolean',
        ]);

        $integration = ApiIntegration::where('user_id', $user->id)->first();

        if (!$integration) {
            $integration = new ApiIntegration();
            $integration->user_id = $user->id;
            $integration->api_key = ApiIntegration::generateApiKey();
        }

        $integration->app_type = $request->app_type;
        $integration->is_active = $request->has('is_active') ? true : false;
        $integration->save();

        // Also update basic_settings if needed
        $basicSetting = \App\Models\BasicSetting::where('user_id', $user->id)->first();
        if ($basicSetting) {
            $basicSetting->api_integration_status = $integration->is_active ? 1 : 0;
            $basicSetting->api_key = $integration->api_key;
            $basicSetting->save();
        }

        return redirect()->back()->with('success', 'تنظیمات با موفقیت ذخیره شد');
    }

    public function regenerateKey(Request $request)
    {
        $user = Auth::guard('web')->user();
        $integration = ApiIntegration::where('user_id', $user->id)->first();

        if ($integration) {
            $integration->api_key = ApiIntegration::generateApiKey();
            $integration->save();
        }

        return redirect()->back()->with('success', 'API Key با موفقیت بازنشانی شد');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::guard('web')->user();
        
        $request->validate([
            'service_type' => 'nullable|in:barber,doctor',
            'specialty' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
        ]);

        $user->update([
            'service_type' => $request->service_type,
            'specialty' => $request->specialty,
            'state' => $request->state,
            'city' => $request->city,
            'district' => $request->district,
            'address' => $request->address,
            'lat' => $request->lat,
            'lng' => $request->lng,
        ]);

        return redirect()->back()->with('success', 'پروفایل با موفقیت به‌روزرسانی شد');
    }

    public function getCities(Request $request, $provinceId)
    {
        $cities = City::where('province_id', $provinceId)->get(['id', 'name']);
        return response()->json($cities);
    }
}
```

#### ۷-۲: View

**فایل: `resources/views/user/settings/api-integration.blade.php`**

```html
@extends('user.layout')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4>یکپارچگی اپ موبایل</h4>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form action="{{ route('api.integration.update') }}" method="POST">
                    @csrf
                    
                    <div class="form-group">
                        <label>نوع فعالیت</label>
                        <select name="app_type" class="form-control">
                            <option value="barber" {{ ($data->app_type ?? 'barber') == 'barber' ? 'selected' : '' }}>آرایشگر</option>
                            <option value="doctor" {{ ($data->app_type ?? '') == 'doctor' ? 'selected' : '' }}>دکتر</option>
                        </select>
                    </div>

                    <div class="form-group mt-3">
                        <label>
                            <input type="checkbox" name="is_active" {{ ($data->is_active ?? false) ? 'checked' : '' }}>
                            فعال‌سازی یکپارچگی
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">ذخیره</button>
                </form>

                @if($data)
                <hr>
                <h5>API Key</h5>
                <div class="input-group">
                    <input type="text" class="form-control" value="{{ $data->api_key }}" readonly>
                    <button class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText('{{ $data->api_key }}')">کپی</button>
                    <form action="{{ route('api.integration.regenerate') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-warning">بازنشانی</button>
                    </form>
                </div>
                @endif

                <hr>
                <h5>اطلاعات مکانی</h5>
                <form action="{{ route('api.integration.profile') }}" method="POST">
                    @csrf
                    
                    <div class="form-group">
                        <label>استان</label>
                        <select name="state" id="province" class="form-control">
                            <option value="">انتخاب کنید</option>
                            @foreach($provinces as $province)
                                <option value="{{ $province->name }}" {{ ($user->state ?? '') == $province->name ? 'selected' : '' }}>
                                    {{ $province->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>شهر</label>
                        <select name="city" id="city" class="form-control">
                            <option value="">انتخاب کنید</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->name }}" {{ ($user->city ?? '') == $city->name ? 'selected' : '' }}>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>منطقه</label>
                        <input type="text" name="district" class="form-control" value="{{ $user->district ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label>آدرس</label>
                        <textarea name="address" class="form-control">{{ $user->address ?? '' }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>عرض جغرافیایی (Lat)</label>
                                <input type="text" name="lat" class="form-control" value="{{ $user->lat ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>طول جغرافیایی (Lng)</label>
                                <input type="text" name="lng" class="form-control" value="{{ $user->lng ?? '' }}">
                            </div>
                        </div>
                    </div>

                    @if($data && $data->app_type == 'doctor')
                    <div class="form-group">
                        <label>تخصص</label>
                        <input type="text" name="specialty" class="form-control" value="{{ $user->specialty ?? '' }}">
                    </div>
                    @endif

                    <button type="submit" class="btn btn-primary mt-3">ذخیره اطلاعات</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$('#province').on('change', function() {
    var provinceName = $(this).val();
    // AJAX call to get cities
    // ...
});
</script>
@endsection
```

#### ۷-۳: مسیرها در `routes/web.php`

```php
Route::prefix('api-integration')->middleware('auth')->group(function () {
    Route::get('/', 'User\ApiIntegrationController@index')->name('api.integration');
    Route::post('/update', 'User\ApiIntegrationController@update')->name('api.integration.update');
    Route::post('/profile', 'User\ApiIntegrationController@updateProfile')->name('api.integration.profile');
    Route::post('/regenerate-key', 'User\ApiIntegrationController@regenerateKey')->name('api.integration.regenerate');
    Route::get('/cities/{provinceId}', 'User\ApiIntegrationController@getCities')->name('api.integration.cities');
});
```

#### ۷-۴: لینک در منوی پنل کاربر

در فایل منوی پنل کاربر، لینک زیر اضافه می‌شود:
```html
<a href="{{ route('api.integration') }}">
    <i class="ti-plug"></i>
    <span>یکپارچگی اپ موبایل</span>
</a>
```

---

### فاز ۸: LocationService

**فایل: `app/Services/LocationService.php`**

```php
<?php

namespace App\Services;

use App\Models\User;
use App\Models\City;
use App\Models\Province;
use Illuminate\Support\Facades\Cache;

class LocationService
{
    /**
     * Get providers within radius (in kilometers)
     */
    public function getProvidersWithinRadius(float $lat, float $lng, float $radiusKm, array $filters = [])
    {
        $haversine = "(6371 * acos(cos(radians($lat)) 
                    * cos(radians(users.lat)) 
                    * cos(radians(users.lng) - radians($lng)) 
                    * sin(radians($lat))))";

        $query = User::query()
            ->select('users.*')
            ->selectRaw("$haversine as distance")
            ->where('status', 1)
            ->whereNotNull('service_type')
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->having('distance', '<=', $radiusKm)
            ->orderBy('distance');

        if (isset($filters['service_type'])) {
            $query->where('service_type', $filters['service_type']);
        }

        if (isset($filters['province'])) {
            $query->where('state', $filters['province']);
        }

        if (isset($filters['city'])) {
            $query->where('city', $filters['city']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        // Only providers with API integration active
        $query->whereHas('apiIntegration', function ($q) {
            $q->where('is_active', true);
        });

        return $query->get();
    }

    /**
     * Get province ID by name
     */
    public function getProvinceIdByName(string $name): ?int
    {
        return Province::where('name', $name)->value('id');
    }

    /**
     * Get cities by province name
     */
    public function getCitiesByProvince(string $provinceName)
    {
        $province = Province::where('name', $provinceName)->first();
        if (!$province) {
            return collect();
        }
        return City::where('province_id', $province->id)->get();
    }

    /**
     * Geocode address to lat/lng (using Google Maps API or similar)
     */
    public function geocode(string $address): ?array
    {
        // Option 1: Use Google Maps Geocoding API
        // Option 2: Use OpenStreetMap Nominatim
        // Option 3: Manual entry by user
        
        // For now, return null and require manual entry
        return null;
    }
}
```

---

### فاز ۹: تنظیمات نهایی

#### ۹-۱: افزودن کانال لاگ `api`

**فایل: `config/logging.php`**

```php
'channels' => [
    // ... existing channels
    'api' => [
        'driver' => 'daily',
        'path' => storage_path('logs/api.log'),
        'level' => 'debug',
        'days' => 30,
    ],
],
```

#### ۹-۲: تنظیم CORS (در صورت نیاز)

**برای .htaccess:**
```apache
<IfModule mod_headers.c>
    Header set Access-Control-Allow-Origin "*"
    Header set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
    Header set Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With"
    Header set Access-Control-Max-Age "3600"
</IfModule>
```

**یا برای توسعه:**
```php
// In AppServiceProvider or dedicated middleware
\Illuminate\Support\Facades\Route::middleware('api')
    ->headers([
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
    ]);
```

---

## ۴. API Endpoints (خلاصه)

| Method | Endpoint | Auth | توضیحات |
|--------|----------|------|---------|
| POST | `/api/v1/register` | No | ثبت‌نام کاربر |
| POST | `/api/v1/login` | No | ورود کاربر |
| POST | `/api/v1/logout` | Yes | خروج |
| GET | `/api/v1/profile` | Yes | دریافت پروفایل |
| GET | `/api/v1/providers` | No | لیست ارائه‌دهندگان |
| GET | `/api/v1/providers/map` | No | لیست برای نقشه |
| GET | `/api/v1/providers/{id}` | No | جزئیات ارائه‌دهنده |
| GET | `/api/v1/appointments/slots/{provider}` | Yes | بازه‌های زمانی آزاد |
| POST | `/api/v1/appointments` | Yes | ثبت نوبت |
| GET | `/api/v1/appointments` | Yes | نوبت‌های من |

---

## ۵. پلاگین پنل کاربر

| قابلیت | توضیحات |
|--------|---------|
| فعال/غیرفعال کردن | توگل در پنل |
| انتخاب نوع اپ | باربر/دکتر |
| نمایش API Key | با دکمه کپی |
| بازنشانی API Key | تولید کلید جدید |
| ویرایش پروفایل | آدرس، مختصات، تخصص |
| انتخاب استان/شهر | Dropdown با AJAX |

---

## ۶. امنیت

| موضوع | راه‌حل |
|--------|--------|
| احراز هویت | Laravel Sanctum (Token-based) |
| تولید API Key | `hash('sha256', Str::random(60))` |
| محدودیت نرخ | `throttle:60,1` در روت‌ها |
| Revoke | دکمه «بازنشانی کلید» |
| لاگ | کانال `api` در `config/logging.php` |
| CORS | Headerها در `.htaccess` یا middleware |
| Validation | Form Request Validation در هر Controller |
| SQL Injection | استفاده از Eloquent/Query Builder (جلوگیری از raw queries) |

---

## ۷. تست و پیاده‌سازی

### مراحل تست (Postman)

#### ۱. ثبت‌نام
```bash
POST /api/v1/register
Body: {
    "name": "test user",
    "email": "test@example.com",
    "password": "password",
    "password_confirmation": "password",
    "phone": "09123456789"
}
```

#### ۲. ورود
```bash
POST /api/v1/login
Body: {
    "login": "test@example.com",
    "password": "password"
}
```

#### ۳. دریافت لیست ارائه‌دهندگان
```bash
GET /api/v1/providers?service_type=barber&province=تهران
```

#### ۴. دریافت جزئیات ارائه‌دهنده
```bash
GET /api/v1/providers/1
```

#### ۵. دریافت بازه‌های زمانی
```bash
GET /api/v1/appointments/slots/1?date=2026-09-01
Headers: Authorization: Bearer {token}
```

#### ۶. ثبت نوبت
```bash
POST /api/v1/appointments
Headers: Authorization: Bearer {token}
Body: {
    "provider_id": 1,
    "category_id": 2,
    "booking_date": "2026-09-01",
    "time_slot_id": 5,
    "notes": "مطمئن شوید وقت خالی هست"
}
```

---

## ۸. تودو لیست کامل (درختی)

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
│   │   └── فیلدها: id, user_id (FK), api_key (unique), is_active, app_type, settings (json), tim