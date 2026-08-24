@extends('admin.layout')

@if (Session::has('admin_lang'))
    @php
        $admin_lang = Session::get('admin_lang');
        $cd = str_replace('admin_', '', $admin_lang);
        $default = \App\Models\Language::where('code', $cd)->first();
    @endphp
@else
    @php
        $default = \App\Models\Language::where('is_default', 1)->first();
    @endphp
@endif
@php
    use App\Models\Language;
    $selLang = Language::where('code', request()->input('language'))->first();
@endphp
@if (!empty($selLang) && $selLang->rtl == 1)
    @section('styles')
        <style>
            form:not(.modal-form) input,
            form:not(.modal-form) textarea,
            form:not(.modal-form) select,
            select[name='language'] {
                direction: rtl;
            }

            form:not(.modal-form) .note-editor.note-frame .note-editing-area .note-editable {
                direction: rtl;
                text-align: right;
            }
        </style>
    @endsection
@endif

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Packages') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') . '?language=' . $default->code }}">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Packages') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card-title d-inline-block">{{ __('Package Page') }}</div>
                        </div>
                        <div class="col-lg-4 offset-lg-4 mt-2 mt-lg-0">
                            <a href="#" class="btn btn-primary float-right btn-sm" data-toggle="modal"
                                data-target="#createModal"><i class="fas fa-plus"></i>
                                {{ __('Add Package') }}</a>
                            <button class="btn btn-danger float-right btn-sm mr-2 d-none bulk-delete"
                                data-href="{{ route('admin.package.bulk.delete') }}"><i class="flaticon-interface-5"></i>
                                {{ __('Delete') }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            @if (count($packages) == 0)
                                <h3 class="text-center">{{ __('NO PACKAGE FOUND YET') }}</h3>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped mt-3" id="basic-datatables">
                                        <thead>
                                            <tr>
                                                <th scope="col">
                                                    <input type="checkbox" class="bulk-check" data-val="all">
                                                </th>
                                                <th scope="col">{{ __('Title') }}</th>
                                                <th scope="col">{{ __('Cost') }}</th>
                                                <th scope="col">{{ __('Status') }}</th>
                                                <th scope="col">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($packages as $key => $package)
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="bulk-check"
                                                            data-val="{{ $package->id }}">
                                                    </td>
                                                    <td>{{ strlen($package->title) > 30 ? mb_substr($package->title, 0, 30, 'UTF-8') . '...' : __($package->title) }}
                                                    </td>
                                                    <td>
                                                        @if ($package->price == 0)
                                                            {{ __('Free') }}
                                                        @else
                                                            {{ format_price($package->price) }}
                                                        @endif

                                                    </td>
                                                    <td>
                                                        @if ($package->status == 1)
                                                            <h2 class="d-inline-block">
                                                                <span
                                                                    class="badge badge-success">{{ __('Active') }}</span>
                                                            </h2>
                                                        @else
                                                            <h2 class="d-inline-block">
                                                                <span
                                                                    class="badge badge-danger">{{ __('Deactive') }}</span>
                                                            </h2>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a class="btn btn-secondary btn-sm"
                                                            href="{{ route('admin.package.edit', $package->id) . '?language=' . $default->code }}">
                                                            <span class="btn-label">
                                                                <i class="fas fa-edit"></i>
                                                            </span>
                                                            {{ __('Edit') }}
                                                        </a>
                                                        <form class="deleteform d-inline-block"
                                                            action="{{ route('admin.package.delete') }}" method="post">
                                                            @csrf
                                                            <input type="hidden" name="package_id"
                                                                value="{{ $package->id }}">
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
    <!-- Create Blog Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Add Package') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <form id="ajaxForm" enctype="multipart/form-data" class="modal-form"
                        action="{{ route('admin.package.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="title">{{ __('Package title') }}*</label>
                            <input id="title" type="text" class="form-control" name="title"
                                placeholder="{{ __('Enter Package title') }}" value="">
                            <p id="errtitle" class="mb-0 text-danger em"></p>
                        </div>
                        <div class="form-group">
                            <label for="price">{{ __('Price') }} ({{ $bex->base_currency_text }})*</label>
                            <input id="price" type="number" class="form-control" name="price"
                                placeholder="{{ __('Enter Package price') }}" value="">
                            <p class="text-warning"><small>{{ __('If price is 0 , than it will appear as free') }}</small>
                            </p>
                            <p id="errprice" class="mb-0 text-danger em"></p>
                        </div>
                        <div class="form-group">
                            <label for="term">{{ __('Package term') }}*</label>
                            <select id="term" name="term" class="form-control" required>
                                <option value="" selected disabled>{{ __('Choose a Package term') }}</option>
                                <option value="monthly">{{ __('monthly') }}</option>
                                <option value="yearly">{{ __('yearly') }}</option>
                                <option value="lifetime">{{ __('lifetime') }}</option>
                            </select>
                            <p id="errterm" class="mb-0 text-danger em"></p>
                        </div>


                        <div class="form-group">
                            <label class="form-label">{{ __('Package Features') }}</label>
                            <div class="selectgroup selectgroup-pills">
                                <label class="selectgroup-item">
                                    <input type="checkbox" name="features[]" value="Custom Domain"
                                        class="selectgroup-input">
                                    <span class="selectgroup-button">{{ __('Custom Domain') }}</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="checkbox" name="features[]" value="Subdomain"
                                        class="selectgroup-input">
                                    <span class="selectgroup-button">{{ __('Subdomain') }}</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="checkbox" name="features[]" value="QR Builder"
                                        class="selectgroup-input">
                                    <span class="selectgroup-button">{{ __('QR Builder') }}</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="checkbox" name="features[]" value="vCard" class="selectgroup-input"
                                        id="vcards">
                                    <span class="selectgroup-button">{{ __('vCard') }}</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="checkbox" name="features[]" value="Online CV & Export"
                                        class="selectgroup-input">
                                    <span class="selectgroup-button">{{ __('Online CV & Export') }}</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="checkbox" name="features[]" value="Follow/Unfollow"
                                        class="selectgroup-input">
                                    <span class="selectgroup-button">{{ __('Follow/Unfollow') }}</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="checkbox" name="features[]" value="Blog" id="blogs"
                                        class="selectgroup-input">
                                    <span class="selectgroup-button">{{ __('Blog') }}</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="checkbox" name="features[]" value="Portfolio" id="portfolios"
                                        class="selectgroup-input">
                                    <span class="selectgroup-button">{{ __('Portfolio') }}</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="checkbox" name="features[]" value="Achievements"
                                        class="selectgroup-input">
                                    <span class="selectgroup-button">{{ __('Achievements') }}</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="checkbox" name="features[]" value="Skill" class="selectgroup-input"
                                        id="skills">
                                    <span class="selectgroup-button">{{ __('Skill') }}</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="checkbox" name="features[]" value="Service" class="selectgroup-input"
                                        id="services">
                                    <span class="selectgroup-button">{{ __('Service') }}</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="checkbox" name="features[]" value="Experience" id="experiences"
                                        class="selectgroup-input">
                                    <span class="selectgroup-button">{{ __('Experience') }}</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="checkbox" name="features[]" value="Testimonial"
                                        class="selectgroup-input">
                                    <span class="selectgroup-button">{{ __('Testimonial') }}</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="checkbox" name="features[]" value="Gallery"
                                        class="selectgroup-input">
                                    <span class="selectgroup-button">{{ __('Gallery') }}</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="checkbox" name="features[]" value="Work Process"
                                        class="selectgroup-input">
                                    <span class="selectgroup-button">{{ __('Work Process') }}</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="checkbox" name="features[]" value="Appointment"
                                        class="selectgroup-input">
                                    <span class="selectgroup-button">{{ __('Appointment') }}</span>
                                </label>

                                <label class="selectgroup-item">
                                    <input type="checkbox" name="features[]" value="Google Analytics"
                                        class="selectgroup-input">
                                    <span class="selectgroup-button">{{ __('Google Analytics') }}</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="checkbox" name="features[]" value="Disqus" class="selectgroup-input">
                                    <span class="selectgroup-button">{{ __('Disqus') }}</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="checkbox" name="features[]" value="WhatsApp" class="selectgroup-input">
                                    <span class="selectgroup-button">{{ __('WhatsApp') }}</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="checkbox" name="features[]" value="Facebook Pixel"
                                        class="selectgroup-input">
                                    <span class="selectgroup-button">{{ __('Facebook Pixel') }}</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="checkbox" name="features[]" value="Tawk.to" class="selectgroup-input">
                                    <span class="selectgroup-button">{{ __('Tawk.to') }}</span>
                                </label>

                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">{{ __('Featured') }} *</label>
                            <div class="selectgroup w-100">
                                <label class="selectgroup-item">
                                    <input type="radio" name="featured" value="1" class="selectgroup-input">
                                    <span class="selectgroup-button">{{ __('Yes') }}</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="radio" name="featured" value="0" class="selectgroup-input"
                                        checked>
                                    <span class="selectgroup-button">{{ __('No') }}</span>
                                </label>
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="form-label">{{ __('Trial') }} *</label>
                            <div class="selectgroup w-100">
                                <label class="selectgroup-item">
                                    <input type="radio" name="is_trial" value="1" class="selectgroup-input">
                                    <span class="selectgroup-button">{{ __('Yes') }}</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="radio" name="is_trial" value="0" class="selectgroup-input"
                                        checked>
                                    <span class="selectgroup-button">{{ __('No') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group" id="trial_day" style="display: none">
                            <label for="trial_days">{{ __('Trial days') }}*</label>
                            <input id="trial_days" type="number" class="form-control" name="trial_days"
                                placeholder="{{ __('Enter trial days') }}" value="">
                            <p id="errtrial_days" class="mb-0 text-danger em"></p>
                        </div>
                        <div class="form-group">
                            <label for="status">{{ __('Status') }}*</label>
                            <select id="status" class="form-control ltr" name="status">
                                <option value="" selected disabled>{{ __('Select a status') }}</option>
                                <option value="1">{{ __('Active') }}</option>
                                <option value="0">{{ __('Deactive') }}</option>
                            </select>
                            <p id="errstatus" class="mb-0 text-danger em"></p>
                        </div>
                        <div class="form-group  blog-number ">
                            <label for="">{{ __('Number of blog') }} * </label>
                            <input type="number" class="form-control" name="number_of_blogs" value="">
                            <p id="errnumber_of_blogs" class="mb-0 text-danger em"></p>
                            <p class="text-warning">{{ __('Enter 999999 , then it will appear as unlimited') }}
                            </p>
                        </div>
                        <div class="form-group blog-category-number">
                            <label for="">{{ __('Number of blog categories') }} * </label>
                            <input type="number" class="form-control" name="number_of_blog_categories" value="">
                            <p id="errnumber_of_blog_categories" class="mb-0 text-danger em"></p>
                            <p class="text-warning">{{ __('Enter 999999 , then it will appear as unlimited') }}
                            </p>
                        </div>

                        <div class="form-group  portfolio-number ">
                            <label for="">{{ __('Number of portfolios') }} * </label>
                            <input type="number" class="form-control" name="number_of_portfolios" value="">
                            <p id="errnumber_of_portfolios" class="mb-0 text-danger em"></p>
                            <p class="text-warning">{{ __('Enter 999999 , then it will appear as unlimited') }}
                            </p>
                        </div>
                        <div class="form-group  portfolio-category-number ">
                            <label for="">{{ __('Number of portfolio categories') }} * </label>
                            <input type="number" class="form-control" name="number_of_portfolio_categories"
                                value="">
                            <p id="errnumber_of_portfolio_categories" class="mb-0 text-danger em"></p>
                            <p class="text-warning">{{ __('Enter 999999 , then it will appear as unlimited') }}
                            </p>
                        </div>

                        <div class="form-group  skill-number ">
                            <label for="">{{ __('Number of skills') }} * </label>
                            <input type="number" class="form-control" name="number_of_skills" value="">
                            <p id="errnumber_of_skills" class="mb-0 text-danger em"></p>
                            <p class="text-warning">{{ __('Enter 999999 , then it will appear as unlimited') }}
                            </p>
                        </div>
                        <div class="form-group  service-number ">
                            <label for="">{{ __('Number of services') }} * </label>
                            <input type="number" class="form-control" name="number_of_services" value="">
                            <p id="errnumber_of_services" class="mb-0 text-danger em"></p>
                            <p class="text-warning">{{ __('Enter 999999 , then it will appear as unlimited') }}
                            </p>
                        </div>
                        <div class="form-group  exprience-number ">
                            <label for="">{{ __('Number of job experiences') }} * </label>
                            <input type="number" class="form-control" name="number_of_job_expriences" value="">
                            <p id="errnumber_of_job_expriences" class="mb-0 text-danger em"></p>
                            <p class="text-warning">{{ __('Enter 999999 , then it will appear as unlimited') }}
                            </p>
                        </div>

                        <div class="form-group  education-number ">
                            <label for="">{{ __('Number of educations') }} * </label>
                            <input type="number" class="form-control" name="number_of_education" value="">
                            <p id="errnumber_of_education" class="mb-0 text-danger em"></p>
                            <p class="text-warning">{{ __('Enter 999999 , then it will appear as unlimited') }}
                            </p>
                        </div>

                        <div class="form-group v-card-box vcrd-none">
                            <label for="">{{ __('Number of vcards') }} * </label>
                            <input type="number" class="form-control" name="number_of_vcards" value="">
                            <p id="errnumber_of_vcards" class="mb-0 text-danger em"></p>
                            <p class="text-warning">{{ __('Enter 999999 , then it will appear as unlimited') }}</p>
                        </div>

                        <div class="form-group  language-number ">
                            <label for="">{{ __('Number of languages') }} * </label>
                            <input type="number" class="form-control" name="number_of_languages" value="">
                            <p id="errnumber_of_languages" class="mb-0 text-danger em"></p>
                            <p class="text-warning">{{ __('Enter 999999 , then it will appear as unlimited') }}
                            </p>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('Themes') }} *</label>
                            <div class="row">
                                <div class="col-3 col-sm-2 mb-2">
                                    <label class="imagecheck mb-2">
                                        <input name="themes[]" type="checkbox" value="1" class="imagecheck-input">
                                        <figure class="imagecheck-figure">
                                            <img src="{{ asset('assets/front/img/user/themes/theme1.jpg') }}"
                                                alt="title" class="imagecheck-image">
                                        </figure>
                                    </label>
                                    <h5 class="text-center">{{ __('Theme 1') }}</h5>
                                </div>
                                <div class="col-3 col-sm-2 mb-2">
                                    <label class="imagecheck mb-2">
                                        <input name="themes[]" type="checkbox" value="2" class="imagecheck-input"
                                            {{ !empty($data->theme) && $data->theme == 2 ? 'checked' : '' }}>
                                        <figure class="imagecheck-figure">
                                            <img src="{{ asset('assets/front/img/user/themes/theme2.jpg') }}"
                                                alt="title" class="imagecheck-image">
                                        </figure>
                                    </label>
                                    <h5 class="text-center">{{ __('Theme 2') }}</h5>
                                </div>
                                <div class="col-3 col-sm-2 mb-2">
                                    <label class="imagecheck mb-2">
                                        <input name="themes[]" type="checkbox" value="3" class="imagecheck-input"
                                            {{ !empty($data->theme) && $data->theme == 3 ? 'checked' : '' }}>
                                        <figure class="imagecheck-figure">
                                            <img src="{{ asset('assets/front/img/user/themes/theme3.jpg') }}"
                                                alt="title" class="imagecheck-image">
                                        </figure>
                                    </label>
                                    <h5 class="text-center">{{ __('Theme 3') }}</h5>
                                </div>
                                <div class="col-3 col-sm-2 mb-2">
                                    <label class="imagecheck mb-2">
                                        <input name="themes[]" type="checkbox" value="4" class="imagecheck-input"
                                            {{ !empty($data->theme) && $data->theme == 4 ? 'checked' : '' }}>
                                        <figure class="imagecheck-figure">
                                            <img src="{{ asset('assets/front/img/user/themes/theme4.jpg') }}"
                                                alt="title" class="imagecheck-image">
                                        </figure>
                                    </label>
                                    <h5 class="text-center">{{ __('Theme 4') }}</h5>
                                </div>
                                <div class="col-3 col-sm-2 mb-2">
                                    <label class="imagecheck mb-2">
                                        <input name="themes[]" type="checkbox" value="5" class="imagecheck-input"
                                            {{ !empty($data->theme) && $data->theme == 5 ? 'checked' : '' }}>
                                        <figure class="imagecheck-figure">
                                            <img src="{{ asset('assets/front/img/user/themes/theme5.jpg') }}"
                                                alt="title" class="imagecheck-image">
                                        </figure>
                                    </label>
                                    <h5 class="text-center">{{ __('Theme 5') }}</h5>
                                </div>
                                <div class="col-3 col-sm-2 mb-2">
                                    <label class="imagecheck mb-2">
                                        <input name="themes[]" type="checkbox" value="6" class="imagecheck-input"
                                            {{ !empty($data->theme) && $data->theme == 6 ? 'checked' : '' }}>
                                        <figure class="imagecheck-figure">
                                            <img src="{{ asset('assets/front/img/user/themes/theme6.png') }}"
                                                alt="title" class="imagecheck-image">
                                        </figure>
                                    </label>
                                    <h5 class="text-center">{{ __('Theme 6') }}</h5>
                                </div>
                                <div class="col-3 col-sm-2 mb-2">
                                    <label class="imagecheck mb-2">
                                        <input name="themes[]" type="checkbox" value="7" class="imagecheck-input"
                                            {{ !empty($data->theme) && $data->theme == 7 ? 'checked' : '' }}>
                                        <figure class="imagecheck-figure">
                                            <img src="{{ asset('assets/front/img/user/themes/theme7.png') }}"
                                                alt="title" class="imagecheck-image">
                                        </figure>
                                    </label>
                                    <h5 class="text-center">{{ __('Theme 7') }}</h5>
                                </div>
                                <div class="col-3 col-sm-2 mb-2">
                                    <label class="imagecheck mb-2">
                                        <input name="themes[]" type="checkbox" value="8" class="imagecheck-input"
                                            {{ !empty($data->theme) && $data->theme == 8 ? 'checked' : '' }}>
                                        <figure class="imagecheck-figure">
                                            <img src="{{ asset('assets/front/img/user/themes/theme8.png') }}"
                                                alt="title" class="imagecheck-image">
                                        </figure>
                                    </label>
                                    <h5 class="text-center">{{ __('Theme 8') }}</h5>
                                </div>


                                <div class="col-3 col-sm-2 mb-2">
                                    <label class="imagecheck mb-2">
                                        <input name="themes[]" type="checkbox" value="9" class="imagecheck-input"
                                            {{ !empty($data->theme) && $data->theme == 9 ? 'checked' : '' }}>
                                        <figure class="imagecheck-figure">
                                            <img src="{{ asset('assets/front/img/user/themes/theme9.png') }}"
                                                alt="title" class="imagecheck-image">
                                        </figure>
                                    </label>
                                    <h5 class="text-center">{{ __('Theme 9') }}</h5>
                                </div>
                                <div class="col-3 col-sm-2 mb-2">
                                    <label class="imagecheck mb-2">
                                        <input name="themes[]" type="checkbox" value="10" class="imagecheck-input"
                                            {{ !empty($data->theme) && $data->theme == 10 ? 'checked' : '' }}>
                                        <figure class="imagecheck-figure">
                                            <img src="{{ asset('assets/front/img/user/themes/theme10.png') }}"
                                                alt="title" class="imagecheck-image">
                                        </figure>
                                    </label>
                                    <h5 class="text-center">{{ __('Theme 10') }}</h5>
                                </div>
                                <div class="col-3 col-sm-2 mb-2">
                                    <label class="imagecheck mb-2">
                                        <input name="themes[]" type="checkbox" value="11" class="imagecheck-input"
                                            {{ !empty($data->theme) && $data->theme == 11 ? 'checked' : '' }}>
                                        <figure class="imagecheck-figure">
                                            <img src="{{ asset('assets/front/img/user/themes/theme11.png') }}"
                                                alt="title" class="imagecheck-image">
                                        </figure>
                                    </label>
                                    <h5 class="text-center">{{ __('Theme 11') }}</h5>
                                </div>
                                <div class="col-3 col-sm-2 mb-2">
                                    <label class="imagecheck mb-2">
                                        <input name="themes[]" type="checkbox" value="12" class="imagecheck-input"
                                            {{ !empty($data->theme) && $data->theme == 12 ? 'checked' : '' }}>
                                        <figure class="imagecheck-figure">
                                            <img src="{{ asset('assets/front/img/user/themes/theme12.png') }}"
                                                alt="title" class="imagecheck-image">
                                        </figure>
                                    </label>
                                    <h5 class="text-center">{{ __('Theme 12') }}</h5>
                                </div>
                                <div class="col-12">
                                    <p id="errthemes" class="mb-0 text-danger em"></p>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="">{{ __('Meta Keywords') }}</label>
                            <input type="text" class="form-control" name="meta_keywords" value=""
                                data-role="tagsinput">
                        </div>
                        <div class="form-group">
                            <label for="meta_description">{{ __('Meta Description') }}</label>
                            <textarea id="meta_description" type="text" class="form-control" name="meta_description" rows="5"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                    <button data-form="ajaxForm" type="button"
                        class="submitBtn btn btn-primary">{{ __('Submit') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/admin/js/packages.js') }}"></script>
@endsection
