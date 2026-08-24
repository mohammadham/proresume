     @extends('user.profile1.theme11.layout')

     @section('tab-title')
         {{ $keywords['Home'] ?? 'Home' }}
     @endsection

     @section('meta-description', !empty($userSeo) ? $userSeo->home_meta_description : '')
     @section('meta-keywords', !empty($userSeo) ? $userSeo->home_meta_keywords : '')

     @section('content')

         <!-- ======= START HERO section ========= -->
         <section class="hero-area">
             <div class="container-fluid px-0">
                 <div class="fluid-left row align-items-center">
                     <div class="col-lg-6">
                         <div class="content mb-50 px-2">
                             <p class="subtitle" data-aos="fade-up" data-aos-delay="100">
                                 {{ @$home_text->hero_section_title }}
                             </p>
                             <h1 class="title hero-title text-uppercase mb-30" data-aos="fade-up" data-aos-delay="150">
                                 {!! @$home_text->hero_section_subtitle !!}
                                 <img class="img-to-svg" src="{{ asset('assets/front/theme11/img/title-sm.svg') }}"
                                     alt="vactor">
                             </h1>
                         </div>
                     </div>
                     <div class="col-lg-6" data-aos="fade-left" data-aos-delay="200">
                         <div class="hero-image">
                             <img class="blur-up lazyload" src="assets/images/placeholder.svg"
                                 data-src="{{ $home_text->hero_image ? asset('assets/front/img/user/home_settings/' . $home_text->hero_image) : asset('assets/admin/img/noimage.jpg') }}"
                                 alt="hero-img">
                             <div class="video-card-sm">
                                 <div class="image">
                                     <img src="{{ $home_text->hero_video_image ? asset('assets/front/img/user/home_settings/' . $home_text->hero_video_image) : asset('assets/admin/img/noimage.jpg') }}"
                                         alt="img">
                                     <a href="{{ @$home_text->hero_section_vurl }}" class="play-btn youtube-popup">
                                         <i class="fa-solid fa-play"></i>
                                     </a>
                                 </div>
                                 <div class="content">
                                     <h6 class="lc-1"><a
                                             href="javascript:void(0)">{{ @$home_text->hero_section_vtitle }}</a></h6>
                                     <p class="small lc-2 mb-0">{{ @$home_text->hero_section_vsubtitle }}</p>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
             <div class="vertor">
                 <span class="vertor-1"></span>
             </div>
         </section>
         <!-- ========= END HERO section ========= -->


         <!-- ========= Start About Area section ========= -->
         <section class="about-area pt-lg-130 pt-50 pb-lg-130 pb-70" data-aos="fade-up" data-aos-delay="200">
             <div class="container">
                 <div class="row about-area-row">
                     <div class="col-lg-8">
                         <div class="left mb-30">
                             <div class="about-content-wrap">
                                 <div class="image">
                                     <div class="lazy-container ratio ratio-1-3">
                                         <img src="{{ $home_text->about_left_image ? asset('assets/front/img/user/home_settings/' . $home_text->about_left_image) : asset('assets/admin/img/noimage.jpg') }}"
                                             alt="image">
                                     </div>
                                 </div>
                                 <div class="content">
                                     <h2 class="mb-lg-50 mb-20 title text-uppercase">
                                         {{ @$home_text->about_title }}
                                         <img class="img-to-svg" src="{{ asset('assets/front/theme11/img/title.svg') }}"
                                             alt="vactor">
                                     </h2>
                                     <p class="mb-lg-60 mb-40 fs-5 desc">
                                         {!! @$home_text->about_content !!}
                                     </p>
                                     @if (!is_null(@$home_text->about_button_name) && !is_null(@$home_text->about_button_url))
                                         <a href="{{ $home_text->about_button_url }}"
                                             class="btn thm-btn anim-btn">{{ $home_text->about_button_name }}</a>
                                     @endif
                                 </div>
                             </div>
                         </div>
                     </div>
                     <div class="col-lg-4">
                         <div class="right">
                             <div class="image-right-wrap">
                                 <div class="lazy-container ratio ratio-5-4">
                                     <img src="{{ $home_text->about_right_image ? asset('assets/front/img/user/home_settings/' . $home_text->about_right_image) : asset('assets/admin/img/noimage.jpg') }}"
                                         alt="image">
                                 </div>
                                 <span class="text-round">
                                     @if ($userCurrentLang->rtl == 1)
                                         <svg viewBox="0 0 200 200" width="200" height="200" class="experience-text">
                                             <defs>
                                                 <!-- Path direction উল্টো করা হয়েছে -->
                                                 <path id="circlePathRtl" d="M100,100
                 m 90,0
                 a 90,90 0 1,0 -180,0
                 a 90,90 0 1,0 180,0"></path>
                                             </defs>
                                             <text font-size="14" fill="#fff" textLength="565" direction="rtl"
                                                 unicode-bidi="bidi-override">
                                                 <textPath href="#circlePathRtl" startOffset="98%">
                                                     05 سنوات من الخبرة المهنية
                                                 </textPath>
                                             </text>
                                         </svg>
                                     @else
                                         <svg viewBox="0 0 200 200" width="200" height="200" class="experience-text">
                                             <defs>
                                                 <path id="circlePath" d="M 100,100
                                                       m -90,0
                                                       a 90,90 0 1,1 180,0
                                                       a 90,90 0 1,1 -180,0" />
                                             </defs>
                                             <text font-size="14" fill="#fff" textLength="565">
                                                 <textPath href="#circlePath" startOffset="2%">
                                                     {{ @$home_text->about_experience_text }}
                                                 </textPath>
                                             </text>
                                         </svg>
                                     @endif

                                 </span>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
         </section>
         <!-- ========= End About Area section ========= -->

         @if (is_array($userPermissions) && in_array('Service', $userPermissions))
             <!-- ========= Start service section ========= -->
             <section class="service-area pb-lg-70 pb-30">
                 <div class="container">
                     <div class="row justify-content-center">
                         <div class="col-xxl-5 col-lg-10">
                             <div class="text-center" data-aos="fade-up" data-aos-delay="100">
                                 <h2 class="text-center title mb-lg-40 mb-30 text-uppercase">
                                     {{ @$home_text->service_title }}
                                     <br>
                                     <img class="img-to-svg" src="{{ asset('assets/front/theme11/img/title.svg') }}"
                                         alt="vactor">
                                 </h2>
                             </div>
                         </div>
                     </div>
                 </div>
                 <div class="container-fuild px-0">
                     <div class="slider-area" data-aos="fade-up" data-aos-delay="300">
                         <!-- Slider main container -->
                         <div class="swiper default-slider pb-70" id="default-slider-service" data-slidespace="0"
                             data-xsmview="1" data-smview="2" data-mdview="2" data-lgview="3" data-xlview="4">
                             <!-- Additional required wrapper -->
                             <div class="swiper-wrapper">
                                 @foreach ($services as $service)
                                     <div class="swiper-slide">
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
                 </div>
             </section>
             <!-- ========= end service section ========= -->
         @endif

         @if (is_array($userPermissions) && in_array('Achievements', $userPermissions))
             <!-- ========= Start Conter section ========= -->
             <section class="conter-area bg-cover bg-img" data-aos="fade-up" data-aos-delay="200">
                 <div class="container">
                     @if (count($achievements) == 0)
                         <h4 class="text-center">{{ $keywords['No Achievements Found'] ?? 'No Achievements Found' }}</h4>
                     @else
                         <div class="row counter-card-row">
                             @foreach ($achievements as $key => $achievement)
                                 <div class="col-xl-3 col-lg-6 col-md-6">
                                     <div class="counter-card mb-50">
                                         <div class="counter-number">
                                             <h3 class="odometer" data-count="{{ $achievement->count }}">
                                                 0</h3>
                                             <h3 class=""><i class="fas fa-plus"></i></h3>
                                         </div>
                                         <div class="icon">
                                             <img class="img-to-svg"
                                                 src="{{ asset('assets/user/images/achievement/' . $achievement->image) }}"
                                                 alt="img">
                                         </div>
                                         <h6 class="font-lg title">{{ $achievement->title }}</h6>
                                         <p class="description small">{{ $achievement->subtitle }}</p>
                                     </div>
                                 </div>
                             @endforeach
                         </div>
                     @endif
                 </div>
             </section>
             <!-- ========= Start Conter section ========= -->
         @endif

         @include('user.profile1.theme11.include.experience')



         <!-- ========= Start Gallery section ========= -->
         @if (is_array($userPermissions) && in_array('Gallery', $userPermissions))
             <section class="gallery-section-2 pt-lg-130 pt-60">
                 <div class="container">
                     <div data-aos="fade-right" data-aos-delay="100">
                         <h2 class="title text-uppercase">{{ @$home_text->gallery_title ?? 'Gallery' }}
                             <img class="img-to-svg" src="{{ asset('assets/front/theme11/img/title.svg') }}"
                                 alt="vactor">
                         </h2>
                     </div>

                     <div class="row gallery-popup" data-aos="fade-up" data-aos-delay="200">
                         @foreach ($galleries as $gallery)
                             @php
                                 $img = 'assets/user/gallery/' . $gallery->image;
                                 $imagePath = file_exists(public_path($img))
                                     ? asset($img)
                                     : asset('assets/front/img/placeholder.jpg');
                             @endphp
                             <div class="col-lg-4 mb-30">
                                 <div class="gallery-item">
                                     <a href="{{ $imagePath }}" title="{{ $gallery->name }}" class="popup-image">
                                         <img src="{{ $imagePath }}" alt="{{ $gallery->name }}">
                                     </a>
                                     <div class="project-info">
                                         <h5 class="vericaltext title"><a
                                                 href="javascript:void(0)">{{ $gallery->name }}</a>
                                         </h5>
                                         <span class="vericaltext"></span>
                                     </div>
                                 </div>
                             </div>
                         @endforeach
                     </div>
                 </div>
             </section>
         @endif
         <!-- ========= Start Gallery section ========= -->




         <!-- ========= Start Pricing section ========= -->
         @if (is_array($userPermissions) && in_array('Appointment', $userPermissions))
             <section class="pricing-area pt-lg-130 pt-60">
                 <div class="container">
                     <div class="row justify-content-center">
                         <div class="col-lg-12">
                             <div data-aos="fade-up" data-aos-delay="100">
                                 <h2 class="title text-center mx-auto text-uppercase">
                                     {{ $home_text->appointment_title ?? 'Appointment' }}
                                     <br>
                                     <img class="img-to-svg" src="{{ asset('assets/front/theme11/img/title.svg') }}"
                                         alt="vactor">
                                 </h2>
                             </div>
                         </div>
                     </div>
                     <div class="pricing-card-wrap" data-aos="fade-up" data-aos-delay="100">
                         @if (count($categories) > 0 && $userBs->appointment_category == 1)
                             <div class="row">
                                 @foreach ($categories as $category)
                                     <div class="col-lg-6 mb-30">
                                         <div class="pricing-card bg-cover bg-img">
                                             <div class="pricing-card-header">
                                                 <div class="icon">
                                                     <img src="{{ asset('assets/user/img/category') . '/' . $category->image }}"
                                                         alt="">
                                                 </div>
                                                 <h3 class="text-uppercase">{{ $category->name }}</h3>
                                             </div>
                                             <div class="pricing-card-footer">
                                                 <div class="price">
                                                     <span
                                                         class="text-primary fs-4 new-price">{{ $userBs->base_currency_symbol_position == 'left' ? $userBs->base_currency_symbol : '' }}{{ $category->appointment_price }}</span>
                                                 </div>
                                                 <a href="{{ route('front.user.appointment.form', ['cat' => $category->id, getParam()]) }}"
                                                     class="btn thm-btn anim-btn">
                                                     {{ $keywords['Book_Now'] ?? 'Book Now' }}
                                                 </a>
                                             </div>
                                         </div>
                                     </div>
                                 @endforeach
                             </div>
                         @else
                             <div class="d-flex justify-content-center align-items-center">
                                 <div data-aos="fade-up" data-aos-delay="200">
                                     <div class="pricing-card-2 mb-30 m-auto">
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
                                                     <button class="btn btn-md btn-primary radius-30 w-100"
                                                         type="submit">{{ $keywords['Next'] ?? 'Next' }}</button>
                                                 </div>
                                             </form>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                         @endif
                     </div>
                 </div>
             </section>
         @endif
         <!-- ========= End Pricing section ========= -->

         <!-- testimonial-area Start -->
         @if (is_array($userPermissions) && in_array('Testimonial', $userPermissions))
             <section class="testimonial-area pt-lg-130 pt-60 pb-lg-100 pb-40" data-aos="fade-up" data-aos-delay="100">
                 <div class="container">
                     <div data-aos="fade-up" data-aos-delay="100">
                         <h2 class="mb-lg-60 mb-20 title text-uppercase">
                             {{ @$home_text->testimonial_title ?? 'Testimonial' }}
                             <img class="img-to-svg" src="{{ asset('assets/front/theme11/img/title.svg') }}"
                                 alt="vactor">
                         </h2>
                     </div>
                 </div>
                 <div class="container-fluid px-0">
                     @if (count($testimonials) > 0)
                         <div class="row testimonial-row fluid-right">
                             <div class="col-lg-7">
                                 <div thumbsSlider="" class="swiper testimonialthumb mb-30">
                                     <div class="swiper-wrapper">
                                         @foreach ($testimonials as $testimonial)
                                             <div class="swiper-slide testimonialthumb-item">
                                                 <img class="blur-up lazyload" src="assets/images/placeholder.svg"
                                                     data-src="{{ $testimonial->image ? asset('assets/front/img/user/testimonials/' . $testimonial->image) : asset('assets/admin/img/noimage.jpg') }}"
                                                     alt="client">
                                                 <a href="{{ $testimonial->video_url }}" class="play-btn youtube-popup">
                                                     <i class="fal fa-play"></i>
                                                 </a>
                                             </div>
                                         @endforeach
                                     </div>
                                 </div>
                             </div>
                             <div class="col-lg-5">
                                 <div class="swiper testimonialtext mb-30">
                                     <div class="swiper-wrapper">
                                         @foreach ($testimonials as $testimonial)
                                             <div class="swiper-slide testimonialtext-item">
                                                 <p class="mb-lg-60 font-lg mb-30">
                                                     {!! nl2br($testimonial->content) !!}
                                                 </p>
                                                 <h4><a href="javascript:void(0)">{{ $testimonial->name }}</a></h4>
                                                 <span class="fw-semibold small">{{ $testimonial->occupation }}</span>
                                             </div>
                                         @endforeach
                                     </div>
                                     <div class="slider-navigation">
                                         <div class="slider-btn-prev slider-btn"><i class="fal fa-angle-left"></i></div>
                                         <div class="slider-btn-next slider-btn"><i class="fal fa-angle-right"></i></div>
                                     </div>
                                 </div>
                             </div>
                         </div>
                     @else
                         <h4 class="text-center">{{ $keywords['No Testimonial Found'] ?? 'No Testimonial Found' }}</h4>
                     @endif
                 </div>
             </section>
         @endif
         <!-- testimonial-area End -->
     @endsection
     @section('scripts')
         <script>
             $(function() {
                 $('.gallery-popup').each(function() {
                     $(this).magnificPopup({
                         delegate: '.popup-image',
                         type: 'image',
                         gallery: {
                             enabled: true,
                             preload: [0, 2],
                             navigateByImgClick: true
                         },
                         image: {
                             titleSrc: function(item) {
                                 return item.el.attr('title') || '';
                             },
                             tError: 'Image could not be loaded.'
                         },
                         mainClass: 'mfp-fade',
                         removalDelay: 300,
                         fixedContentPos: false
                     });
                 });
             });
         </script>
     @endsection
