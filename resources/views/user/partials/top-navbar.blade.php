@php
    $user = Auth::guard('web')->user();
    $userLanguages = \App\Models\User\Language::where('user_id', $user->id)->get();
    // dd(session()->all());
    

@endphp

@if (Session::has('currentLangCode'))
    @php
    $code = Session::get('currentLangCode');
        $default = \App\Models\User\Language::where('code', Session::get('currentLangCode'))
            ->where('user_id', $user->id)
            ->first();
    @endphp
@else
    @php
        $default = \App\Models\User\Language::where('is_default', 1)
            ->where('user_id', $user->id)
            ->first();
    @endphp
@endif

<div class="main-header">
    <!-- Logo Header -->
    <div class="logo-header" @if (request()->cookie('user-theme') == 'dark') data-background-color="dark2" @endif>
        <a href="{{ route('front.index') }}" class="logo" target="_blank">
            <img src="{{ !empty($userBs->logo) ? asset('assets/front/img/user/' . $userBs->logo) : asset('assets/front/img/profile/lgoo.png') }}"
                alt="navbar brand" class="navbar-brand" width="45">
        </a>
        <button class="navbar-toggler sidenav-toggler ml-auto" type="button" data-toggle="collapse"
            data-target="collapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon">
                <i class="icon-menu"></i>
            </span>
        </button>
        <button class="topbar-toggler more"><i class="icon-options-vertical"></i></button>
        <div class="nav-toggle">
            <button class="btn btn-toggle toggle-sidebar">
                <i class="icon-menu"></i>
            </button>
        </div>
    </div>
    <!-- End Logo Header -->
    <!-- Navbar Header -->
    <nav class="navbar navbar-header navbar-expand-lg"
        @if (request()->cookie('user-theme') == 'dark') data-background-color="dark" @endif>
        <div class="container-fluid">
            <ul class="navbar-nav topbar-nav ml-md-auto align-items-center">
                <li>
                    <button type="button" class="btn  btn-primary mr-2" data-toggle="modal" data-target="#allLimits">
                        {{  __('Features Limit') }}

                    </button>
                </li>

                {{-- <li>
                    @if (!empty($adminLangs))
                        <select name="adminLanguage" class="form-control language-select"
                            onchange="window.location='{{ route('user.change.language') . '?language=' }}'+this.value">

                            @foreach ($adminLangs as $lang)
                                <option value="{{ $lang->code }}"
                                    {{ $lang->code == $userDashboardLang->code ? 'selected' : '' }}>
                                    {{ $lang->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </li> --}}

                <li>
                    @if (!empty($adminLangs))
                        <select name="adminLanguage" class="form-control language-select langBtn">
                            @foreach ($adminLangs as $lang)
                                <option value="{{ $lang->code }}"
                                    {{ $lang->code == $userDashboardLang->code ? 'selected' : '' }}>
                                    {{ $lang->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </li>
                <input type="hidden" id="setLocale" value="{{ route('user.change.language') }}">

                <li>
                    <form action="{{ route('user.theme.change', ['language' => $default->code]) }}"
                        class="mr-4 form-inline" id="adminThemeForm">
                        <div class="form-group">
                            <div class="selectgroup selectgroup-secondary selectgroup-pills">
                                <label class="selectgroup-item">
                                    <input type="radio" name="theme" value="light" class="selectgroup-input"
                                        {{ empty(request()->cookie('user-theme')) || request()->cookie('user-theme') == 'light' ? 'checked' : '' }}
                                        onchange="document.getElementById('adminThemeForm').submit();">
                                    <span class="selectgroup-button selectgroup-button-icon"><i
                                            class="fa fa-sun"></i></span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="radio" name="theme" value="dark" class="selectgroup-input"
                                        {{ request()->cookie('user-theme') == 'dark' ? 'checked' : '' }}
                                        onchange="document.getElementById('adminThemeForm').submit();">
                                    <span class="selectgroup-button selectgroup-button-icon"><i
                                            class="fa fa-moon"></i></span>
                                </label>
                            </div>
                        </div>
                    </form>



                <li>
                <li style="margin-right: 20px">
                    @php
                        if (Auth::user()->custom_domain_status == 1 && !empty(Auth::user()->custom_domain)) {
                            $domain = Auth::user()->custom_domain;
                        } else {
                            $domain = Auth::user()->username . '.' . env('WEBSITE_HOST');
                        }
                    @endphp
                    <a class="btn btn-primary btn-sm btn-round" target="_blank"
                        href="{{ route('front.user.detail.view', Auth::user()->username) }}" title="View Profile">
                        <i class="fas fa-eye"></i>
                    </a>
                </li>
                </li>
                <li class="d-flex mr-4">
                    <label class="switch">
                        <input type="checkbox" name="online_status" id="toggle-btn" data-toggle="toggle" data-on="1"
                            data-off="0" @if (Auth::user()->online_status == 1) checked @endif>
                        <span class="slider round"></span>
                    </label>

                    @if (Auth::user()->online_status == 1)
                        <h5 class="mt-2 ml-2 @if (request()->cookie('user-theme') == 'dark') text-white @endif">
                            {{ __('Active') }}
                        </h5>
                    @else
                        <h5 class="mt-2 ml-2 @if (request()->cookie('user-theme') == 'dark') text-white @endif">
                            {{ __('Deactive') }}
                        </h5>
                    @endif
                </li>
                <li class="nav-item dropdown hidden-caret">
                    <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#" aria-expanded="false">
                        <div class="avatar-sm">
                            @if (!empty(Auth::user()->photo))
                                <img src="{{ asset('assets/front/img/user/' . Auth::user()->photo) }}" alt="..."
                                    class="avatar-img rounded-circle">
                            @else
                                <img src="{{ asset('assets/admin/img/propics/blank_user.jpg') }}" alt="..."
                                    class="avatar-img rounded-circle">
                            @endif
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-user animated fadeIn">
                        <div class="dropdown-user-scroll scrollbar-outer">
                            <li>
                                <div class="user-box">
                                    <div class="avatar-lg">
                                        @if (!empty(Auth::user()->photo))
                                            <img src="{{ asset('assets/front/img/user/' . Auth::user()->photo) }}"
                                                alt="..." class="avatar-img rounded">
                                        @else
                                            <img src="{{ asset('assets/admin/img/propics/blank_user.jpg') }}"
                                                alt="..." class="avatar-img rounded">
                                        @endif
                                    </div>
                                    <div class="u-text">
                                        <h4>{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</h4>
                                        <p class="text-muted">{{ Auth::user()->email }}</p>
                                        <a href="{{ route('user-profile-update', ['language' => $default->code]) }}"
                                            class="btn btn-xs btn-secondary btn-sm">{{ __('Edit Profile') }}</a>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item"
                                    href="{{ route('user-profile-update', ['language' => $default->code]) }}">{{ __('Edit Profile') }}</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item"
                                    href="{{ route('user.changePass', ['language' => $default->code]) }}">{{ __('Change Password') }}</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item"
                                    href="{{ route('user-logout', ['language' => $default->code]) }}">{{ __('Logout') }}</a>
                            </li>
                        </div>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
    <!-- End Navbar -->
</div>


<!-- Modal -->
@if (!is_null($userCurrentPackage))
    <div class="modal fade" id="allLimits" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="exampleModalLabel">
                        {{ __('All Features Left') }}</h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @php

                        $blogLeft = $userCurrentPackage->number_of_blogs - $userFeaturesCount['blogs'];
                        $blogCartegoriesLeft =
                            $userCurrentPackage->number_of_blog_categories - $userFeaturesCount['blogCategories'];
                        $serviceLeft = $userCurrentPackage->number_of_services - $userFeaturesCount['services'];
                        $skillLeft = $userCurrentPackage->number_of_skills - $userFeaturesCount['skills'];
                        $portfolioLeft = $userCurrentPackage->number_of_portfolios - $userFeaturesCount['portfolios'];
                        $portfolioCategoryLeft =
                            $userCurrentPackage->number_of_portfolio_categories -
                            $userFeaturesCount['portfolioCategories'];
                        $languagesLeft = $userCurrentPackage->number_of_languages - $userFeaturesCount['languages'];
                        $jobExprienceLeft =
                            $userCurrentPackage->number_of_job_expriences - $userFeaturesCount['jobExpriences'];
                        $educationLeft = $userCurrentPackage->number_of_education - $userFeaturesCount['educations'];
                        $vcardsLeft = $userCurrentPackage->number_of_vcards - $userFeaturesCount['vcards'];

                    @endphp
                    <div class="alert alert-warning">
                        <span
                            class="text-warning">{{ __('If anyone feature is down graded') . ', ' .__('you can not create or edit any feature') . '.' }}</span>
                    </div>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ __('Blog Left') }}
                            @if ($userCurrentPackage->number_of_blogs < 999999)
                                @if ($blogLeft == 0)
                                    <span
                                        class="mx-2 d-inline-block text-danger">{{ __('Limit is over') }}</span>
                                @elseif($blogLeft < 0)
                                    <span
                                        class="mx-2 d-inline-block text-danger">{{ __('Down Graded') }}</span>
                                @endif
                            @endif
                            @if ($userCurrentPackage->number_of_blogs < 999999)
                                <span
                                    class="mx-2 d-inline-block badge @if ($blogLeft < 0) badge-danger @elseif($blogLeft == 0) badge-warning @else badge-primary @endif badge-pill">{{ $blogLeft }}
                                </span>
                            @else
                                <span class="mx-2 d-inline-block badge badge-success badge-pill">
                                    {{ __('unlimited') }}</span>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ __('Blog Categories Left') }}
                            @if ($userCurrentPackage->number_of_blog_categories < 999999)
                                @if ($blogCartegoriesLeft == 0)
                                    <span
                                        class="mx-2 d-inline-block text-danger">{{ __('Limit is over') }}</span>
                                @elseif($blogCartegoriesLeft < 0)
                                    <span
                                        class="mx-2 d-inline-block text-danger">{{ __('Down Graded') }}</span>
                                @endif
                            @endif
                            @if ($userCurrentPackage->number_of_blog_categories < 999999)
                                <span
                                    class="mx-2 d-inline-block badge  @if ($blogCartegoriesLeft < 0) badge-danger @elseif($blogCartegoriesLeft == 0) badge-warning @else badge-primary @endif  badge-pill">{{ $blogCartegoriesLeft }}</span>
                            @else
                                <span class="mx-2 d-inline-block badge badge-success badge-pill">
                                    {{ __('unlimited') }}</span>
                            @endif

                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ __('Services Left') }}
                            @if ($userCurrentPackage->number_of_services < 999999)
                                @if ($serviceLeft == 0)
                                    <span
                                        class="mx-2 d-inline-block text-danger">{{ __('Limit is over') }}</span>
                                @elseif($serviceLeft < 0)
                                    <span
                                        class="mx-2 d-inline-block text-danger">{{ __('Down Graded') }}</span>
                                @endif
                            @endif
                            @if ($userCurrentPackage->number_of_services < 999999)
                                <span
                                    class="mx-2 d-inline-block badge  @if ($serviceLeft < 0) badge-danger @elseif($serviceLeft == 0) badge-warning @else badge-primary @endif badge-pill">{{ $serviceLeft }}</span>
                            @else
                                <span class="mx-2 d-inline-block badge badge-success badge-pill">
                                    {{ __('unlimited') }}</span>
                            @endif
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ __('Skills Left') }}
                            @if ($userCurrentPackage->number_of_skills < 999999)
                                @if ($skillLeft == 0)
                                    <span
                                        class="mx-2 d-inline-block text-danger">{{ __('Limit is over') }}</span>
                                @elseif($skillLeft < 0)
                                    <span
                                        class="mx-2 d-inline-block text-danger">{{ __('Down Graded') }}</span>
                                @endif
                            @endif
                            @if ($userCurrentPackage->number_of_skills < 999999)
                                <span
                                    class="mx-2 d-inline-block badge @if ($skillLeft < 0) badge-danger @elseif($skillLeft == 0) badge-warning @else badge-primary @endif badge-pill">{{ $skillLeft }}</span>
                            @else
                                <span class="mx-2 d-inline-block badge badge-success badge-pill">
                                    {{ __('unlimited') }}</span>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ __('Portfolios Left') }}
                            @if ($userCurrentPackage->number_of_portfolios < 999999)
                                @if ($portfolioLeft == 0)
                                    <span
                                        class="mx-2 d-inline-block text-danger">{{ __('Limit is over') }}</span>
                                @elseif($portfolioLeft < 0)
                                    <span
                                        class="mx-2 d-inline-block text-danger">{{ __('Down Graded') }}</span>
                                @endif
                            @endif
                            @if ($userCurrentPackage->number_of_portfolios < 999999)
                                <span
                                    class="mx-2 d-inline-block badge @if ($portfolioLeft < 0) badge-danger @elseif($portfolioLeft == 0) badge-warning @else badge-primary @endif badge-pill">{{ $portfolioLeft }}</span>
                            @else
                                <span class="mx-2 d-inline-block badge badge-success badge-pill">
                                    {{ __('unlimited') }}</span>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ __('Portfolio Categories Left') }}
                            @if ($userCurrentPackage->number_of_portfolio_categories < 999999)
                                @if ($portfolioCategoryLeft == 0)
                                    <span
                                        class="mx-2 d-inline-block text-danger">{{ __('Limit is over') }}</span>
                                @elseif($portfolioCategoryLeft < 0)
                                    <span
                                        class="mx-2 d-inline-block text-danger">{{ __('Down Graded') }}</span>
                                @endif
                            @endif
                            @if ($userCurrentPackage->number_of_portfolio_categories < 999999)
                                <span
                                    class="mx-2 d-inline-block badge @if ($portfolioCategoryLeft < 0) badge-danger @elseif($portfolioCategoryLeft == 0) badge-warning @else badge-primary @endif badge-pill">{{ $portfolioCategoryLeft }}</span>
                            @else
                                <span class="mx-2 d-inline-block badge badge-success badge-pill">
                                    {{ __('unlimited') }}</span>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ __('Job Experiences Categories Left') }}
                            @if ($userCurrentPackage->number_of_job_expriences < 999999)
                                @if ($jobExprienceLeft == 0)
                                    <span
                                        class="mx-2 d-inline-block text-danger">{{ __('Limit is over') }}</span>
                                @elseif($jobExprienceLeft < 0)
                                    <span
                                        class="mx-2 d-inline-block text-danger">{{ __('Down Graded') }}</span>
                                @endif
                            @endif
                            @if ($userCurrentPackage->number_of_job_expriences < 999999)
                                <span
                                    class="mx-2 d-inline-block badge @if ($jobExprienceLeft < 0) badge-danger @elseif($jobExprienceLeft == 0) badge-warning @else badge-primary @endif badge-pill">{{ $jobExprienceLeft }}</span>
                            @else
                                <span class="mx-2 d-inline-block badge badge-success badge-pill">
                                    {{ __('unlimited') }}</span>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ __('Educations Left') }}
                            @if ($userCurrentPackage->number_of_education < 999999)
                                @if ($educationLeft == 0)
                                    <span
                                        class="mx-2 d-inline-block text-danger">{{ __('Limit is over') }}</span>
                                @elseif($educationLeft < 0)
                                    <span
                                        class="mx-2 d-inline-block text-danger">{{ __('Down Graded') }}</span>
                                @endif
                            @endif
                            @if ($userCurrentPackage->number_of_education < 999999)
                                <span
                                    class="mx-2 d-inline-block badge @if ($educationLeft < 0) badge-danger @elseif($educationLeft == 0) badge-warning @else badge-primary @endif badge-pill">{{ $educationLeft }}</span>
                            @else
                                <span class="mx-2 d-inline-block badge badge-success badge-pill">
                                    {{ __('unlimited') }}</span>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ __('Languages Left') }}
                            @if ($userCurrentPackage->number_of_languages < 999999)
                                @if ($languagesLeft == 0)
                                    <span
                                        class="mx-2 d-inline-block text-danger">{{__('Limit is over') }}</span>
                                @elseif($languagesLeft < 0)
                                    <span
                                        class="mx-2 d-inline-block text-danger">{{ __('Down Graded') }}</span>
                                @endif
                            @endif
                            @if ($userCurrentPackage->number_of_languages < 999999)
                                <span
                                    class="mx-2 d-inline-block badge @if ($languagesLeft < 0) badge-danger @elseif($languagesLeft == 0) badge-warning @else badge-primary @endif badge-pill">{{ $languagesLeft }}</span>
                            @else
                                <span class="mx-2 d-inline-block badge badge-success badge-pill">
                                    {{ __('unlimited') }}</span>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ __('vCards Left') }} @if ($userCurrentPackage->number_of_vcards < 999999)
                                @if ($vcardsLeft == 0)
                                    <span
                                        class=" mx-2 d-inline-block text-danger">{{ __('Limit is over') }}</span>
                                @elseif($vcardsLeft < 0)
                                    <span
                                        class=" mx-2 d-inline-blocktext-danger">{{ __('Down Graded') }}</span>
                                @endif
                            @endif
                            @if ($userCurrentPackage->number_of_vcards < 999999)
                                <span
                                    class="mx-2 d-inline-block badge @if ($vcardsLeft < 0) badge-danger @elseif($vcardsLeft == 0) badge-warning @else badge-primary @endif badge-pill">{{ $vcardsLeft }}</span>
                            @else
                                <span class="mx-2 d-inline-block badge badge-success badge-pill">
                                    {{ __('unlimited') }}</span>
                            @endif
                        </li>

                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-dismiss="modal">{{ __('Close') }}</button>

                </div>
            </div>
        </div>
    </div>
@endif
