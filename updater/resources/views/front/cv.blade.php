@extends('front.layout')

@section('pagename')
    - {{ __('CV Templates') }}
@endsection

@section('meta-description', !empty($seo) ? $seo->cv_meta_keywords : '')
@section('meta-keywords', !empty($seo) ? $seo->cv_meta_description : '')
@section('breadcrumb-title')
    {{ __('CV Templates') }}
@endsection
@section('breadcrumb-link')
    {{ __('CV Templates') }}
@endsection

@section('content')
    <section class="cv-template-section py-120 pt-80 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                @foreach ($cvs as $cv)
                    @php
                        $pathUrl = env('WEBSITE_HOST') . '/' . $cv->user->username . '/cv/' . $cv->id;
                        $imagePath = asset('assets/front/img/user/prevtemplate/' . $cv->preview_template_image);
                    @endphp
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                        <div class="cv-card position-relative overflow-hidden rounded-4 shadow-sm border-0">
                            <a href="//{{ $pathUrl }}" target="_blank" class="d-block">
                                <div class="cv-img-wrapper">
                                    <img src="{{ $imagePath }}" alt="CV Template" class="cv-img w-100">
                                </div>
                                <div class="cv-hover-overlay d-flex align-items-center justify-content-center">
                                    <span class="btn btn-primary px-4 py-2">View Template</span>
                                </div>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
