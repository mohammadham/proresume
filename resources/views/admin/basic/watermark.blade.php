@extends('admin.layout')

@section('content')
<div class="page-header">
    <h4 class="page-title">{{ __('Watermark Badge Settings') }}</h4>
    <ul class="breadcrumbs">
        <li class="nav-home"><a href="{{route('admin.dashboard')}}"><i class="flaticon-home"></i></a></li>
        <li class="separator"><i class="flaticon-right-arrow"></i></li>
        <li class="nav-item"><a href="#">{{ __('Basic Settings') }}</a></li>
        <li class="separator"><i class="flaticon-right-arrow"></i></li>
        <li class="nav-item"><a href="#">{{ __('Watermark Badge') }}</a></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <form action="{{route('admin.watermark.update')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-10">
                            <div class="card-title">{{ __('Update Watermark Badge') }}</div>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-5 pb-5">
                    <div class="row">
                        <div class="col-lg-6 offset-lg-3">
                            <div class="form-group">
                                <label>{{ __('Watermark Status') }}</label>
                                <div class="selectgroup w-100">
                                    <label class="selectgroup-item">
                                        <input type="radio" name="watermark_status" value="1" class="selectgroup-input" {{ $abs->watermark_status == 1 ? 'checked' : '' }}>
                                        <span class="selectgroup-button">{{ __('Active') }}</span>
                                    </label>
                                    <label class="selectgroup-item">
                                        <input type="radio" name="watermark_status" value="0" class="selectgroup-input" {{ $abs->watermark_status == 0 ? 'checked' : '' }}>
                                        <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                    </label>
                                </div>
                                @if ($errors->has('watermark_status'))
                                    <p class="mb-0 text-danger">{{$errors->first('watermark_status')}}</p>
                                @endif
                            </div>

                            <div class="form-group">
                                <label>{{ __('Watermark Text') }}</label>
                                <input type="text" class="form-control" name="watermark_text" value="{{ $abs->watermark_text }}" placeholder="{{ __('e.g. Secure Payment with Bank Mellat') }}">
                                @if ($errors->has('watermark_text'))
                                    <p class="mb-0 text-danger">{{$errors->first('watermark_text')}}</p>
                                @endif
                                <small class="form-text text-muted">{{ __('Text to display when no image is provided') }}</small>
                            </div>

                            <div class="form-group">
                                <label>{{ __('Watermark Link URL') }}</label>
                                <input type="url" class="form-control" name="watermark_url" value="{{ $abs->watermark_url }}" placeholder="https://example.com">
                                @if ($errors->has('watermark_url'))
                                    <p class="mb-0 text-danger">{{$errors->first('watermark_url')}}</p>
                                @endif
                                <small class="form-text text-muted">{{ __('Optional link when watermark is clicked') }}</small>
                            </div>

                            <div class="form-group">
                                <label>{{ __('Watermark Image') }}</label>
                                <input type="file" class="form-control" name="watermark_image" accept="image/*">
                                @if ($errors->has('watermark_image'))
                                    <p class="mb-0 text-danger">{{$errors->first('watermark_image')}}</p>
                                @endif
                                @if ($abs->watermark_image)
                                    <div class="mt-2">
                                        <img src="{{ asset('assets/front/img/' . $abs->watermark_image) }}" alt="Current Watermark" style="max-width: 150px; height: auto; border-radius: 4px;">
                                        <p class="text-muted mt-1">{{ __('Current watermark image') }}</p>
                                    </div>
                                @endif
                                <small class="form-text text-muted">{{ __('Optional image (max 2MB). If provided, text will be ignored.') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="form">
                        <div class="form-group from-show-notify row">
                            <div class="col-12 text-center">
                                <button type="submit" id="displayNotif" class="btn btn-success">{{ __('Update') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection