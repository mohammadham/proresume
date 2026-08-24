@extends("user.$folder.layout")
@section('styles')
    @if (isset($css_file))
        <link rel="stylesheet" href="{{ $css_file }}">
    @endif
@endsection
@section('tab-title')
    {{ $keywords['Login'] ?? 'Login' }}
@endsection
@section('br-title')
    {{ $keywords['Login'] ?? 'Login' }}
@endsection
@section('br-link')
    {{ $keywords['Login'] ?? 'Login' }}
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
                            <h1>{{ $keywords['Login'] ?? 'Login' }}</h1>
                            <ul class="breadcrumbs-link">
                                <li><a
                                        href="{{ route('front.user.detail.view', getParam()) }}">{{ $keywords['Home'] ?? 'Home' }}</a>
                                </li>
                                <li class="">{{ $keywords['Login'] ?? 'Login' }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--====== Breadcrumbs End ======-->
    @endif

    <!--====== SING IN PART START ======-->
    <section class="dashboard-area sign-in-area mt-50 mb-50">
        <div class="container">
            @if ($userBs->guest_checkout && !empty(request('redirected')))
                @if ($userBs->theme == 9 || $userBs->theme == 10 || $userBs->theme == 11 || $userBs->theme == 12)
                    <div class="d-flex justify-content-center align-items-center w-100 py-3">
                        <a class="btn btn-md btn-primary"
                            href="{{ route('front.user.appointment', [getParam(), 'type' => 'guest']) }}">{{ $keywords['Book_as_guest'] ?? __('Book as guest') }}</a>
                    </div>
                @else
                    <div class="single-form text-center p-5">
                        <a type="submit" href="{{ route('front.user.appointment', [getParam(), 'type' => 'guest']) }}"
                            class=" @if ($userBs->theme == 6 || $userBs->theme == 7 || $userBs->theme == 8) main-btn @else template-btn main-btn arrow-btn @endif">{{ $keywords['Book_as_guest'] ?? __('Book as guest') }}</a>
                    </div>
                @endif
                <p class="text-center">{{ $keywords['OR'] ?? __('OR') }}</p>
            @endif
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="sing-in-form-area wow fadeIn" data-wow-duration="1s" data-wow-delay="0.4s">
                        <div class="sing-in-form-wrapper">
                            <form action="{{ route('customer.login_submit', getParam()) }}" method="POST">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                <div class="single-form mb-20">
                                    <label>{{ $keywords['Email'] ?? __('Email') }} *</label>
                                    <input type="email"
                                        placeholder="{{ $keywords['Enter_Email_Address'] ?? __('Enter email address') }}"
                                        class="form_control" name="email" value="{{ old('email') }}">
                                    @error('email')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div> <!-- single-form -->
                                <div class="single-form mb-20">
                                    <label>{{ $keywords['Password'] ?? __('Password') }} * </label>
                                    <input type="password" class="form_control" name="password"
                                        value="{{ old('password') }}"
                                        placeholder="{{ $keywords['Enter_password'] ?? __('Enter password') }}">
                                    @error('password')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div> <!-- single-form -->
                                <div class="single-form mt-35 d-sm-flex justify-content-between">
                                    <div class="form-checkbox mt-10">
                                        @if ($userBs->theme == 10 || $userBs->theme == 11 || $userBs->theme == 12)
                                            <p class="text">{{ $keywords['New_user'] ?? 'New user' }}?
                                                <a
                                                    href="{{ route('customer.signup', getParam()) }}">{{ $keywords['Donot_have_an_account'] ?? "Don't have an account" }}?</a>
                                            </p>
                                        @endif
                                    </div>
                                    <div class="form-forget mt-10">
                                        <a href="{{ route('customer.forget_password', getParam()) }}"
                                            class="link">{{ $keywords['Lost_your_password'] ?? __('Lost your password') }}
                                            ?</a>
                                    </div>
                                </div>
                                <!-- single-form -->
                                @if ($userBs->theme == 9 || $userBs->theme == 10 || $userBs->theme == 11 || $userBs->theme == 12)
                                    <div class="form-group pt-3">
                                        <button
                                            class="btn btn-md btn-primary @if ($userBs->theme == 10) radius-30 @endif w-100"
                                            type="submit">{{ $keywords['Login'] ?? __('Login') }}</button>
                                    </div>
                                @else
                                    <div class="single-form ">
                                        <button type="submit"
                                            class="@if ($userBs->theme == 1 || $userBs->theme == 2) template-btn @else  main-btn @endif">{{ $keywords['Login'] ?? __('Login') }}</button>
                                    </div>
                                @endif
                                <!-- single-form -->
                            </form>
                            @if ($userBs->theme != 10 && $userBs->theme != 11 && $userBs->theme != 12)
                                <div class="new-user text-center mt-5">
                                    <p class="text">{{ $keywords['New_user'] ?? 'New user' }}?
                                        <a
                                            href="{{ route('customer.signup', getParam()) }}">{{ $keywords['Donot_have_an_account'] ?? "Don't have an account" }}?</a>
                                    </p>
                                </div>
                            @endif
                        </div> <!-- sing in form wrapper -->
                    </div> <!-- sing in form areasing-in-form-area -->
                </div>
            </div> <!-- row -->
        </div> <!-- container -->
    </section>
    <!--====== SING IN PART ENDS ======-->
@endsection
