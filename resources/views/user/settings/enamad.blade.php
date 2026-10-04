@extends('user.layout')

@section('content')
<div class="page-header">
    <h4 class="page-title">{{ __('Enamad Settings') }}</h4>
    <ul class="breadcrumbs">
        <li class="nav-home"><a href="{{route('user-dashboard')}}"><i class="flaticon-home"></i></a></li>
        <li class="separator"><i class="flaticon-right-arrow"></i></li>
        <li class="nav-item"><a href="#">{{ __('Settings') }}</a></li>
        <li class="separator"><i class="flaticon-right-arrow"></i></li>
        <li class="nav-item"><a href="#">{{ __('Enamad Settings') }}</a></li>
    </ul>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4>{{ __('Enamad Settings') }}</h4>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form action="{{ route('user.enamad.update') }}" method="POST">
                    @csrf
                    
                    <div class="form-group">
                        <label>{{ __('Enamad Status') }}</label>
                        <div class="selectgroup w-100">
                            <label class="selectgroup-item">
                                <input type="radio" name="enamad_status" value="1" class="selectgroup-input" {{ old('enamad_status', $data->enamad_status ?? 0) == 1 ? 'checked' : '' }}>
                                <span class="selectgroup-button">{{ __('Active') }}</span>
                            </label>
                            <label class="selectgroup-item">
                                <input type="radio" name="enamad_status" value="0" class="selectgroup-input" {{ old('enamad_status', $data->enamad_status ?? 0) == 0 ? 'checked' : '' }}>
                                <span class="selectgroup-button">{{ __('Deactive') }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <label>{{ __('Enamad Code') }}</label>
                        <input type="text" class="form-control" name="enamad_code" value="{{ old('enamad_code', $data->enamad_code ?? '') }}" placeholder="{{ __('Enamad Code') }}">
                    </div>

                    <div class="form-group mt-3">
                        <label>{{ __('Enamad Site ID') }}</label>
                        <input type="text" class="form-control" name="enamad_site_id" value="{{ old('enamad_site_id', $data->enamad_site_id ?? '') }}" placeholder="{{ __('Site ID from Enamad') }}">
                    </div>

                    <div class="form-group mt-3">
                        <label>{{ __('Enamad Secret Key') }}</label>
                        <input type="password" class="form-control" name="enamad_secret_key" value="{{ old('enamad_secret_key', $data->enamad_secret_key ?? '') }}" placeholder="{{ __('Secret Key from Enamad') }}">
                    </div>

                    <div class="form-group mt-3">
                        <label>{{ __('Enamad Expire Date') }}</label>
                        <input type="date" class="form-control" name="enamad_expire_date" value="{{ old('enamad_expire_date', $data->enamad_expire_date ?? '') }}">
                    </div>

                    <div class="form-group mt-3">
                        <label>{{ __('Logo Type') }}</label>
                        <select name="enamad_logo_type" class="form-control">
                            <option value="auto" {{ old('enamad_logo_type', $data->enamad_logo_type ?? 'auto') == 'auto' ? 'selected' : '' }}>Auto (Detect Theme)</option>
                            <option value="light" {{ old('enamad_logo_type', $data->enamad_logo_type ?? '') == 'light' ? 'selected' : '' }}>Light</option>
                            <option value="dark" {{ old('enamad_logo_type', $data->enamad_logo_type ?? '') == 'dark' ? 'selected' : '' }}>Dark</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">{{ __('Save') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection