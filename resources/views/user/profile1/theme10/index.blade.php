@extends('user.profile1.theme10.layout')

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
            data-bg-image="{{ isset($home_text->hero_background_image)
                ? asset('assets/front/img/user/home_settings/' . $home_text->hero_background_image)
                : asset('assets/images/hero/default-bg.jpg') }}">
            <div class="container">
                <div class="row align-items-center">
                    <!-- Left Content -->
                    <div class="col-lg-6">
                        <div class="content mb-50">
                            <h1 class="title mb-30" data-aos="fade-up" data-aos-delay="100">
                                {{ @$home_text->hero_title }}
                            </h1>

                            <p class="desc font-lg mb-lg-50 mb-30" data-aos="fade-up" data-aos-delay="200">
                                {{ $home_text->designation ?? 'Specialist in Cardiology. Providing care and consultation for your heart health.' }}
                            </p>

                            @if (!is_null(@$home_text->hero_button_name) && !is_null(@$home_text->hero_button_url))
                                <div class="d-flex gap-30 flex-wrap align-items-center" data-aos="fade-up"
                                    data-aos-delay="300">
                                    <a href="{{ @$home_text->hero_button_url }}" class="btn btn-md btn-primary radius-30">
                                        {{ @$home_text->hero_button_name }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Right Image -->
                    <div class="col-lg-6" data-aos="fade-left" data-aos-delay="200">
                        <div class="hero-image position-relative">
                            <img class="blur-up lazyload" src="{{ asset('assets/images/placeholder.svg') }}"
                                data-src="{{ isset($home_text->hero_image)
                                    ? asset('assets/front/img/user/home_settings/' . $home_text->hero_image)
                                    : asset('assets/images/hero/hero-img.png') }}"
                                alt="hero-img">

                            <!-- Optional Decorative Shapes -->
                            <div class="shape-1">
                                <img class="blur-up lazyload" src="{{ asset('assets/images/placeholder.svg') }}"
                                    data-src="{{ asset('assets/front/theme10/images/hero-img-shape-1.png') }}"
                                    alt="shape">
                                <span>{{ $home_text->hero_rating_text ?? '5 Star Rating' }}</span>
                            </div>
                            <div class="shape-2">
                                <img class="blur-up lazyload" src="{{ asset('assets/images/placeholder.svg') }}"
                                    data-src="{{ asset('assets/front/theme10/images/hero-img-shape-2.png') }}"
                                    alt="shape">
                                <span>{{ $home_text->hero_experience_text ?? '05 Years Experience' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========= END HERO section ========= -->

        <!-- ========= Start services section ========= -->
        @if (is_array($userPermissions) && in_array('Service', $userPermissions))
            <section class="category-area pt-lg-120 pt-60 pb-lg-70 pb-30">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-xxl-5 col-lg-10">
                            <div class="text-center" data-aos="fade-up" data-aos-delay="100">
                                <p class="text-primary fw-semibold mb-10">{{ $home_text->service_title ?? 'My Treatment' }}
                                </p>
                                <h2 class="text-center mb-lg-40 mb-30">
                                    {{ $home_text->service_subtitle ?? 'Which Treatment I Provide To The Patient' }}
                                </h2>
                            </div>
                        </div>
                    </div>

                    <div class="slider-area" data-aos="fade-up" data-aos-delay="300">
                        <!-- Slider main container -->
                        <div class="swiper default-slider pb-60" id="default-slider-category" data-slidespace="30"
                            data-xsmview="1" data-smview="2" data-mdview="4" data-lgview="5" data-xlview="6">
                            <!-- Additional required wrapper -->
                            <div class="swiper-wrapper">
                                @foreach ($services as $service)
                                    <div class="swiper-slide">
                                        <div class="category-card"
                                            @if ($service->detail_page == 1) onclick="window.location.href='{{ route('front.user.service.detail', [getParam(), 'slug' => $service->slug, 'id' => $service->id]) }}'" @endif>
                                            <div class="card_img">
                                                <img class="lazyload" src="assets/images/placeholder.svg"
                                                    data-src="{{ isset($service->image) ? asset('assets/front/img/user/services/' . $service->image) : asset('assets/front/img/profile/service-1.jpg') }}"
                                                    alt="image">
                                            </div>
                                            <div class="content">
                                                <h6 class="mb-1 fw-semibold lc-1">
                                                    <a
                                                        @if ($service->detail_page == 1) href="{{ route('front.user.service.detail', [getParam(), 'slug' => $service->slug, 'id' => $service->id]) }}" @endif>
                                                        {{ strlen($service->name) > 30 ? mb_substr($service->name, 0, 30, 'UTF-8') . '...' : $service->name }}
                                                    </a>
                                                </h6>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <!--  pagination -->
                            <div class="swiper-pagination" id="default-slider-category-pagination"></div>
                        </div>
                    </div>
                </div>
            </section>
        @endif
        <!-- ========= end services section ========= -->

        <!-- ========= Start About Area section ========= -->
        <section class="about-area pt-lg-70 pb-lg-120 pb-50">
            <div class="container">
                <div class="row about-area-row">
                    <div class="col-xl-6 col-lg-12">
                        <div class="about-image">
                            <!-- size w900 h510 -->
                            @if (!empty($home_text->about_image))
                                <img data-src="{{ asset('assets/front/img/user/home_settings/' . $home_text->about_image) }}"
                                    class="lazyload blur-up" alt="About Image">
                            @endif
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-12">
                        <div class="about-content">
                            <p class="text-primary fw-semibold mb-10" data-aos="fade-up" data-aos-delay="100">
                                {{ $home_text->about_title ?? 'About Me' }}
                            </p>
                            <h2 class="mb-30" data-aos="fade-up" data-aos-delay="200">
                                {{ @$home_text->about_subtitle }}
                            </h2>
                            <p class="mb-lg-40 mb-20" data-aos="fade-up" data-aos-delay="300">
                                {!! nl2br($home_text->about_content ?? '') !!}
                            </p>

                            <div class="btn-group" data-aos="fade-up" data-aos-delay="400">
                                <div>
                                    @if (!is_null(@$home_text->about_button_name) && !is_null(@$home_text->about_button_url))
                                        <a href="{{ @$home_text->about_button_url }}"
                                            class="btn btn-md btn-primary radius-30" data-aos="fade-up"
                                            data-aos-delay="300">
                                            {{ @$home_text->about_button_name }}
                                        </a>
                                    @endif
                                </div>
                                @if (!is_null(@$home_text->about_video_url) && !is_null(@$home_text->about_video_text))
                                    <a class="youtube-popup video_popup_btn" href=" {{ @$home_text->about_video_url }}">
                                        <div class="video-btn">
                                            <i class="fa fa-play"></i>
                                        </div>
                                        <span> {{ @$home_text->about_video_text }}</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- ========= End About Area section ========= -->


        @include('user.profile1.theme10.include.experience')

        <!-- ========= Start Pricing section ========= -->
        @if (is_array($userPermissions) && in_array('Appointment', $userPermissions))
            <section class="pricing-area pt-lg-70 pt-50 pb-50 pb-lg-40 pb-20">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-xxl-5 col-lg-10">
                            <div class="text-center" data-aos="fade-up" data-aos-delay="100">
                                <p class="text-primary fw-semibold mb-10">
                                    {{ $home_text->appointment_title ?? 'Appointment' }}
                                </p>
                                <h2 class="text-center mb-lg-40 mb-30">{{ @$home_text->appointment_subtitle }}</h2>
                            </div>
                        </div>
                    </div>

                    <div class="row justify-content-center">
                        @if ($userBs->appointment_category == 1)
                            @foreach ($categories as $category)
                                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                                    <div class="pricing-card mb-30">
                                        <div class="pricing-card-header">
                                            <h3 class="mb-2">{{ $category->name }} </h3>
                                            <div class="price">
                                                <span
                                                    class="new-price fs-4 fw-bold">{{ $userBs->base_currency_symbol_position == 'left' ? $userBs->base_currency_symbol : '' }}{{ $category->appointment_price }}</span>
                                                <span
                                                    class="old-price">{{ $userBs->base_currency_symbol_position == 'right' ? $userBs->base_currency_symbol : '' }}</span>
                                            </div>
                                            <div class="icon">
                                                <img class="blur-up lazyload" src="assets/images/placeholder.svg"
                                                    data-src="{{ asset('assets/user/img/category') . '/' . $category->image }}"
                                                    alt="icon">
                                            </div>
                                        </div>


                                        <div class="pricing-card-footer mt-30">
                                            <a href="{{ route('front.user.appointment.form', ['cat' => $category->id, getParam()]) }}"
                                                class="btn btn-md btn-primary radius-30">{{ $keywords['Book_Now'] ?? 'Book Now' }}</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                                <div class="pricing-card mb-30">
                                    <div class="pricing-card-header">
                                        <form action="{{ route('front.user.appointment.booking.step1', getParam()) }}"
                                            enctype="multipart/form-data" method="POST">
                                            @csrf
                                            <input type="hidden" name="category_id" value="{{ $cat ?? null }}">
                                            <div class="form-group mb-10">
                                                <label for="name"
                                                    class="mb-1">{{ $keywords['Name'] ?? __('Name') }}</label>
                                                <input class="form-control" id="name" type="text" name="name"
                                                    value="{{ old('name') }}"
                                                    value="{{ Auth::guard('customer')->user()->username ?? '' }}"
                                                    placeholder="{{ $keywords['Name'] ?? __('Name') }}">
                                                @error('name')
                                                    <p class="text-danger">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div class="form-group mb-10">
                                                <label for="email"
                                                    class="mb-1">{{ $keywords['Email'] ?? __('Email') }}</label>
                                                <input class="form-control" id="email" type="email" name="email"
                                                    value="{{ old('email') }}"
                                                    value="{{ Auth::guard('customer')->user()->email ?? '' }}"
                                                    placeholder="{{ $keywords['Email'] ?? __('Email') }}">
                                                @error('email')
                                                    <p class="text-danger">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            @include('user.profile1.theme10.inputs')
                                            <div class="form-group pt-3">
                                                <button class="btn btn-md btn-primary radius-30 w-100"
                                                    type="submit">{{ $keywords['Next'] ?? 'Next' }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        @endif
        <!-- ========= End Pricing section ========= -->

        <!-- ========= Start choose us section ========= -->
        @if (is_array($userPermissions) && in_array('Features', $userPermissions))
        <section class="choose-area pt-lg-70 pt-50 pb-50 pb-lg-40 pb-20">
            <div class="container">
                <div class="row">
                    <div class="col-xl-6 col-lg-10">
                        <div data-aos="fade-up" data-aos-delay="100">
                            <p class="text-primary fw-semibold mb-10">{{ $home_text->features_title ?? 'Why Chose Me' }}
                            </p>
                            <h2 class="mb-lg-60 mb-30">
                                {{ @$home_text->features_subtitle }}
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-6 col-lg-12" data-aos="fade-up" data-aos-delay="200">
                        <div class="choose-card-2 mb-30">
                            <div class="content">
                                <h4 class="mb-sm-30 mb-20 fw-semibold">
                                    {{ @$home_text->features_image_title }}</h4>
                                @if (!is_null(@$home_text->features_button_name) && !empty(@$home_text->features_button_url))
                                    <a href="{{ @$home_text->features_button_url }}"
                                        class="btn-link">{{ @$home_text->features_button_name }}</a>
                                @endif
                            </div>

                            <div class="image text-center">
                                <img class="blur-up lazyload" src="assets/images/placeholder.svg"
                                    data-src="{{ $home_text->features_image ? asset('assets/front/img/user/home_settings/' . $home_text->features_image) : asset('assets/admin/img/noimage.jpg') }}"
                                    alt="image">
                            </div>

                            <div class="vactor">
                                <img class="vactor-1 blur-up lazyload" src="assets/images/placeholder.svg"
                                    data-src="{{ asset('assets/front/theme10/images/vactor-1.png') }}" alt="image">
                            </div>

                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-12" data-aos="fade-up" data-aos-delay="400">
                        <div class="row choose-card-row">
                            @if (count($features) == 0)
                                <h3 class="text-center">{{ __('NO FEATURE FOUND') }}</h3>
                            @else
                                @foreach ($features as $key => $feature)
                                    <div class="col-sm-6">
                                        <div class="choose-card mb-30">
                                            <div class="icon">
                                                <img class="blur-up lazyload" src="assets/images/placeholder.svg"
                                                    data-src="{{ asset('assets/user/features/' . $feature->image) }}"
                                                    alt="icon">
                                            </div>
                                            <h6 class="mb-18 fw-semibold lc-2">
                                                {{ $feature->title }}
                                            </h6>
                                            <p class="mb-0 lc-3"> {{ $feature->subtitle }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @endif
        <!-- ========= End choose us section ========= -->


        <!-- testimonial-area Start -->
        @if (is_array($userPermissions) && in_array('Testimonial', $userPermissions))
            <section class="testimonial-area" data-aos="fade-up" data-aos-delay="100">
                <div class="container">
                    <div class="testimonial-item-wrapper">
                        <div class="testimonial-heading">
                            <div class="text-center">
                                <p class="text-primary mb-10">
                                    {{ @$home_text->testimonial_title ?? 'Testimonial' }}
                                </p>
                                <h2 class="mb-40">
                                    {{ @$home_text->testimonial_subtitle }}
                                </h2>
                            </div>
                        </div>

                        <div class="swiper testimonial-slider">
                            @if (count($testimonials) > 0)
                                <div class="swiper-wrapper">
                                    @foreach ($testimonials as $testimonial)
                                        <div class="swiper-slide">
                                            <div class="testimonial-item">
                                                <p class="testimonial-desc lc-5 mb-1">
                                                    {!! nl2br($testimonial->content) !!}
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="swiper-pagination"></div>
                            @else
                                <h4>{{ $keywords['No Testimonial Found'] ?? 'No Testimonial Found' }}</h4>
                            @endif
                        </div>

                        <div class="round-1"></div>
                        <div class="round-2">
                            <div class="user-images">
                                @foreach ($testimonials->take(1) as $testimonial)
                                    <img class="user-5 blur-up lazyload" src="assets/images/placeholder.svg"
                                        data-src="{{ asset('assets/front/img/user/testimonials/' . $testimonial->image) }}"
                                        alt="user">
                                @endforeach
                            </div>
                        </div>
                        <div class="round-3">
                            <div class="user-images">
                                @foreach ($testimonials->skip(1)->take(2) as $key => $testimonial)
                                    <img class="user-{{ $key + 5 }} blur-up lazyload"
                                        src="assets/images/placeholder.svg"
                                        data-src="{{ asset('assets/front/img/user/testimonials/' . $testimonial->image) }}"
                                        alt="user">
                                @endforeach
                            </div>
                        </div>

                        <div class="user-images">
                            @foreach ($testimonials->skip(3)->take(4) as $key => $testimonial)
                                <img class="user-{{ $key - 2 }} blur-up lazyload"
                                    src="assets/images/placeholder.svg"
                                    data-src="{{ asset('assets/front/img/user/testimonials/' . $testimonial->image) }}"
                                    alt="user">
                            @endforeach
                        </div>

                    </div>
                </div>
                <div class="vactor">
                    <span class="vactor-1">
                        <img class="blur-up lazyload" src="assets/images/placeholder.svg"
                            data-src="{{ asset('assets/front/theme10/images/testimonial/vactor/vactor-1.png') }}"
                            alt="vactor">
                    </span>
                    <span class="vactor-2">
                        <img class="blur-up lazyload" src="assets/images/placeholder.svg"
                            data-src="{{ asset('assets/front/theme10/images/testimonial/vactor/vactor-2.png') }}"
                            alt="vactor">
                    </span>
                </div>
            </section>
        @endif
        <!-- testimonial-area End -->


        <!-- ========= Start Blog Section ========= -->
        @if (is_array($userPermissions) && in_array('Blog', $userPermissions))
            <section class="section-blog pt-lg-100 pt-50 pb-lg-70 pb-40">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-12" data-aos="fade-up" data-aos-delay="100">
                            <div class="section-title section-title-inline mb-40">
                                <div>
                                    <p class="text-primary mb-10">{{ $home_text->blog_title ?? 'Blog' }}</p>
                                    <h2 class="mb-0" data-aos="fade-up">{{ @$home_text->blog_subtitle }}</h2>
                                </div>
                                @if (count($blogs) > 0)
                                    <a href="{{ route('front.user.blogs', getParam()) }}"
                                        class="btn btn-primary radius-30">{{ $keywords['Read_All_Posts'] ?? 'Read All Post' }}</a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row gx-lg-5" data-aos="fade-up" data-aos-delay="300">
                        @if (count($blogs) > 0)
                            <div class="col-lg-5">
                                @php
                                    $mainBlog = $blogs->first();
                                    $remainingBlogs = $blogs->skip(1);
                                @endphp
                                <!-- blog-card  -->
                                @if ($mainBlog)
                                    <article class="blog-card radius-md">
                                        <figure class="blog-image">
                                            <a href="{{ route('front.user.blog.detail', [getParam(), $mainBlog->slug, $mainBlog->id]) }}"
                                                class="lazy-container radius-md ratio ratio-5-3">
                                                <img src="assets/images/placeholder.svg"
                                                    data-src="{{ asset('assets/front/img/user/blogs/' . $mainBlog->image) }}"
                                                    alt="product">
                                            </a>
                                        </figure>
                                        <div class="blog-content">
                                            <h4 class="title fw-semibold lc-2">
                                                <a
                                                    href="{{ route('front.user.blog.detail', [getParam(), $mainBlog->slug, $mainBlog->id]) }}">
                                                    {{ strlen($mainBlog->title) > 45 ? mb_substr($mainBlog->title, 0, 45, 'UTF-8') . '...' : $mainBlog->title }}
                                                </a>
                                            </h4>
                                            <p class="card-text lc-3">
                                                {!! substr($mainBlog->content, 0, 120) !!}
                                            </p>
                                            <a href="{{ route('front.user.blog.detail', [getParam(), $mainBlog->slug, $mainBlog->id]) }}"
                                                class="btn-link">
                                                {{ $keywords['Read_More'] ?? 'Read More' }}</a>
                                        </div>
                                    </article>
                                @endif
                            </div>

                            @if ($remainingBlogs->isNotEmpty())
                                <div class="col-lg-7">
                                    <div class="d-flex flex-column gap-30">
                                        @foreach ($remainingBlogs as $blog)
                                            <article class="blog-card blog-card-row radius-md">
                                                <div class="blog-content">
                                                    <h4 class="title fw-semibold lc-2">
                                                        <a
                                                            href="{{ route('front.user.blog.detail', [getParam(), $blog->slug, $blog->id]) }}">
                                                            {{ strlen($blog->title) > 45 ? mb_substr($blog->title, 0, 45, 'UTF-8') . '...' : $blog->title }}
                                                        </a>
                                                    </h4>
                                                    <p class="card-text lc-3">
                                                        {!! substr($blog->content, 0, 120) !!}
                                                    </p>
                                                    <a href="{{ route('front.user.blog.detail', [getParam(), $blog->slug, $blog->id]) }}"
                                                        class="btn-link">
                                                        {{ $keywords['Read_More'] ?? 'Read More' }}
                                                    </a>
                                                </div>
                                                <figure class="blog-image">
                                                    <a href="{{ route('front.user.blog.detail', [getParam(), $blog->slug, $mainBlog->id]) }}"
                                                        class="lazy-container radius-md ratio ratio-5-3">
                                                        <img src="assets/images/placeholder.svg"
                                                            data-src="{{ asset('assets/front/img/user/blogs/' . $blog->image) }}"
                                                            alt="product">
                                                    </a>
                                                </figure>
                                            </article>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @else
                            <h4 class="text-center">{{ $keywords['No_Blog_Found'] ?? 'No Blog Found' }}</h4>
                        @endif
                    </div>
                </div>
            </section>
        @endif
        <!-- ========= End Blog Section ========= -->

    </div><!-- End pages -->

@endsection
