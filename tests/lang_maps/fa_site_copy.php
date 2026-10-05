<?php
/**
 * Persian site copy for the per-language content tables.
 *
 * The UI strings live in the JSON translation files, but the home page copy
 * (section titles, hero text, cookie notice, SEO meta) is stored per language
 * in basic_settings / basic_extendeds / seos. This file is what
 * tests/register_persian.php writes into the fa rows when it clones the
 * default language.
 *
 * Keys are "table.column". Anything absent here is cloned verbatim - that is
 * correct for data (email addresses, phone numbers, image file names, SMTP
 * credentials, brand names) and wrong for prose, so the harness asserts that
 * every long English prose column below has an entry.
 *
 * package_features is a JSON array of feature names; those go through the
 * front fa.json map instead of this file.
 */

return [
    'basic_settings' => [
        'footer_text' => 'ما یک شرکت چندملیتی و برندهٔ جوایز هستیم و به کیفیت و استانداردهای جهانی اعتقاد داریم.',
        'newsletter_text' => 'برای دریافت تازه‌ترین اخبار و پیشنهادها مشترک شوید و با ما در ارتباط باشید.',
        'copyright_text' => 'کپی‌رایت © ۲۰۲۳. همهٔ حقوق برای Profilex محفوظ است.',
        'intro_subtitle' => 'ما به اعتماد مشتریان باور داریم',
        'intro_title' => 'دربارهٔ ما',
        'intro_text' => 'ما باور داریم که رابطهٔ خوب با مشتری، کلید موفقیت است. تیم ما با دقت و تعهد در کنار شماست تا تجربه‌ای ساده و مطمئن داشته باشید.',
        'contact_form_title' => 'ارسال پاسخ',
        'contact_text' => 'تماس با ما',
        'contact_info_title' => 'اطلاعات تماس',
        'maintainance_text' => "در حال ارتقای سایت هستیم و به‌زودی برمی‌گردیم.\nلطفاً با ما بمانید.\nاز صبوری شما سپاسگزاریم.",
        'feature_title' => 'ویژگی‌ها',
        'work_process_title' => 'فرایند کار',
        'work_process_subtitle' => 'چگونه کار می‌کند',
        'preview_templates_title' => 'قالب‌های در دسترس',
        'preview_templates_subtitle' => 'قالب‌های زیبای ما را ببینید',
        'featured_users_title' => 'کاربران ویژه',
        'featured_users_subtitle' => 'کاربران ویژهٔ ما را ببینید',
        'pricing_title' => 'تعرفه‌ها',
        'pricing_subtitle' => 'بستهٔ مناسب خود را انتخاب کنید',
        'testimonial_title' => 'نظر مشتریان',
        'testimonial_subtitle' => 'مشتریان ما چه می‌گویند',
        'blog_subtitle' => 'تازه‌ترین وبلاگ‌های ما',
        'useful_links_title' => 'لینک‌های مفید',
        'newsletter_title' => 'خبرنامه',
        'newsletter_subtitle' => 'تازه‌ترین به‌روزرسانی‌ها را زودتر دریافت کنید',
        'whatsapp_header_title' => 'سلام!',
        'whatsapp_popup_message' => "چطور می‌توانم کمک کنم؟\nخوشحال می‌شوم.",
    ],

    'basic_extendeds' => [
        'cookie_alert_text' => 'تجربهٔ شما در این سایت با پذیرفتن کوکی‌ها بهتر خواهد شد.',
        'cookie_alert_button_text' => 'پذیرفتن کوکی',
        'hero_section_title' => 'پلتفرم ما، موفقیت شما',
        'hero_section_text' => 'زمان برقراری ارتباط میان شما و مشتری را به کمترین حد برسانید.',
        'hero_section_button_text' => 'مشاهدهٔ طرح‌ها',
        'domain_request_success_message' => 'درخواست دامنهٔ اختصاصی شما دریافت شد. لطفاً دو روز کاری به ما فرصت دهید تا دامنه را به سرور خود متصل کنیم.',
        'cname_record_section_title' => 'پیش از ارسال درخواست دامنهٔ اختصاصی، این مطلب را بخوانید',
        'cname_record_section_text' => '<ul><li><span style="font-weight:600;">پیش از ارسال درخواست دامنهٔ اختصاصی، باید رکوردهای CNAME (که در جدول زیر آمده‌اند) را از پنل ثبت‌کنندهٔ دامنهٔ خود (مانند Namecheap، GoDaddy و ...) روی دامنهٔ خود اضافه کنید.</span></li><li><span style="font-weight:600;">رکورد CNAME برای اشارهٔ دامنهٔ اختصاصی شما به دامنهٔ ما لازم است تا سایت شما روی دامنهٔ اختصاصی‌تان نمایش داده شود.</span></li><li><span style="font-weight:600;">هر ثبت‌کنندهٔ دامنه (مانند GoDaddy، Namecheap و ...) پنل متفاوتی برای افزودن رکورد CNAME دارد. اگر محل افزودن رکورد CNAME را پیدا نکردید، با پشتیبانی ثبت‌کنندهٔ دامنهٔ خود تماس بگیرید؛ آن‌ها محل درست را به شما نشان می‌دهند و در افزودن رکورد هم کمک می‌کنند.</span></li></ul><p><span style="font-weight:600;">رکوردهای CNAME (مقادیر جدول زیر) را از پنل ثبت‌کنندهٔ دامنهٔ خود اضافه کنید:</span></p><table class="table table-bordered"><tbody><tr><td>نوع</td><td>میزبان</td><td>مقدار</td><td>TTL</td></tr><tr><td>CNAME Record</td><td>www</td><td>sassotest.xyz.</td><td>خودکار</td></tr><tr><td>CNAME Record</td><td>@</td><td>sassotest.xyz.</td><td>خودکار</td></tr></tbody></table>',
    ],

    'seos' => [
        'home_meta_keywords' => 'خانه، صفحه',
        'home_meta_description' => 'ساخت رزومه و کارت ویزیت آنلاین به‌صورت حرفه‌ای و سریع.',
        'profiles_meta_keywords' => 'نمایه‌ها، رزومه',
        'profiles_meta_description' => 'نمایه‌های کاربران سایت را مشاهده کنید و با آن‌ها آشنا شوید.',
        'pricing_meta_keywords' => 'تعرفه‌ها، بسته‌ها',
        'pricing_meta_description' => 'بستهٔ مورد نظر خود را انتخاب کنید و به امکانات کامل دسترسی داشته باشید.',
        'blogs_meta_keywords' => 'وبلاگ‌ها، مقالات',
        'blogs_meta_description' => 'تازه‌ترین مقالات و نوشته‌ها را در وبلاگ ما بخوانید.',
        'faqs_meta_keywords' => 'پرسش‌های متداول، راهنما',
        'faqs_meta_description' => 'پاسخ پرسش‌های پرتکرار در مورد خدمات و امکانات سایت.',
        'contact_meta_keywords' => 'تماس، پشتیبانی',
        'contact_meta_description' => 'برای دریافت راهنمایی و پشتیبانی با ما در تماس باشید.',
        'login_meta_keywords' => 'ورود، حساب کاربری',
        'login_meta_description' => 'ورود به حساب کاربری خود و مدیریت نمایه و رزومه.',
        'forget_password_meta_keywords' => 'رمز عبور، بازیابی',
        'forget_password_meta_description' => 'بازیابی رمز عبور حساب کاربری خود.',
        'checkout_meta_keywords' => 'پرداخت، تکمیل خرید',
        'checkout_meta_description' => 'تکمیل فرایند خرید و پرداخت بستهٔ انتخابی شما.',
        'vcard_meta_keywords' => 'وی‌کارت، کارت ویزیت',
        'vcard_meta_description' => 'قالب‌های وی‌کارت دیجیتال برای معرفی خود به مشتریان.',
        'templates_meta_keywords' => 'قالب‌ها، تم‌ها',
        'templates_meta_description' => 'قالب‌های متنوع برای نمایه، رزومه و وی‌کارت خود.',
        'cv_meta_keywords' => 'رزومه، رزومهٔ دیجیتال',
        'cv_meta_description' => 'رزومهٔ آنلاین بسازید و آن را در قالب‌های مختلف خروجی بگیرید.',
    ],
];