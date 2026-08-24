@extends('admin.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('User CVs') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}">
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
                <a href="#">{{ __('User CVs') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card-title d-inline-block">{{ __('User CVs') }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            @if (count($cvs) == 0)
                                <h3 class="text-center">{{ __('NO CV FOUND') }}</h3>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped mt-3" id="basic-datatables">
                                        <thead>
                                            <tr>
                                                <th scope="col">
                                                    #
                                                </th>
                                                <th scope="col">{{ __('CV Name') }} </th>
                                                <th scope="col">{{ __('Preview Template') }}</th>
                                                <th scope="col">{{ __('Status') }}</th>
                                                <th scope="col">{{ __('Direction') }}</th>
                                                <th scope="col">{{ __('Preview') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($cvs as $key => $cv)
                                                <tr>
                                                    <td>
                                                        {{ $key + 1 }}
                                                    </td>
                                                    <td>{{ $cv->cv_name }}</td>
                                                    <!--preview template -->
                                                    <td>
                                                        <div class="d-inline-block">
                                                            <form class="d-inline-block"
                                                                id="previewTemForm{{ $cv->id }}"
                                                                action="{{ route('admin.register.user.cv_template_delete') }}"
                                                                method="post">
                                                                @csrf
                                                                <select data-id="{{ $cv->id }}"
                                                                    class="form-control form-control-sm {{ $cv->preview_template_status == 1 ? 'bg-success' : 'bg-danger' }}"
                                                                    name="preview_template_status">
                                                                    <option value="1"
                                                                        {{ $cv->preview_template_status == 1 ? 'selected' : '' }}>
                                                                        {{ __('Yes') }}
                                                                    </option>
                                                                    <option value="0"
                                                                        {{ $cv->preview_template_status == 0 ? 'selected' : '' }}>
                                                                        {{ __('No') }}
                                                                    </option>
                                                                </select>
                                                                <input type="hidden" name="cv_id"
                                                                    value="{{ $cv->id }}">
                                                            </form>

                                                            @if ($cv->preview_template_status == 1)
                                                                <button type="button"
                                                                    data-target="#editTemplate{{ $cv->id }}"
                                                                    data-toggle="modal"
                                                                    class="btn btn-primary btn-sm editBtn">
                                                                    <i class="fas fa-edit"></i> {{ __('Edit') }}
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-inline-block">
                                                            <form id="vcardFormStatus{{ $cv->id }}"
                                                                class="d-inline-block"
                                                                action="{{ route('admin.register.user.cv_status_update') }}"
                                                                method="post">
                                                                @csrf
                                                                <select
                                                                    onchange="document.getElementById('vcardFormStatus{{ $cv->id }}').submit();"
                                                                    class="form-control form-control-sm {{ $cv->status == 1 ? 'bg-success' : 'bg-danger' }}"
                                                                    name="status">
                                                                    <option value="1"
                                                                        {{ $cv->status == 1 ? 'selected' : '' }}>
                                                                        {{ __('Yes') }}
                                                                    </option>
                                                                    <option value="0"
                                                                        {{ $cv->status == 0 ? 'selected' : '' }}>
                                                                        {{ __('No') }}
                                                                    </option>
                                                                </select>
                                                                <input type="hidden" name="cv_id"
                                                                    value="{{ $cv->id }}">
                                                            </form>
                                                        </div>
                                                    </td>
                                                    <td>{{ $cv->direction == 1 ? 'Left to Right' : 'Right to Left' }}</td>
                                                    <td>
                                                        <button class="btn btn-primary btn-sm" data-toggle="modal"
                                                            data-target="#urlsModal{{ $cv->id }}"><i
                                                                class="fas fa-link"></i>
                                                            {{ __('URLs') }}</button>
                                                    </td>
                                                </tr>

                                                <!-- Modal -->
                                                <div class="modal fade" id="urlsModal{{ $cv->id }}" tabindex="-1"
                                                    role="dialog" aria-labelledby="urlsModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="urlsModalLabel">
                                                                    {{ __('CV URLs') }}</h5>
                                                                <button type="button" class="close" data-dismiss="modal"
                                                                    aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <ul>
                                                                    <li>
                                                                        @php
                                                                            $pathUrl =
                                                                                env('WEBSITE_HOST') .
                                                                                '/' .
                                                                                $cv->user->username .
                                                                                '/cv/' .
                                                                                $cv->id;
                                                                        @endphp
                                                                        <strong
                                                                            class="mr-2">{{ __('Path Based URL') }}:</strong>
                                                                        <a target="_blank"
                                                                            href="//{{ $pathUrl }}">{{ $pathUrl }}</a>
                                                                    </li>
                                                                    @if (cPackageHasSubdomain($cv->user))
                                                                        <li>
                                                                            @php
                                                                                $subUrl =
                                                                                    $cv->user->username .
                                                                                    '.' .
                                                                                    env('WEBSITE_HOST') .
                                                                                    '/cv/' .
                                                                                    $cv->id;
                                                                            @endphp
                                                                            <strong
                                                                                class="mr-2">{{ __('Subdomain Based URL') }}:</strong>
                                                                            <a target="_blank"
                                                                                href="//{{ $subUrl }}">{{ $subUrl }}</a>
                                                                        </li>
                                                                    @endif
                                                                    @if (cPackageHasCdomain($cv->user))
                                                                        @php
                                                                            $domUrl = $cv->user
                                                                                ->custom_domains()
                                                                                ->where('status', 1)
                                                                                ->orderBy('id', 'DESC')
                                                                                ->first();
                                                                        @endphp
                                                                        @if (!empty($domUrl))
                                                                            <li>
                                                                                <strong
                                                                                    class="mr-2">{{ __('Domain Based URL') }}:</strong>
                                                                                <a target="_blank"
                                                                                    href="//{{ $domUrl->requested_domain }}/cv/{{ $cv->id }}">{{ $domUrl->requested_domain }}/cv/{{ $cv->id }}</a>
                                                                            </li>
                                                                        @endif
                                                                    @endif
                                                                </ul>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-dismiss="modal">{{ __('Close') }}</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @include('admin.cv.add-template')
                                                @include('admin.cv.edit-template')
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Create CV Modal -->
@endsection

@section('scripts')
    <script>
        "user strict";
        $(document).ready(function() {
            $("select[name='direction']").on('change', function() {
                val = $(this).val();
                let $formControls = $(".form-control:not(.ltr)");

                // if RTL is selected
                if (val == 2) {
                    $formControls.each(function() {
                        $(this).addClass('rtl');
                    });
                    $("#ltrAlert").show();
                } else {
                    $formControls.each(function() {
                        $(this).removeClass('rtl');
                    });
                    $("#ltrAlert").hide();
                }
            });
        });

        $('body').on('change', 'select[name="preview_template_status"]', function() {
            let vcardId = $(this).data('id');
            let dataVal = $(this).val();

            if (dataVal == 1) {
                $("#addTemplate" + vcardId).modal('show');
            } else {
                $('#previewTemForm' + vcardId).trigger('submit');
            }
        });
    </script>
@endsection
