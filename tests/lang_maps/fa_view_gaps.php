<?php
/**
 * Persian translations for front-namespace keys the views ask for but that are
 * absent from en.json.
 *
 * These are the gaps found by tests/lang_coverage_audit.php: strings the
 * blades call with __('...') that never made it into the English JSON file.
 * They exist only in the view layer, so en.json parity cannot catch them -
 * and without this map they render in English inside an otherwise Persian
 * page (profile theme footers, PDF invoices, the 404 page, checkout).
 *
 * Consumed by tests/_build_fa.php.
 */

return [
    // ---- 404 page ---------------------------------------------------------
    // The blade used to call __('404'), but json_decode turns that JSON key into
    // the *integer* 404, so the string lookup never matched and every language
    // silently fell back to Latin digits. The blade now uses a word key.
    'Page Not Found Code' => '۴۰۴',
    'You\'re lost' => 'شما گم شده‌اید',
    'The page you are looking for might have been moved, renamed, or might never existed.' => 'صفحه‌ای که به دنبال آن هستید شاید جابه‌جا، تغییرنام یا هرگز وجود نداشته باشد.',
    'Back Home' => 'بازگشت به خانه',

    // ---- profile theme footers / headers ------------------------------------
    'All Right Reserved' => 'همهٔ حقوق محفوظ است',
    'Copyright' => 'کپی‌رایت',
    'Sign out' => 'خروج',
    'NO FEATURE FOUND' => 'ویژگی‌ای یافت نشد',
    'Work Process Not Found' => 'فرایند کاری یافت نشد',

    // ---- public profile forms -----------------------------------------------
    'Name' => 'نام',
    'Optional' => 'اختیاری',
    'Allowed extensions:' => 'پسوندهای مجاز:',
    'Please choose a date to see the available slots' => 'برای دیدن بازه‌های آزاد تاریخی انتخاب کنید',
    'Select a date' => 'تاریخی انتخاب کنید',
    'Select a slot' => 'یک بازه انتخاب کنید',

    // ---- checkout / payment --------------------------------------------------
    'Discount' => 'تخفیف',
    'Package Price' => 'قیمت بسته',
    'Package Purchase via Midtrans' => 'خرید بسته از طریق میدترنس',
    'Pay Now' => 'همین حالا پرداخت کنید',

    // ---- success page ---------------------------------------------------------
    'Success' => 'موفق',

    // ---- PDF invoices ----------------------------------------------------------
    'Invoice' => 'فاکتور',
    'Completed' => 'تکمیل‌شده',
    'Amount' => 'مبلغ',
    'Booking Date' => 'تاریخ رزرو',
    'Category' => 'دسته',
    'Customer' => 'مشتری',
    'Date' => 'تاریخ',
    'Due Amount' => 'مبلغ پرداخت‌نشده',
    'Paid Amount' => 'مبلغ پرداخت‌شده',
    'Total Amount' => 'مبلغ کل',
    'Serial No' => 'شماره',
    'Time Slot' => 'بازهٔ زمانی',
    'Transaction ID' => 'شناسهٔ تراکنش',

    // ---- vcard listing ---------------------------------------------------------
    'VCards' => 'وی‌کارت‌ها',

    // ---- password reset ---------------------------------------------------------
    'Confirm password' => 'تکرار رمز عبور',
    'confirm Password' => 'تکرار رمز عبور',

    // ---- Enamad trust seal ------------------------------------------------------
    // The partial already passes Persian text; it must exist as a key too so the
    // lookup does not fall through to the raw string on other locales.
    'نماد اعتماد الکترونیک' => 'نماد اعتماد الکترونیک',
];