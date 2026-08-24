@extends('user.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{  __('Sections') }}</h4>
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
                <a href="{{ route('user.cv') }}">{{ __('CVs Management') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ $cv->cv_name }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Sections') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card-title d-inline-block">{{ __('Sections') }}</div>
                        </div>
                        <div class="col-lg-4">
                        </div>
                        <div class="col-lg-4 mt-2 mt-lg-0">
                            <a class="btn btn-primary float-right text-white" data-toggle="modal"
                                data-target="#createModal">{{ __('Add Section') }}</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            @if (count($sections) == 0)
                                <h3 class="text-center">{{ __('NO SECTION FOUND') }}</h3>
                            @else
                                <div>
                                    <p class="text-warning mb-0">**
                                        {{ __('Drag & Drop the sections to change the order') }}
                                    </p>
                                    <table class="table table-striped mt-3">
                                        <thead>
                                            <tr>
                                                <th scope="col">
                                                    #
                                                </th>
                                                <th scope="col">{{ __('Icon') }}</th>
                                                <th scope="col">{{ __('Section Name') }}
                                                </th>
                                                @if ($cv->template == 1)
                                                    <th scope="col">
                                                        {{ __('Column Side') }}</th>
                                                @endif
                                                <th scope="col">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="sortable">
                                            @foreach ($sections as $key => $section)
                                                <tr class="ui-state-default" data-id="{{ $section->id }}">
                                                    <td>
                                                        <span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
                                                    </td>
                                                    <td>
                                                        <i class="{{ $section->icon }}"></i>
                                                    </td>
                                                    <td dir="{{ $section->user_cv->direction == 2 ? 'rtl' : 'ltr' }}">
                                                        {!! $section->name !!}</td>
                                                    @if ($cv->template == 1)
                                                        <td>{{ $section->column == 1 ? 'Left' : 'Right' }}</td>
                                                    @endif
                                                    <td>
                                                        <a class="btn btn-secondary btn-sm text-white"
                                                            href="{{ route('user.cv.section.edit', $section->id) . '?language=' . request('language') }}">
                                                            <span class="btn-label">
                                                                <i class="fas fa-edit"></i>
                                                            </span>
                                                            {{ __('Edit') }}
                                                        </a>
                                                        <a class="btn btn-secondary btn-sm text-white"
                                                            href="{{ route('user.cv.section.content', $section->id) . '?language=' . request('language') }}">
                                                            <span class="btn-label">
                                                                <i class="fas fa-edit"></i>
                                                            </span>
                                                            {{ __('Section Content') }}
                                                        </a>
                                                        <form class="deleteform d-inline-block"
                                                            action="{{ route('user.cv.section.delete') }}" method="post">
                                                            @csrf
                                                            <input type="hidden" name="section_id"
                                                                value="{{ $section->id }}">
                                                            <button type="submit" class="btn btn-danger btn-sm deletebtn">
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
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- Create Section Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">
                        {{ __('Add Section') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if ($cv->direction == 2)
                        <div class="col-12">
                            <div class="alert alert-info text-dark">
                                {{ __('If you want to enter') }} <strong>{{ __('LTR word / text') }}</strong> {{ __('in') }} <strong>{{ __('Section Name') }}</strong> {{ __('field, then wrap that') }}<strong>{{ __('word / text') }}</strong> {{ __('with') }}
                                <strong><code>{{ '<span dir="ltr"></span>' }}</code></strong>
                                <br>
                                {{ __('For example,') }}
                                <div class="row">
                                    <div class="col-6">
                                        <ul class="pl-3">
                                            <li class="mb-2">
                                                <input dir="rtl" class="form-control"
                                                    value="{{ '<span dir="ltr">This is LTR Text</span>' }} هناك حقيقة">
                                            </li>
                                            <li class="mb-2">
                                                <input dir="rtl" class="form-control"
                                                    value="هناك حقيقة {{ '<span dir="ltr">1234567</span>' }}">
                                            </li>
                                            <li>
                                                <input dir="rtl" class="form-control"
                                                    value="{{ '<span dir="ltr">This is LTR Text</span>' }}">
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    <form id="ajaxForm" enctype="multipart/form-data" class="modal-form"
                        action="{{ route('user.cv.section.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="cv_id" value="{{ $cv->id }}">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="">{{ __('Icon') }} **</label>
                                    <div class="btn-group d-block">
                                        <button type="button" class="btn btn-primary iconpicker-component"><i
                                                class="fas fa-heart"></i></button>
                                        <button type="button" class="icp icp-dd btn btn-primary dropdown-toggle"
                                            data-selected="fa-car" data-toggle="dropdown">
                                        </button>
                                        <div class="dropdown-menu"></div>
                                    </div>
                                    <input id="inputIcon" type="hidden" name="icon" value="fas fa-heart">
                                    <p class="em text-danger mb-0" id="erricon"></p>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="">{{ __('Section Name') }}
                                        **</label>
                                    <input type="text" class="form-control {{ $cv->direction == 2 ? 'rtl' : '' }}"
                                        name="name" value="">
                                    <p class="em text-danger mb-0" id="errname"></p>
                                </div>
                            </div>
                        </div>
                        @if ($cv->template == 1)
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="">{{ __('Which Column') }}
                                            **</label>
                                        <select name="column" id="" class="form-control">
                                            <option value="" selected disabled>
                                                {{ __('Select a Column') }}</option>
                                            <option value="1">
                                                {{ __('Left Column in CV') }}</option>
                                            <option value="2">
                                                {{ __('Right Column in CV') }}
                                            </option>
                                        </select>
                                        <p id="errcolumn" class="em text-danger mb-0"></p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-dismiss="modal">{{ __('Close') }}</button>
                    <button id="" data-form="ajaxForm" type="button"
                        class="submitBtn btn btn-primary">{{ __('Submit') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $("#sortable").sortable({
                stop: function(event, ui) {
                    $(".request-loader").addClass('show');
                    let fd = new FormData();
                    $(".ui-state-default").each(function(index) {
                        fd.append('ids[]', $(this).data('id'));
                        let order = parseInt(index) + 1
                        fd.append('orders[]', order);
                    });
                    $.ajax({
                        url: "{{ route('user.cv.section.order') }}",
                        method: 'POST',
                        data: fd,
                        contentType: false,
                        processData: false,
                        success: function(data) {
                            $(".request-loader").removeClass('show');
                            bootnotify("Order updated!", "Success", "success");
                        }
                    })
                }
            });
            $("#sortable").disableSelection();
        });
    </script>
@endsection
