@extends("user.$folder.layout")
@section('styles')
    @if (isset($css_file))
        <link rel="stylesheet" href="{{ $css_file }}">
    @endif
@endsection

@section('tab-title')
    {{ $keywords['Gallery'] ?? 'Gallery' }}
@endsection

@section('meta-description', !empty($userSeo) ? $userSeo->gallery_meta_description : '')
@section('meta-keywords', !empty($userSeo) ? $userSeo->gallery_meta_keywords : '')

@section('br-title')
    {{ $keywords['Gallery'] ?? 'Gallery' }}
@endsection
@section('br-link')
    {{ $keywords['Gallery'] ?? 'Gallery' }}
@endsection

@section('content')
    <section class="breadcrumbs-section">
        <div class="container">
            <div class="row align-items-center justify-content-center">
                <div class="col-lg-10">
                    <div class="page-title">
                        <h1>{{ $keywords['Gallery'] ?? 'Gallery' }}</h1>
                        <ul class="breadcrumbs-link">
                            <li><a
                                    href="{{ route('front.user.detail.view', getParam()) }}">{{ $keywords['Home'] ?? 'Home' }}</a>
                            </li>
                            <li class="">{{ $keywords['Gallery'] ?? 'Gallery' }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--====== Start Vaughn-Work section ======-->
    <section class="gallery-section-2 pt-lg-130 pt-60">
        <div class="container">
            <div class="row gallery-popup" data-aos="fade-up" data-aos-delay="200">
                @foreach ($galleries as $gallery)
                    @php
                        $img = 'assets/user/gallery/' . $gallery->image;
                        $imagePath = file_exists(public_path($img))
                            ? asset($img)
                            : asset('assets/front/img/placeholder.jpg');
                    @endphp
                    <div class="col-lg-4 mb-30">
                        <div class="gallery-item">
                            <a href="{{ $imagePath }}" title="{{ $gallery->name }}" class="popup-image">
                                <img src="{{ $imagePath }}" alt="{{ $gallery->name }}">
                            </a>
                            <div class="project-info">
                                <h5 class="vericaltext title"><a href="javascript:void(0)">{{ $gallery->name }}</a></h5>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!--====== End Vaughn-Work section ======-->
@endsection
@section('scripts')
    <script>
        $(function() {
            $('.gallery-popup').each(function() {
                $(this).magnificPopup({
                    delegate: '.popup-image',
                    type: 'image',
                    gallery: {
                        enabled: true,
                        preload: [0, 2],
                        navigateByImgClick: true
                    },
                    image: {
                        titleSrc: function(item) {
                            return item.el.attr('title') || '';
                        },
                        tError: 'Image could not be loaded.'
                    },
                    mainClass: 'mfp-fade',
                    removalDelay: 300,
                    fixedContentPos: false
                });
            });
        });
    </script>
@endsection
