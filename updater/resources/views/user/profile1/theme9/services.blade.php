@extends('user.profile1.theme9.layout')
@section('tab-title')
    {{ $keywords['Services'] ?? 'Services' }}
@endsection
@section('styles')
@endsection
@section('meta-description', !empty($userSeo) ? $userSeo->services_meta_description : '')
@section('meta-keywords', !empty($userSeo) ? $userSeo->services_meta_keywords : '')
@section('content')

@section('br-title')
    {{ $keywords['Services'] ?? 'Services' }}
@endsection
@section('br-link')
    {{ $keywords['Services'] ?? 'Services' }}
@endsection

<div class="pages">

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
            <section class="service-area pt-lg-120 pt-50 pb-lg-100 pb-50">
                <div class="container">
                    <div class="slider-area" data-aos="fade-up" data-aos-delay="300">
                        <!-- Slider main container -->
                        <div class="swiper default-slider pb-60" id="default-slider-service" data-slidespace="0"
                            data-xsmview="1" data-smview="2" data-mdview="3" data-lgview="4" data-xlview="4">
                            <!-- Additional required wrapper -->
                            <div class="swiper-wrapper">
                                @foreach ($services as $service)
                                    <div class="swiper-slide">
                                     <div class="service-card" @if ($service->detail_page == 1) onclick="window.location.href='{{ route('front.user.service.detail', [getParam(), 'slug' => $service->slug, 'id' => $service->id]) }}'" @endif>
                                            <div class="image lazy-container ratio ratio-1-2">
                                                <img src="assets/images/placeholder.svg"
                                                    data-src="{{ isset($service->image) ? asset('assets/front/img/user/services/' . $service->image) : asset('assets/front/img/profile/service-1.jpg') }}"
                                                    alt="image">
                                            </div>
                                            <div class="content">
                                                <div class="icon">
                                                    <i class="{{ $service->icon ?? 'icon-education' }}"></i>
                                                </div>
                                                <h4 class="title">
                                                    <a
                                                        @if ($service->detail_page == 1) href="{{ route('front.user.service.detail', [getParam(), 'slug' => $service->slug, 'id' => $service->id]) }}" @endif>
                                                        {{ strlen($service->name) > 30 ? mb_substr($service->name, 0, 30, 'UTF-8') . '...' : $service->name }}
                                                    </a>
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <!--  pagination -->
                            <div class="swiper-pagination" id="default-slider-service-pagination"></div>
                        </div>
                    </div>
                </div>
            </section>
        @endif
</div>
@endsection
