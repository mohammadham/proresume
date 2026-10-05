<?php
/**
 * One-shot generator: builds resources/lang/fa.json from an English->Persian
 * map so the file can never drift from en.json's key set (a hand-written JSON
 * risks a typo'd and dropped key, which silently falls back to English).
 *
 * Two kinds of extra keys beyond en.json's set are allowed, each from an
 * explicit reviewed list rather than by accident:
 *   - $menuLabels: labels the admin menu builder writes into the menus table
 *   - $viewGaps:   strings the blades call with __('...') that never made it
 *                  into en.json at all (404 page, PDF invoices, profile theme
 *                  footers). Found by tests/lang_coverage_audit.php.
 *
 * Usage: php tests/_build_fa.php
 */
$en = json_decode(file_get_contents(__DIR__ . '/../resources/lang/en.json'), true);

$t = [
    'Thanks & Regards' => 'با تشکر و احترام',
    'Free' => 'رایگان',
    'Price' => 'قیمت',
    'Currency' => 'ارز',
    'Expire Date' => 'تاریخ انقضا',
    'Package Title' => 'عنوان بسته',
    'Order Date' => 'تاریخ سفارش',
    'Payment Status' => 'وضعیت پرداخت',
    'Payment Method' => 'روش پرداخت',
    'Order Price' => 'مبلغ سفارش',
    'Order ID' => 'شناسه سفارش',
    'Order Details' => 'جزئیات سفارش',
    'Phone' => 'تلفن',
    'Email' => 'ایمیل',
    'Bill to' => 'صورتحساب برای',
    'INVOICE' => 'فاکتور',
    'message' => 'پیام',
    'subject' => 'موضوع',
    'payment_method' => 'روش پرداخت',
    'to verify your email address. Please click link in that email to continue' => 'برای تأیید ایمیل خود. برای ادامه روی پیوند داخل آن ایمیل کلیک کنید',
    'Email Verified Successfully' => 'ایمیل با موفقیت تأیید شد',
    'Minimum 10 INR required for this payment gateway' => 'برای این درگاه پرداخت حداقل ۱۰ روپیه لازم است',
    'Captcha error! try again later or contact site admin' => 'خطای کپچا! بعداً دوباره تلاش کنید یا با مدیر سایت تماس بگیرید',
    'Please verify that you are not a robot' => 'لطفاً تأیید کنید که ربات نیستید',
    'Credentials Does not Match' => 'اطلاعات ورود مطابقت ندارد',
    'Too many attempt, your account has been banned for 15 minutes' => 'تلاش بیش از حد؛ حساب شما به مدت ۱۵ دقیقه مسدود شد',
    'Your account has been banned' => 'حساب شما مسدود شده است',
    'Please verify your account' => 'لطفاً حساب خود را تأیید کنید',
    'Golden' => 'طلایی',
    'Platinum' => 'پلاتینی',
    'Beginner' => 'تازه‌کار',
    'Freelancer' => 'فریلنسر',
    'Entrepreneur' => 'کارآفرین',
    'password' => 'رمز عبور',
    'username' => 'نام کاربری',
    'Follow' => 'دنبال کردن',
    'receipt' => 'رسید',
    'try again later or contact site admin' => 'بعداً دوباره تلاش کنید یا با مدیر سایت تماس بگیرید',
    'Captcha error' => 'خطای کپچا',
    'fullname' => 'نام و نام خانوادگی',
    'new_password_confirmation' => 'تکرار رمز عبور جدید',
    'new_password' => 'رمز عبور جدید',
    'current_password' => 'رمز عبور فعلی',
    'images are allowed' => 'تصویر مجاز است',
    'Only' => 'فقط',
    'Please select a payment method' => 'لطفاً یک روش پرداخت انتخاب کنید',
    'Token Problem With Your Token' => 'مشکل توکن با توکن شما',
    'Mercadopago' => 'مرکادوپاگو',
    'Mollie' => 'مولی',
    'Please try another slot' => 'لطفاً زمان دیگری را امتحان کنید',
    'This time slot is booked' => 'این بازه زمانی رزرو شده است',
    'Please select a time slot' => 'لطفاً یک بازه زمانی انتخاب کنید',
    'field is required' => 'این فیلد الزامی است',
    'The' => 'این',
    'The email field is required' => 'فیلد ایمیل الزامی است',
    'The name field is required' => 'فیلد نام الزامی است',
    'files are allowed' => 'فایل مجاز است',
    'Your password was not updated, since the provided current password does not match' => 'رمز عبور شما تغییر نکرد، زیرا رمز عبور فعلی واردشده مطابقت ندارد',
    'Password updated successfully' => 'رمز عبور با موفقیت به‌روزرسانی شد',
    'Password confirmation failed' => 'تأیید رمز عبور ناموفق بود',
    'Your profile updated successfully' => 'نمایه شما با موفقیت به‌روزرسانی شد',
    'Mail could not be sent' => 'ارسال ایمیل ممکن نبود',
    'A verification mail has been sent to your email address' => 'ایمیل تأیید به نشانی ایمیل شما ارسال شد',
    'Please click link in that email to continue' => 'برای ادامه روی پیوند داخل آن ایمیل کلیک کنید',
    'to verify your email address' => 'برای تأیید ایمیل خود',
    'We need to verify your email address. We have sent an email to' => 'ما باید ایمیل شما را تأیید کنیم. یک ایمیل به این نشانی ارسال کردیم',
    'Username has already been taken' => 'این نام کاربری قبلاً استفاده شده است',
    'password_confirmation' => 'تکرار رمز عبور',
    'The provided credentials do not match our records' => 'اطلاعات ورود واردشده با اطلاعات ما مطابقت ندارد',
    'Email status updated for' => 'وضعیت ایمیل به‌روزرسانی شد برای',
    'Share' => 'اشتراک‌گذاری',
    'All Categories' => 'همهٔ دسته‌ها',
    'Please provide valid CVV' => 'لطفاً CVV معتبر وارد کنید',
    'Expiration date must be in the future' => 'تاریخ انقضا باید در آینده باشد',
    'Please provide valid expiration month' => 'لطفاً ماه انقضای معتبر وارد کنید',
    'Please provide valid expiration year' => 'لطفاً سال انقضای معتبر وارد کنید',
    'Please provide valid credit card number' => 'لطفاً شمارهٔ کارت اعتباری معتبر وارد کنید',
    'Your card number is incomplete' => 'شمارهٔ کارت شما ناقص است',
    'Receipt' => 'رسید',
    'Card Code' => 'کد کارت',
    'Expire Year' => 'سال انقضا',
    'Expire Month' => 'ماه انقضا',
    'Card Number' => 'شمارهٔ کارت',
    'successful payment' => 'پرداخت موفق',
    'continue' => 'ادامه',
    'Coupon applied successfully' => 'کد تخفیف با موفقیت اعمال شد',
    'This coupon is not valid for this package' => 'این کد تخفیف برای این بسته معتبر نیست',
    'This coupon reached maximum limit' => 'این کد تخفیف به سقف مجاز رسیده است',
    'This coupon does not exist' => 'این کد تخفیف وجود ندارد',
    'Apply' => 'اعمال',
    'Coupon already applied' => 'کد تخفیف قبلاً اعمال شده است',
    'Enter Coupon Code Here' => 'کد تخفیف را اینجا وارد کنید',
    'Your subdomain based profile URL will be' => 'نشانی نمایهٔ مبتنی بر زیردامنهٔ شما خواهد بود',
    'Dashboard' => 'پیشخوان',
    'Unfollow' => 'لغو دنبال کردن',
    'You have unfollowed successfully' => 'دنبال کردن لغو شد',
    'You have followed successfully' => 'با موفقیت دنبال شد',
    'You are subscribed successfully' => 'با موفقیت عضویت شما ثبت شد',
    'Search by city' => 'جستجو بر اساس شهر',
    'NO PROFILE FOUND' => 'نمایه‌ای یافت نشد',
    'Tawk.to' => 'تاک‌تو',
    'Languages' => 'زبان‌ها',
    'Education' => 'تحصیلات',
    'vCard' => 'وی‌کارت',
    'Facebook Pixel' => 'فیس‌بوک پیکسل',
    'WhatsApp' => 'واتساپ',
    'Disqus' => 'دیسکیوز',
    'Google Analytics' => 'گوگل آنالیتیکس',
    'Appointment' => 'نوبت‌دهی',
    'Portfolio' => 'نمونه‌کار',
    'Portfolio Category' => 'دستهٔ نمونه‌کار',
    'Blog Category' => 'دستهٔ وبلاگ',
    'unlimited' => 'نامحدود',
    'Themes' => 'قالب‌ها',
    'Receipt image must be' => 'تصویر رسید باید باشد',
    'Updated successfully!' => 'با موفقیت به‌روزرسانی شد!',
    'Publicly Hidden Status Changed successfully!' => 'وضعیت نمایش عمومی با موفقیت تغییر کرد!',
    'laguage is set as defualt' => 'زبان به‌عنوان پیش‌فرض تنظیم شد',
    'New Frontend Keyword Added successfully' => 'کلیدواژهٔ جدید سایت با موفقیت افزوده شد',
    'Mail sent successfully!' => 'ایمیل با موفقیت ارسال شد!',
    'Cache, route, view, config cleared successfully!' => 'کش، مسیر، ویو و تنظیمات با موفقیت پاک شد!',
    'Bulk deleted successfully!' => 'حذف گروهی با موفقیت انجام شد!',
    'Deleted successfully!' => 'با موفقیت حذف شد!',
    'Store successfully!' => 'با موفقیت ذخیره شد!',
    'Update successfully!' => 'با موفقیت به‌روزرسانی شد!',
    'Home' => 'خانه',
    'Profiles' => 'نمایه‌ها',
    'All Profiles' => 'همهٔ نمایه‌ها',
    'Pricing' => 'تعرفه‌ها',
    'FAQs' => 'پرسش‌های متداول',
    'Contact' => 'تماس',
    'Log in' => 'ورود',
    'Monthly' => 'ماهانه',
    'Yearly' => 'سالانه',
    'View Profile' => 'مشاهدهٔ نمایه',
    'Trial' => 'آزمایشی',
    'Full Name' => 'نام و نام خانوادگی',
    'Email Address' => 'نشانی ایمیل',
    'Subject' => 'موضوع',
    'Message' => 'پیام',
    'Login' => 'ورود',
    'login' => 'ورود',
    'email' => 'ایمیل',
    'Password' => 'رمز عبور',
    'Username' => 'نام کاربری',
    'Checkout' => 'تکمیل خرید',
    'First Name' => 'نام',
    'Last Name' => 'نام خانوادگی',
    'Phone Number' => 'شمارهٔ تلفن',
    'Company Name' => 'نام شرکت',
    'Street Address' => 'نشانی خیابان',
    'City' => 'شهر',
    'State' => 'استان',
    'Address' => 'نشانی',
    'Postcode/Zip' => 'کد پستی',
    'Country' => 'کشور',
    'Click Here to Login' => 'برای ورود اینجا کلیک کنید',
    'Package Summary' => 'خلاصهٔ بسته',
    'Package' => 'بسته',
    'Start Date' => 'تاریخ شروع',
    'Expiry Date' => 'تاریخ پایان',
    'Total' => 'مجموع',
    'Call Us' => 'تماس با ما',
    'Email Us' => 'ایمیل به ما',
    'Confirm' => 'تأیید',
    'Choose an option' => 'یک گزینه انتخاب کنید',
    'Post Code' => 'کد پستی',
    'Billing Details' => 'جزئیات صورتحساب',
    'LOG IN' => 'ورود',
    'Email address' => 'نشانی ایمیل',
    'Confirm Password' => 'تأیید رمز عبور',
    'Send Password Reset Link' => 'ارسال لینک بازنشانی رمز عبور',
    'Reset Password' => 'بازنشانی رمز عبور',
    'Lost your password' => 'رمز عبور خود را فراموش کرده‌اید؟',
    'Enter Your Email' => 'ایمیل خود را وارد کنید',
    'FEATURED USER' => 'کاربر ویژه',
    'Search by name' => 'جستجو بر اساس نام',
    'successful_payment' => 'پرداخت موفق',
    'cancel_payment' => 'پرداخت لغو شد',
    'Something went wrong.Please recheck' => 'مشکلی پیش آمد. لطفاً دوباره بررسی کنید',
    'payment_success_msg' => 'پرداخت شما با موفقیت انجام شد',
    'extend_success_msg' => 'مدت اشتراک شما با موفقیت تمدید شد',
    'payment_success' => 'پرداخت موفق',
    'offline_payment_success' => 'پرداخت آفلاین موفق',
    'offline_payment_success_msg' => 'پرداخت آفلاین شما ثبت شد',
    'offline_extend_success_msg' => 'تمدید آفلاین با موفقیت انجام شد',
    'trial_success_msg' => 'دورهٔ آزمایشی شما فعال شد',
    'trial_success' => 'دورهٔ آزمایشی موفق',
    'only_paypal_INR' => 'فقط با پی‌پال و ارز روپیه',
    'only_paytm_INR' => 'فقط با پی‌تی‌ام و ارز روپیه',
    'only_paystack_NGN' => 'فقط با پی‌استک و ارز نایریا',
    'only_razorpay_INR' => 'فقط با رزورپی و ارز روپیه',
    'only_instamojo_INR' => 'فقط با اینستاموجو و ارز روپیه',
    'only_mercadopago_BRL' => 'فقط با مرکادوپاگو و ارز برزیل',
    'only_INR' => 'فقط با ارز روپیه',
    'invalid_currency' => 'ارز نامعتبر',
    'What I Do ?' => 'کار من چیست؟',
    'Experience' => 'سوابق کاری',
    'Technical Skills' => 'مهارت‌های تخصصی',
    'About Me' => 'دربارهٔ من',
    'My Resume' => 'رزومهٔ من',
    'Achievements' => 'دستاوردها',
    'Skills' => 'مهارت‌ها',
    'Services' => 'خدمات',
    'Portfolios' => 'نمونه‌کارها',
    'Testimonials' => 'نظر مشتریان',
    'Blogs' => 'وبلاگ‌ها',
    'Blog Details' => 'جزئیات وبلاگ',
    'Get in touch' => 'تماس با ما',
    'Send Message' => 'ارسال پیام',
    'Download CV' => 'دانلود رزومه',
    'Present' => 'معرفی',
    'Read More' => 'ادامهٔ مطلب',
    'All' => 'همه',
    'Purchase' => 'خرید',
    'Submit' => 'ارسال',
    'Page Not Found' => 'صفحه پیدا نشد',
    '404' => '۴۰۴',
    'Go to Home' => 'بازگشت به خانه',
    'Go to Dashboard' => 'رفتن به پیشخوان',
    'Search by first name, last name, username' => 'جستجو بر اساس نام، نام خانوادگی یا نام کاربری',
    'Search by designation' => 'جستجو بر اساس عنوان شغلی',
    'QR Builder' => 'سازندهٔ QR',
    'Follow/Unfollow' => 'دنبال کردن / لغو دنبال کردن',
    'Blog' => 'وبلاگ',
    'Skill' => 'مهارت',
    'Service' => 'خدمت',
    'Testimonial' => 'نظر مشتری',
    'monthly' => 'ماهانه',
    'yearly' => 'سالانه',
    'Custom Domain' => 'دامنهٔ اختصاصی',
    'Subdomain' => 'زیردامنه',
    'Online CV & Export' => 'رزومهٔ آنلاین و خروجی گرفتن',
    "Don't have an account?" => 'حساب کاربری ندارید؟',
    'Click Here' => 'اینجا کلیک کنید',
    'to Signup' => 'برای ثبت‌نام',
    'Signup' => 'ثبت‌نام',
    'Unlimited' => 'نامحدود',
    'vCards' => 'وی‌کارت‌ها',
    'Google Analytics, Disqus, WhatsApp, Facebook Pixel, Tawk.to' => 'گوگل آنالیتیکس، دیسکیوز، واتساپ، فیس‌بوک پیکسل، تاک‌تو',
    'Lifetime' => 'مادام‌العمر',
    'Coupons' => 'کدهای تخفیف',
    'lifetime' => 'مادام‌العمر',
    'Mercado Pago' => 'مرکادو پاگو',
    'Mollie Payment' => 'پرداخت مولی',
    'Flutterwave' => 'فلاترویو',
    'Razorpay' => 'رازورپی',
    'Paytm' => 'پی‌تی‌ام',
    'Paystack' => 'پی‌استک',
    'Instamojo' => 'اینستاموجو',
    'Stripe' => 'استرایپ',
    'Paypal' => 'پی‌پال',
    'Authorize.net' => 'اورتورایز.نت',
    'Auth Token' => 'توکن احراز هویت',
];

/*
 * Labels the admin menu builder writes into the `menus` table via
 * __('Website Templates') etc. Those keys are not in en.json - the menu is
 * translated when it is built, not at render time - so they are listed here
 * as an explicit, reviewed allow-list instead of silently drifting.
 */
$menuLabels = [
    'Templates' => 'قالب‌ها',
    'Website Templates' => 'قالب‌های سایت',
    'Cv Templates' => 'قالب‌های رزومه',
    'CV Templates' => 'قالب‌های رزومه',
    'Vcards' => 'وی‌کارت‌ها',
    'Vcards Templates' => 'قالب‌های وی‌کارت',
    'Pages' => 'صفحات',
    'Custom Page' => 'صفحهٔ اختصاصی',
    'About Us' => 'دربارهٔ ما',
    'Terms & Conditions' => 'شرایط و مقررات',
    'Privacy Policy' => 'سیاست حفظ حریم خصوصی',
    'Our Blogs' => 'وبلاگ‌های ما',
    'Contact Us' => 'تماس با ما',
    // Package feature names (basic_extendeds.package_features)
    'Gallery' => 'گالری',
    'Work Process' => 'فرایند کار',
];

/*
 * Keys the views ask for that are absent from en.json entirely, so parity
 * cannot catch them. See tests/lang_coverage_audit.php.
 */
$viewGaps = require __DIR__ . '/lang_maps/fa_view_gaps.php';

$missing = array_diff(array_keys($en), array_keys($t));
$extra = array_diff(array_keys($t), array_keys($en));
if ($missing || $extra) {
    fwrite(STDERR, "KEY MISMATCH\n");
    if ($missing) {
        fwrite(STDERR, "missing from map:\n  " . implode("\n  ", $missing) . "\n");
    }
    if ($extra) {
        fwrite(STDERR, "not in en.json:\n  " . implode("\n  ", $extra) . "\n");
    }
    exit(1);
}

// Guard every value, including the allow-listed menu labels: a Persian page
// must never render a raw English string.
$hasPersian = static function ($s) {
    return (bool) preg_match('/[\x{0600}-\x{06FF}]/u', $s);
};
$untranslated = [];
foreach (array_merge($t, $menuLabels, $viewGaps) as $k => $v) {
    if (!$hasPersian($v)) {
        $untranslated[] = $k;
    }
}
if ($untranslated) {
    fwrite(STDERR, "NO PERSIAN SCRIPT in:\n  " . implode("\n  ", $untranslated) . "\n");
    exit(1);
}

$out = [];
foreach ($en as $k => $v) {
    $out[$k] = $t[$k];
}
foreach (array_merge($menuLabels, $viewGaps) as $k => $v) {
    if (!isset($out[$k])) {
        $out[$k] = $v;
    }
}
file_put_contents(
    __DIR__ . '/../resources/lang/fa.json',
    json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n"
);
echo 'wrote fa.json with ' . count($out) . " keys\n";