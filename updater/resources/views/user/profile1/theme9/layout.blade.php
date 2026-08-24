<!DOCTYPE html>
<html lang="en" @if ($userCurrentLang->rtl == 1) dir="rtl" @endif>

<head>
    <!--====== Required meta tags ======-->
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="@yield('meta-description')">
    <meta name="keywords" content="@yield('meta-keywords')">
    @yield('og-meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!--====== Title ======-->
    <title>{{ isset($userBs) && $userBs->website_title ? $userBs->website_title : '' }} - @yield('tab-title')</title>
    <!--====== Favicon Icon ======-->
    <link rel="shortcut icon"
        href="{{ !empty($userBs->favicon) ? asset('assets/front/img/user/' . $userBs->favicon) : '' }}"
        type="image/png">


    <link rel="stylesheet" href="{{ asset('assets/front/theme9-12/css/vendor/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/front/theme9-12') }}/css/vendor/fontawesome.min.css">
    <!-- icomoon -->
    <link rel="stylesheet" href="{{ asset('assets/front/theme9-12') }}/fonts/icomoon/style.css">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/jquery.timepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/front/css/pignose.calendar.min.css') }}">
    <!-- Link Swiper's CSS -->
    <link rel="stylesheet" href="{{ asset('assets/front/theme9-12') }}/css/vendor/swiper-bundle.min.css">
    <!-- slick slider CSS -->
    <link rel="stylesheet" href="{{ asset('assets/front/theme9-12') }}/css/vendor/slick/slick.css">
    <link rel="stylesheet" href="{{ asset('assets/front/theme9-12') }}/css/vendor/slick/slick-theme.css">
    <!-- Aos animate css -->
    <link rel="stylesheet" href="{{ asset('assets/front/theme9-12/css/vendor/aos.min.css') }}">
    <!-- Nice-select -->
    <link rel="stylesheet" href="{{ asset('assets/front/theme9-12') }}/css/vendor/nice-select.css">
    <!-- magnific Popup -->
    <link rel="stylesheet" href="{{ asset('assets/front/theme9-12') }}/css/vendor/magnific-popup.min.css">
    <!-- select2 -->
    <link rel="stylesheet" href="{{ asset('assets/front/theme9-12') }}/css/vendor/select2.min.css">
    <!-- CountDown -->
    <link rel="stylesheet" href="{{ asset('assets/front/theme9-12') }}/css/vendor/odometer.min.css">

    <!-- bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('assets/front/theme9-12/css/vendor/bootstrap.min.css') }}">

    <!-- Style CSS -->
    <link rel="stylesheet" href="{{ asset('assets/front/theme9') }}/css/common.css">
    <!-- custom-dev CSS -->
    <link rel="stylesheet" href="{{ asset('assets/front/theme9') }}/css/custom-dev.css">
    <link rel="stylesheet" href="{{ asset('assets/front/theme9-12/css/inner-common.css') }}">
    @if ($userCurrentLang->rtl == 1)
        <link rel="stylesheet" href="{{ asset('assets/front/theme9') }}/css/rtl-style.css">
    @endif
    @yield('styles')
    @php
        $holidays = App\Models\User\UserHoliday::where('user_id', $user->id)->pluck('date')->toArray();
        $dats = [];
        foreach ($holidays as $value) {
            $dats[] = Carbon\Carbon::parse($value)->format('Y-m-d');
        }
        $holidays = $dats;
        $weekends = App\Models\User\UserDay::where('user_id', $user->id)
            ->where('weekend', 1)
            ->pluck('index')
            ->toArray();
        $primaryColor = $userBs->base_color;
        // if, primary color value does not contain '#', then add '#' before color value
        if (isset($primaryColor) && checkColorCode($primaryColor) == 0) {
            $primaryColor = '#' . $primaryColor;
        }
    @endphp

    <style>
        :root {
            --bs-primary: {{ $primaryColor }}
        }
    </style>

    @foreach ($weekends as $wek)
        <style>
            .pignose-calendar .pignose-calendar-header div.pignose-calendar-week:nth-child({{ $wek + 1 }}) {
                color: #ff6060 !important;
                /* Set the color of the text in the weekend cells */
            }

            .pignose-calendar .pignose-calendar-body .pignose-calendar-row .pignose-calendar-unit-date:nth-child({{ $wek + 1 }}) a {
                color: #ff6060;
                /* Set the color of the text in the weekend cells */
            }
        </style>
    @endforeach


</head>

<body>
    <!-- page-line -->
    @if (request()->routeIs('front.user.detail.view', getParam()))
        <div class="page-line">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
        </div>
    @endif

    <!-- Start preloader  -->
    <div class="preloader">
        <div class="preloader-content">
            <div class="sk-wave sk-center">
                <div class="sk-wave-rect"></div>
                <div class="sk-wave-rect"></div>
                <div class="sk-wave-rect"></div>
                <div class="sk-wave-rect"></div>
                <div class="sk-wave-rect"></div>
            </div>
        </div>
    </div>
    <!-- End preloader  -->

    <!-- header -->
    @include('user.profile1.theme9.header')
    @include('user.profile1.theme9.mobile-header')


    @yield('content')


    <!--footer-->
    <footer class="footer-area pt-lg-70 pt-50 pb-lg-70 pb-50 bg-cover bg-img"
        data-bg-image="{{ asset('assets/front/img/user/footer/' . @$userBs->footer_section_image) }}">
        <div class="container">
            @if (is_array($userPermissions) && in_array('Footer Mail', $userPermissions))
                <div class="footer-top text-center mb-20">
                    <h4>{{ $keywords['Stay_Connected_With_Me'] ?? 'Stay Connected With Me' }}</h4>
                    <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                </div>
            @endif
            <div class="footer-copyright">
                <div class="socials mb-20 justify-content-center">
                    @if (isset($social_medias))
                        @foreach ($social_medias as $social_media)
                            <a href="{{ $social_media->url }}" target="_blank"><i
                                    class="{{ $social_media->icon }}"></i>
                            </a>
                        @endforeach
                    @endif
                </div>
                <!-- footer-copyright -->
                <div class="copyright-content">
                    <p class="fw-medium small text-center mb-0">
                        {{ $keywords['Copyright'] ?? __('Copyright') }} {{ date('Y') }}
                        {{ $keywords['All_Right_Reserved'] ?? __('All Right Reserved') }}
                    </p>
                </div>
            </div>
        </div>
    </footer>
    <div class="progress-wrap">
        <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
            <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
        </svg>
    </div>

    <!-- jQuery Js -->
    <script src="{{ asset('assets/front/theme9-12') }}/js/vendor/jquery-min.js"></script>
    <!-- bootstrap -->
    <script src="{{ asset('assets/front/theme9-12') }}/js/vendor/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/front/js/jquery.timepicker.min.js') }}"></script>
    <script src="{{ asset('assets/front/js/pignose.calendar.full.min.js') }}"></script>
    <!-- swiper -->
    <script src="{{ asset('assets/front/theme9-12') }}/js/vendor/swiper-bundle.min.js"></script>
    <!-- slick Slider js -->
    <script src="{{ asset('assets/front/theme9-12') }}/js/vendor/slick/slick.min.js"></script>
    <!-- nice-select -->
    <script src="{{ asset('assets/front/theme9-12') }}/js/vendor/jquery.nice-select.min.js"></script>
    <!-- jquery-ui -->
    <script src="{{ asset('assets/front/theme9-12') }}/js/vendor/jquery-ui.min.js"></script>
    <!-- Aos js -->
    <script src="{{ asset('assets/front/theme9-12') }}/js/vendor/aos.min.js"></script>
    <!-- lazy Image -->
    <script src="{{ asset('assets/front/theme9-12') }}/js/vendor/lazy.image.js"></script>
    <!-- lazy Image -->
    <script src="{{ asset('assets/front/theme9-12') }}/js/vendor/lazysizes.min.js"></script>
    <!-- magnific popup -->
    <script src="{{ asset('assets/front/theme9-12') }}/js/vendor/jquery.magnific-popup.min.js"></script>
    <!-- mouse hover tab -->
    <script src="{{ asset('assets/front/theme9-12') }}/js/vendor/mouse-hover-move.js"></script>
    <!-- select2 -->
    <script src="{{ asset('assets/front/theme9-12') }}/js/vendor/select2.min.js"></script>

    <!-- CountDown -->
    <script src="{{ asset('assets/front/theme9-12') }}/js/vendor/appear.min.js"></script>
    <script src="{{ asset('assets/front/theme9-12') }}/js/vendor/odometer.min.js"></script>

    <!-- header-menu JS -->
    <script src="{{ asset('assets/front/theme9') }}/js/header-menu.js"></script>
    <!-- back-to-top -->
    <script src="{{ asset('assets/front/theme9') }}/js/back-to-top.js"></script>

    <script>
        "use strict";
        var rtl = {{ $userCurrentLang->rtl }};
        var $holidays = '<?php echo json_encode($holidays); ?>'
        var $weekends = '<?php echo json_encode($weekends); ?>'
        var timeSlotUrl = "{{ route('getTimeSlot', getParam()) }}";;
        var checkThisSlot = "{{ route('checkThisSlot', getParam()) }}";
    </script>
    <!--====== Common js ======-->
    <script src="{{ asset('assets/front/js/profile/common.js') }}"></script>
    <!-- custom -->
    <script src="{{ asset('assets/front/theme9') }}/js/script.js"></script>
    @if (session()->has('success'))
        <script>
            "use strict";
            toastr['success']("{{ __(session('success')) }}");
        </script>
    @endif
    @if (session()->has('error'))
        <script>
            "use strict";
            toastr['error']("{{ __(session('error')) }}");
        </script>
    @endif
    {{-- plugins --}}
    @includeif('user.profile1.partials.plugins')
    {{-- plugins end --}}
    @yield('scripts')
</body>

</html>
