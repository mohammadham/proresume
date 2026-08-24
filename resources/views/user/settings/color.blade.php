@extends('user.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{  __('Color Settings') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{route('user-dashboard')}}">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{  __('Basic Settings') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Color Settings') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header">
                    <div class="card-title d-inline-block">{{  __('Color Settings') }}</div>
                </div>
                <div class="card-body">
                    <div class="row justify-content-center">
                        <div class="col-lg-6">
                            <form id="permissionsForm" class="" action="{{route('user.color.update')}}"
                                  method="post">
                                {{csrf_field()}}
                                <div class="form-group">
                                    <label for="">
                                        {{ __('Base Color') }}
                                        @if ($userBs->theme == 3 || $userBs->theme == 5)
                                            ({{ __('Primary') }})
                                        @endif
                                    </label>
                                    <input type="text" class="form-control jscolor" name="base_color" value="{{$data->base_color}}">
                                </div>

                                @if ($userBs->theme == 3 || $userBs->theme == 5)
                                    <div class="form-group">
                                        <label for="">
                                            {{ __('Base Color') }}  ({{ __('Secondary') }})
                                        </label>
                                        <input type="text" class="form-control jscolor" name="secondary_base_color" value="{{$data->secondary_base_color}}">
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="form">
                        <div class="form-group from-show-notify row">
                            <div class="col-12 text-center">
                                <button type="submit" id="permissionBtn" class="btn btn-success">{{ __('Update') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
