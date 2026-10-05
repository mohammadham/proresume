<?php
/**
 * Persian translations for admin-namespace keys the views ask for but that are
 * absent from admin_en.json - the gaps found by
 * tests/lang_coverage_audit.php.
 *
 * Heavily weighted toward the payment-gateway settings form and the Enamad /
 * watermark panels, both of which are English-only strings hardcoded in the
 * blades.
 *
 * Consumed by tests/_build_admin_fa.php.
 */

return [
    // ---- menu builder --------------------------------------------------------
    'Website Templates' => 'قالب‌های سایت',
    'Cv Templates' => 'قالب‌های رزومه',
    'Vcards' => 'وی‌کارت‌ها',

    // ---- blog / categories ----------------------------------------------------
    'Add Category' => 'افزودن دسته',
    'Edit Category' => 'ویرایش دسته',
    'NO CATEGORY FOUND' => 'دسته‌ای یافت نشد',
    'Edit Post' => 'ویرایش نوشته',
    'Posts' => 'نوشته‌ها',

    // ---- CV / vCard templates ---------------------------------------------------
    'Add Template' => 'افزودن قالب',
    'Enter Template Name' => 'نام قالب را وارد کنید',
    'Template Name' => 'نام قالب',
    'CV Name' => 'نام رزومه',
    'CV URLs' => 'نشانی‌های رزومه',
    'NO CV FOUND' => 'رزومه‌ای یافت نشد',
    'User CVs' => 'رزومه‌های کاربران',
    'NO VCARD FOUND' => 'وی‌کارتی یافت نشد',
    'URLs' => 'نشانی‌ها',

    // ---- mail templates ------------------------------------------------------------
    'NO MAIL TEMPLATE FOUND!' => 'قالب ایمیلی یافت نشد!',

    // ---- popups ---------------------------------------------------------------------
    'Only png, jpg, jpeg image is allowed' => 'فقط تصاویر png، jpg و jpeg مجاز هستند',

    // ---- Enamad trust seal ----------------------------------------------------------------
    'Enamad Settings' => 'تنظیمات اینماد',
    'Enamad Status' => 'وضعیت اینماد',
    'Enamad Code' => 'کد اینماد',
    'Enamad Site ID' => 'شناسهٔ سایت اینماد',
    'Enamad Secret Key' => 'کلید محرمانهٔ اینماد',
    'Enamad Expire Date' => 'تاریخ انقضای اینماد',
    'Site ID provided by Enamad' => 'شناسهٔ سایت ارائه‌شده توسط اینماد',
    'Secret Key provided by Enamad' => 'کلید محرمانهٔ ارائه‌شده توسط اینماد',
    'Code provided by Enamad' => 'کد ارائه‌شده توسط اینماد',
    'Site ID from Enamad' => 'شناسهٔ سایت از اینماد',
    'Secret Key from Enamad' => 'کلید محرمانه از اینماد',
    'Expiration date of Enamad certificate' => 'تاریخ انقضای گواهی اینماد',
    'Logo Type' => 'نوع لوگو',
    'Logo theme for Enamad badge' => 'پوستهٔ لوگو برای نشان اینماد',
    'Verify Enamad' => 'تأیید اینماد',
    'Verifying...' => 'در حال تأیید...',
    'Verification failed' => 'تأیید ناموفق بود',

    // ---- watermark badge -------------------------------------------------------------------
    'Watermark Badge Settings' => 'تنظیمات نشان واترمارک',
    'Update Watermark Badge' => 'به‌روزرسانی نشان واترمارک',
    'Watermark Badge' => 'نشان واترمارک',
    'Watermark Text' => 'متن واترمارک',
    'Watermark Image' => 'تصویر واترمارک',
    'Current watermark image' => 'تصویر فعلی واترمارک',
    'Watermark Status' => 'وضعیت واترمارک',
    'Watermark Link URL' => 'نشانی لینک واترمارک',
    'Optional link when watermark is clicked' => 'لینک اختیاری هنگام کلیک روی واترمارک',
    'Text to display when no image is provided' => 'متنی که در نبود تصویر نمایش داده می‌شود',
    'Optional image (max 2MB). If provided, text will be ignored.' => 'تصویر اختیاری (حداکثر ۲ مگابایت). اگر ارائه شود، متن نادیده گرفته می‌شود.',
    'e.g. Secure Payment with Bank Mellat' => 'مثال: پرداخت امن با بانک ملت',

    // ---- custom domain details ------------------------------------------------------------------
    'Custom Domain:' => 'دامنهٔ اختصاصی:',

    // ---- SEO: pages that only the admin form knows about ------------------------------------------------
    'Meta Description For Cv Page' => 'توضیحات متا برای صفحهٔ رزومه',
    'Meta Keywords For Cv Page' => 'کلیدواژه‌های متا برای صفحهٔ رزومه',
    'Meta Description For Templates Page' => 'توضیحات متا برای صفحهٔ قالب‌ها',
    'Meta Keywords For Templates Page' => 'کلیدواژه‌های متا برای صفحهٔ قالب‌ها',
    'Meta Description For Vcards Page' => 'توضیحات متا برای صفحهٔ وی‌کارت‌ها',
    'Meta Keywords For Vcards Page' => 'کلیدواژه‌های متا برای صفحهٔ وی‌کارت‌ها',

    // ---- generic labels ------------------------------------------------------------------------------
    'Description' => 'توضیحات',
    'Profile Id' => 'شناسهٔ نمایه',
    'Verification failed.' => 'تأیید ناموفق بود.',
    'of' => 'از',
    'to' => 'تا',

    // ---- payment gateways: shared field labels --------------------------------------------------------------
    'API Endpoint' => 'نقطهٔ پایانی API',
    'API Key' => 'کلید API',
    'Callback URL' => 'نشانی بازگشت',
    'Category Code' => 'کد دسته',
    'Cron Job Command' => 'دستور کرون‌جاب',
    'Global' => 'سراسری',
    'Merchant Id' => 'شناسهٔ پذیرنده',
    'Merchant ID' => 'شناسهٔ پذیرنده',
    'Salt Index' => 'اندیس Salt',
    'Salt Key' => 'کلید Salt',
    'Sandbox Mode' => 'حالت سندباکس',
    'Sandbox Status' => 'وضعیت سندباکس',
    'Secret Key' => 'کلید محرمانه',
    'Server Key' => 'کلید سرور',
    'Step' => 'گام',
    'Terminal ID' => 'شناسهٔ پایانه',
    'Payment description shown to user' => 'توضیح پرداخت که به کاربر نمایش داده می‌شود',
    'Enable sandbox mode for testing' => 'حالت سندباکس را برای آزمایش فعال کنید',
    'Enable sandbox mode for testing.' => 'حالت سندباکس را برای آزمایش فعال کنید.',
    'Enable sandbox mode for testing. Use test merchant ID:' => 'حالت سندباکس را برای آزمایش فعال کنید. از شناسهٔ پذیرندهٔ آزمایشی استفاده کنید:',

    // ---- Iran / regional gateways ---------------------------------------------------------------------------
    'Bank Mellat' => 'بانک ملت',
    'Bank Mellat Status' => 'وضعیت بانک ملت',
    'Set this URL in your Bank Mellat dashboard.' => 'این نشانی را در پنل بانک ملت خود ثبت کنید.',
    'You can get your Terminal ID from Bank Mellat dashboard' => 'شناسهٔ پایانهٔ خود را از پنل بانک ملت دریافت کنید',
    'Iyzico' => 'ای‌زیکو',
    'Iyzico Api Key' => 'کلید API ای‌زیکو',
    'Iyzico Secret Key' => 'کلید محرمانهٔ ای‌زیکو',
    'Iyzico Status' => 'وضعیت ای‌زیکو',
    'Iyzico Test Mode' => 'حالت تست ای‌زیکو',
    'Set the cron job following this video' => 'کرون‌جاب را طبق این ویدیو تنظیم کنید',
    "Without cronjob setup, Iyzico payment method won't work" => 'بدون تنظیم کرون‌جاب، روش پرداخت ای‌زیکو کار نمی‌کند',
    'ZarinPal' => 'زرین‌پال',
    'ZarinPal Status' => 'وضعیت زرین‌پال',
    'ZarinPal Merchant ID' => 'شناسهٔ پذیرندهٔ زرین‌پال',
    'You can get your Merchant ID from ZarinPal dashboard' => 'شناسهٔ پذیرندهٔ خود را از پنل زرین‌پال دریافت کنید',
    'This URL will be used as callback. Set this in your ZarinPal account.' => 'این نشانی به‌عنوان بازگشت استفاده می‌شود؛ آن را در حساب زرین‌پال خود ثبت کنید.',
    'Zibal' => 'زیبال',
    'Zibal Status' => 'وضعیت زیبال',
    'Zibal Merchant ID' => 'شناسهٔ پذیرندهٔ زیبال',
    'You can get your Merchant ID from Zibal dashboard' => 'شناسهٔ پذیرندهٔ خود را از پنل زیبال دریافت کنید',
    'IDPay' => 'آیدی‌پی',
    'IDPay API Key' => 'کلید API آیدی‌پی',
    'IDPay Status' => 'وضعیت آیدی‌پی',
    'You can get your API Key from IDPay dashboard' => 'کلید API خود را از پنل آیدی‌پی دریافت کنید',
    'NextPay' => 'نکست‌پی',
    'NextPay API Key' => 'کلید API نکست‌پی',
    'NextPay Status' => 'وضعیت نکست‌پی',
    'You can get your API Key from NextPay dashboard' => 'کلید API خود را از پنل نکست‌پی دریافت کنید',
    'Pay.ir' => 'پی‌ایر',
    'Pay.ir API Key' => 'کلید API پی‌ایر',
    'Pay.ir Status' => 'وضعیت پی‌ایر',
    'You can get your API Key from Pay.ir dashboard' => 'کلید API خود را از پنل پی‌ایر دریافت کنید',
    'Paytabs' => 'پی‌تبز',
    'Paytabs Status' => 'وضعیت پی‌تبز',
    'You will get your API Endpoint from PayTabs dashboard' => 'نقطهٔ پایانی API خود را از پنل پی‌تبز دریافت کنید',
    'Perfect Money' => 'پرفکت مانی',
    'Perfect Money Status' => 'وضعیت پرفکت مانی',
    'Perfect Money Wallet Id' => 'شناسهٔ کیف پول پرفکت مانی',
    'You will get wallet id form here' => 'شناسهٔ کیف پول خود را از اینجا دریافت می‌کنید',
    'Phonepe' => 'فون‌پی',
    'Phonepe Status' => 'وضعیت فون‌پی',
    'Toyyibpay' => 'توییب‌پی',
    'Toyyibpay Status' => 'وضعیت توییب‌پی',
    'Toyyibpay Test Mode' => 'حالت تست توییب‌پی',
    'Xendit' => 'زن‌دیت',
    'Xendit Status' => 'وضعیت زن‌دیت',
    'Yoco' => 'یوکو',
    'Yoco Status' => 'وضعیت یوکو',
    'Midtrans' => 'میدترنس',
    'Midtrans Server Key' => 'کلید سرور میدترنس',
    'Midtrans Status' => 'وضعیت میدترنس',
    'Midtrans Test Mode' => 'حالت تست میدترنس',
    'Set these URLs in Midtrans Dashboard like this' => 'این نشانی‌ها را مانند زیر در پنل میدترنس ثبت کنید',
    'MyFatoorah' => 'مای‌فاتوره',
    'MyFatoorah Status' => 'وضعیت مای‌فاتوره',
    'Your Success URL' => 'نشانی موفقیت شما',
    'Your Cancel URL' => 'نشانی لغو شما',

    // ---- countries used by the Iranian gateways -----------------------------------------------------------------
    'Iran' => 'ایران',
    'Iraq' => 'عراق',
    'Oman' => 'عمان',
    'Jordan' => 'اردن',
    'Egypt' => 'مصر',
    'Saudi Arabia' => 'عربستان',
    'United Arab Emirates' => 'امارات',
];