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

    <!--====== Start Vaughn-Features section ======-->
    <section class="pricing-area pt-lg-130 pt-60">
        <div class="container">

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
                    </div>
                @endif
            </div>
        </div>
    </section>
    <!--====== End Vaughn-Features section ======-->
@endsection
