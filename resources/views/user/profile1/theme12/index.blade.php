     @extends('user.profile1.theme12.layout')

     @section('tab-title')
         {{ $keywords['Home'] ?? 'Home' }}
     @endsection

     @section('meta-description', !empty($userSeo) ? $userSeo->home_meta_description : '')
     @section('meta-keywords', !empty($userSeo) ? $userSeo->home_meta_keywords : '')

     @section('content')
         <!-- ======= START HERO section ========= -->
         <section class="hero-area bg-cover bg-img" data-bg-image="assets/images/hero/hero-bg.png">
             <div class="container">
                 <div class="row align-items-center">
                     <div class="col-lg-6">
                         <div class="content mb-50">
                             <h1 class="title  mb-30" data-aos="fade-up" data-aos-delay="100">
                                 {{ @$home_text->hero_section_title }}
                             </h1>
                             <p class="desc font-lg mb-lg-50 mb-30" data-aos="fade-up" data-aos-delay="200">
                                 {{ @$home_text->hero_section_subtitle }}
                             </p>
                             @if (!is_null(@$home_text->hero_button_name) && !is_null(@$home_text->hero_button_url))
                                 <div class="d-flex gap-30 flex-wrap align-items-center" data-aos="fade-up"
                                     data-aos-delay="300">
                                     <a href="{{ @$home_text->hero_button_url }}" class="btn btn-md thm-btn radius-md">
                                         {{ @$home_text->hero_button_name }}
                                         <i class="fa-solid fa-arrow-right-long"></i>
                                     </a>

                                 </div>
                             @endif
                         </div>
                     </div>
                     <div class="col-lg-6" data-aos="fade-left" data-aos-delay="200">
                         <div class="hero-image">
                             <img src="{{ $home_text->hero_image ? asset('assets/front/img/user/home_settings/' . $home_text->hero_image) : asset('assets/admin/img/noimage.jpg') }}"
                                 alt="hero-img">
                             <div class="shape">
                                 <div class="shape-1">
                                     <img class="blur-up lazyload"
                                         src="{{ asset('assets/front/theme12/img') }}/shape/hero-img-shape-1.png"
                                         alt="shape">
                                 </div>
                                 <div class="shape-2">
                                     <img class="blur-up lazyload"
                                         src="{{ asset('assets/front/theme12/img') }}/shape/hero-img-shape-2.png"
                                         alt="shape">
                                     <div class="content">
                                         <div class="counter-number mb-10">
                                             <h3 class="mb-0 fw-bold odometer"
                                                 data-count="{{ $home_text->hero_experience_text ?? 0 }}">
                                                 {{ $home_text->hero_experience_text ?? 0 }}</h3>
                                             <h4 class="mb-1 fw-bold"><i class="fas fa-plus"></i></h4>
                                         </div>
                                         <p>{{ $home_text->hero_rating_text ?? 'Successfully Case' }}</p>
                                     </div>
                                 </div>

                             </div>
                         </div>
                     </div>
                 </div>
             </div>

             <div class="vactor">
                 <img class="vactor-1 blur-up lazyload" src="{{ asset('assets/front/theme12/img') }}/shape/shape-1.png"
                     alt="shape">
                 <img class="vactor-2 blur-up lazyload" src="{{ asset('assets/front/theme12/img') }}/shape/shape-2.png"
                     alt="shape">
                 <img class="vactor-3 blur-up lazyload" src="{{ asset('assets/front/theme12/img') }}/shape/shape-3.png"
                     alt="shape">
                 <img class="vactor-4 blur-up lazyload" src="{{ asset('assets/front/theme12/img') }}/shape/shape-4.png"
                     alt="shape">
                 <img class="vactor-5 blur-up lazyload" src="{{ asset('assets/front/theme12/img') }}/shape/shape-5.png"
                     alt="shape">
                 <img class="vactor-6 blur-up lazyload" src="{{ asset('assets/front/theme12/img') }}/shape/shape-6.png"
                     alt="shape">
                 <img class="vactor-7 blur-up lazyload" src="{{ asset('assets/front/theme12/img') }}/shape/shape-7.png"
                     alt="shape">
                 <img class="vactor-8 blur-up lazyload" src="{{ asset('assets/front/theme12/img') }}/shape/shape-8.png"
                     alt="shape">
             </div>
         </section>
         <!-- ========= END HERO section ========= -->

         <!-- ========= Start Category section ========= -->
         @if (is_array($userPermissions) && in_array('Service', $userPermissions))
             <section class="category-area pt-lg-120 pt-60 pb-lg-70 pb-30">
                 <div class="container">
                     <div class="row justify-content-center">
                         <div class="col-xxl-4 col-lg-10">
                             <div class="text-center" data-aos="fade-up" data-aos-delay="100">
                                 <p class="text-primary mb-10"> {{ @$home_text->service_title ?? 'My Services' }}</p>
                                 <h2 class="text-center mb-30"> {{ @$home_text->service_subtitle }}</h2>
                             </div>
                         </div>
                     </div>

                     <div class="slider-area" data-aos="fade-up" data-aos-delay="300">
                         <!-- Slider main container -->
                         <div class="swiper default-slider pb-60" id="default-slider-category" data-slidespace="30"
                             data-xsmview="1" data-smview="2" data-mdview="2" data-lgview="2" data-xlview="4">
                             <!-- Additional required wrapper -->
                             <div class="swiper-wrapper">
                                 @foreach ($services as $service)
                                     <div class="swiper-slide">
                                         <div class="category-card"
                                             @if ($service->detail_page == 1) onclick="window.location.href='{{ route('front.user.service.detail', [getParam(), 'slug' => $service->slug, 'id' => $service->id]) }}'" @endif>
                                             <div class="card_img">
                                                 <div class="lazy-container ratio ratio-1-2">
                                                     <img class="lazyload" src="assets/images/placeholder.svg"
                                                         data-src="{{ isset($service->image) ? asset('assets/front/img/user/services/' . $service->image) : asset('assets/front/img/profile/service-1.jpg') }}"
                                                         alt="">
                                                 </div>
                                             </div>
                                             <div class="content">
                                                 <h4 class="mb-20 text-white lc-1">
                                                     {{ strlen($service->name) > 30 ? mb_substr($service->name, 0, 30, 'UTF-8') . '...' : $service->name }}
                                                 </h4>

                                                 @if ($service->detail_page == 1)
                                                     <a href="{{ route('front.user.service.detail', [getParam(), 'slug' => $service->slug, 'id' => $service->id]) }}"
                                                         class="btn mb-20 thm-btn-outline">{{ $keywords['Read_More'] ?? 'Read More' }}</a>
                                                 @endif
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

                 <div class="vactor">
                     <img class="vactor-1 blur-up lazyload"
                         src="{{ asset('assets/front/theme12/img') }}/category/vactor/vactor-1.png" alt="vactor">
                     <img class="vactor-2 blur-up lazyload"
                         src="{{ asset('assets/front/theme12/img') }}/category/vactor/vactor-2.png" alt="vactor">
                     <img class="vactor-3 blur-up lazyload"
                         src="{{ asset('assets/front/theme12/img') }}/category/vactor/vactor-3.png" alt="vactor">
                     <img class="vactor-4 blur-up lazyload"
                         src="{{ asset('assets/front/theme12/img') }}/category/vactor/vactor-4.png" alt="vactor">
                 </div>
             </section>
         @endif
         <!-- ========= Start Category section ========= -->

         <!-- ========= Start About Area section ========= -->
         <section class="about-area pt-lg-70 pb-lg-120 pb-50">
             <div class="container">
                 <div class="row">
                     <div class="col-lg-6">
                         <div class="about-image" data-aos="fade-right" data-aos-delay="100">
                             <!-- size w900 h510 -->
                             <img class="lazyload blur-up" src="assets/images/placeholder.svg"
                                 data-src="{{ $home_text->about_image ? asset('assets/front/img/user/home_settings/' . $home_text->about_image) : asset('assets/admin/img/noimage.jpg') }}"
                                 alt="image">
                         </div>
                     </div>
                     <div class="col-lg-6">
                         <div class="content">
                             <p class="text-primary mb-10" data-aos="fade-up" data-aos-delay="100">
                                 {{ @$home_text->about_title }}</p>
                             <h2 class="mb-30" data-aos="fade-up" data-aos-delay="200">
                                 {{ @$home_text->about_subtitle }}
                             </h2>
                             <p class="mb-20" data-aos="fade-up" data-aos-delay="300">
                                 {!! nl2br($home_text->about_content ?? '') !!}
                             </p>

                             <div data-aos="fade-up" data-aos-delay="400">
                                 @if (!is_null(@$home_text->about_button_name) && !is_null(@$home_text->about_button_url))
                                     <a href="{{ $home_text->about_button_url }}"
                                         class="btn btn-md thm-btn radius-sm">{{ $home_text->about_button_name }}<i
                                             class="fa-solid fa-arrow-right-long"></i></a>
                                 @endif
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
             <div class="vactor">
                 <img class="vactor-1 blur-up lazyload"
                     src="{{ asset('assets/front/theme12/img') }}/about/vactor/vactor-1.png" alt="vactor">
                 <img class="vactor-2 blur-up lazyload"
                     src="{{ asset('assets/front/theme12/img') }}/about/vactor/vactor-2.png" alt="vactor">
                 <img class="vactor-3 blur-up lazyload"
                     src="{{ asset('assets/front/theme12/img') }}/about/vactor/vactor-3.png" alt="vactor">
             </div>
         </section>
         <!-- ========= End About Area section ========= -->

         <!-- ========= Start Project tab section ========= -->
         @if (is_array($userPermissions) && in_array('Portfolio', $userPermissions))
             <section class="project-area pb-lg-90 pb-50">
                 <div class="container">
                     <div class="row justify-content-center">
                         <div class="col-12" data-aos="fade-up" data-aos-delay="100">
                             <div class="text-center">
                                 <p class="text-primary mb-10" data-aos="fade-up" data-aos-delay="100">
                                     {{ @$home_text->portfolio_title ?? 'My Case Project' }}
                                 </p>
                                 <h2 class="mb-30" data-aos="fade-up" data-aos-delay="200">
                                     {{ @$home_text->portfolio_subtitle }}
                                 </h2>
                             </div>

                             <!-- tabs-navigation -->
                             <div class="tabs-navigation tabs-navigation-v2  text-center mb-lg-40 mb-30"
                                 data-aos="fade-up" data-aos-delay="300">
                                 <ul class="nav nav-tabs gap-24" data-hover="fancyHover">
                                     @if (count($portfolio_categories) > 0)
                                         <li class="nav-item active">
                                             <button class="nav-link hover-effect active" data-bs-toggle="tab"
                                                 data-bs-target="#allproject"
                                                 type="button">{{ $keywords['All'] ?? __('All') }}</button>
                                         </li>
                                     @endif
                                     @foreach ($portfolio_categories as $portfolio_category)
                                         <li class="nav-item">
                                             <button class="nav-link hover-effect" data-bs-toggle="tab"
                                                 data-bs-target="#cat-{{ $portfolio_category->id }}" type="button">
                                                 {{ $portfolio_category->name }}
                                             </button>
                                         </li>
                                     @endforeach
                                 </ul>
                             </div>
                         </div>
                     </div>

                     <div class="tab-content" id="projectTabContent" data-aos="fade-up" data-aos-delay="400">
                         <div class="tab-pane fade show active" id="allproject" role="tabpanel">
                             <div class="row justify-content-center">
                                 @foreach ($portfolios as $portfolio)
                                     <div class="col-lg-4 col-sm-6">
                                         <div class="project-card mb-30">
                                             <figure class="project-image lazy-container ratio ratio-5-3 radius-lg">
                                                 <img class="lazyload" src="assets/images/placeholder.svg"
                                                     data-src="{{ asset('assets/front/img/user/portfolios/' . $portfolio->image) }}"
                                                     alt="project">
                                                 <div class="portfolio-project-info">
                                                     <h5 class="vericaltext title"><a href="javascript:void(0)">
                                                             {{ strlen($portfolio->title) > 20 ? mb_substr($portfolio->title, 0, 20, 'UTF-8') . '...' : $portfolio->title }}
                                                         </a>
                                                     </h5>
                                                     <span class="vericaltext"></span>
                                                 </div>
                                             </figure>
                                             <a href="{{ route('front.user.portfolio.detail', [getParam(), $portfolio->slug, $portfolio->id]) }}"
                                                 class="btn thm-btn">
                                                 {{ $keywords['Details'] ?? 'Details' }}
                                             </a>
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
                             <div class="tab-pane fade" id="cat-{{ $portfolio_category->id }}" role="tabpanel">
                                 <div class="row justify-content-center">
                                     @foreach ($categoryPortfolios as $categoryportfolio)
                                         <div class="col-lg-4 col-sm-6">
                                             <div class="project-card mb-30">
                                                 <figure class="project-image lazy-container ratio ratio-5-3 radius-lg">
                                                     <img class="lazyload" src="assets/images/placeholder.svg"
                                                         data-src="{{ asset('assets/front/img/user/portfolios/' . $categoryportfolio->image) }}"
                                                         alt="project">
                                                     <div class="portfolio-project-info">
                                                         <h5 class="vericaltext title"><a href="javascript:void(0)">
                                                                 {{ strlen($categoryportfolio->title) > 20 ? mb_substr($categoryportfolio->title, 0, 20, 'UTF-8') . '...' : $categoryportfolio->title }}
                                                             </a>
                                                         </h5>
                                                         <span class="vericaltext"></span>
                                                     </div>
                                                 </figure>
                                                 <a href="{{ route('front.user.portfolio.detail', [getParam(), $categoryportfolio->slug, $categoryportfolio->id]) }}"
                                                     class="btn thm-btn">
                                                     {{ $keywords['Details'] ?? 'Details' }}
                                                 </a>
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
         <!-- ========= Start Project tab section ========= -->


         <!-- ========= Start Experience section ========= -->
         @include('user.profile1.theme12.include.experience')
         <!-- ========= End Experience section ========= -->

         <!-- ========= Start Appointment section ========= -->
         @if (is_array($userPermissions) && in_array('Appointment', $userPermissions))
             <section class="appointment-area pb-lg-90 pb-50">
                 <div class="container">
                     <div class="row justify-content-center">
                         <div class="col-xl-5 col-lg-10" data-aos="fade-up" data-aos-delay="100">
                             <div class="text-center">
                                 <p class="text-primary mb-10" data-aos="fade-up" data-aos-delay="100">
                                     {{ $home_text->appointment_title ?? 'Appointment' }}
                                 </p>
                                 <h2 class="mb-sm-80 mb-30" data-aos="fade-up" data-aos-delay="200">
                                     {{ @$home_text->appointment_subtitle }}
                                 </h2>
                             </div>
                         </div>
                     </div>

                     <div class="row justify-content-between align-items-center">
                         <div class="col-lg-6">
                             @if (count($categories) > 0 && $userBs->appointment_category == 1)
                                 <div class="row appointment-card-row">
                                     @foreach ($categories as $category)
                                         <div class="col-xl-6 col-sm-6 appointment-card-item" data-aos="fade-up"
                                             data-aos-delay="100">
                                             <div class="appointment-card"
                                              onclick="window.location.href='{{ route('front.user.appointment.form', ['cat' => $category->id, getParam()]) }}'">
                                                 <div class="card_img">
                                                     <div class="lazy-container ratio ratio-5-4">
                                                         <img class="lazyload" src="assets/images/placeholder.svg"
                                                             data-src="{{ asset('assets/user/img/category') . '/' . $category->image }}"
                                                             alt="">
                                                     </div>
                                                 </div>
                                                 <div class="content">
                                                     <h4 class="mb-20 text-white lc-1">{{ $category->name }}</h4>
                                                     <a href="{{ route('front.user.appointment.form', ['cat' => $category->id, getParam()]) }}"
                                                         class="btn mb-20 thm-btn-outline">
                                                         {{ $keywords['Make_Appointment'] ?? 'Make Appointment' }}
                                                     </a>
                                                 </div>
                                             </div>
                                         </div>
                                     @endforeach
                                 </div>
                             @else
                                 <div class="col-lg-12 col-md-12" data-aos="fade-up" data-aos-delay="200">
                                     <div class="pricing-card mb-30">
                                         <div class="pricing-card-header">
                                             <form
                                                 action="{{ route('front.user.appointment.booking.step1', getParam()) }}"
                                                 enctype="multipart/form-data" method="POST">
                                                 @csrf
                                                 <input type="hidden" name="category_id" value="{{ $cat ?? null }}">
                                                 <div class="form-group mb-10">
                                                     <label for="name"
                                                         class="mb-1">{{ $keywords['Name'] ?? __('Name') }}</label>
                                                     <input class="form-control" id="name" type="text"
                                                         name="name" value="{{ old('name') }}"
                                                         value="{{ Auth::guard('customer')->user()->username ?? '' }}"
                                                         placeholder="{{ $keywords['Name'] ?? __('Name') }}">
                                                     @error('name')
                                                         <p class="text-danger">{{ $message }}</p>
                                                     @enderror
                                                 </div>

                                                 <div class="form-group mb-10">
                                                     <label for="email"
                                                         class="mb-1">{{ $keywords['Email'] ?? __('Email') }}</label>
                                                     <input class="form-control" id="email" type="email"
                                                         name="email" value="{{ old('email') }}"
                                                         value="{{ Auth::guard('customer')->user()->email ?? '' }}"
                                                         placeholder="{{ $keywords['Email'] ?? __('Email') }}">
                                                     @error('email')
                                                         <p class="text-danger">{{ $message }}</p>
                                                     @enderror
                                                 </div>
                                                 @include('user.profile1.theme10.inputs')
                                                 <div class="form-group pt-3">
                                                     <button class="btn btn-md btn-primary w-100"
                                                         type="submit">{{ $keywords['Next'] ?? 'Next' }}</button>
                                                 </div>
                                             </form>
                                         </div>
                                     </div>
                                 </div>
                             @endif
                         </div>

                         <div class="col-lg-6">
                             <div class="appointment-table-area">
                                 <div class="appointment-table" data-aos="fade-up" data-aos-delay="100">
                                     <h3 class="appointment-title">
                                         {{ $keywords['Business_Day_Of_Appointment'] ?? 'Business Day Of Appointment' }}
                                     </h3>
                                     <div class="table-wrap mb-lg-50 mb-30">
                                         <div class="table-responsive">

                                             <table class="table">
                                                 <thead>
                                                     <tr>
                                                         <th scope="col">
                                                             <i class="fa-solid fa-calendar-days"></i>
                                                             {{ $keywords['Day'] ?? 'Day' }}
                                                         </th>
                                                         <th scope="col">
                                                             <i class="fa-solid fa-clock"></i>
                                                             {{ $keywords['Time'] ?? 'Time' }}
                                                         </th>
                                                         <th scope="col">
                                                             <i class="fa-regular fa-house"></i>
                                                             {{ $keywords['Status'] ?? 'Status' }}
                                                         </th>
                                                     </tr>
                                                 </thead>
                                                 <tbody>
                                                     @foreach ($user_days as $user_day)
                                                         @php
                                                             $isWeekend = $user_day->weekend == 1 ? 'Closed' : 'Open';
                                                             $daySlots = $time_slots->where('day', $user_day->day);

                                                             if ($daySlots->count() > 0) {
                                                                 $minStart = $daySlots->min('start');
                                                                 $maxEnd = $daySlots->max('end');
                                                                 $timeRange = $minStart . ' - ' . $maxEnd;
                                                             } else {
                                                                 $timeRange = '----';
                                                             }
                                                         @endphp
                                                         <tr>
                                                             <td>{{ $keywords[$user_day->day] ?? $user_day->day }}</td>
                                                             <td>{{ $user_day->weekend == 1 ? '----' : $timeRange }}</td>
                                                             <td>
                                                                 <span
                                                                     class="{{ $user_day->weekend == 1 ? 'text-danger' : 'text-success' }}">{{ $keywords[$isWeekend] ?? $isWeekend }}</span>
                                                             </td>
                                                         </tr>
                                                     @endforeach
                                                 </tbody>
                                             </table>
                                         </div>

                                     </div>

                                 </div>
                             </div>
                         </div>

                     </div>
                 </div>
                 <div class="vactor">
                     <img class="vactor-1 blur-up lazyload"
                         src="{{ asset('assets/front/theme12/img') }}/appointment/vactor/vactor-1.png" alt="vactor">
                     <img class="vactor-2 blur-up lazyload"
                         src="{{ asset('assets/front/theme12/img') }}/appointment/vactor/vactor-2.png" alt="vactor">
                     <img class="vactor-3 blur-up lazyload"
                         src="{{ asset('assets/front/theme12/img') }}/appointment/vactor/vactor-3.png" alt="vactor">
                     <img class="vactor-4 blur-up lazyload"
                         src="{{ asset('assets/front/theme12/img') }}/appointment/vactor/vactor-4.png" alt="vactor">
                     <img class="vactor-5 blur-up lazyload"
                         src="{{ asset('assets/front/theme12/img') }}/appointment/vactor/vactor-5.png" alt="vactor">
                     <img class="vactor-6 blur-up lazyload"
                         src="{{ asset('assets/front/theme12/img') }}/appointment/vactor/vactor-6.png" alt="vactor">
                     <img class="vactor-7 blur-up lazyload"
                         src="{{ asset('assets/front/theme12/img') }}/appointment/vactor/vactor-7.png" alt="vactor">
                 </div>
             </section>
         @endif
         <!-- ========= End Appointment section ========= -->

         <!-- testimonial-area Start -->
         @if (is_array($userPermissions) && in_array('Testimonial', $userPermissions))
             <section class="testimonial-area  pb-lg-70 pb-50" data-aos="fade-up" data-aos-delay="100">
                 <div class="container">
                     <div class="row justify-content-center">
                         <div class="col-xl-5 col-lg-10" data-aos="fade-up" data-aos-delay="100">
                             <div class="text-center">
                                 <p class="text-primary mb-10" data-aos="fade-up" data-aos-delay="100">
                                     {{ @$home_text->testimonial_title ?? 'Testimonial' }}
                                 </p>
                                 <h2 class="mb-40" data-aos="fade-up" data-aos-delay="200">
                                     {{ @$home_text->testimonial_subtitle ?? 'Testimonial' }}
                                 </h2>
                             </div>
                         </div>
                     </div>
                     <div class="testimonial-item-wrapper" data-aos="fade-up" data-aos-delay="100">
                         <div class="testimonial-slider">
                             @foreach ($testimonials as $testimonial)
                                 <div class="slider-item">
                                     <div class="testimonial-item">
                                         <div class="testimonial-header mb-lg-30 mb-20">
                                             <div class="user-image">
                                                 <img class="blur-up lazyload" src="assets/images/placeholder.svg"
                                                     data-src="{{ $testimonial->image ? asset('assets/front/img/user/testimonials/' . $testimonial->image) : asset('assets/admin/img/noimage.jpg') }}"
                                                     alt="user">
                                             </div>
                                             <h6 class="mb-0"><a
                                                     href="javascript:void(0);">{{ $testimonial->name }}</a></h6>
                                             <p class="mb-0">{{ $testimonial->occupation }}</p>
                                         </div>
                                         <div class="rating-area mb-lg-40 mb-30">
                                             @php
                                                 // 5 star scale theke percentage calculate
                                                 $ratingPercentage = ($testimonial->rate / 5) * 100;
                                             @endphp
                                             <div class="rate">
                                                 <div class="rating" style="width: {{ $ratingPercentage }}%;"></div>
                                             </div>
                                             <p class="mb-0">
                                                 {{ $testimonial->rate }} {{ $keywords['star_of'] ?? 'star of' }}
                                                 {{ count($testimonials) }}
                                                 {{ $keywords['review'] ?? 'review' }}</p>
                                         </div>
                                         <p class="testimonial-desc lc-3 mb-1">
                                             {!! nl2br($testimonial->content) !!}
                                         </p>

                                         <div class="right-quote">
                                             <img src="{{ asset('assets/front/theme12/img') }}/testimonial/right-quote-sign.png"
                                                 alt="">
                                         </div>
                                         <div class="left-quote">
                                             <img src="{{ asset('assets/front/theme12/img') }}/testimonial/left-quote-sign.png"
                                                 alt="">
                                         </div>
                                     </div>
                                 </div>
                             @endforeach
                         </div>
                     </div>
                 </div>
                 <div class="vactor">
                     <img class="vactor-1 blur-up lazyload"
                         src="{{ asset('assets/front/theme12/img') }}/testimonial/vactor/vactor-1.png" alt="vactor">
                     <img class="vactor-2 blur-up lazyload"
                         src="{{ asset('assets/front/theme12/img') }}/testimonial/vactor/vactor-2.png" alt="vactor">
                     <img class="vactor-3 blur-up lazyload"
                         src="{{ asset('assets/front/theme12/img') }}/testimonial/vactor/vactor-3.png" alt="vactor">
                     <img class="vactor-4 blur-up lazyload"
                         src="{{ asset('assets/front/theme12/img') }}/testimonial/vactor/vactor-4.png" alt="vactor">
                 </div>
             </section>
         @endif
         <!-- testimonial-area End -->

         <!-- ========= Start counter section ========= -->
         @if (is_array($userPermissions) && in_array('Achievements', $userPermissions))
             <section class="section-counter-area bg-cover bg-img"
                 data-bg-image="{{ asset('assets/front/img/user/home_settings/' . $home_text->achievement_image) }}">
                 <div class="container">
                     @if (count($achievements) == 0)
                         <h4 class="text-center">{{ $keywords['No Achievements Found'] ?? 'No Achievements Found' }}</h4>
                     @else
                         <div class="row counter-card-row">
                             @foreach ($achievements as $key => $achievement)
                                 <div class="col-lg-3 col-sm-6 counter-card-item" data-aos="fade-up"
                                     data-aos-delay="100">
                                     <div class="counter-card-v1 mb-30">
                                         <div class="icon">
                                             <img src="{{ asset('assets/user/images/achievement/' . $achievement->image) }}"
                                                 alt="icon">
                                         </div>
                                         <div class="counter-number mb-10">
                                             <h2 class="mb-0 fw-bold odometer" data-count="{{ $achievement->count }}">0
                                             </h2>
                                             <h2 class="mb-1 fw-bold">{{ $achievement->symbol }}</h2>
                                         </div>
                                         <p class="mb-0">{{ $achievement->subtitle }}</p>
                                     </div>
                                 </div>
                             @endforeach
                         </div>
                     @endif
                 </div>
             </section>
         @endif
         <!-- ========= End counter section ========= -->

         <!-- ========= Start Blog Section ========= -->
         @if (is_array($userPermissions) && in_array('Blog', $userPermissions))
             <section class="section-blog pt-lg-100 pt-50 pb-lg-70 pb-40">
                 <div class="container">
                     <div class="row justify-content-center">
                         <div class="col-lg-10" data-aos="fade-up" data-aos-delay="100">
                             <div class="text-center">
                                 <p class="text-primary mb-10">{{ $home_text->blog_title ?? 'Blog' }}</p>
                                 <h2 class="mb-40" data-aos="fade-up">{{ @$home_text->blog_subtitle }}</h2>
                             </div>
                         </div>
                     </div>

                     <div class="slider-area" data-aos="fade-up" data-aos-delay="300">
                         <!-- Slider main container -->
                         @if (count($blogs) > 0)
                             <div class="swiper default-slider pb-60" id="default-slider-blog" data-slidespace="30"
                                 data-xsmview="1" data-smview="1" data-mdview="2" data-lgview="2" data-xlview="4">
                                 <!-- Additional required wrapper -->
                                 <div class="swiper-wrapper">
                                     <!-- Slides -->
                                     @foreach ($blogs as $blog)
                                         <div class="swiper-slide">
                                             <!-- blog-card  -->
                                             <article class="blog-card radius-md">
                                                 <figure class="blog-image">
                                                     <a href="{{ route('front.user.blog.detail', [getParam(), $blog->slug, $blog->id]) }}"
                                                         class="lazy-container radius-md ratio ratio-5-3">
                                                         <img src="assets/images/placeholder.svg"
                                                             data-src="{{ asset('assets/front/img/user/blogs/' . $blog->image) }}"
                                                             alt="product">
                                                     </a>
                                                 </figure>
                                                 <div class="blog-content">
                                                     <h4 class="title fw-semibold lc-2">
                                                         <a
                                                             href="{{ route('front.user.blog.detail', [getParam(), $blog->slug, $blog->id]) }}">
                                                             {{ @$blog->title }}
                                                         </a>
                                                     </h4>
                                                     <p class="card-text lc-3">
                                                         {!! substr($blog->content, 0, 100) !!}
                                                     </p>
                                                     <a href="{{ route('front.user.blog.detail', [getParam(), $blog->slug, $blog->id]) }}"
                                                         class="btn-link">{{ $keywords['Read_More'] ?? 'Read More' }}</a>
                                                 </div>
                                             </article>
                                         </div>
                                     @endforeach
                                 </div>
                                 <!--  pagination -->
                                 <div class="swiper-pagination" id="default-slider-blog-pagination"></div>
                             </div>
                         @endif
                     </div>
                 </div>
                 <div class="vactor">
                     <img class="vactor-1 blur-up lazyload"
                         src="{{ asset('assets/front/theme12/img') }}/blog/vactor/vactor-1.png" alt="vactor">
                     <img class="vactor-2 blur-up lazyload"
                         src="{{ asset('assets/front/theme12/img') }}/blog/vactor/vactor-2.png" alt="vactor">
                     <img class="vactor-3 blur-up lazyload"
                         src="{{ asset('assets/front/theme12/img') }}/blog/vactor/vactor-3.png" alt="vactor">
                     <img class="vactor-4 blur-up lazyload"
                         src="{{ asset('assets/front/theme12/img') }}/blog/vactor/vactor-4.png" alt="vactor">
                 </div>
             </section>
         @endif
         <!-- ========= End Blog Section ========= -->
     @endsection
