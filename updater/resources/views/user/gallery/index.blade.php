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
        <h4 class="page-title">{{ __('Gallery') }}</h4>
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
                <a href="#">{{ __('Gallery') }}</a>
            </li>
        </ul>
    </div>



    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card-title d-inline-block">{{ __('Gallery') }}</div>
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
                                    data-href="{{ route('user.gallery.bulkdelete') }}"><i
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
                                @if (count($galleries) == 0)
                                    <h3 class="text-center">{{ __('NO GALLERY IMAGE FOUND') }}</h3>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-striped mt-3" id="basic-datatables">
                                            <thead>
                                                <tr>
                                                    <th scope="col">
                                                        <input type="checkbox" class="bulk-check" data-val="all">
                                                    </th>
                                                    <th scope="col">{{ __('Image') }}</th>
                                                    <th scope="col">{{ __('Name') }}</th>
                                                    <th scope="col">{{ __('Serial Number') }}
                                                    </th>
                                                    <th scope="col">{{ __('Actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($galleries as $key => $gallery)
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox" class="bulk-check"
                                                                data-val="{{ $gallery->id }}">
                                                        </td>
                                                        <td>
                                                            <img width="100"
                                                                src="{{ asset('assets/user/gallery/' . $gallery->image) }}"
                                                                alt="">
                                                        </td>
                                                        <td>
                                                            {{ $gallery->name }}
                                                        </td>
                                                        <td>{{ $gallery->serial_number }}</td>
                                                        <td>
                                                            <a class="btn btn-secondary btn-sm editbtn" data-toggle="modal"
                                                                data-name="{{ $gallery->name }}"
                                                                data-status="{{ $gallery->status }}"
                                                                data-serial_number="{{ $gallery->serial_number }}"
                                                                data-id="{{ $gallery->id }}"
                                                                data-image="{{ asset('assets/user/gallery/' . $gallery->image) }}"
                                                                data-target="#editModal">
                                                                <span class="btn-label">
                                                                    <i class="fas fa-edit"></i>
                                                                </span>
                                                                {{ __('Edit') }}
                                                            </a>
                                                            <form class="deleteform d-inline-block"
                                                                action="{{ route('user.gallery.delete') }}"
                                                                method="post">
                                                                @csrf
                                                                <input type="hidden" name="gallery_id"
                                                                    value="{{ $gallery->id }}">
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
    @includeIf('user.gallery.create')
    @includeIf('user.gallery.edit')
@endsection
