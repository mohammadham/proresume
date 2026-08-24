@extends("user.$folder.layout")
@section('styles')
    @if (isset($css_file))
        <link rel="stylesheet" href="{{ $css_file }}">
    @endif
@endsection
@section('tab-title')
    {{ $keywords['Signup'] ?? 'Signup' }}
@endsection
@section('br-title')
    {{ $keywords['Signup'] ?? 'Signup' }}
@endsection
@section('br-link')
    {{ $keywords['Signup'] ?? 'Signup' }}
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
                            <h1>{{ $keywords['Signup'] ?? 'Signup' }}</h1>
                            <ul class="breadcrumbs-link">
                                <li><a
                                        href="{{ route('front.user.detail.view', getParam()) }}">{{ $keywords['Home'] ?? 'Home' }}</a>
                                </li>
                                <li class="">{{ $keywords['Signup'] ?? 'Signup' }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--====== Breadcrumbs End ======-->
    @endif


    <!--====== SING IN PART START ======-->
    <section class="dashboard-area sign-in-area mt-30">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    @if (Session::has('warning'))
                        <div class="alert alert-danger text-danger">{{ Session::get('warning') }}</div>
                    @endif
                    <div class="sing-in-form-area  wow fadeIn" data-wow-duration="1s" data-wow-delay="0.4s">
                        <div class="sing-in-form-wrapper">
                            @if (Session::has('sendmail'))
                                <div class="alert alert-success mb-4">
                                    <p>{{ __(Session::get('sendmail')) }}</p>
                                </div>
                            @endif
                            <form action="{{ route('customer.signup.submit', getParam()) }}" method="POST">
                                @csrf
                                <div class="single-form mb-20">
                                    <label>{{ $keywords['User_name'] ?? 'Username' }} *</label>
                                    <input type="text"
                                        placeholder="{{ $keywords['Enter_username'] ?? 'Enter username' }}"
                                        class="form_control" name="username" value="{{ old('username') }}">
                                    @error('username')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="single-form mb-20">
                                    <label>{{ $keywords['Email_Address'] ?? 'Email Address' }} *</label>
                                    <input type="email"
                                        placeholder="{{ $keywords['Enter_Email_Address'] ?? __('Enter email address') }}"
                                        class="form_control" name="email" value="{{ old('email') }}">
                                    @error('email')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="single-form mb-20">
                                    <label>{{ $keywords['Password'] ?? 'Password' }} *</label>
                                    <input type="password"
                                        placeholder="{{ $keywords['Enter_password'] ?? __('Enter password') }}"
                                        class="form_control" name="password" value="{{ old('password') }}">
                                    @error('password')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="single-form">
                                    <label>{{ $keywords['Confirm_Password'] ?? 'Confirm Password' }} *</label>
                                    <input type="password"
                                        placeholder="{{ $keywords['Confirm_Password'] ?? 'Confirm password' }}"
                                        class="form_control" name="password_confirmation"
                                        value="{{ old('password_confirmation') }}">
                                    @error('password_confirmation')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                @if ($userBs->theme == 9 || $userBs->theme == 10 || $userBs->theme == 11 || $userBs->theme == 12)
                                    <div class="form-group pt-3">
                                        <button
                                            class="btn btn-md btn-primary {{ $userBs->theme == 10 ? 'radius-30' : '' }} w-100"
                                            type="submit">{{ $keywords['Signup'] ?? __('Signup') }}</button>
                                    </div>
                                @else
                                    <div class="single-form mt-5">
                                        <button type="submit"
                                            class="@if ($userBs->theme == 1 || $userBs->theme == 2) template-btn @else  main-btn @endif">{{ $keywords['Signup'] ?? __('Signup') }}</button>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div> <!-- row -->
        </div> <!-- container -->
    </section>
    <!--====== SING IN PART ENDS ======-->
@endsection
