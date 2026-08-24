@extends("user.$folder.layout")
@section('styles')
    @if (isset($css_file))
        <link rel="stylesheet" href="{{ $css_file }}">
    @endif
@endsection
@section('tab-title')
    {{ $keywords['Appointment'] ?? 'Appointment' }}
@endsection

@section('meta-description', !empty($userSeo) ? $userSeo->services_meta_description : '')
@section('meta-keywords', !empty($userSeo) ? $userSeo->services_meta_keywords : '')

@section('br-title')
    {{ $keywords['Appointment'] ?? 'Appointment' }}
@endsection
@section('br-link')
    {{ $keywords['Appointment'] ?? 'Appointment' }}
@endsection

@section('content')

    @if (
        $userBs->theme == 6 ||
            $userBs->theme == 7 ||
            $userBs->theme == 8 ||
            $userBs->theme == 9 ||
            $userBs->theme == 10 ||
            $userBs->theme == 11 ||
            $userBs->theme == 12)
        <!--====== Breadcrumbs Start ======-->
        <section class="breadcrumbs-section">
            <div class="container">
                <div class="row align-items-center justify-content-center">
                    <div class="col-lg-10">
                        <div class="page-title">
                            <h1>{{ $keywords['Appointment'] ?? 'Appointment' }}</h1>
                            <ul class="breadcrumbs-link">
                                <li><a
                                        href="{{ route('front.user.detail.view', getParam()) }}">{{ $keywords['Home'] ?? 'Home' }}</a>
                                </li>
                                <li class="">{{ $keywords['Appointment'] ?? 'Appointment' }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--====== Breadcrumbs End ======-->
    @endif
    <!--====== Start Vaughn-Features section ======-->
    @if ($userBs->theme == 10)
        <section class="pricing-area pt-lg-70 pt-50 pb-50 pb-lg-40 pb-20">
            <div class="container">
                <div class="row justify-content-center">
                    @if (count($categories) > 0)
                        @foreach ($categories as $category)
                            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                                <div class="pricing-card mb-30" >
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
    @elseif ($userBs->theme == 12)
        <section class="appointment-area pt-lg-100 pt-50 pb-50 pb-lg-40 pb-20">
            <div class="container">
                <div class="row">
                    @if (count($categories) > 0)
                        <div class="row appointment-card-row">
                            @foreach ($categories as $category)
                                <div class="col-xl-4 col-sm-6 appointment-card-item" data-aos="fade-up"
                                    data-aos-delay="100" onclick="window.location.href='{{ route('front.user.appointment.form', ['cat' => $category->id, getParam()]) }}'">
                                    <div class="appointment-card">
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
                    @endif
                </div>
            </div>
        </section>
    @else
        <section class="pt-5 mt-5 mb-5 @if ($userBs->theme == 6 || $userBs->theme == 7 || $userBs->theme == 8) page-content-section @endif vaughn-features"
            id="service">
            <div class="container">
                @if (count($categories) > 0)
                    <div class="row justify-content-center">
                        @foreach ($categories as $category)
                            <div class="col-lg-3 col-md-6">
                                <a class="features-img mb-1 d-block"
                                    href="{{ route('front.user.appointment.form', ['cat' => $category->id, getParam()]) }}">
                                    <div class="features-box border bg-white p-3 mb-50 text-center">
                                        <img data-src="{{ asset('assets/user/img/category') . '/' . $category->image }}"
                                            class="img-fluid lazy" alt="img">
                                        <div>
                                            ({{ $userBs->base_currency_symbol_position == 'left' ? $userBs->base_currency_symbol : '' }}{{ $category->appointment_price }}{{ $userBs->base_currency_symbol_position == 'right' ? $userBs->base_currency_symbol : '' }})
                                        </div>
                                        <h5>{{ $category->name }} </h5>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif
    <!--====== End Vaughn-Features section ======-->
@endsection
