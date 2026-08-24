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
        <h4 class="page-title">{{ __('Achievements') }}</h4>
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
                <a href="#">{{ __('Achievements') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card-title d-inline-block">{{ __('Achievements') }}
                            </div>
                        </div>
                        <div class="col-lg-3">
                            @includeIf('user.partials.language')
                        </div>
                        <div class="col-lg-5   mt-2 mt-lg-0">
                            @if (!is_null($userDefaultLang))
                                <a href="#" class="btn btn-primary float-right btn-sm" data-toggle="modal"
                                    data-target="#createModal"><i class="fas fa-plus"></i>
                                    {{ __('Add Achievement') }}</a>
                                <button class="btn btn-danger float-right btn-sm mr-2 d-none bulk-delete"
                                    data-href="{{ route('user.achievement.bulk.delete') }}"><i
                                        class="flaticon-interface-5"></i>
                                    {{ __('Delete') }}</button>
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
                                @if (count($achievements) == 0)
                                    <h3 class="text-center">
                                        {{ __('NO ACHIEVEMENT FOUND') }}</h3>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-striped mt-3" id="basic-datatables">
                                            <thead>
                                                <tr>
                                                    <th scope="col">
                                                        <input type="checkbox" class="bulk-check" data-val="all">
                                                    </th>
                                                    @if ($userBs->theme == 9 || $userBs->theme == 12 || $userBs->theme == 11)
                                                        <th scope="col">{{ __('Image') }}</th>
                                                    @endif
                                                    @if ($userBs->theme != 12)
                                                        <th scope="col">{{ __('Title') }}</th>
                                                    @endif
                                                    @if ($userBs->theme == 9 || $userBs->theme == 12)
                                                    <th scope="col">{{ __('Symbol') }}</th>
                                                    @endif
                                                    <th scope="col">{{ __('Count') }}</th>
                                                    <th scope="col">{{ __('Actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($achievements as $key => $achievement)
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox" class="bulk-check"
                                                                data-val="{{ $achievement->id }}">
                                                        </td>
                                                        @if ($userBs->theme == 9 || $userBs->theme == 11 || $userBs->theme == 12)
                                                            <td><img src="{{ asset('assets/user/images/achievement/' . $achievement->image) }}"
                                                                    alt="" width="50"></td>
                                                        @endif
                                                        @if ($userBs->theme != 12)
                                                            <td>{{ strlen($achievement->title) > 30 ? mb_substr($achievement->title, 0, 30, 'UTF-8') . '...' : $achievement->title }}
                                                            </td>
                                                        @endif
                                                        @if ($userBs->theme == 9 || $userBs->theme == 12)
                                                        <td>{{ $achievement->symbol }}</td>
                                                        @endif
                                                        <td>{{ $achievement->count }}</td>
                                                        <td>
                                                            <a class="btn btn-secondary btn-sm"
                                                                href="{{ route('user.achievement.edit', $achievement->id) . '?language=' . $achievement->language->code }}">
                                                                <span class="btn-label">
                                                                    <i class="fas fa-edit"></i>
                                                                </span>
                                                                {{ __('Edit') }}
                                                            </a>
                                                            <form class="deleteform d-inline-block"
                                                                action="{{ route('user.achievement.delete') }}"
                                                                method="post">
                                                                @csrf
                                                                <input type="hidden" name="achievement_id"
                                                                    value="{{ $achievement->id }}">
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
    <!-- Create Skill Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">
                        {{ __('Add Achievements') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="ajaxForm" enctype="multipart/form-data" class="modal-form"
                        action="{{ route('user.achievement.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="">{{ __('Language') }} **</label>
                            <select id="language" name="user_language_id" class="form-control">
                                <option value="" selected disabled>
                                    {{ __('Select a language') }}</option>
                                @foreach ($userLanguages as $lang)
                                    <option value="{{ $lang->id }}">{{ $lang->name }}</option>
                                @endforeach
                            </select>
                            <p id="erruser_language_id" class="mb-0 text-danger em"></p>
                        </div>
                        @if ($userBs->theme == 9 || $userBs->theme == 11 || $userBs->theme == 12)
                            <div class="form-group">
                                <label for="image"><strong>{{ __('Image') }}**</strong></label>
                                <div class="showImage mb-3">
                                    <img src="{{ asset('assets/admin/img/noimage.jpg') }}" alt="..."
                                        class="img-thumbnail" width="170">
                                </div>
                                <input type="file" name="image" id="image" class="form-control">
                                <p id="errimage" class="mb-0 text-danger em"></p>
                            </div>
                        @endif

                        @if ($userBs->theme != 12)
                            <div class="form-group">
                                <label for="">{{ __('Title') }} **</label>
                                <input type="text" class="form-control" name="title"
                                    placeholder="{{ __('Enter title') }}" value="">
                                <p id="errtitle" class="mb-0 text-danger em"></p>
                            </div>
                        @endif
                        @if ($userBs->theme == 11)
                            <div class="form-group">
                                <label for="">{{ __('Subtitle') }} **</label>
                                <input type="text" class="form-control" name="subtitle"
                                    placeholder="{{ __('Enter subtitle') }}" value="">
                                <p id="errsubtitle" class="mb-0 text-danger em"></p>
                            </div>
                        @endif
                        <div class="form-group">
                            <label for="count">{{ __('Count') }}**</label>
                            <input id="count" type="number" class="form-control ltr" name="count" value=""
                                placeholder="{{ __('Enter achievement count') }}" min="1">
                            <p id="errcount" class="mb-0 text-danger em"></p>
                        </div>

                        @if ($userBs->theme == 9 || $userBs->theme == 12)
                            <div class="form-group">
                                <label for="">{{ __('Symbol') }} **</label>
                                <input type="text" class="form-control" name="symbol"
                                    placeholder="{{ __('Enter symbol') }}" value="">
                                <p id="errsymbol" class="mb-0 text-danger em"></p>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="">{{ __('Serial Number') }}
                                **</label>
                            <input type="number" class="form-control ltr" name="serial_number" value=""
                                placeholder="{{ __('Enter Serial Number') }}">
                            <p id="errserial_number" class="mb-0 text-danger em"></p>
                            <p class="text-warning mb-0">
                                <small>{{ __('The higher the serial number is, the later the Skill will be shown') . '.' }}</small>
                            </p>

                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                    <button id="" data-form="ajaxForm" type="button"
                        class="submitBtn btn btn-primary">{{ __('Submit') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection
