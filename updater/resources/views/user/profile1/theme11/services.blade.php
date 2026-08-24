@extends('user.profile1.theme11.layout')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/front/theme9-12/css/inner-common.css') }}">
@endsection
@section('tab-title')
    {{ $keywords['Services'] ?? 'Services' }}
@endsection
@section('meta-description', !empty($userSeo) ? $userSeo->services_meta_description : '')
@section('meta-keywords', !empty($userSeo) ? $userSeo->services_meta_keywords : '')

@section('br-title')
    {{ $keywords['Services'] ?? 'Services' }}
@endsection
@section('br-link')
    {{ $keywords['Services'] ?? 'Services' }}
@endsection
@section('content')

    <section class="breadcrumbs-section">
        <div class="container">
            <div class="row align-items-center justify-content-center">
                <div class="col-lg-10">
                    <div class="page-title">
                        <h1>@yield('br-title')</h1>
                        <ul class="breadcrumbs-link">
                            <li><a
                                    href="{{ route('front.user.detail.view', getParam()) }}">{{ $keywords['Home'] ?? 'Home' }}</a>
                            </li>
                            <li class="">@yield('br-title')</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if (is_array($userPermissions) && in_array('Service', $userPermissions))
        <!-- ========= Start service section ========= -->
        <section class="service-area pb-lg-70 pt-90 pb-30">
            <div class="container px-0">
                <div class="slider-area" data-aos="fade-up" data-aos-delay="300">
                    <div class="row">
                        @foreach ($services as $service)
                            <div class="col-lg-3 mb-30">
                                <div class="service-card"
                                    @if ($service->detail_page == 1) onclick="window.location.href='{{ route('front.user.service.detail', [getParam(), 'slug' => $service->slug, 'id' => $service->id]) }}'" @endif>
                                    <div class="card_img lazy-container ratio ratio-1-1">
                                        <img class="blur-up lazyload" src="assets/images/placeholder.svg"
                                            data-src="{{ isset($service->image) ? asset('assets/front/img/user/services/' . $service->image) : asset('assets/front/img/profile/service-1.jpg') }}"
                                            alt="image">
                                    </div>
                                    <div class="category">
                                        <span class="icon"><i class="{{ $service->icon }}"></i></span>
                                        @if ($service->detail_page == 1)
                                            <p>
                                                <a
                                                    href="{{ route('front.user.service.detail', [getParam(), 'slug' => $service->slug, 'id' => $service->id]) }}">
                                                    {{ strlen($service->name) > 30 ? mb_substr($service->name, 0, 30, 'UTF-8') . '...' : $service->name }}
                                                </a>
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
        <!-- ========= end service section ========= -->
    @endif

@endsection
