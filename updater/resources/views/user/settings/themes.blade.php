@extends('user.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Home Page Version') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('user-dashboard') }}">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Home Page Version') }}</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card-title">{{ __('Home Settings') }}</div>
                        </div>
                    </div>
                </div>

                <div class="card-body pt-5 pb-5">
                    <div class="row">
                        <div class="col-lg-6 offset-lg-3">
                            <form id="ajaxForm" action="{{ route('user.theme.update') }}" method="post">
                                @csrf

                                <div class="form-group">
                                    <label class="form-label">{{ __('Themes') }} *</label>
                                    <div class="row">
                                        @php
                                            $profile = file_exists('core/resources/views/user/profile');
                                            $packages = App\Http\Helpers\UserPermissionHelper::currentPackagePermission(
                                                Auth::guard('web')->user()->id,
                                            );
                                            if (!empty($packages->themes)) {
                                                $themes = json_decode($packages->themes);
                                            }
                                        @endphp
                                        @if ($profile)
                                            <div class="col-6 col-sm-4 mb-2">
                                                <label class="imagecheck mb-2">
                                                    <input name="theme" type="radio" value="light"
                                                        class="imagecheck-input"
                                                        {{ !empty($data->theme) && $data->theme == 'light' ? 'checked' : '' }}>
                                                    <figure class="imagecheck-figure">
                                                        <img src="{{ asset('assets/front/img/user/themes/light_theme.png') }}"
                                                            alt="title" class="imagecheck-image">
                                                    </figure>
                                                </label>
                                                <h5 class="text-center">{{ __('Light Theme') }}
                                                </h5>
                                            </div>
                                            <div class="col-6 col-sm-4 mb-2">
                                                <label class="imagecheck mb-2">
                                                    <input name="theme" type="radio" value="dark"
                                                        class="imagecheck-input"
                                                        {{ !empty($data->theme) && $data->theme == 'dark' ? 'checked' : '' }}>
                                                    <figure class="imagecheck-figure">
                                                        <img src="{{ asset('assets/front/img/user/themes/dark_theme.png') }}"
                                                            alt="title" class="imagecheck-image">
                                                    </figure>
                                                </label>
                                                <h5 class="text-center">{{ __('Dark Theme') }}
                                                </h5>
                                            </div>
                                        @endif
                                        @if (is_array($themes) && in_array(1, $themes))
                                            <div class="col-6 col-sm-4 mb-2">
                                                <label class="imagecheck mb-2">
                                                    <input name="theme" type="radio" value="1"
                                                        class="imagecheck-input"
                                                        {{ !empty($data->theme) && $data->theme == 1 ? 'checked' : '' }}>
                                                    <figure class="imagecheck-figure">
                                                        <img src="{{ asset('assets/front/img/user/themes/theme1.jpg') }}"
                                                            alt="title" class="imagecheck-image">
                                                    </figure>
                                                </label>
                                                <h5 class="text-center">{{ __('Theme 1') }}</h5>
                                            </div>
                                        @endif
                                        @if (is_array($themes) && in_array(2, $themes))
                                            <div class="col-6 col-sm-4 mb-2">
                                                <label class="imagecheck mb-2">
                                                    <input name="theme" type="radio" value="2"
                                                        class="imagecheck-input"
                                                        {{ !empty($data->theme) && $data->theme == 2 ? 'checked' : '' }}>
                                                    <figure class="imagecheck-figure">
                                                        <img src="{{ asset('assets/front/img/user/themes/theme2.jpg') }}"
                                                            alt="title" class="imagecheck-image">
                                                    </figure>
                                                </label>
                                                <h5 class="text-center">{{ __('Theme 2') }}</h5>
                                            </div>
                                        @endif
                                        @if (is_array($themes) && in_array(3, $themes))
                                            <div class="col-6 col-sm-4 mb-2">
                                                <label class="imagecheck mb-2">
                                                    <input name="theme" type="radio" value="3"
                                                        class="imagecheck-input"
                                                        {{ !empty($data->theme) && $data->theme == 3 ? 'checked' : '' }}>
                                                    <figure class="imagecheck-figure">
                                                        <img src="{{ asset('assets/front/img/user/themes/theme3.jpg') }}"
                                                            alt="title" class="imagecheck-image">
                                                    </figure>
                                                </label>
                                                <h5 class="text-center">{{ __('Theme 3') }}</h5>
                                            </div>
                                        @endif
                                        @if (is_array($themes) && in_array(4, $themes))
                                            <div class="col-6 col-sm-4 mb-2">
                                                <label class="imagecheck mb-2">
                                                    <input name="theme" type="radio" value="4"
                                                        class="imagecheck-input"
                                                        {{ !empty($data->theme) && $data->theme == 4 ? 'checked' : '' }}>
                                                    <figure class="imagecheck-figure">
                                                        <img src="{{ asset('assets/front/img/user/themes/theme4.jpg') }}"
                                                            alt="title" class="imagecheck-image">
                                                    </figure>
                                                </label>
                                                <h5 class="text-center">{{ __('Theme 4') }}</h5>
                                            </div>
                                        @endif
                                        @if (is_array($themes) && in_array(5, $themes))
                                            <div class="col-6 col-sm-4 mb-2">
                                                <label class="imagecheck mb-2">
                                                    <input name="theme" type="radio" value="5"
                                                        class="imagecheck-input"
                                                        {{ !empty($data->theme) && $data->theme == 5 ? 'checked' : '' }}>
                                                    <figure class="imagecheck-figure">
                                                        <img src="{{ asset('assets/front/img/user/themes/theme5.jpg') }}"
                                                            alt="title" class="imagecheck-image">
                                                    </figure>
                                                </label>
                                                <h5 class="text-center">{{ __('Theme 5') }}</h5>
                                            </div>
                                        @endif
                                        @if (is_array($themes) && in_array(6, $themes))
                                            <div class="col-6 col-sm-4 mb-2">
                                                <label class="imagecheck mb-2">
                                                    <input name="theme" type="radio" value="6"
                                                        class="imagecheck-input"
                                                        {{ !empty($data->theme) && $data->theme == 6 ? 'checked' : '' }}>
                                                    <figure class="imagecheck-figure">
                                                        <img src="{{ asset('assets/front/img/user/themes/theme6.png') }}"
                                                            alt="title" class="imagecheck-image">
                                                    </figure>
                                                </label>
                                                <h5 class="text-center">{{ __('Theme 6') }}</h5>
                                            </div>
                                        @endif
                                        @if (is_array($themes) && in_array(7, $themes))
                                            <div class="col-6 col-sm-4 mb-2">
                                                <label class="imagecheck mb-2">
                                                    <input name="theme" type="radio" value="7"
                                                        class="imagecheck-input"
                                                        {{ !empty($data->theme) && $data->theme == 7 ? 'checked' : '' }}>
                                                    <figure class="imagecheck-figure">
                                                        <img src="{{ asset('assets/front/img/user/themes/theme7.png') }}"
                                                            alt="title" class="imagecheck-image">
                                                    </figure>
                                                </label>
                                                <h5 class="text-center">{{ __('Theme 7') }}</h5>
                                            </div>
                                        @endif
                                        @if (is_array($themes) && in_array(8, $themes))
                                            <div class="col-6 col-sm-4 mb-2">
                                                <label class="imagecheck mb-2">
                                                    <input name="theme" type="radio" value="8"
                                                        class="imagecheck-input"
                                                        {{ !empty($data->theme) && $data->theme == 8 ? 'checked' : '' }}>
                                                    <figure class="imagecheck-figure">
                                                        <img src="{{ asset('assets/front/img/user/themes/theme8.png') }}"
                                                            alt="title" class="imagecheck-image">
                                                    </figure>
                                                </label>
                                                <h5 class="text-center">{{ __('Theme 8') }}</h5>
                                            </div>
                                        @endif
                                        @if (is_array($themes) && in_array(9, $themes))
                                            <div class="col-6 col-sm-4 mb-2">
                                                <label class="imagecheck mb-2">
                                                    <input name="theme" type="radio" value="9"
                                                        class="imagecheck-input"
                                                        {{ !empty($data->theme) && $data->theme == 9 ? 'checked' : '' }}>
                                                    <figure class="imagecheck-figure">
                                                        <img src="{{ asset('assets/front/img/user/themes/theme9.png') }}"
                                                            alt="title" class="imagecheck-image">
                                                    </figure>
                                                </label>
                                                <h5 class="text-center">{{ __('Theme 9') }}</h5>
                                            </div>
                                        @endif
                                        @if (is_array($themes) && in_array(10, $themes))
                                            <div class="col-6 col-sm-4 mb-2">
                                                <label class="imagecheck mb-2">
                                                    <input name="theme" type="radio" value="10"
                                                        class="imagecheck-input"
                                                        {{ !empty($data->theme) && $data->theme == 10 ? 'checked' : '' }}>
                                                    <figure class="imagecheck-figure">
                                                        <img src="{{ asset('assets/front/img/user/themes/theme10.png') }}"
                                                            alt="title" class="imagecheck-image">
                                                    </figure>
                                                </label>
                                                <h5 class="text-center">{{ __('Theme 10') }}</h5>
                                            </div>
                                        @endif
                                        @if (is_array($themes) && in_array(11, $themes))
                                            <div class="col-6 col-sm-4 mb-2">
                                                <label class="imagecheck mb-2">
                                                    <input name="theme" type="radio" value="11"
                                                        class="imagecheck-input"
                                                        {{ !empty($data->theme) && $data->theme == 11 ? 'checked' : '' }}>
                                                    <figure class="imagecheck-figure">
                                                        <img src="{{ asset('assets/front/img/user/themes/theme11.png') }}"
                                                            alt="title" class="imagecheck-image">
                                                    </figure>
                                                </label>
                                                <h5 class="text-center">{{ __('Theme 11') }}</h5>
                                            </div>
                                        @endif
                                        @if (is_array($themes) && in_array(12, $themes))
                                            <div class="col-6 col-sm-4 mb-2">
                                                <label class="imagecheck mb-2">
                                                    <input name="theme" type="radio" value="12"
                                                        class="imagecheck-input"
                                                        {{ !empty($data->theme) && $data->theme == 12 ? 'checked' : '' }}>
                                                    <figure class="imagecheck-figure">
                                                        <img src="{{ asset('assets/front/img/user/themes/theme12.png') }}"
                                                            alt="title" class="imagecheck-image">
                                                    </figure>
                                                </label>
                                                <h5 class="text-center">{{ __('Theme 12') }}</h5>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="row">
                        <div class="col-12 text-center">
                            <button type="submit" data-form="ajaxForm" id=""
                                class="submitBtn btn btn-success">
                                {{ __('Update') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
