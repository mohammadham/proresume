@extends('user.profile1.theme9.layout')

@section('tab-title')
    {{ $keywords['Home'] ?? 'Home' }}
@endsection

@section('meta-description', !empty($userSeo) ? $userSeo->home_meta_description : '')
@section('meta-keywords', !empty($userSeo) ? $userSeo->home_meta_keywords : '')

@section('content')


    <!-- pages -->
    <div class="pages">
        <!-- ======= START HERO section ========= -->
        <section class="hero-area bg-cover bg-img"
            data-bg-image="{{ asset('assets/front/img/user/home_settings/' . $home_text->hero_background_image) }}">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-5">
                        <div class="content mb-50">
                            <h1 class="title lc-2 mb-20" data-aos="fade-up" data-aos-delay="100">
                                {{ $home_text->first_name ?? $user->first_name }}
                                {{ $home_text->last_name ?? $user->last_name }}</h1>
                            <p class="desc mb-lg-50 mb-30" data-aos="fade-up" data-aos-delay="200">
                                {{ @$home_text->designation }}
                            </p>

                            @if (!is_null(@$home_text->hero_button_name) && !is_null(@$home_text->hero_button_url))
                                <a href="{{ @$home_text->hero_button_url }}" class="theme-btn" data-aos="fade-up"
                                    data-aos-delay="300">
                                    {!! @$home_text->hero_button_name !!}
                                    <i class="fal fa-arrow-right"></i>
                                </a>
                            @endif

                        </div>
                    </div>
                    <div class="col-lg-7" data-aos="fade-left" data-aos-delay="200">
                        <div class="row hero-image-row">
                            <div class="col-sm-12 col-12 hero-image-item">
                                <div class="hero-image">
                                    <img class="blur-up lazyload" src="assets/images/placeholder.svg"
                                        data-src="{{ asset('assets/front/img/user/home_settings/' . $home_text->hero_image) }}"
                                        alt="hero-img">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- ========= END HERO section ========= -->

        <!-- ========= Step Area section ========= -->
        @if (is_array($userPermissions) && in_array('Work Process', $userPermissions))
            <section class="step-area pt-lg-120 pt-50 pb-lg-120 pb-50">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-xl-6 col-lg-10" data-aos="fade-up" data-aos-delay="100">
                            <h2 class="text-center mb-lg-40 mb-30">
                                {{ @$home_text->work_process_title ?? 'Which Steps I Follow For Painting' }}</h2>
                        </div>
                    </div>
                    @if (count($workprocess) == 0)
                        <h4 class="text-center">{{ __('Work Process Not Found') }}</h4>
                    @else
                        <div class="step-card-grid" data-aos="fade-up" data-aos-delay="300">
                            @foreach ($workprocess as $key => $workproces)
                                <div class="step-card">
                                    <div class="serial">0{{ $key + 1 }}</div>
                                    <div class="icon">
                                        <img class="blur-up lazyload" src="assets/images/placeholder.svg"
                                            data-src="{{ asset('assets/user/work-process/' . $workproces->image) }}"
                                            alt="icon">
                                    </div>
                                    <h4 class="title lc-1 mb-10">{{ $workproces->title }}</h4>
                                    <p class="desc small fw-medium lc-2 mb-0">{{ $workproces->subtitle }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        @endif
        <!-- ========= Step Area section ========= -->

        <!-- ========= Start About Area section ========= -->
        <section class="about-area">
            <div class="container">
                <div class="title-wrap">
                    <h2 class="title-1 lc-1">{{ $home_text->about_title ?? 'The History of My' }}</h2>
                    <h2 class="title-2 lc-1">{{ $home_text->about_subtitle ?? 'Artist Carrier' }}</h2>
                </div>
                <div class="row about-area-row">
                    <div class="col-xl-6 col-lg-12">
                        <div class="about-image">
                            @if (!empty($home_text->about_image))
                                <img data-src="{{ asset('assets/front/img/user/home_settings/' . $home_text->about_image) }}"
                                    class="lazyload blur-up" alt="About Image">
                            @endif
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-12">
                        <div class="about-content">
                            <p class="mb-lg-40 mb-20" data-aos="fade-up" data-aos-delay="100">
                                {!! nl2br($home_text->about_content ?? '') !!}
                            </p>
                            @if ($userBs->cv)
                                <a href="{{ asset('assets/front/img/user/cv/' . $userBs->cv) }}" class="theme-btn"
                                    data-aos="fade-up" data-aos-delay="300" target="_blank"
                                    download="{{ getUser()->username }}.pdf">
                                    <span>{{ $keywords['Dow'] ?? 'Dow' }}</span>
                                    {{ $keywords['nload My CV'] ?? 'nload My CV' }}
                                    <i class="fal fa-arrow-right"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- ========= End About Area section ========= -->

        <!-- ========= service Area section ========= -->
        @if (is_array($userPermissions) && in_array('Service', $userPermissions))
            <section class="service-area pt-lg-120 pt-50 pb-50">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-xl-6 col-lg-10" data-aos="fade-up" data-aos-delay="200">
                            <h2 class="text-center mb-lg-40 mb-30">{{ $home_text->service_title ?? $keywords['Services'] }}
                            </h2>
                        </div>
                    </div>

                    <div class="slider-area" data-aos="fade-up" data-aos-delay="300">
                        <!-- Slider main container -->
                        <div class="swiper default-slider pb-60" id="default-slider-service" data-slidespace="0"
                            data-xsmview="1" data-smview="2" data-mdview="3" data-lgview="4" data-xlview="4">
                            <!-- Additional required wrapper -->
                            <div class="swiper-wrapper">
                                @foreach ($services as $service)
                                    <div class="swiper-slide">
                                        <div class="service-card"
                                            @if ($service->detail_page == 1) onclick="window.location.href='{{ route('front.user.service.detail', [getParam(), 'slug' => $service->slug, 'id' => $service->id]) }}'" @endif>
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
        <!-- ========= service Area section ========= -->


        @include('user.profile1.theme9.include.experience')

        <!-- Project Tab Start -->
        @if (is_array($userPermissions) && in_array('Portfolio', $userPermissions))
            <section class="project-tab-area pb-lg-70 pb-50">
                <div class="container">
                    <div class="row">
                        <div class="col-12">
                            <div class="section-title">
                                <h2 class="mb-40 title" data-aos="fade-up" data-aos-delay="100">
                                    {{ !empty($home_text->portfolio_title) ? $home_text->portfolio_title : $keywords['Portfolios'] }}
                                </h2>
                                <!-- tabs-navigation -->
                                <div class="tabs-navigation tabs-navigation-2 text-center" data-aos="fade-up"
                                    data-aos-delay="200">
                                    <ul class="nav nav-tabs gap-3" data-hover="fancyHover">
                                        <li class="nav-item active">
                                            <button class="nav-link hover-effect active" data-bs-toggle="tab"
                                                data-bs-target="#allproject"
                                                type="button">{{ $keywords['All'] ?? __('All') }}</button>
                                        </li>
                                        @foreach ($portfolio_categories as $portfolio_category)
                                            <li class="nav-item">
                                                <button class="nav-link hover-effect" data-bs-toggle="tab"
                                                    data-bs-target="#cat-{{ $portfolio_category->id }}"
                                                    type="button">{{ $portfolio_category->name }}</button>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-content" data-aos="fade-up" data-aos-delay="200">
                        <div class="tab-pane fade show active" id="allproject">
                            <div class="row project-card-row ">
                                @foreach ($portfolios as $portfolio)
                                    <div class="project-card-item col-lg-6 col-md-6"
                                        onclick="window.location.href='{{ route('front.user.portfolio.detail', [getParam(), $portfolio->slug, $portfolio->id]) }}'">
                                        <div class="project-card mb-lg-50 mb-30">
                                            <div class="project-image lazy-container ratio ratio-1-1">
                                                <img class="blur-up lazyload" src="assets/images/placeholder.png"
                                                    data-src="{{ asset('assets/front/img/user/portfolios/' . $portfolio->image) }}"
                                                    alt="image">
                                                <a href="{{ route('front.user.portfolio.detail', [getParam(), $portfolio->slug, $portfolio->id]) }}"
                                                    target="_blank" class="link">
                                                    <i class="fal fa-arrow-right"></i>
                                                </a>
                                            </div>
                                            <div class="project-info">
                                                <div class="vericaltext">
                                                    <h4 class="mb-0 location">
                                                        {{ strlen($portfolio->title) > 25 ? mb_substr($portfolio->title, 0, 25, 'UTF-8') . '...' : $portfolio->title }}
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @foreach ($portfolio_categories as $portfolio_category)
                            @php
                                $categoryPortfolios =
                                    $user
                                        ->portfolios()
                                        ->where('language_id', $userCurrentLang->id)
                                        ->where('featured', 1)
                                        ->where('category_id', $portfolio_category->id)
                                        ->orderBy('serial_number', 'ASC')
                                        ->get() ?? collect([]);
                            @endphp
                            <div class="tab-pane fade" id="cat-{{ $portfolio_category->id }}">
                                <div class="row project-card-row ">
                                    @foreach ($categoryPortfolios as $categoryportfolio)
                                        <div class="project-card-item col-lg-6 col-md-6"
                                            onclick="window.location.href='{{ route('front.user.portfolio.detail', [getParam(), $categoryportfolio->slug, $categoryportfolio->id]) }}'">
                                            <div class="project-card mb-lg-50 mb-30">
                                                <div class="project-image lazy-container ratio ratio-1-1">
                                                    <img class="blur-up lazyload" src="assets/images/placeholder.png"
                                                        data-src="{{ asset('assets/front/img/user/portfolios/' . $categoryportfolio->image) }}"
                                                        alt="image">
                                                    <a href="{{ route('front.user.portfolio.detail', [getParam(), $categoryportfolio->slug, $categoryportfolio->id]) }}"
                                                        target="_blank" class="link">
                                                        <i class="fal fa-arrow-right"></i>
                                                    </a>
                                                </div>
                                                <div class="project-info">
                                                    <div class="vericaltext">
                                                        <h4 class="mb-0 location">
                                                            {{ strlen($categoryportfolio->title) > 25 ? mb_substr($categoryportfolio->title, 0, 25, 'UTF-8') . '...' : $categoryportfolio->title }}
                                                        </h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>
            </section>
        @endif
        <!-- Project Tab End -->

        <!-- Counter Area Start -->
        @if (is_array($userPermissions) && in_array('Achievements', $userPermissions))
            <section class="counter-area bg-cover bg-img" data-aos="fade-up" data-aos-delay="200"
                data-bg-image="{{ asset('assets/front/theme9/img/default-counter-bg.jpg') }}">
                <div class="container">
                    @if (count($achievements) == 0)
                        <h4 class="text-center">{{ $keywords['No Achievements Found'] ?? 'No Achievements Found' }}</h4>
                    @else
                        <div class="counter-wrapper">
                            @foreach ($achievements as $achievement)
                                <div class="counter-item">
                                    <div class="icon">
                                        <img class="blur-up lazyload" src="assets/images/placeholder.svg"
                                            data-src="{{ asset('assets/user/images/achievement/' . $achievement->image) }}"
                                            alt="icon-1">
                                    </div>
                                    <div class="counter-number-wrap">
                                        <h3 class="odometer counter-number" data-count="{{ $achievement->count }}">
                                            {{ $achievement->count }}</h3>
                                        <h3 class="counter-number">{{ $achievement->symbol }}</h3>
                                    </div>
                                    <p class="fw-semibold mb-0">{{ $achievement->title }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        @endif
        <!-- Counter Area End -->

        <!-- testimonial-area Start -->
        @if (is_array($userPermissions) && in_array('Testimonial', $userPermissions))
            <section class="testimonial-area pt-lg-120 pt-50 pb-lg-120 pb-50" data-aos="fade-up" data-aos-delay="100">
                <div class="container-fluid px-0">
                    <div class="row fluid-left">
                        <div class="col-lg-4">
                            <h2 class="mb-lg-70 mb-40">
                                {{ @$home_text->testimonial_title ?? 'What Say My Beloved Client’s About Me' }}
                            </h2>
                            <div class="swiper testimonialtext">
                                <div class="swiper-wrapper">
                                    @foreach ($testimonials as $testimonial)
                                        <div class="swiper-slide">
                                            <p class="mb-lg-50 mb-30">
                                                {!! nl2br($testimonial->content) !!}
                                            </p>
                                            <h4>{{ $testimonial->name }}</h4>
                                            @if (!empty($testimonial->occupation))
                                                <span class="fw-semibold small">{{ $testimonial->occupation }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                <div class="swiper-pagination"></div>
                            </div>
                        </div>
                        @if (count($testimonials) > 0)
                            <div class="col-lg-8">
                                <div thumbsSlider="" class="swiper testimonialthumb">
                                    <div class="swiper-wrapper">
                                        @foreach ($testimonials as $testimonial)
                                            <div class="swiper-slide">
                                                <img class="lazy"
                                                    data-src="{{ asset('assets/front/img/user/testimonials/' . $testimonial->image) }}"
                                                    alt="Image">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @else
                            <h4>{{ $keywords['No Testimonial Found'] ?? 'No Testimonial Found' }}</h4>
                        @endif
                    </div>
                </div>
            </section>
        @endif
        <!-- testimonial-area End -->

        <!-- ==== Cta area Start ==== -->
        <section class="section-cta pb-lg-120 pb-50" data-aos="fade-up" data-aos-delay="200">
            <div class="container">
                <div class="cta-area bg-cover bg-img"
                    data-bg-image="{{ $home_text->call_to_action_bg_image ? asset('assets/front/img/user/home_settings/' . $home_text->call_to_action_bg_image) : asset('assets/admin/img/noimage.jpg') }}">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="content">
                                <h2 class="mb-lg-40 mb-30">
                                    {{ @$home_text->call_to_action_title ?? 'Start Your Art Carrier With Artifo' }}</h2>
                                @if (!is_null(@$home_text->call_to_action_button_name) && !is_null(@$home_text->call_to_action_button_url))
                                    <a href="{{ @$home_text->call_to_action_button_url }}" class="theme-btn">
                                        {!! @$home_text->call_to_action_button_name !!}
                                        <i class="fal fa-arrow-right"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="image">
                                <img class="blur-up lazyload" src="assets/images/placeholder.svg"
                                    data-src="{{ $home_text->call_to_action_image ? asset('assets/front/img/user/home_settings/' . $home_text->call_to_action_image) : asset('assets/admin/img/noimage.jpg') }}"
                                    alt="image">
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
        <!-- ==== Cta area End ==== -->

    </div><!-- End pages -->

@endsection
