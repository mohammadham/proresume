@extends('front.layout')
@section('styles')
    <style>
        .template-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            opacity: 0;
            transition: 0.3s ease;
        }

        .features-item:hover .template-overlay {
            opacity: 1;
        }
    </style>
@endsection
@section('pagename')
    - {{ __('Templates') }}
@endsection

@section('meta-description', !empty($seo) ? $seo->templates_meta_description : '')
@section('meta-keywords', !empty($seo) ? $seo->templates_meta_keywords : '')
@section('breadcrumb-title')
    {{ __('Templates') }}
@endsection
@section('breadcrumb-link')
    {{ __('Templates') }}
@endsection
@section('content')
    @if ($bs->templates_section == 1)
        <section class="saas-features pt-120">
            <div class="container">
                <div class="row">
                    @foreach ($templates as $template)
                        <div class="col-lg-4 col-md-6 col-sm-12">
                            <div class="features-item mb-40 position-relative overflow-hidden">
                                <a href="{{ detailsUrl($template) }}" target="_blank">
                                    <img src="{{ asset('assets/front/img/template-previews/' . $template->template_img) }}"
                                        alt="{{ $template->name }}" class="w-100 lazy">
                                    <div class="template-overlay d-flex align-items-center justify-content-center">
                                        <h5 class="text-white m-0">{{ $template->template_name }}</h5>
                                    </div>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
