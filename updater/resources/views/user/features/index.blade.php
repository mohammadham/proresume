@extends('user.layout')
@php
    $userDefaultLang = \App\Models\User\Language::where([
        ['user_id', \Illuminate\Support\Facades\Auth::id()],
        ['is_default', 1],
    ])->first();
    $userLanguages = \App\Models\User\Language::where('user_id', \Illuminate\Support\Facades\Auth::id())->get();
@endphp

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Work Process') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="#">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Work Process') }}</a>
            </li>
        </ul>
    </div>



    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card-title d-inline-block">{{ __('Work Process') }}</div>
                        </div>
                        <div class="col-lg-3">
                            @includeIf('user.partials.language')
                        </div>
                        <div class="col-lg-5  mt-2 mt-lg-0">
                            @if (!is_null($userDefaultLang))
                                <a href="#" class="btn btn-primary float-right btn-sm" data-toggle="modal"
                                    data-target="#createModal"><i class="fas fa-plus"></i>
                                    {{ __('Add New') }}</a>
                                <button class="btn btn-danger float-right btn-sm mr-2 d-none bulk-delete"
                                    data-href="{{ route('user.features.bulkdelete') }}"><i
                                        class="flaticon-interface-5"></i>{{ __('Delete') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            @if (is_null($userDefaultLang))
                                <h3 class="text-center">{{ __('NO LANGUAGE FOUND') }}
                                </h3>
                            @else
                                @if (count($features) == 0)
                                    <h3 class="text-center">{{ __('NO FEATURE FOUND') }}</h3>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-striped mt-3" id="basic-datatables">
                                            <thead>
                                                <tr>
                                                    <th scope="col">
                                                        <input type="checkbox" class="bulk-check" data-val="all">
                                                    </th>
                                                    <th scope="col">{{ __('Image') }}</th>
                                                    <th scope="col">{{ __('Title') }}</th>
                                                    <th scope="col">{{ __('Subtitle') }}
                                                    </th>
                                                    <th scope="col">{{ __('Serial Number') }}
                                                    </th>
                                                    <th scope="col">{{ __('Actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($features as $key => $feature)
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox" class="bulk-check"
                                                                data-val="{{ $feature->id }}">
                                                        </td>
                                                        <td>
                                                            <img width="100"
                                                                src="{{ asset('assets/user/features/' . $feature->image) }}"
                                                                alt="">
                                                        </td>
                                                        <td>{{ strlen($feature->title) > 30 ? mb_substr($feature->title, 0, 30, 'UTF-8') . '...' : $feature->title }}
                                                        </td>
                                                        <td>{{ $feature->subtitle }}</td>
                                                        <td>{{ $feature->serial_number }}</td>
                                                        <td>
                                                            <a class="btn btn-secondary btn-sm editbtn" data-toggle="modal"
                                                                data-title="{{ $feature->title }}"
                                                                data-subtitle="{{ $feature->subtitle }}"
                                                                data-serial_number="{{ $feature->serial_number }}"
                                                                data-id="{{ $feature->id }}"
                                                                data-image="{{ asset('assets/user/feature/' . $feature->image) }}"
                                                                data-target="#editModal">
                                                                <span class="btn-label">
                                                                    <i class="fas fa-edit"></i>
                                                                </span>
                                                                {{ __('Edit') }}
                                                            </a>
                                                            <form class="deleteform d-inline-block"
                                                                action="{{ route('user.features.delete') }}"
                                                                method="post">
                                                                @csrf
                                                                <input type="hidden" name="feature_id"
                                                                    value="{{ $feature->id }}">
                                                                <button type="submit"
                                                                    class="btn btn-danger btn-sm deletebtn">
                                                                    <span class="btn-label">
                                                                        <i class="fas fa-trash"></i>
                                                                    </span>
                                                                    {{ __('Delete') }}
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    @includeIf('user.features.create')
    @includeIf('user.features.edit')
@endsection
