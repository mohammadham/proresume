@extends('user.layout')

@if (!empty($achievement->language) && $achievement->language->rtl == 1)
    @section('styles')
        <style>
            form input,
            form textarea,
            form select {
                direction: rtl;
            }

            form .note-editor.note-frame .note-editing-area .note-editable {
                direction: rtl;
                text-align: right;
            }
        </style>
    @endsection
@endif

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Edit Achievement') }}</h4>
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
                <a href="#">{{ __('Achievement Page') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Edit Achievement') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title d-inline-block">{{ __('Edit Achievement') }}
                    </div>
                    <a class="btn btn-info btn-sm float-right d-inline-block"
                        href="{{ route('user.achievement.index') . '?language=' . $achievement->language->code }}">
                        <span class="btn-label">
                            <i class="fas fa-backward"></i>
                        </span>
                        {{ __('Back') }}
                    </a>
                </div>
                <div class="card-body pt-5 pb-5">
                    <div class="row">
                        <div class="col-lg-6 offset-lg-3">
                            <form id="ajaxForm" class="" action="{{ route('user.achievement.update') }}"
                                method="post" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="achievement_id" value="{{ $achievement->id }}">
                                @if ($userBs->theme == 9 || $userBs->theme == 11 || $userBs->theme == 12)
                                    <div class="form-group">
                                        <label for="image"><strong>{{ __('Image') }}**</strong></label>
                                        <div class="showImage mb-3">
                                            <img src="{{ asset('assets/user/images/achievement/' . $achievement->image) }}"
                                                alt="..." class="img-thumbnail" width="170">
                                        </div>
                                        <input type="file" name="image" id="image" class="form-control">
                                        <p id="errimage" class="mb-0 text-danger em"></p>
                                    </div>
                                @endif
                                 @if ($userBs->theme != 12)
                                <div class="form-group">
                                    <label for="">{{ __('Title') }} **</label>
                                    <input type="text" class="form-control" name="title"
                                        value="{{ $achievement->title }}" placeholder="{{ __('Enter title') }}">
                                    <p id="errtitle" class="mb-0 text-danger em"></p>
                                </div>
                                @endif
                                @if ($userBs->theme == 11)
                                    <div class="form-group">
                                        <label for="">{{ __('Subtitle') }} **</label>
                                        <input type="text" class="form-control" name="subtitle"
                                            value="{{ $achievement->subtitle }}" placeholder="{{ __('Enter subtitle') }}">
                                        <p id="errsubtitle" class="mb-0 text-danger em"></p>
                                    </div>
                                @endif
                                <div class="form-group">
                                    <label for="count">{{ __('Count') }}**</label>
                                    <input id="count" type="number" class="form-control ltr" name="count"
                                        value="{{ $achievement->count }}"
                                        placeholder="{{ __('Enter achievement count') }}">
                                    <p id="errcount" class="mb-0 text-danger em"></p>
                                </div>
                             @if ($userBs->theme == 9 || $userBs->theme ==12)
                                    <div class="form-group">
                                        <label for="">{{ __('Symbol') }} **</label>
                                        <input type="text" class="form-control" name="symbol"
                                            placeholder="{{ __('Enter symbol') }}" value="{{ $achievement->symbol }}">
                                        <p id="errsymbol" class="mb-0 text-danger em"></p>
                                    </div>
                                @endif
                                <div class="form-group">
                                    <label for="">{{ __('Serial Number') }}
                                        **</label>
                                    <input type="number" class="form-control ltr" name="serial_number"
                                        value="{{ $achievement->serial_number }}"
                                        placeholder="{{ __('Enter Serial Number') }}">
                                    <p id="errserial_number" class="mb-0 text-danger em"></p>
                                    <p class="text-warning">
                                        <small>{{ __('The higher the serial number is, the later theSkill will be shown') . '.' }}</small>
                                    </p>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="form">
                        <div class="form-group from-show-notify row">
                            <div class="col-12 text-center">
                                <button type="submit" data-form="ajaxForm" id=""
                                    class="submitBtn btn btn-success">{{ __('Update') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
