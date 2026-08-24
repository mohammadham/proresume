@extends('user.layout')

@includeIf('user.partials.rtl-style')
@php
    $permissions = \App\Http\Helpers\UserPermissionHelper::packagePermission(Auth::user()->id);
    $permissions = json_decode($permissions, true);
@endphp

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Home Sections') }}</h4>
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
                <a href="#">{{ __('Home Sections') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card-title d-inline-block">{{ __('Home Sections') }}
                            </div>
                        </div>
                        <div class="col-lg-3 offset-lg-3">
                            @includeIf('user.partials.language')
                        </div>

                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-10 mx-auto">
                            <form id="ajaxForm" class="" action="{{ route('user.home.page.text.update') }}"
                                method="post" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="id" value="{{ $home_setting->id }}">
                                <input type="hidden" name="language_id" value="{{ $home_setting->language_id }}">

                                <!--hereo section -->
                                <fieldset class="form-group border m-2 mb-5 border-secondary rounded">
                                    <legend class="w-auto px-2 h3 font-weight-bold text-warning">
                                        {{ __('Hero Section') }}
                                    </legend>
                                    <div class="row">
                                        <!--section image -->
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label
                                                    for="logo"><strong>{{ __('Hero Section Image') }}</strong></label>
                                                <div class="showHeroImage mb-3 show-image-div">
                                                    <img width="200"
                                                        src="{{ $home_setting->hero_image ? asset('assets/front/img/user/home_settings/' . $home_setting->hero_image) : asset('assets/admin/img/noimage.jpg') }}"
                                                        alt="..." class="img-thumbnail">
                                                    @isset($home_setting->hero_image)
                                                        <button class="btn btn-danger btn-sm image-cross-btn"
                                                            data-type="hero_image">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    @endisset
                                                </div>
                                                <input type="hidden" name="types[]" value="hero_image">
                                                <input type="file" name="hero_image" id="hero_image"
                                                    class="form-control ltr">
                                                <p id="errhero_image" class="mb-0 text-danger em"></p>
                                            </div>
                                        </div>

                                        @if ($userBs->theme == 9)
                                            <!--section background image -->
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <label
                                                        for="logo"><strong>{{ __('Hero Section Background Image') }}</strong></label>
                                                    <div class="showHeroBgImage mb-3 show-image-div">
                                                        <img width="200"
                                                            src="{{ $home_setting->hero_background_image ? asset('assets/front/img/user/home_settings/' . $home_setting->hero_background_image) : asset('assets/admin/img/noimage.jpg') }}"
                                                            alt="..." class="img-thumbnail">
                                                        @isset($home_setting->hero_background_image)
                                                            <button class="btn btn-danger btn-sm image-cross-btn"
                                                                data-type="hero_background_image">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        @endisset
                                                    </div>
                                                    <input type="hidden" name="types[]" value="hero_background_image">
                                                    <input type="file" name="hero_background_image"
                                                        id="hero_background_image" class="form-control ltr">
                                                    <p id="errhero_background_image" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                        @endif
                                        @if ($userBs->theme == 11)
                                            <!--section video image -->
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <label
                                                        for="logo"><strong>{{ __('Hero Section Video Image') }}</strong></label>
                                                    <div class="showHeroVideoImage mb-3 show-image-div">
                                                        <img width="200"
                                                            src="{{ $home_setting->hero_video_image ? asset('assets/front/img/user/home_settings/' . $home_setting->hero_video_image) : asset('assets/admin/img/noimage.jpg') }}"
                                                            alt="..." class="img-thumbnail">
                                                        @isset($home_setting->hero_video_image)
                                                            <button class="btn btn-danger btn-sm image-cross-btn"
                                                                data-type="hero_video_image">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        @endisset
                                                    </div>
                                                    <input type="hidden" name="types[]" value="hero_video_image">
                                                    <input type="file" name="hero_video_image" id="hero_video_image"
                                                        class="form-control ltr">
                                                    <p id="errhero_video_image" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($userBs->theme != 10 && $userBs->theme != 11 && $userBs->theme != 12)
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="">{{ __('First Name') }}</label>
                                                    <input type="hidden" name="types[]" value="first_name">
                                                    <input type="text" class="form-control" name="first_name"
                                                        placeholder="{{ __('First Name') }}"
                                                        value="{{ $home_setting->first_name }}">
                                                    <p id="errfirst_name" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="">{{ __('Last Name') }}</label>
                                                    <input type="hidden" name="types[]" value="last_name">
                                                    <input type="text" class="form-control" name="last_name"
                                                        placeholder="{{ __('Last Name') }}"
                                                        value="{{ $home_setting->last_name }}">
                                                    <p id="errlast_name" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                        @endif
                                        @if ($userBs->theme != 9 && $userBs->theme != 10 && $userBs->theme != 11 && $userBs->theme != 12)
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <label for="">{{ __('Designation') }}**</label>
                                                    <input type="hidden" name="types[]" value="designation">
                                                    <input type="text" class="form-control" name="designation"
                                                        placeholder="{{ __('Enter designations') }}"
                                                        value="{{ $home_setting->designation }}" data-role="tagsinput">
                                                    <p class="text-warning mb-0">
                                                        {{ __('use comma (,) to add multiple designations') }}
                                                    </p>
                                                    <p id="errdesignation" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                        @endif
                                        @if ($userBs->theme == 10)
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <label for="">{{ __('Hero Section Title') }}</label>
                                                    <input type="hidden" name="types[]" value="hero_title">
                                                    <input type="text" class="form-control" name="hero_title"
                                                        placeholder="{{ __('Hero Section Title') }}"
                                                        value="{{ $home_setting->hero_title }}">
                                                    <p id="errhero_title" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                        @endif
                                        @if ($userBs->theme == 9 || $userBs->theme == 10)
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <label for="">{{ __('Designation Text') }}</label>
                                                    <input type="hidden" name="types[]" value="designation">
                                                    <textarea name="designation" class="form-control" rows="3">{{ $home_setting->designation }}</textarea>
                                                    <p id="errdesignation" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($userBs->theme == 9 || $userBs->theme == 10 || $userBs->theme == 12)
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="">{{ __('Button Name') }}</label>
                                                    <input type="hidden" name="types[]" value="hero_button_name">
                                                    <input type="text" class="form-control" name="hero_button_name"
                                                        value="{{ $home_setting->hero_button_name }}">
                                                    <p id="errhero_button_name" class="mb-0 text-danger em"></p>
                                                    @if ($userBs->theme == 9)
                                                        <p class="mb-0">
                                                            <span
                                                                class="text-warning">{{ __('wrap the text with a') }}</span>
                                                            <span
                                                                class="font-weight-bold text-warning">&lt;span&gt;&lt;/span&gt;</span>
                                                            <span
                                                                class="text-warning">{{ __('tag to style it like') }}</span>
                                                            <a href="https://prnt.sc/L5zXr3QUd7pV" target="_blank"
                                                                class="text-decoration-underline">
                                                                {{ __('this screenshot') }}
                                                            </a>.
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="">{{ __('Button Url') }}</label>
                                                    <input type="hidden" name="types[]" value="hero_button_url">
                                                    <input type="text" class="form-control" name="hero_button_url"
                                                        value="{{ $home_setting->hero_button_url }}">
                                                    <p id="errhero_button_url" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                        @endif


                                        @if ($userBs->theme == 10 || $userBs->theme == 12)
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="">
                                                        @if ($userBs->theme == 12)
                                                            {{ __('Experience Text') }}
                                                        @else
                                                            {{ __('Rating Text') }}
                                                        @endif
                                                    </label>
                                                    <input type="hidden" name="types[]" value="hero_rating_text">
                                                    <input type="text" class="form-control" name="hero_rating_text"
                                                        value="{{ $home_setting->hero_rating_text }}">
                                                    <p id="errhero_rating_text" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="">{{ __('Experience') }}</label>
                                                    <input type="hidden" name="types[]" value="hero_experience_text">
                                                    <input type="text" class="form-control"
                                                        name="hero_experience_text"
                                                        value="{{ $home_setting->hero_experience_text }}">
                                                    <p id="errhero_experience_text" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($userBs->theme == 11 || $userBs->theme == 12)
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="">{{ __('Hero Section Title') }}</label>
                                                    <input type="hidden" name="types[]" value="hero_section_title">
                                                    <input type="text" class="form-control" name="hero_section_title"
                                                        value="{{ $home_setting->hero_section_title }}">
                                                    <p id="errhero_section_title" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="">{{ __('Hero Section Subtitle') }}</label>
                                                    <input type="hidden" name="types[]" value="hero_section_subtitle">
                                                    <input type="text" class="form-control"
                                                        name="hero_section_subtitle"
                                                        value="{{ $home_setting->hero_section_subtitle }}">
                                                    <p id="errhero_section_subtitle" class="mb-0 text-danger em"></p>
                                                    @if ($userBs->theme == 11)
                                                        <p class="mb-0">
                                                            <span
                                                                class="text-warning">{{ __('wrap the text with a') }}</span>
                                                            <span
                                                                class="font-weight-bold text-warning">&lt;span&gt;&lt;/span&gt;</span>
                                                            <span
                                                                class="text-warning">{{ __('tag to style it like') }}</span>
                                                            <a href="https://prnt.sc/8ykHqZknc8a5" target="_blank"
                                                                class="text-decoration-underline">
                                                                {{ __('this screenshot') }}
                                                            </a>.
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                        @if ($userBs->theme == 11)
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="">{{ __('Hero Section Video Title') }}</label>
                                                    <input type="hidden" name="types[]" value="hero_section_vtitle">
                                                    <input type="text" class="form-control" name="hero_section_vtitle"
                                                        value="{{ $home_setting->hero_section_vtitle }}">
                                                    <p id="errhero_section_vtitle" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="">{{ __('Hero Section Video Subtitle') }}</label>
                                                    <input type="hidden" name="types[]" value="hero_section_vsubtitle">
                                                    <input type="text" class="form-control"
                                                        name="hero_section_vsubtitle"
                                                        value="{{ $home_setting->hero_section_vsubtitle }}">
                                                    <p id="errhero_section_vsubtitle" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="">{{ __('Hero Section Video Url') }}</label>
                                                    <input type="hidden" name="types[]" value="hero_section_vurl">
                                                    <input type="text" class="form-control" name="hero_section_vurl"
                                                        value="{{ $home_setting->hero_section_vurl }}">
                                                    <p id="errhero_section_vurl" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                        @endif

                                    </div>
                                </fieldset>

                                <!--workprocess section-->
                                @php
                                    $allowWorkProcess = [9];
                                @endphp
                                @if (in_array($userBs->theme, $allowWorkProcess))
                                    <fieldset class="form-group border m-2 mb-5 border-secondary rounded">
                                        <legend class="w-auto px-2 h3 font-weight-bold text-warning">
                                            {{ __('Work Process Section') }}
                                        </legend>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <label for="">{{ __('Work Process Section Title') }}</label>
                                                    <input type="hidden" name="types[]" value="work_process_title">
                                                    <input type="text" class="form-control" name="work_process_title"
                                                        value="{{ $home_setting->work_process_title }}">
                                                    <p id="errwork_process_title" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                @endif

                                <!--about section-->
                                <fieldset class="form-group border m-2 mb-5 border-secondary rounded">
                                    <legend class="w-auto px-2 h3 font-weight-bold text-warning">
                                        {{ __('About Section') }}
                                    </legend>
                                    <div class="row">
                                        @if ($userBs->theme != 11)
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <label
                                                        for="logo"><strong>{{ __('About Section Image') }}</strong></label>
                                                    <div class="showAboutImage mb-3 show-image-div">
                                                        <img width="200"
                                                            src="{{ $home_setting->about_image ? asset('assets/front/img/user/home_settings/' . $home_setting->about_image) : asset('assets/admin/img/noimage.jpg') }}"
                                                            alt="..." class="img-thumbnail">
                                                        @isset($home_setting->about_image)
                                                            <button class="btn btn-danger btn-sm image-cross-btn"
                                                                data-type="about_image">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        @endisset
                                                    </div>
                                                    <input type="hidden" name="types[]" value="about_image">
                                                    <input type="file" name="about_image" id="about_image"
                                                        class="form-control ltr">
                                                    <p id="errabout_image" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                        @endif
                                        @if ($userBs->theme == 11)
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <label
                                                        for="logo"><strong>{{ __('About Section Left Image') }}</strong></label>
                                                    <div class="showAboutLeftImage mb-3 show-image-div">
                                                        <img width="200"
                                                            src="{{ $home_setting->about_left_image ? asset('assets/front/img/user/home_settings/' . $home_setting->about_left_image) : asset('assets/admin/img/noimage.jpg') }}"
                                                            alt="..." class="img-thumbnail">
                                                        @isset($home_setting->about_left_image)
                                                            <button class="btn btn-danger btn-sm image-cross-btn"
                                                                data-type="about_left_image">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        @endisset
                                                    </div>
                                                    <input type="hidden" name="types[]" value="about_left_image">
                                                    <input type="file" name="about_left_image" id="about_left_image"
                                                        class="form-control ltr">
                                                    <p id="errabout_left_image" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <label
                                                        for="logo"><strong>{{ __('About Section Right Image') }}</strong></label>
                                                    <div class="showAboutRightImage mb-3 show-image-div">
                                                        <img width="200"
                                                            src="{{ $home_setting->about_right_image ? asset('assets/front/img/user/home_settings/' . $home_setting->about_right_image) : asset('assets/admin/img/noimage.jpg') }}"
                                                            alt="..." class="img-thumbnail">
                                                        @isset($home_setting->about_right_image)
                                                            <button class="btn btn-danger btn-sm image-cross-btn"
                                                                data-type="about_right_image">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        @endisset
                                                    </div>
                                                    <input type="hidden" name="types[]" value="about_right_image">
                                                    <input type="file" name="about_right_image" id="about_right_image"
                                                        class="form-control ltr">
                                                    <p id="errabout_right_image" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>

                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="">{{ __('Experience Text') }}
                                                    </label>
                                                    <input type="hidden" name="types[]" value="about_experience_text">
                                                    <input type="text" class="form-control" name="about_experience_text"
                                                        placeholder="" value="{{ $home_setting->about_experience_text }}">
                                                    <p id="errabout_experience_text" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="">{{ __('About Section Title') }}
                                                </label>
                                                <input type="hidden" name="types[]" value="about_title">
                                                <input type="text" class="form-control" name="about_title"
                                                    placeholder="" value="{{ $home_setting->about_title }}">
                                                <p id="errabout_title" class="mb-0 text-danger em"></p>
                                            </div>
                                        </div>
                                        @if ($userBs->theme != 11)
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="">{{ __('About Section Subtitle') }}
                                                    </label>
                                                    <input type="hidden" name="types[]" value="about_subtitle">
                                                    <input type="text" class="form-control" name="about_subtitle"
                                                        placeholder="" value="{{ $home_setting->about_subtitle }}">
                                                    <p id="errabout_subtitle" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                        @endif
                                        @if ($userBs->theme == 10 || $userBs->theme == 11 || $userBs->theme == 12)
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="">{{ __('Button Name') }}</label>
                                                    <input type="hidden" name="types[]" value="about_button_name">
                                                    <input type="text" class="form-control" name="about_button_name"
                                                        value="{{ $home_setting->about_button_name }}">
                                                    <p id="errabout_button_name" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="">{{ __('Button Url') }}</label>
                                                    <input type="hidden" name="types[]" value="about_button_url">
                                                    <input type="text" class="form-control" name="about_button_url"
                                                        value="{{ $home_setting->about_button_url }}">
                                                    <p id="errabout_button_url" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                            @if ($userBs->theme == 10)
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="">{{ __('Video Url') }}</label>
                                                        <input type="hidden" name="types[]" value="about_video_url">
                                                        <input type="text" class="form-control" name="about_video_url"
                                                            value="{{ $home_setting->about_video_url }}">
                                                        <p id="errabout_video_url" class="mb-0 text-danger em"></p>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="">{{ __('Video Button Text') }}</label>
                                                        <input type="hidden" name="types[]" value="about_video_text">
                                                        <input type="text" class="form-control"
                                                            name="about_video_text"
                                                            value="{{ $home_setting->about_video_text }}">
                                                        <p id="errabout_video_text" class="mb-0 text-danger em"></p>
                                                    </div>
                                                </div>
                                            @endif
                                        @endif

                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label for="">{{ __('About Section Content') }}
                                                </label>
                                                <input type="hidden" name="types[]" value="about_content">
                                                <textarea class="form-control" name="about_content" rows="5">{{ $home_setting->about_content }}</textarea>
                                                <p id="errabout_content" class="mb-0 text-danger em"></p>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>

                                <!--skills section-->
                                @php
                                    $allowSkills = [1, 2, 4, 5, 6, 7, 8];
                                @endphp
                                @if (in_array($userBs->theme, $allowSkills))
                                    @if (!empty($permissions) && in_array('Skill', $permissions))
                                        <fieldset class="form-group border m-2 mb-5 border-secondary rounded">
                                            <legend class="w-auto px-2 h3 font-weight-bold text-warning">
                                                {{ __('Skills Section') }}
                                            </legend>
                                            <div class="row">
                                                @if ($userBs->theme != 6 && $userBs->theme != 7)
                                                    <div class="col-lg-12">
                                                        <div class="form-group">
                                                            <label
                                                                for="logo"><strong>{{ __('Skills Image') }}</strong></label>
                                                            <div class="showSkillsImage mb-3 show-image-div">
                                                                <img width="200"
                                                                    src="{{ $home_setting->skills_image ? asset('assets/front/img/user/home_settings/' . $home_setting->skills_image) : asset('assets/admin/img/noimage.jpg') }}"
                                                                    alt="..." class="img-thumbnail">
                                                                @isset($home_setting->skills_image)
                                                                    <button class="btn btn-danger btn-sm image-cross-btn"
                                                                        data-type="skills_image">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                @endisset
                                                            </div>
                                                            <input type="hidden" name="types[]" value="skills_image">
                                                            <input type="file" name="skills_image" id="skills_image"
                                                                class="form-control ltr">
                                                            <p id="errskills_image" class="mb-0 text-danger em"></p>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="">{{ __('Skills Section Title') }}</label>
                                                        <input type="hidden" name="types[]" value="skills_title">
                                                        <input type="text" class="form-control" name="skills_title"
                                                            placeholder="" value="{{ $home_setting->skills_title }}">
                                                        <p id="errskills_title" class="mb-0 text-danger em"></p>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="">{{ __('Skills Section Subtitle') }}</label>
                                                        <input type="hidden" name="types[]" value="skills_subtitle">
                                                        <input type="text" class="form-control" name="skills_subtitle"
                                                            placeholder="" value="{{ $home_setting->skills_subtitle }}">
                                                        <p id="errskills_subtitle" class="mb-0 text-danger em">
                                                        </p>
                                                    </div>
                                                </div>
                                                @if ($userBs->theme != 6 && $userBs->theme != 7 && $userBs->theme != 8)
                                                    <div class="col-lg-12">
                                                        <div class="form-group">
                                                            <label
                                                                for="">{{ __('Skills Section Content') }}</label>
                                                            <input type="hidden" name="types[]" value="skills_content">
                                                            <textarea class="form-control" name="skills_content" rows="5" placeholder="">{{ $home_setting->skills_content }}</textarea>
                                                            <p id="errskills_content" class="mb-0 text-danger em"></p>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </fieldset>
                                    @endif
                                @endif

                                <!--services section-->
                                @if (!empty($permissions) && in_array('Service', $permissions))
                                    <fieldset class="form-group border m-2 mb-5 border-secondary rounded">
                                        <legend class="w-auto px-2 h3 font-weight-bold text-warning">
                                            {{ __('Service Section') }}
                                        </legend>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="">{{ __('Service Section Title') }}</label>
                                                    <input type="hidden" name="types[]" value="service_title">
                                                    <input type="text" class="form-control" name="service_title"
                                                        placeholder="" value="{{ $home_setting->service_title }}">
                                                    <p id="errservice_title" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                            @if ($userBs->theme != 9 && $userBs->theme != 11)
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="">{{ __('Service Section Subtitle') }}</label>
                                                        <input type="hidden" name="types[]" value="service_subtitle">
                                                        <input type="text" class="form-control"
                                                            name="service_subtitle" placeholder=""
                                                            value="{{ $home_setting->service_subtitle }}">
                                                        <p id="errservice_subtitle" class="mb-0 text-danger em"></p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </fieldset>
                                @endif

                                <!--appointment section-->
                                @php
                                    $allowAppointment = [10, 11, 12];
                                @endphp
                                @if (in_array($userBs->theme, $allowAppointment))
                                    @if (!empty($permissions) && in_array('Service', $permissions))
                                        <fieldset class="form-group border m-2 mb-5 border-secondary rounded">
                                            <legend class="w-auto px-2 h3 font-weight-bold text-warning">
                                                {{ __('Appointment Section') }}
                                            </legend>
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label
                                                            for="">{{ __('Appointment Section Title') }}</label>
                                                        <input type="hidden" name="types[]" value="appointment_title">
                                                        <input type="text" class="form-control"
                                                            name="appointment_title" placeholder=""
                                                            value="{{ $home_setting->appointment_title }}">
                                                        <p id="errappointment_title" class="mb-0 text-danger em"></p>
                                                    </div>
                                                </div>
                                                @if ($userBs->theme != 11)
                                                    <div class="col-lg-6">
                                                        <div class="form-group">
                                                            <label
                                                                for="">{{ __('Appointment Section Subtitle') }}</label>
                                                            <input type="hidden" name="types[]"
                                                                value="appointment_subtitle">
                                                            <input type="text" class="form-control"
                                                                name="appointment_subtitle" placeholder=""
                                                                value="{{ $home_setting->appointment_subtitle }}">
                                                            <p id="errappointment_subtitle" class="mb-0 text-danger em">
                                                            </p>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </fieldset>
                                    @endif
                                @endif


                                <!--features section -->
                                @php
                                    $allowFeatures = [10];
                                @endphp
                                @if (in_array($userBs->theme, $allowFeatures))
                                    <fieldset class="form-group border m-2 mb-5 border-secondary rounded">
                                        <legend class="w-auto px-2 h3 font-weight-bold text-warning">
                                            {{ __('Features Section') }}
                                        </legend>
                                        <div class="row">
                                            <!--section image -->
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <label
                                                        for="logo"><strong>{{ __('Features Section Image') }}</strong></label>
                                                    <div class="showFeaturesImage mb-3 show-image-div">
                                                        <img width="200"
                                                            src="{{ $home_setting->features_image ? asset('assets/front/img/user/home_settings/' . $home_setting->features_image) : asset('assets/admin/img/noimage.jpg') }}"
                                                            alt="..." class="img-thumbnail">
                                                        @isset($home_setting->features_image)
                                                            <button class="btn btn-danger btn-sm image-cross-btn"
                                                                data-type="features_image">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        @endisset
                                                    </div>
                                                    <input type="hidden" name="types[]" value="features_image">
                                                    <input type="file" name="features_image" id="features_image"
                                                        class="form-control ltr">
                                                    <p id="errfeatures_image" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>

                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <label for="">{{ __('Features Image Title') }}</label>
                                                    <input type="hidden" name="types[]" value="features_image_title">
                                                    <input type="text" class="form-control"
                                                        name="features_image_title"
                                                        value="{{ $home_setting->features_image_title }}">
                                                    <p id="errfeatures_image_title" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="">{{ __('Features Section Title') }}</label>
                                                    <input type="hidden" name="types[]" value="features_title">
                                                    <input type="text" class="form-control" name="features_title"
                                                        value="{{ $home_setting->features_title }}">
                                                    <p id="errfeatures_title" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="">{{ __('Features Section Subtitle') }}</label>
                                                    <input type="hidden" name="types[]" value="features_subtitle">
                                                    <input type="text" class="form-control" name="features_subtitle"
                                                        value="{{ $home_setting->features_subtitle }}">
                                                    <p id="errfeatures_subtitle" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="">{{ __('Button Name') }}</label>
                                                    <input type="hidden" name="types[]" value="features_button_name">
                                                    <input type="text" class="form-control"
                                                        name="features_button_name"
                                                        value="{{ $home_setting->features_button_name }}">
                                                    <p id="errfeatures_button_name" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="">{{ __('Button Url') }}</label>
                                                    <input type="hidden" name="types[]" value="features_button_url">
                                                    <input type="text" class="form-control" name="features_button_url"
                                                        value="{{ $home_setting->features_button_url }}">
                                                    <p id="errfeatures_button_url" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                @endif

                                <!--experience section-->
                                @php
                                    $allowExperience = [1, 2, 3, 4, 5, 8, 9, 10, 11, 12];
                                @endphp
                                @if (in_array($userBs->theme, $allowExperience))
                                    @if (!empty($permissions) && in_array('Experience', $permissions))
                                        <fieldset class="form-group border m-2 mb-5 border-secondary rounded">
                                            <legend class="w-auto px-2 h3 font-weight-bold text-warning">
                                                {{ __('Experience Section') }}
                                            </legend>
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label
                                                            for="">{{ __('Experience Section Title') }}</label>
                                                        <input type="hidden" name="types[]" value="experience_title">
                                                        <input type="text" class="form-control"
                                                            name="experience_title" placeholder=""
                                                            value="{{ $home_setting->experience_title }}">
                                                        <p id="errexperience_title" class="mb-0 text-danger em">
                                                        </p>
                                                    </div>
                                                </div>
                                                @if ($userBs->theme != 9)
                                                    <div class="col-lg-6">
                                                        <div class="form-group">
                                                            <label
                                                                for="">{{ __('Experience Section Subtitle') }}</label>
                                                            <input type="hidden" name="types[]"
                                                                value="experience_subtitle">
                                                            <input type="text" class="form-control"
                                                                name="experience_subtitle" placeholder=""
                                                                value="{{ $home_setting->experience_subtitle }}">
                                                            <p id="errexperience_subtitle" class="mb-0 text-danger em">
                                                            </p>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </fieldset>
                                    @endif
                                @endif

                                <!--achievements section-->
                                @php
                                    $allowAchievements = [1, 2, 4, 5, 9, 12];
                                @endphp
                                @if (in_array($userBs->theme, $allowAchievements))
                                    @if (!empty($permissions) && in_array('Achievements', $permissions))
                                        <fieldset class="form-group border m-2 mb-5 border-secondary rounded">
                                            <legend class="w-auto px-2 h3 font-weight-bold text-warning">
                                                {{ __('Achievements Section') }}
                                            </legend>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="form-group">
                                                        <label
                                                            for="logo"><strong>{{ __('Achievements Image') }}</strong></label>
                                                        <div class="showAchievementImage mb-3 show-image-div">
                                                            <img width="200"
                                                                src="{{ $home_setting->achievement_image ? asset('assets/front/img/user/home_settings/' . $home_setting->achievement_image) : asset('assets/admin/img/noimage.jpg') }}"
                                                                alt="..." class="img-thumbnail">
                                                            @isset($home_setting->achievement_image)
                                                                <button class="btn btn-danger btn-sm image-cross-btn"
                                                                    data-type="achievement_image">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            @endisset
                                                        </div>
                                                        <input type="hidden" name="types[]" value="achievement_image">
                                                        <input type="file" name="achievement_image"
                                                            id="achievement_image" class="form-control ltr">
                                                        <p id="errachievement_image" class="mb-0 text-danger em"></p>
                                                    </div>
                                                </div>
                                                @if ($userBs->theme != 9 && $userBs->theme != 12)
                                                    <div class="col-lg-6">
                                                        <div class="form-group">
                                                            <label
                                                                for="">{{ __('Achievement Section Title') }}</label>
                                                            <input type="hidden" name="types[]"
                                                                value="achievement_title">
                                                            <input type="text" class="form-control"
                                                                name="achievement_title" placeholder=""
                                                                value="{{ $home_setting->achievement_title }}">
                                                            <p id="errachievement_title" class="mb-0 text-danger em"></p>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="form-group">
                                                            <label
                                                                for="">{{ __('Achievement Section Subtitle') }}</label>
                                                            <input type="hidden" name="types[]"
                                                                value="achievement_subtitle">
                                                            <input type="text" class="form-control"
                                                                name="achievement_subtitle" placeholder=""
                                                                value="{{ $home_setting->achievement_subtitle }}">
                                                            <p id="errachievement_subtitle" class="mb-0 text-danger em">
                                                            </p>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </fieldset>
                                    @endif
                                @endif

                                <!--portfolio section-->
                                @php
                                    $allowPortfolio = [1, 2, 3, 4, 5, 6, 7, 8, 9, 12];
                                @endphp
                                @if (in_array($userBs->theme, $allowPortfolio))
                                    @if (!empty($permissions) && in_array('Portfolio', $permissions))
                                        <fieldset class="form-group border m-2 mb-5 border-secondary rounded">
                                            <legend class="w-auto px-2 h3 font-weight-bold text-warning">
                                                {{ __('Portfolio Section') }}
                                            </legend>
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="">{{ __('Portfolio Section Title') }}</label>
                                                        <input type="hidden" name="types[]" value="portfolio_title">
                                                        <input type="text" class="form-control" name="portfolio_title"
                                                            placeholder="" value="{{ $home_setting->portfolio_title }}">
                                                        <p id="errportfolio_title" class="mb-0 text-danger em"></p>
                                                    </div>
                                                </div>
                                                @if ($userBs->theme != 9)
                                                    <div class="col-lg-6">
                                                        <div class="form-group">
                                                            <label
                                                                for="">{{ __('Portfolio Section Subtitle') }}</label>
                                                            <input type="hidden" name="types[]"
                                                                value="portfolio_subtitle">
                                                            <input type="text" class="form-control"
                                                                name="portfolio_subtitle" placeholder=""
                                                                value="{{ $home_setting->portfolio_subtitle }}">
                                                            <p id="errportfolio_subtitle" class="mb-0 text-danger em"></p>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </fieldset>
                                    @endif
                                @endif

                                <!--gallery section-->
                                @if (!empty($permissions) && in_array('Gallery', $permissions))
                                    @if ($userBs->theme == 11)
                                        <fieldset class="form-group border m-2 mb-5 border-secondary rounded">
                                            <legend class="w-auto px-2 h3 font-weight-bold text-warning">
                                                {{ __('Gallery Section') }}
                                            </legend>
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="">{{ __('Gallery Section Title') }}</label>
                                                        <input type="hidden" name="types[]" value="gallery_title">
                                                        <input type="text" class="form-control" name="gallery_title"
                                                            placeholder="" value="{{ $home_setting->gallery_title }}">
                                                        <p id="errgallery_title" class="mb-0 text-danger em"></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </fieldset>
                                    @endif
                                @endif

                                <!--testimonial section-->
                                @if (!empty($permissions) && in_array('Testimonial', $permissions))
                                    <fieldset class="form-group border m-2 mb-5 border-secondary rounded">
                                        <legend class="w-auto px-2 h3 font-weight-bold text-warning">
                                            {{ __('Testimonial Section') }}
                                        </legend>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="">{{ __('Testimonial Section Title') }}</label>
                                                    <input type="hidden" name="types[]" value="testimonial_title">
                                                    <input type="text" class="form-control" name="testimonial_title"
                                                        placeholder="" value="{{ $home_setting->testimonial_title }}">
                                                    <p id="errtestimonial_title" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                            @if ($userBs->theme != 9 && $userBs->theme != 11)
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label
                                                            for="">{{ __('Testimonial Section Subtitle') }}</label>
                                                        <input type="hidden" name="types[]"
                                                            value="testimonial_subtitle">
                                                        <input type="text" class="form-control"
                                                            name="testimonial_subtitle" placeholder=""
                                                            value="{{ $home_setting->testimonial_subtitle }}">
                                                        <p id="errtestimonial_subtitle" class="mb-0 text-danger em">
                                                        </p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </fieldset>
                                @endif

                                <!--blog section-->
                                @php
                                    $allowBlog = [1, 2, 3, 4, 5, 6, 7, 8, 10, 12];
                                @endphp
                                @if (in_array($userBs->theme, $allowBlog))
                                    @if (!empty($permissions) && in_array('Blog', $permissions))
                                        <fieldset class="form-group border m-2 mb-5 border-secondary rounded">
                                            <legend class="w-auto px-2 h3 font-weight-bold text-warning">
                                                {{ __('Blog Section') }}
                                            </legend>
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="">{{ __('Blog Section Title') }}</label>
                                                        <input type="hidden" name="types[]" value="blog_title">
                                                        <input type="text" class="form-control" name="blog_title"
                                                            placeholder="" value="{{ $home_setting->blog_title }}">
                                                        <p id="errblog_title" class="mb-0 text-danger em"></p>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="">{{ __('Blog Section Subtitle') }}</label>
                                                        <input type="hidden" name="types[]" value="blog_subtitle">
                                                        <input type="text" class="form-control" name="blog_subtitle"
                                                            placeholder="" value="{{ $home_setting->blog_subtitle }}">
                                                        <p id="errblog_subtitle" class="mb-0 text-danger em"></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </fieldset>
                                    @endif
                                @endif

                                <!--contact section-->
                                @php
                                    $allowContact = [1, 2, 3, 4, 5, 6, 7, 8];
                                @endphp
                                @if (in_array($userBs->theme, $allowContact))
                                    <fieldset class="form-group border m-2 mb-5 border-secondary rounded">
                                        <legend class="w-auto px-2 h3 font-weight-bold text-warning">
                                            {{ __('Contact Section') }}
                                        </legend>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="">{{ __('Contact Section Title') }}</label>
                                                    <input type="hidden" name="types[]" value="contact_title">
                                                    <input type="text" class="form-control" name="contact_title"
                                                        placeholder="" value="{{ $home_setting->contact_title }}">
                                                    <p id="errcontact_title" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="">{{ __('Contact Section Subtitle') }}</label>
                                                    <input type="hidden" name="types[]" value="contact_subtitle">
                                                    <input type="text" class="form-control" name="contact_subtitle"
                                                        placeholder="" value="{{ $home_setting->contact_subtitle }}">
                                                    <p id="errcontact_subtitle" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                @endif

                                <!--call to action section-->
                                @if ($userBs->theme == 9)
                                    <fieldset class="form-group border m-2 mb-5 border-secondary rounded">
                                        <legend class="w-auto px-2 h3 font-weight-bold text-warning">
                                            {{ __('Call to Action Section') }}
                                        </legend>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <label
                                                        for="logo"><strong>{{ __('Call to Action Section Background Image') }}</strong></label>
                                                    <div class="showCallSectionBgImage mb-3 show-image-div">
                                                        <img width="200"
                                                            src="{{ $home_setting->call_to_action_bg_image ? asset('assets/front/img/user/home_settings/' . $home_setting->call_to_action_bg_image) : asset('assets/admin/img/noimage.jpg') }}"
                                                            alt="..." class="img-thumbnail">
                                                        @isset($home_setting->call_to_action_bg_image)
                                                            <button class="btn btn-danger btn-sm image-cross-btn"
                                                                data-type="call_to_action_bg_image">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        @endisset
                                                    </div>
                                                    <input type="hidden" name="types[]" value="call_to_action_bg_image">
                                                    <input type="file" name="call_to_action_bg_image"
                                                        id="call_to_action_bg_image" class="form-control ltr">
                                                    <p id="errcall_to_action_bg_image" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>

                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <label
                                                        for="logo"><strong>{{ __('Call to Action Section Image') }}</strong></label>
                                                    <div class="showCallSectionImage mb-3 show-image-div">
                                                        <img width="200"
                                                            src="{{ $home_setting->call_to_action_image ? asset('assets/front/img/user/home_settings/' . $home_setting->call_to_action_image) : asset('assets/admin/img/noimage.jpg') }}"
                                                            alt="..." class="img-thumbnail">
                                                        @isset($home_setting->call_to_action_image)
                                                            <button class="btn btn-danger btn-sm image-cross-btn"
                                                                data-type="call_to_action_image">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        @endisset
                                                    </div>
                                                    <input type="hidden" name="types[]" value="call_to_action_image">
                                                    <input type="file" name="call_to_action_image"
                                                        id="call_to_action_image" class="form-control ltr">
                                                    <p id="errcall_to_action_image" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>

                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <label
                                                        for="">{{ __('Call to Action Section Title') }}</label>
                                                    <input type="hidden" name="types[]" value="call_to_action_title">
                                                    <input type="text" class="form-control"
                                                        name="call_to_action_title"
                                                        value="{{ $home_setting->call_to_action_title }}">
                                                    <p id="errcall_to_action_title" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label
                                                        for="">{{ __('Call to Action Section Button Name') }}</label>
                                                    <input type="hidden" name="types[]"
                                                        value="call_to_action_button_name">
                                                    <input type="text" class="form-control"
                                                        name="call_to_action_button_name"
                                                        value="{{ $home_setting->call_to_action_button_name }}">
                                                    <p id="errcall_to_action_button_name" class="mb-0 text-danger em"></p>
                                                    @if ($userBs->theme == 9)
                                                        <p class="mb-0">
                                                            <span
                                                                class="text-warning">{{ __('wrap the text with a') }}</span>
                                                            <span
                                                                class="font-weight-bold text-warning">&lt;span&gt;&lt;/span&gt;</span>
                                                            <span
                                                                class="text-warning">{{ __('tag to style it like') }}</span>
                                                            <a href="https://prnt.sc/L5zXr3QUd7pV" target="_blank"
                                                                class="text-decoration-underline">
                                                                {{ __('this screenshot') }}
                                                            </a>.
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label
                                                        for="">{{ __('Call to Action Section button url') }}</label>
                                                    <input type="hidden" name="types[]"
                                                        value="call_to_action_button_url">
                                                    <input type="text" class="form-control"
                                                        name="call_to_action_button_url"
                                                        value="{{ $home_setting->call_to_action_button_url }}">
                                                    <p id="errcall_to_action_button_url" class="mb-0 text-danger em"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="form">
                        <div class="form-group from-show-notify row">
                            <div class="col-12 text-center">
                                <button data-form="ajaxForm" type="submit" id=""
                                    class="submitBtn btn btn-success">{{ __('Update') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection

@section('scripts')
    <script>
        var imageRemoveRoute = "{{ route('user.home.image.remove') }}";
        var userId = {{ Auth::user()->id }};
        var langId = {{ $language->id }};
    </script>
    <script src="{{ asset('assets/admin/js/home-sections.js') }}"></script>
@endsection
