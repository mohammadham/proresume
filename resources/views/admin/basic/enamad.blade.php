@extends('admin.layout')

@section('content')
<div class="page-header">
    <h4 class="page-title">{{ __('Enamad Settings') }}</h4>
    <ul class="breadcrumbs">
        <li class="nav-home"><a href="{{route('admin.dashboard')}}"><i class="flaticon-home"></i></a></li>
        <li class="separator"><i class="flaticon-right-arrow"></i></li>
        <li class="nav-item"><a href="#">{{ __('Basic Settings') }}</a></li>
        <li class="separator"><i class="flaticon-right-arrow"></i></li>
        <li class="nav-item"><a href="#">{{ __('Enamad Settings') }}</a></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-lg-10">
                        <div class="card-title">{{ __('Enamad Settings') }}</div>
                    </div>
                </div>
            </div>
            <div class="card-body pt-5 pb-5">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form action="{{ route('admin.enamad.update') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>{{ __('Enamad Status') }}</label>
                                <div class="selectgroup w-100">
                                    <label class="selectgroup-item">
                                        <input type="radio" name="enamad_status" value="1" class="selectgroup-input" {{ $abs->enamad_status == 1 ? 'checked' : '' }}>
                                        <span class="selectgroup-button">{{ __('Active') }}</span>
                                    </label>
                                    <label class="selectgroup-item">
                                        <input type="radio" name="enamad_status" value="0" class="selectgroup-input" {{ $abs->enamad_status == 0 ? 'checked' : '' }}>
                                        <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                    </label>
                                </div>
                                @if ($errors->has('enamad_status'))
                                    <p class="mb-0 text-danger">{{$errors->first('enamad_status')}}</p>
                                @endif
                            </div>

                            <div class="form-group">
                                <label>{{ __('Enamad Code') }}</label>
                                <input type="text" class="form-control" name="enamad_code" value="{{ $abs->enamad_code }}" placeholder="{{ __('Enamad Code') }}">
                                @if ($errors->has('enamad_code'))
                                    <p class="mb-0 text-danger">{{$errors->first('enamad_code')}}</p>
                                @endif
                                <small class="form-text text-muted">{{ __('Code provided by Enamad') }}</small>
                            </div>

                            <div class="form-group">
                                <label>{{ __('Enamad Site ID') }}</label>
                                <input type="text" class="form-control" name="enamad_site_id" value="{{ $abs->enamad_site_id }}" placeholder="{{ __('Site ID from Enamad') }}">
                                @if ($errors->has('enamad_site_id'))
                                    <p class="mb-0 text-danger">{{$errors->first('enamad_site_id')}}</p>
                                @endif
                                <small class="form-text text-muted">{{ __('Site ID provided by Enamad') }}</small>
                            </div>

                            <div class="form-group">
                                <label>{{ __('Enamad Secret Key') }}</label>
                                {{-- Masked on purpose: never echo the stored secret into HTML.
                                    A blank submit keeps the stored key (handled in the controller). --}}
                                <input type="password" class="form-control" name="enamad_secret_key" value="" placeholder="{{ __('Secret Key from Enamad') }}">
                                @if ($errors->has('enamad_secret_key'))
                                    <p class="mb-0 text-danger">{{$errors->first('enamad_secret_key')}}</p>
                                @endif
                                <small class="form-text text-muted">{{ __('Secret Key provided by Enamad') }}</small>
                            </div>

                            <div class="form-group">
                                <label>{{ __('Enamad Expire Date') }}</label>
                                <input type="date" class="form-control" name="enamad_expire_date" value="{{ $abs->enamad_expire_date }}">
                                @if ($errors->has('enamad_expire_date'))
                                    <p class="mb-0 text-danger">{{$errors->first('enamad_expire_date')}}</p>
                                @endif
                                <small class="form-text text-muted">{{ __('Expiration date of Enamad certificate') }}</small>
                            </div>

                            <div class="form-group">
                                <label>{{ __('Logo Type') }}</label>
                                <select name="enamad_logo_type" class="form-control">
                                    <option value="auto" {{ ($abs->enamad_logo_type ?? 'auto') == 'auto' ? 'selected' : '' }}>Auto (Detect Theme)</option>
                                    <option value="light" {{ ($abs->enamad_logo_type ?? '') == 'light' ? 'selected' : '' }}>Light</option>
                                    <option value="dark" {{ ($abs->enamad_logo_type ?? '') == 'dark' ? 'selected' : '' }}>Dark</option>
                                </select>
                                <small class="form-text text-muted">{{ __('Logo theme for Enamad badge') }}</small>
                            </div>

                            <div class="form-group mt-3">
                                <button type="button" class="btn btn-primary" id="verify-enamad">{{ __('Verify Enamad') }}</button>
                                <span id="verify-result" class="ml-2"></span>
                            </div>

                            <button type="submit" class="btn btn-primary mt-3">{{ __('Update') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('#verify-enamad').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var $result = $('#verify-result');
        
        $btn.prop('disabled', true).text('{{ __('Verifying...') }}');
        $result.removeClass('text-success text-danger').text('');
        
        $.ajax({
            url: '{{ route("enamad.verify") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $result.addClass('text-success').text(response.message);
                    if (response.data && response.data.is_valid) {
                        $('#enamad_status').val(1).prop('checked', true);
                    } else {
                        $('#enamad_status').val(0).prop('checked', true);
                    }
                } else {
                    $result.addClass('text-danger').text(response.message);
                }
            },
            error: function(xhr) {
                var msg = xhr.responseJSON?.message || '{{ __('Verification failed') }}';
                $result.addClass('text-danger').text(msg);
            },
            complete: function() {
                $btn.prop('disabled', false).text('{{ __('Verify Enamad') }}');
            }
        });
    });
});
</script>
@endsection