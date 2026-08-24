@extends('front.layout')

@section('pagename')
    - {{ __('VCards') }}
@endsection

@section('meta-description', !empty($seo) ? $seo->vcard_meta_keywords : '')
@section('meta-keywords', !empty($seo) ? $seo->vcard_meta_description : '')
@section('breadcrumb-title')
    {{ __('VCards') }}
@endsection
@section('breadcrumb-link')
    {{ __('VCards') }}
@endsection
@section('content')
    <section class="saas-features pt-120">
        <div class="container">
            @if ($vcards->count() == 0)
                <h4 class="text-center pb-120">{{ $keywords['no_vcards_found'] ?? 'No vCards found' }}</h4>
            @else
                <div class="row">
                    @foreach ($vcards as $vcard)
                        @php
                            $pathUrl = $vcard->user->username . '/vcard/' . $vcard->id;
                        @endphp
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-30">
                            <a class="d-block features-item p-0" href="{{ $pathUrl }}" target="_blank">
                                <div class="vcard-thumb">
                                    <img src="{{ asset('assets/front/img/user/prevtemplate/' . $vcard->preview_template_image) }}"
                                        alt="vCard Preview" class="img-fluid">
                                </div>
                            </a>
                            <h4 class="d-flex justify-content-center align-items-center pt-20">
                                <a href="{{ $pathUrl }}" target="_blank" >{{ $vcard->preview_template_name }}</a>
                            </h4>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

@endsection
