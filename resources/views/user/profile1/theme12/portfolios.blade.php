@extends('user.profile1.theme12.layout')
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
    @if (is_array($userPermissions) && in_array('Portfolio', $userPermissions))
        <section class="project-area pb-lg-90 pb-50 pt-50">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-12" data-aos="fade-up" data-aos-delay="100">


                        <!-- tabs-navigation -->
                        <div class="tabs-navigation tabs-navigation-v2  text-center mb-lg-40 mb-30" data-aos="fade-up"
                            data-aos-delay="300">
                            <ul class="nav nav-tabs gap-24" data-hover="fancyHover">
                                @if (count($portfolio_categories) > 0)
                                    <li class="nav-item active">
                                        <button class="nav-link hover-effect active" data-bs-toggle="tab"
                                            data-bs-target="#allproject"
                                            type="button">{{ $keywords['All'] ?? __('All') }}</button>
                                    </li>
                                @endif
                                @foreach ($portfolio_categories as $portfolio_category)
                                    <li class="nav-item">
                                        <button class="nav-link hover-effect" data-bs-toggle="tab"
                                            data-bs-target="#cat-{{ $portfolio_category->id }}"
                                            type="button">{{ $portfolio_category->name }}</button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="tab-content" id="projectTabContent" data-aos="fade-up" data-aos-delay="400">
                    <div class="tab-pane fade show active" id="allproject" role="tabpanel">
                        <div class="row justify-content-center">
                            @foreach ($portfolios as $portfolio)
                                <div class="col-lg-4 col-sm-6">
                                    <div class="project-card mb-30" onclick="window.location.href='{{ route('front.user.portfolio.detail', [getParam(), $portfolio->slug, $portfolio->id]) }}'">
                                        <figure class="project-image lazy-container ratio ratio-5-3 radius-lg">
                                            <img class="lazyload" src="assets/images/placeholder.svg"
                                                data-src="{{ asset('assets/front/img/user/portfolios/' . $portfolio->image) }}"
                                                alt="project">
                                            <div class="portfolio-project-info">
                                                <h5 class="vericaltext title"><a href="javascript:void(0)">
                                                        {{ strlen($portfolio->title) > 20 ? mb_substr($portfolio->title, 0, 20, 'UTF-8') . '...' : $portfolio->title }}
                                                    </a>
                                                </h5>
                                                <span class="vericaltext"></span>
                                            </div>
                                        </figure>
                                        <a href="{{ route('front.user.portfolio.detail', [getParam(), $portfolio->slug, $portfolio->id]) }}"
                                            class="btn thm-btn">
                                            {{ $keywords['Details'] ?? 'Details' }}
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @foreach ($portfolio_categories as $portfolio_category)
                        @php
                            $categoryPortfolios =
                                $user
                                    ->portfolios()
                                    ->where('language_id', $userCurrentLang->id)
                                    ->where('featured', 1)
                                    ->where('category_id', $portfolio_category->id)
                                    ->orderBy('serial_number', 'ASC')
                                    ->get() ?? collect([]);
                        @endphp
                        <div class="tab-pane fade" id="cat-{{ $portfolio_category->id }}" role="tabpanel">
                            <div class="row justify-content-center">
                                @foreach ($categoryPortfolios as $categoryportfolio)
                                    <div class="col-lg-4 col-sm-6">
                                        <div class="project-card mb-30">
                                            <figure class="project-image lazy-container ratio ratio-5-3 radius-lg">
                                                <img class="lazyload" src="assets/images/placeholder.svg"
                                                    data-src="{{ asset('assets/front/img/user/portfolios/' . $categoryportfolio->image) }}"
                                                    alt="project">
                                                <div class="portfolio-project-info">
                                                    <h5 class="vericaltext title"><a href="javascript:void(0)">
                                                            {{ strlen($categoryportfolio->title) > 20 ? mb_substr($categoryportfolio->title, 0, 20, 'UTF-8') . '...' : $categoryportfolio->title }}
                                                        </a>
                                                    </h5>
                                                    <span class="vericaltext"></span>
                                                </div>
                                            </figure>
                                            <a href="{{ route('front.user.portfolio.detail', [getParam(), $categoryportfolio->slug, $categoryportfolio->id]) }}"
                                                class="btn thm-btn">
                                                {{ $keywords['Details'] ?? 'Details' }}
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </section>
    @endif
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
