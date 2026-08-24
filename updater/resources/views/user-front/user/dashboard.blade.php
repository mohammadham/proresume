@extends("user.$folder.layout")
@section('styles')
    @if (isset($css_file) && ($userBs->theme == 9 || $userBs->theme == 10 || $userBs->theme == 11 || $userBs->theme == 12))
        <link rel="stylesheet" href="{{ $css_file }}">
    @endif
@endsection
@section('tab-title')
    {{ $keywords['Dashboard'] ?? 'Dashboard' }}
@endsection
@section('br-title')
    {{ $keywords['Dashboard'] ?? 'Dashboard' }}
@endsection
@section('br-link')
    {{ $keywords['Dashboard'] ?? 'Dashboard' }}
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
                            <h1>{{ $keywords['Dashboard'] ?? 'Dashboard' }}</h1>
                            <ul class="breadcrumbs-link">
                                <li><a
                                        href="{{ route('front.user.detail.view', getParam()) }}">{{ $keywords['Home'] ?? 'Home' }}</a>
                                </li>
                                <li class="">{{ $keywords['Dashboard'] ?? 'Dashboard' }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--====== Breadcrumbs End ======-->
    @endif
    <!--====== PROFILE PART START ======-->
    <section class="dashboard-area">
        <div class="container">
            <div class="row">
                @includeIf('user-front.user.side-navbar')
                <div class="col-lg-9">
                    <div class="profile-dashboard mt-30">
                        <div class="profile-sidebar-title">
                            <h4 class="title">{{ $keywords['account_information'] ?? __('Account Information') }}</h4>
                        </div>
                        <div class="profile-dashboard-wrapper">
                            <div class="row">
                                <div class="col-md-8 dashboard-col">
                                    <div class="user-profile-details">
                                        <div class="account-info mb-3">
                                            <div class="main-info">
                                                <ul class="list">
                                                    <li><strong>{{ $keywords['Name'] ?? __('Name') }}:</strong>
                                                        {{ Auth::guard('customer')->user()->first_name }}
                                                        {{ Auth::guard('customer')->user()->last_name }}</li>
                                                    <li><strong>{{ $keywords['Email'] ?? __('Email') }}:</strong>
                                                        {{ Auth::guard('customer')->user()->email }}</li>
                                                    <li><strong>{{ $keywords['Phone'] ?? __('Phone') }}:</strong>
                                                        {{ Auth::guard('customer')->user()->contact_number }}</li>
                                                    <li><strong>{{ $keywords['Address'] ?? __('Address') }}:</strong>
                                                        {{ Auth::guard('customer')->user()->address }}</li>
                                                    <li><strong>{{ $keywords['State'] ?? __('State') }}:</strong>
                                                        {{ Auth::guard('customer')->user()->state }}</li>
                                                    <li><strong>{{ $keywords['City'] ?? __('City') }}:</strong>
                                                        {{ Auth::guard('customer')->user()->city }}
                                                    </li>
                                                    <li><strong>{{ $keywords['Country'] ?? __('Country') }}:</strong>
                                                        {{ Auth::guard('customer')->user()->country }}</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--====== PROFILE PART ENDS ======-->
@endsection
