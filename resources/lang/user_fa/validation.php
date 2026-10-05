<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => 'فیلد :attribute باید پذیرفته شود.',
    'active_url'           => 'فیلد :attribute یک آدرس اینترنتی معتبر نیست.',
    'after'                => 'فیلد :attribute باید تاریخی بعد از :date باشد.',
    'after_or_equal'       => 'فیلد :attribute باید تاریخی بعد از یا برابر با :date باشد.',
    'alpha'                => 'فیلد :attribute فقط باید شامل حروف باشد.',
    'alpha_dash'           => 'فیلد :attribute فقط باید شامل حروف، اعداد، خط تیره و زیرخط باشد.',
    'alpha_num'            => 'فیلد :attribute فقط باید شامل حروف و اعداد باشد.',
    'array'                => 'فیلد :attribute باید یک آرایه باشد.',
    'before'               => 'فیلد :attribute باید تاریخی قبل از :date باشد.',
    'before_or_equal'      => 'فیلد :attribute باید تاریخی قبل از یا برابر با :date باشد.',
    'between'              => [
        'numeric' => 'فیلد :attribute باید بین :min و :max باشد.',
        'file'    => 'حجم فایلد :attribute باید بین :min و :max کیلوبایت باشد.',
        'string'  => 'فیلد :attribute باید بین :min و :max کاراکتر باشد.',
        'array'   => 'فیلد :attribute باید بین :min و :max آیتم باشد.',
    ],
    'boolean'              => 'فیلد :attribute باید درست یا نادرست باشد.',
    'confirmed'            => 'تأیید فیلد :attribute مطابقت ندارد.',
    'date'                 => 'فیلد :attribute یک تاریخ معتبر نیست.',
    'date_equals'          => 'فیلد :attribute باید تاریخ :date باشد.',
    'date_format'          => 'فیلد :attribute باید با فرمت :format همخوانی داشته باشد.',
    'different'            => 'فیلد :attribute و :other باید متفاوت باشند.',
    'digits'               => 'فیلد :attribute باید :digits رقم باشد.',
    'digits_between'       => 'فیلد :attribute باید بین :min و :max رقم باشد.',
    'dimensions'           => 'فیلد :attribute دارای ابعاد تصویر نامعتبر است.',
    'distinct'             => 'فیلد :attribute مقدار تکراری دارد.',
    'email'                => 'فیلد :attribute باید یک آدرس ایمیل معتبر باشد.',
    'ends_with'            => 'فیلد :attribute باید با :values پایان یابد.',
    'exists'               => 'فیلد :attribute انتخاب‌شده معتبر نیست.',
    'file'                 => 'فیلد :attribute باید یک فایل باشد.',
    'filled'               => 'فیلد :attribute باید مقدار داشته باشد.',
    'gt'                   => [
        'numeric' => 'فیلد :attribute باید بزرگ‌تر از :value باشد.',
        'file'    => 'حجم فایلد :attribute باید بیشتر از :value کیلوبایت باشد.',
        'string'  => 'فیلد :attribute باید بیشتر از :value کاراکتر باشد.',
        'array'   => 'فیلد :attribute باید بیشتر از :value آیتم باشد.',
    ],
    'gte'                  => [
        'numeric' => 'فیلد :attribute باید بزرگ‌تر یا برابر با :value باشد.',
        'file'    => 'حجم فایلد :attribute باید بزرگ‌تر یا برابر با :value کیلوبایت باشد.',
        'string'  => 'فیلد :attribute باید بزرگ‌تر یا برابر با :value کاراکتر باشد.',
        'array'   => 'فیلد :attribute باید بزرگ‌تر یا برابر با :value آیتم باشد.',
    ],
    'image'                => 'فیلد :attribute باید یک تصویر باشد.',
    'in'                   => 'فیلد :attribute انتخاب‌شده معتبر نیست.',
    'in_array'             => 'فیلد :attribute در :other وجود ندارد.',
    'integer'              => 'فیلد :attribute باید یک عدد صحیح باشد.',
    'ip'                   => 'فیلد :attribute باید یک نشانی IP معتبر باشد.',
    'ipv4'                 => 'فیلد :attribute باید یک نشانی IPv4 معتبر باشد.',
    'ipv6'                 => 'فیلد :attribute باید یک نشانی IPv6 معتبر باشد.',
    'json'                 => 'فیلد :attribute باید یک رشتهٔ JSON معتبر باشد.',
    'lt'                   => [
        'numeric' => 'فیلد :attribute باید کوچک‌تر از :value باشد.',
        'file'    => 'حجم فایلد :attribute باید کمتر از :value کیلوبایت باشد.',
        'string'  => 'فیلد :attribute باید کمتر از :value کاراکتر باشد.',
        'array'   => 'فیلد :attribute باید کمتر از :value آیتم باشد.',
    ],
    'lte'                  => [
        'numeric' => 'فیلد :attribute باید کوچک‌تر یا برابر با :value باشد.',
        'file'    => 'حجم فایلد :attribute باید کمتر یا برابر با :value کیلوبایت باشد.',
        'string'  => 'فیلد :attribute باید کمتر یا برابر با :value کاراکتر باشد.',
        'array'   => 'فیلد :attribute باید کمتر یا برابر با :value آیتم باشد.',
    ],
    'max'                  => [
        'numeric' => 'فیلد :attribute نباید بیشتر از :max باشد.',
        'file'    => 'حجم فایلد :attribute نباید بیشتر از :max کیلوبایت باشد.',
        'string'  => 'فیلد :attribute نباید بیشتر از :max کاراکتر باشد.',
        'array'   => 'فیلد :attribute نباید بیشتر از :max آیتم باشد.',
    ],
    'mimes'                => 'فیلد :attribute باید یکی از این نوع فایل باشد: :values.',
    'mimetypes'            => 'فیلد :attribute باید یکی از این نوع فایل باشد: :values.',
    'min'                  => [
        'numeric' => 'فیلد :attribute نباید کمتر از :min باشد.',
        'file'    => 'حجم فایلد :attribute نباید کمتر از :min کیلوبایت باشد.',
        'string'  => 'فیلد :attribute نباید کمتر از :min کاراکتر باشد.',
        'array'   => 'فیلد :attribute نباید کمتر از :min آیتم باشد.',
    ],
    'multiple_of'          => 'فیلد :attribute باید مضربی از :value باشد.',
    'not_in'               => 'فیلد :attribute انتخاب‌شده معتبر نیست.',
    'not_regex'            => 'قالب فیلد :attribute معتبر نیست.',
    'numeric'              => 'فیلد :attribute باید یک عدد باشد.',
    'password'             => 'رمز عبور باید حداقل هشت کاراکتر باشد.',
    'present'              => 'فیلد :attribute باید وجود داشته باشد.',
    'regex'                => 'قالب فیلد :attribute معتبر نیست.',
    'required'              => 'فیلد :attribute الزامی است.',
    'required_if'          => 'فیلد :attribute الزامی است وقتی :other برابر با :value باشد.',
    'required_unless'      => 'فیلد :attribute الزامی است مگر اینکه :other برابر با :value باشد.',
    'required_with'        => 'فیلد :attribute الزامی است وقتی :values وجود دارد.',
    'required_with_all'    => 'فیلد :attribute الزامی است وقتی :values وجود دارد.',
    'required_without'     => 'فیلد :attribute الزامی است وقتی :values وجود ندارد.',
    'required_without_all' => 'فیلد :attribute الزامی است وقتی :values وجود ندارد.',
    'prohibited'           => 'فیلد :attribute مجاز نیست.',
    'prohibited_if'        => 'فیلد :attribute مجاز نیست وقتی :other برابر با :value باشد.',
    'prohibited_unless'    => 'فیلد :attribute مجاز نیست مگر اینکه :other برابر با :value باشد.',
    'same'                 => 'فیلد :attribute و :other باید یکسان باشند.',
    'size'                 => [
        'numeric' => 'فیلد :attribute باید برابر با :size باشد.',
        'file'    => 'حجم فایلد :attribute باید برابر با :size کیلوبایت باشد.',
        'string'  => 'فیلد :attribute باید برابر با :size کاراکتر باشد.',
        'array'   => 'فیلد :attribute باید :size آیتم داشته باشد.',
    ],
    'starts_with'          => 'فیلد :attribute باید با :values شروع شود.',
    'string'               => 'فیلد :attribute باید یک رشته باشد.',
    'timezone'             => 'فیلد :attribute باید یک منطقهٔ زمانی معتبر باشد.',
    'unique'               => 'فیلد :attribute قبلاً استفاده شده است.',
    'uploaded'             => 'فیلد :attribute بارگذاری نشده است.',
    'url'                  => 'فیلد :attribute باید یک آدرس اینترنتی معتبر باشد.',
    'uuid'                 => 'فیلد :attribute باید یک UUID معتبر باشد.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */

    'attributes' => [],

];