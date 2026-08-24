@extends('user.profile1.theme10.layout')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/front/theme9-12/css/inner-common.css') }}">
@endsection

@section('tab-title')
    {{ $keywords['Portfolios'] ?? 'Portfolios' }}
@endsection

@section('meta-description', !empty($userSeo) ? $userSeo->portfolios_meta_description : '')
@section('meta-keywords', !empty($userSeo) ? $userSeo->portfolios_meta_keywords : '')

@section('br-title')
    {{ $keywords['Portfolios'] ?? 'Portfolios' }}
@endsection
@section('br-link')
    {{ $keywords['Portfolios'] ?? 'Portfolios' }}
@endsection

@section('content')
    <section class="breadcrumbs-section">
        <div class="container">
            <div class="row align-items-center justify-content-center">
                <div class="col-lg-10">
                    <div class="page-title">
                        <h1>{{ $keywords['Portfolios'] ?? 'Portfolios' }}</h1>
                        <ul class="breadcrumbs-link">
                            <li><a
                                    href="{{ route('front.user.detail.view', getParam()) }}">{{ $keywords['Home'] ?? 'Home' }}</a>
                            </li>
                            <li class="">{{ $keywords['Portfolios'] ?? 'Portfolios' }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--====== Start Vaughn-Work section ======-->
    <section class="page-content-section section-gap">
        <div class="container">
            <div class="project-filter">
                <ul>
                    <li data-filter="*" class="{{ empty(request('category')) ? 'active' : '' }}">
                        {{ $keywords['All'] ?? 'All' }}</li>
                    @foreach ($portfolio_categories as $portfolio_category)
                        <li class="{{ request('category') == $portfolio_category->id ? 'active' : '' }}"
                            data-filter=".cat-{{ $portfolio_category->id }}">{{ $portfolio_category->name }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="project-loop row">
                @foreach ($portfolios as $portfolio)
                    <div class="col-lg-4 col-md-6 cat-{{ $portfolio->bcategory->id }}">
                        <div class="project-item">
                            <div class="project-thumbnail">
                                <img class="lazy"
                                    data-src="{{ asset('assets/front/img/user/portfolios/' . $portfolio->image) }}"
                                    alt="ProjectImage">
                            </div>
                            <div class="hover-content">
                                <a href="{{ route('front.user.portfolio.detail', [getParam(), $portfolio->slug, $portfolio->id]) }}"
                                    class="plus-icon"></a>
                                <a href="{{ route('front.user.portfolio.detail', [getParam(), $portfolio->slug, $portfolio->id]) }}"
                                    class="title">{{ strlen($portfolio->title) > 25 ? mb_substr($portfolio->title, 0, 25, 'UTF-8') . '...' : $portfolio->title }}</a>
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
    <!--====== Isotope ======-->
    <script src="{{ asset('assets/front/js/profile/theme6-8/isotope.pkgd.min.js') }}"></script>
    <!--====== Images loaded ======-->
    <script src="{{ asset('assets/front/js/profile/theme6-8/imagesloaded.pkgd.min.js') }}"></script>
    <script>
        $(window).on('load', function() {
            // Wait for images to be loaded before initializing Isotope
            $('.project-loop').imagesLoaded(function() {
                var $grid = $('.project-loop').isotope({
                    itemSelector: '.col-lg-4',
                    layoutMode: 'fitRows'
                });

                // Bind filter button click
                $('.project-filter ul').on('click', 'li', function() {
                    var filterValue = $(this).attr('data-filter');
                    $grid.isotope({
                        filter: filterValue
                    });

                    // Add 'active' class to the clicked button and remove from others
                    $(this).addClass('active').siblings().removeClass('active');
                });
            });
        });
    </script>
@endsection
