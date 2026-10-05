<?php

return [

    /*
     *
     * Shared translations.
     *
     */
    'title' => config('installer.item_name') . ' نصب‌کننده',
    'next' => 'گام بعدی',
    'back' => 'گام قبل',
    'finish' => 'نصب',
    'forms' => [
        'errorTitle' => 'خطاهای زیر رخ داد:',
    ],

    /*
     *
     * Home page translations.
     *
     */
    'welcome' => [
        'templateTitle' => 'خوش آمدید',
        'title'   => config('installer.item_name') . ' نصب‌کننده',
        'message' => 'نصب و راه‌اندازی آسان با دستیار گام‌به‌گام.',
        'next'    => 'بررسی پیش‌نیازها',
    ],

    /*
     *
     * Requirements page translations.
     *
     */
    'requirements' => [
        'templateTitle' => 'گام ۱ | پیش‌نیازهای سرور',
        'title' => 'پیش‌نیازهای سرور',
        'next'    => 'بررسی دسترسی‌ها',
    ],

    /*
     *
     * Permissions page translations.
     *
     */
    'permissions' => [
        'templateTitle' => 'گام ۲ | دسترسی‌ها',
        'title' => 'دسترسی‌ها',
        'next' => 'پیکربندی محیط',
    ],

    /*
     *
     * License page translations.
     *
     */
    'license' => [
        'templateTitle' => 'گام ۳ | تأیید مجوز',
        'title' => 'تأیید مجوزها',
        'next' => 'تأیید',
    ],

    /*
     *
     * Environment page translations.
     *
     */
    'environment' => [
        'menu' => [
            'templateTitle' => 'گام ۳ | تنظیمات محیط',
            'title' => 'تنظیمات محیط',
            'desc' => 'لطفاً انتخاب کنید فایل <code>.env</code> را چگونه پیکربندی می‌خواهید.',
            'wizard-button' => 'راه‌اندازی با فرم',
            'classic-button' => 'ویرایشگر متنی کلاسیک',
        ],
        'wizard' => [
            'templateTitle' => 'گام ۴ | تنظیم محیط و پایگاه‌داده',
            'title' => 'تنظیم محیط و پایگاه‌داده',
            'tabs' => [
                'environment' => 'محیط',
                'database' => 'پایگاه‌داده',
                'application' => 'برنامه',
            ],
            'form' => [
                'name_required' => 'وارد کردن نام محیط الزامی است.',
                'app_name_label' => 'نام برنامه',
                'app_name_placeholder' => 'نام برنامه',
                'app_environment_label' => 'محیط برنامه',
                'app_environment_label_local' => 'محلی',
                'app_environment_label_developement' => 'توسعه',
                'app_environment_label_qa' => 'کنترل کیفیت',
                'app_environment_label_production' => 'عملیاتی',
                'app_environment_label_other' => 'سایر',
                'app_environment_placeholder_other' => 'محیط خود را وارد کنید...',
                'app_debug_label' => 'حالت اشکال‌زدایی برنامه',
                'app_debug_label_true' => 'روشن',
                'app_debug_label_false' => 'خاموش',
                'app_log_level_label' => 'سطح ثبت گزارش برنامه',
                'app_log_level_label_debug' => 'debug',
                'app_log_level_label_info' => 'info',
                'app_log_level_label_notice' => 'notice',
                'app_log_level_label_warning' => 'warning',
                'app_log_level_label_error' => 'error',
                'app_log_level_label_critical' => 'critical',
                'app_log_level_label_alert' => 'alert',
                'app_log_level_label_emergency' => 'emergency',
                'app_url_label' => 'نشانی برنامه',
                'app_url_placeholder' => 'نشانی برنامه',
                'db_connection_failed' => 'اتصال به پایگاه‌داده ممکن نبود.',
                'db_connection_label' => 'اتصال پایگاه‌داده',
                'db_connection_label_mysql' => 'mysql',
                'db_connection_label_sqlite' => 'sqlite',
                'db_connection_label_pgsql' => 'pgsql',
                'db_connection_label_sqlsrv' => 'sqlsrv',
                'db_host_label' => 'میزبان پایگاه‌داده',
                'db_host_placeholder' => 'میزبان پایگاه‌داده',
                'db_port_label' => 'درگاه پایگاه‌داده',
                'db_port_placeholder' => 'درگاه پایگاه‌داده',
                'db_name_label' => 'نام پایگاه‌داده',
                'db_name_placeholder' => 'نام پایگاه‌داده',
                'db_username_label' => 'نام کاربری پایگاه‌داده',
                'db_username_placeholder' => 'نام کاربری پایگاه‌داده',
                'db_password_label' => 'رمز عبور پایگاه‌داده',
                'db_password_placeholder' => 'رمز عبور پایگاه‌داده',

                'app_tabs' => [
                    'more_info' => 'اطلاعات بیشتر',
                    'broadcasting_title' => 'پخش، کش، نشست و صف',
                    'broadcasting_label' => 'درایور پخش',
                    'broadcasting_placeholder' => 'درایور پخش',
                    'cache_label' => 'درایور کش',
                    'cache_placeholder' => 'درایور کش',
                    'session_label' => 'درایور نشست',
                    'session_placeholder' => 'درایور نشست',
                    'queue_label' => 'درایور صف',
                    'queue_placeholder' => 'درایور صف',
                    'redis_label' => 'درایور ردیس',
                    'redis_host' => 'میزبان ردیس',
                    'redis_password' => 'رمز عبور ردیس',
                    'redis_port' => 'درگاه ردیس',

                    'mail_label' => 'ایمیل',
                    'mail_driver_label' => 'درایور ایمیل',
                    'mail_driver_placeholder' => 'درایور ایمیل',
                    'mail_host_label' => 'میزبان ایمیل',
                    'mail_host_placeholder' => 'میزبان ایمیل',
                    'mail_port_label' => 'درگاه ایمیل',
                    'mail_port_placeholder' => 'درگاه ایمیل',
                    'mail_username_label' => 'نام کاربری ایمیل',
                    'mail_username_placeholder' => 'نام کاربری ایمیل',
                    'mail_password_label' => 'رمز عبور ایمیل',
                    'mail_password_placeholder' => 'رمز عبور ایمیل',
                    'mail_encryption_label' => 'رمزنگاری ایمیل',
                    'mail_encryption_placeholder' => 'رمزنگاری ایمیل',

                    'pusher_label' => 'پاشر',
                    'pusher_app_id_label' => 'شناسهٔ برنامهٔ پاشر',
                    'pusher_app_id_palceholder' => 'شناسهٔ برنامهٔ پاشر',
                    'pusher_app_key_label' => 'کلید برنامهٔ پاشر',
                    'pusher_app_key_palceholder' => 'کلید برنامهٔ پاشر',
                    'pusher_app_secret_label' => 'سیکرت برنامهٔ پاشر',
                    'pusher_app_secret_palceholder' => 'سیکرت برنامهٔ پاشر',
                ],
                'buttons' => [
                    'setup_database' => 'راه‌اندازی پایگاه‌داده و محیط',
                    'setup_application' => 'راه‌اندازی برنامه',
                    'install' => 'نصب',
                ],
            ],
        ],
        'classic' => [
            'templateTitle' => 'گام ۳ | تنظیمات محیط | ویرایشگر کلاسیک',
            'title' => 'ویرایشگر کلاسیک محیط',
            'save' => 'ذخیرهٔ .env',
            'back' => 'استفاده از فرم',
            'install' => 'ذخیره و نصب',
        ],
        'success' => 'تنظیمات فایل .env شما ذخیره شد.',
        'errors' => 'ذخیرهٔ فایل .env ممکن نبود؛ لطفاً آن را به‌صورت دستی بسازید.',
    ],

    'install' => 'نصب',

    /*
     *
     * Installed Log translations.
     *
     */
    'installed' => [
        'success_log_message' => config('installer.item_name') . ' با موفقیت نصب شد در ',
    ],

    /*
     *
     * Final page translations.
     *
     */
    'final' => [
        'title' => 'نصب به پایان رسید',
        'templateTitle' => 'نصب به پایان رسید',
        'finished' => 'برنامه با موفقیت نصب شد.',
        'migration' => 'خروجی کنسول مایگریشن و سید:',
        'console' => 'خروجی کنسول برنامه:',
        'log' => 'ورودی گزارش نصب:',
        'env' => 'فایل نهایی .env:',
        'exit' => 'برای خروج اینجا کلیک کنید',
    ],

    /*
     *
     * Update specific translations
     *
     */
    'updater' => [
        /*
         *
         * Shared translations.
         *
         */
        'title' => 'به‌روزرسان لاراول',

        /*
         *
         * Welcome page translations for update feature.
         *
         */
        'welcome' => [
            'title'   => 'به به‌روزرسان خوش آمدید',
            'message' => 'به دستیار به‌روزرسانی خوش آمدید.',
        ],

        /*
         *
         * Welcome page translations for update feature.
         *
         */
        'overview' => [
            'title'   => 'نمای کلی',
            'message' => '۱ به‌روزرسانی وجود دارد.|:number به‌روزرسانی وجود دارد.',
            'install_updates' => 'نصب به‌روزرسانی‌ها',
        ],

        /*
         *
         * Final page translations.
         *
         */
        'final' => [
            'title' => 'به پایان رسید',
            'finished' => 'پایگاه‌دادهٔ برنامه با موفقیت به‌روزرسانی شد.',
            'exit' => 'برای خروج اینجا کلیک کنید',
        ],

        'log' => [
            'success_message' => 'نصب‌کنندهٔ لاراول با موفقیت به‌روزرسانی شد در ',
        ],
    ],
];