@extends('admin.layout')
@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Users vCards') }}</h4>
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
                <a href="#">{{ __('Users vCards') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title d-inline-block">{{ __('vCards') }}</div>
                    <button class="btn btn-danger float-right btn-sm mr-2 d-none bulk-delete"
                        data-href="{{ route('admin.register.user.vcard_bulk_delete') }}"><i
                            class="flaticon-interface-5"></i>
                        {{ __('Delete') }}</button>
                </div>
                <div class="card-body">

                    <div class="row">
                        <div class="col-lg-12">
                            @if (count($vcards) == 0)
                                <h3 class="text-center">{{ __('NO VCARD FOUND') }}</h3>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped mt-3" id="basic-datatables">
                                        <thead>
                                            <tr>
                                                <th scope="col">
                                                    #
                                                </th>
                                                <th scope="col">{{ __('vCard Name') }}</th>
                                                <th scope="col">{{ __('Preview') }}</th>
                                                <th scope="col">{{ __('Preview Template') }}</th>
                                                <th scope="col">{{ __('Status') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($vcards as $key => $vcard)
                                                <tr>
                                                    <td>
                                                        {{ $key + 1 }}
                                                    </td>
                                                    <td>
                                                        {{ strlen($vcard->vcard_name) > 20 ? mb_substr($vcard->vcard_name, 0, 20, 'UTF-8') . '...' : $vcard->vcard_name }}
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-primary btn-sm" data-toggle="modal"
                                                            data-target="#urlsModal{{ $vcard->id }}"><i
                                                                class="fas fa-link"></i>
                                                            {{ __('URLs') }}</button>
                                                    </td>
                                                    <!--preview template -->
                                                    <td>
                                                        <div class="d-inline-block">
                                                            <form class="d-inline-block"
                                                                id="previewTemForm{{ $vcard->id }}"
                                                                action="{{ route('admin.register.user.delete_prevtemplate') }}"
                                                                method="post">
                                                                @csrf
                                                                <select data-id="{{ $vcard->id }}"
                                                                    class="form-control form-control-sm {{ $vcard->preview_template_status == 1 ? 'bg-success' : 'bg-danger' }}"
                                                                    name="preview_template_status">
                                                                    <option value="1"
                                                                        {{ $vcard->preview_template_status == 1 ? 'selected' : '' }}>
                                                                        {{ __('Yes') }}
                                                                    </option>
                                                                    <option value="0"
                                                                        {{ $vcard->preview_template_status == 0 ? 'selected' : '' }}>
                                                                        {{ __('No') }}
                                                                    </option>
                                                                </select>
                                                                <input type="hidden" name="vcard_id"
                                                                    value="{{ $vcard->id }}">
                                                            </form>

                                                            @if ($vcard->preview_template_status == 1)
                                                                <button type="button"
                                                                    data-target="#editTemplate{{ $vcard->id }}"
                                                                    data-toggle="modal"
                                                                    class="btn btn-primary btn-sm editBtn">
                                                                    <i class="fas fa-edit"></i> {{ __('Edit') }}
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-inline-block">
                                                            <form id="vcardFormStatus{{ $vcard->id }}"
                                                                class="d-inline-block"
                                                                action="{{ route('admin.register.user.vcard_status_update') }}"
                                                                method="post">
                                                                @csrf
                                                                <select
                                                                    onchange="document.getElementById('vcardFormStatus{{ $vcard->id }}').submit();"
                                                                    class="form-control form-control-sm {{ $vcard->status == 1 ? 'bg-success' : 'bg-danger' }}"
                                                                    name="status">
                                                                    <option value="1"
                                                                        {{ $vcard->status == 1 ? 'selected' : '' }}>
                                                                        {{ __('Yes') }}
                                                                    </option>
                                                                    <option value="0"
                                                                        {{ $vcard->status == 0 ? 'selected' : '' }}>
                                                                        {{ __('No') }}
                                                                    </option>
                                                                </select>
                                                                <input type="hidden" name="vcard_id"
                                                                    value="{{ $vcard->id }}">
                                                            </form>
                                                        </div>
                                                    </td>

                                                </tr>

                                                <!-- Modal -->
                                                <div class="modal fade" id="urlsModal{{ $vcard->id }}" tabindex="-1"
                                                    role="dialog" aria-labelledby="urlsModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="urlsModalLabel">
                                                                    {{ __('vCard URLs') }}</h5>
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
                                                                                $vcard->user->username .
                                                                                '/vcard/' .
                                                                                $vcard->id;
                                                                        @endphp
                                                                        <strong
                                                                            class="mr-2">{{ __('Path Based URL') }}:</strong>
                                                                        <a target="_blank"
                                                                            href="//{{ $pathUrl }}">{{ $pathUrl }}</a>
                                                                    </li>
                                                                    @if (cPackageHasSubdomain($vcard->user))
                                                                        <li>
                                                                            @php
                                                                                $subUrl =
                                                                                    $vcard->user->username .
                                                                                    '.' .
                                                                                    env('WEBSITE_HOST') .
                                                                                    '/vcard/' .
                                                                                    $vcard->id;
                                                                            @endphp
                                                                            <strong
                                                                                class="mr-2">{{ __('Subdomain Based URL') }}:</strong>
                                                                            <a target="_blank"
                                                                                href="//{{ $subUrl }}">{{ $subUrl }}</a>
                                                                        </li>
                                                                    @endif
                                                                    @if (cPackageHasCdomain($vcard->user))
                                                                        @php
                                                                            $domUrl = $vcard->user
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
                                                                                    href="//{{ $domUrl->requested_domain }}/vcard/{{ $vcard->id }}">{{ $domUrl->requested_domain }}/vcard/{{ $vcard->id }}</a>
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


                                                <!-- preview template modal-->
                                                @include('admin.register_user.vcard.add-template')
                                                @include('admin.register_user.vcard.edit-template')
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
@endsection
@section('scripts')
    <script>
        "user strict";
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
