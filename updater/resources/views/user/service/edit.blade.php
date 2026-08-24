@extends('user.layout')

@if (!empty($service->language) && $service->language->rtl == 1)
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
        <h4 class="page-title">{{ __('Edit Service') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('user.services.index') }}">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Service Page') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Edit Service') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title d-inline-block">{{ __('Edit Service') }}</div>
                    <a class="btn btn-info btn-sm float-right d-inline-block"
                        href="{{ route('user.services.index') . '?language=' . $service->language->code }}">
                        <span class="btn-label">
                            <i class="fas fa-backward"></i>
                        </span>
                        {{ __('Back') }}
                    </a>
                </div>
                <div class="card-body pt-5 pb-5">
                    <div class="row">
                        <div class="col-lg-6 offset-lg-3">
                            <form id="ajaxForm" class="" action="{{ route('user.service.update') }}" method="post"
                                enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="id" value="{{ $service->id }}">
                                <div class="form-group">
                                    <label for="image"><strong>{{ __('Image') }}*</strong></label>
                                    <div class="showImage mb-3">
                                        <img width="200"
                                            src="{{ isset($service->image) ? asset('assets/front/img/user/services/' . $service->image) : asset('assets/admin/img/noimage.jpg') }}"
                                            alt="..." class="img-thumbnail">
                                    </div>
                                    <input type="file" name="image" id="image" class="form-control">
                                    <p id="errimage" class="mb-0 text-danger em"></p>
                                </div>

                                @if ($userBs->theme == 9 || $userBs->theme == 11)
                                    <div class="form-group">
                                        <label for="">{{ __('Icon') }} **</label>
                                        <div class="btn-group d-block">
                                            <button type="button" class="btn btn-primary iconpicker-component"><i
                                                    class="{{ $service->icon }}"></i></button>
                                            <button type="button" class="icp icp-dd btn btn-primary dropdown-toggle"
                                                data-selected="fa-car" data-toggle="dropdown">
                                            </button>
                                            <div class="dropdown-menu"></div>
                                        </div>
                                        <input id="inputIcon" type="hidden" name="icon" value="fas fa-heart">
                                        <p id="erricon" class="mb-0 text-danger em"></p>
                                        <div class="mt-2">
                                            <small>{{ __('NB: click on the dropdown sign to select an icon.') }}</small>
                                        </div>
                                        <p id="erricon" class="mb-0 text-danger em"></p>
                                    </div>
                                @endif


                                <div class="form-group">
                                    <label for="">{{ __('Name') }}*</label>
                                    <input type="text" class="form-control" name="name" value="{{ $service->name }}"
                                        placeholder="{{ __('Enter Name') }}">
                                    <p id="errname" class="mb-0 text-danger em"></p>
                                </div>
                                <div class="form-group">
                                    <label for="">{{ __('Content') }} **</label>
                                    <textarea class="form-control summernote" name="content" data-height="300" placeholder="{{ __('Enter content') }}">{{ replaceBaseUrl($service->content) }}</textarea>
                                    <p id="errcontent" class="mb-0 text-danger em"></p>
                                </div>

                                <div class="form-group">
                                    <label for="">{{ __('Serial Number') }}
                                        **</label>
                                    <input type="number" class="form-control ltr" name="serial_number"
                                        value="{{ $service->serial_number }}"
                                        placeholder="{{ __('Enter Serial Number') }}">
                                    <p id="errserial_number" class="mb-0 text-danger em"></p>
                                    <p class="text-warning">
                                        <small>{{ __('The higher the serial number is, the later the service will be shown') }}.</small>
                                    </p>
                                </div>
                                <div class="form-group">
                                    <label for="featured" class="my-label mr-3">{{ __('Featured') }}</label>
                                    <input id="featured" type="checkbox" name="featured" value="1"
                                        {{ $service->featured == 1 ? 'checked' : '' }}>
                                    <p id="errfeatured" class="mb-0 text-danger em"></p>
                                </div>
                                <div class="form-group">
                                    <div class="d-flext">
                                        <label class="mr-3">{{ __('Detail Page') }}</label>
                                        <div class="radio mr-3">
                                            <label><input class="mr-1" type="radio" name="detail_page"
                                                    value="1"
                                                    {{ $service->detail_page == 1 ? 'checked' : '' }}>{{ __('Enable') }}</label>
                                        </div>
                                        <div class="radio">
                                            <label><input class="mr-1" type="radio" name="detail_page"
                                                    value="0"
                                                    {{ $service->detail_page == 0 ? 'checked' : '' }}>{{ __('Disable') }}</label>
                                        </div>
                                    </div>
                                    <p id="errdetail_page" class="mb-0 text-danger em"></p>
                                </div>
                                <div class="form-group">
                                    <label for="">{{ __('Meta Keywords') }}</label>
                                    <input type="text" class="form-control" name="meta_keywords"
                                        value="{{ $service->meta_keywords }}" data-role="tagsinput">
                                    <p id="errmeta_keywords" class="mb-0 text-danger em"></p>
                                </div>
                                <div class="form-group">
                                    <label for="">{{ __('Meta Description') }}</label>
                                    <textarea type="text" class="form-control" name="meta_description" rows="5">{{ $service->meta_description }}</textarea>
                                    <p id="errmeta_description" class="mb-0 text-danger em"></p>
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
