@extends('admin.layout')
@if (Session::has('admin_lang'))
    @php
        $admin_lang = Session::get('admin_lang');
        $cd = str_replace('admin_', '', $admin_lang);
        $default = \App\Models\Language::where('code', $cd)->first();
    @endphp
@else
    @php
        $default = \App\Models\Language::where('is_default', 1)->first();
    @endphp
@endif
@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Settings') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') . '?language=' . $default->code }}">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('User Management') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Settings') }}</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card-title">{{ __('Update Settings') }}</div>
                        </div>
                    </div>
                </div>

                <div class="card-body pt-5 pb-5">
                    <div class="row">
                        <div class="col-lg-6 offset-lg-3">
                            <form id="registrationStatusForm"
                                action="{{ route('admin.register.user.settings.update', ['id' => $data->id]) }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <div class="col-12 mb-2">
                                                <label for="image"><strong>{{ __('Image') }}
                                                        **</strong></label>
                                            </div>
                                            <div class="col-md-12 showImage mb-3">
                                                <img src="{{ !empty($data->user_registration_deactive_img) ? asset('assets/front/img/' . $data->user_registration_deactive_img) : asset('assets/admin/img/noimage.jpg') }}"
                                                    alt="..." class="img-thumbnail" width="170">
                                            </div>
                                            <input type="file" name="image" id="image" class="form-control">
                                            @error('image')
                                                <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                {{-- <div class="form-group">
                                    <label for="">{{ __('Language') }} **</label>
                                    <select id="language" name="language_id" class="form-control">
                                        <option value="" selected disabled>{{ __('Select a Language') }}</option>
                                        @foreach ($languages as $lang)
                                            <option value="{{ $lang->id }}"
                                                {{ $language->id == $lang->id ? 'selected' : '' }}>{{ $lang->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('language_id')
                                        <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                                    @enderror
                                </div> --}}
                                <div class="form-group">
                                    <label>{{ __('User Login Attempts') }} *</label>

                                    <input type="text" class="form-control" name="user_login_attempts"
                                        value="{{ $data->user_login_attempts }}">
                                    @error('user_login_attempts')
                                        <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                                    @enderror
                                    <p class="text-warning mb-0">
                                        {{ __("User's account will be deactive after") .' '.$data->user_login_attempts .' '. __('failed login attempts') }}
                                    </p>

                                </div>
                                <div class="form-group">
                                    <label>{{ __('Countries') }}</label>
                                    <input class="form-control" name="user_registraion_countries"
                                        value="{{ $data->user_registraion_countries }}"
                                        placeholder="{{ __('Enter countries') }}" data-role="tagsinput">
                                </div>
                                <div class="form-group">
                                    <label>{{ __('User Registration Status') }} *</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="user_registraion_status" value="1"
                                                class="selectgroup-input"
                                                {{ $data->user_registraion_status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="user_registraion_status" value="0"
                                                class="selectgroup-input"
                                                {{ $data->user_registraion_status == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                    @error('user_registraion_status')
                                        <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Registration Deactive Text') }} *</label>
                                    <textarea class="form-control" name="user_registration_deactive_text" rows="3" cols="80">{{ $data->user_registration_deactive_text }}</textarea>
                                    @error('user_registration_deactive_text')
                                        <p class="mt-2 mb-0 text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="row">
                        <div class="col-12 text-center">
                            <button type="submit" form="registrationStatusForm" class="btn btn-success">
                                {{ __('Update') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
