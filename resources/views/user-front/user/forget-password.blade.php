@extends("user.$folder.layout")
@section('styles')
    @if (isset($css_file))
        <link rel="stylesheet" href="{{ $css_file }}">
    @endif
@endsection
@section('tab-title')
    {{ $keywords['forget_password'] ?? 'forget password' }}
@endsection
@section('br-title')
    {{ $keywords['forget_password'] ?? 'forget password' }}
@endsection
@section('br-link')
    {{ $keywords['forget_password'] ?? 'forget password' }}
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
                            <h1>{{ $keywords['forget_password'] ?? 'forget password' }}</h1>
                            <ul class="breadcrumbs-link">
                                <li><a
                                        href="{{ route('front.user.detail.view', getParam()) }}">{{ $keywords['Home'] ?? 'Home' }}</a>
                                </li>
                                <li class="">{{ $keywords['forget_password'] ?? 'forget password' }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--====== Breadcrumbs End ======-->
    @endif

    <!--======FORGET PASSWORD PART START ======-->
    <section class="dashboard-area sign-in-area mt-30">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="sing-in-form-area  wow fadeIn" data-wow-duration="1s" data-wow-delay="0.4s">
                        <div class="sing-in-form-wrapper">
                            <form action="{{ route('customer.send_forget_password_mail', getParam()) }}" method="POST">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                <div class="single-form ">
                                    <label>{{ $keywords['Email_Address'] ? $keywords['Email_Address'] . '*' : __('Email Address') . '*' }}</label>
                                    <input type="email"
                                        placeholder="{{ $keywords['Enter_Email_Address'] ?? __('Email Address') . '*' }}"
                                        class="form_control" name="email" value="{{ old('email') }}">
                                    @error('email')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div> <!-- single-form -->
                                @if ($userBs->theme == 9 || $userBs->theme == 10 ||  $userBs->theme == 11 ||
            $userBs->theme == 12)
                                    <div class="form-group pt-3">
                                        <button class="btn btn-md btn-primary w-100"
                                            type="submit">{{ $keywords['Send_Mail'] ?? __('Send Mail') }}</button>
                                    </div>
                                @else
                                    <div class="single-form mt-5">
                                        <button type="submit"
                                            class="@if ($userBs->theme == 1 || $userBs->theme == 2) template-btn @else  main-btn @endif">{{ $keywords['Send_Mail'] ?? __('Send Mail') }}</button>
                                    </div> <!-- single-form -->
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--====== FORGET PASSWORD PART ENDS ======-->
@endsection
