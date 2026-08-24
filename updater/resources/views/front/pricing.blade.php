@extends('front.layout')

@section('pagename')
  - {{ __('Pricing') }}
@endsection

@section('meta-description', !empty($seo) ? $seo->pricing_meta_description : '')
@section('meta-keywords', !empty($seo) ? $seo->pricing_meta_keywords : '')

@section('breadcrumb-title')
  {{ __('Pricing') }}
@endsection
@section('breadcrumb-link')
  {{ __('Pricing') }}
@endsection

@section('content')

  <!--====== Start saas-pricing section ======-->
  <section class="saas-pricing pricing-page pt-110 pb-120">
    <div class="container">

      @if (count($terms) > 1)
        <div class="row justify-content-center">
          <div class="col-lg-4">
            <div class="pricing-tabs text-center">
              <ul class="nav nav-tabs">
                @foreach ($terms as $term)
                  <li class="nav-item mr-1">
                    <a class="nav-link {{ $loop->first ? 'active' : '' }}" data-toggle="tab"
                      href="#{{ strtolower($term) }}">{{ __("$term") }}</a>
                  </li>
                @endforeach
              </ul>
            </div>
          </div>
        </div>
      @endif

      <div class="pricing-wrapper tab-content">
        @foreach ($terms as $term)
          <div id="{{ strtolower($term) }}" class="tab-pane {{ $loop->first ? 'show active' : '' }} fade">
            <div class="row no-gutters ">
              @php
                $packages = \App\Models\Package::where('status', '1')->where('term', strtolower($term))->get();
              @endphp
              @foreach ($packages as $package)
                @php
                  $pFeatures = json_decode($package->features);
                  // dd($pFeatures,$allPfeatures)
                @endphp
                <div class="col-lg-4 col-md-6 col-sm-12">
                  <div class="pricing-item">
                    <div class="title">
                      <h3>{{ __($package->title) }}</h3>
                      <h2 class="price">
                        {{ $package->price != 0 && $be->base_currency_symbol_position == 'left' ? $be->base_currency_symbol : '' }}{{ $package->price == 0 ? __('Free') : $package->price }}{{ $package->price != 0 && $be->base_currency_symbol_position == 'right' ? $be->base_currency_symbol : '' }}
                        <span class="month">/ {{ __($package->term) }}</span>
                      </h2>
                    </div>
                    <div class="pricing-body">
                      <ul class="list">
                        @php
                          $themes = 0;
                        @endphp
                        @if ($package->themes)
                          @php
                            $themes = count(json_decode($package->themes, true));
                          @endphp
                        @endif
                        <li class="{{ $themes > 0 ? 'checked' : 'unchecked' }}">

                          {{ __('Themes') }}
                          @if ($themes == 999999)
                            {{ __('unlimited') }}
                          @elseif($themes > 0)
                            {{ '(' . $themes . ')' }}
                          @endif
                        </li>


                        @foreach ($allPfeatures as $feature)
                          @if ($feature == 'vCard')
                            <li
                              class="{{ is_array($pFeatures) && in_array($feature, $pFeatures) && $package->number_of_vcards > 0 ? 'checked' : 'unchecked' }}">
                              {{ __($feature) }}
                              @if (is_null($package->number_of_vcards) || empty($package->number_of_vcards))
                              @elseif($package->number_of_vcards == 999999)
                                {{ __('unlimited') }}
                              @else
                                {{ '(' . $package->number_of_vcards . ')' }}
                              @endif
                            </li>
                          @elseif ($feature == 'Blog')
                            <li
                              class="{{ is_array($pFeatures) && in_array($feature, $pFeatures) && $package->number_of_blogs > 0 ? 'checked' : 'unchecked' }}">
                              {{ __($feature) }}

                              @if (is_null($package->number_of_blogs) || empty($package->number_of_blogs))
                              @elseif($package->number_of_blogs == 999999)
                                {{ __('unlimited') }}
                              @else
                                {{ '(' . $package->number_of_blogs . ')' }}
                              @endif

                            </li>

                            <li class="{{ $package->number_of_blog_categories ? 'checked' : 'unchecked' }}">

                              {{ __('Blog Category') }}
                              @if ($package->number_of_blog_categories == 999999)
                                {{ __('unlimited') }}
                              @elseif($package->number_of_blog_categories > 0)
                                {{ ' (' . $package->number_of_blog_categories . ')' }}
                              @endif
                            </li>
                          @elseif ($feature == 'Portfolio')
                            <li
                              class="{{ is_array($pFeatures) && in_array($feature, $pFeatures) && $package->number_of_portfolios > 0 ? 'checked' : 'unchecked' }}">
                              {{ __($feature) }}

                              @if (is_null($package->number_of_portfolios) || empty($package->number_of_portfolios))
                              @elseif($package->number_of_portfolios == 999999)
                                {{ __('unlimited') }}
                              @else
                                {{ '(' . $package->number_of_portfolios . ')' }}
                              @endif

                            </li>


                            <li class="{{ $package->number_of_portfolio_categories ? 'checked' : 'unchecked' }}">

                              {{ __('Portfolio Category') }}

                              @if ($package->number_of_portfolio_categories == 999999)
                                {{ __('unlimited') }}
                              @elseif($package->number_of_portfolio_categories > 0)
                                {{ ' (' . $package->number_of_portfolio_categories . ')' }}
                              @endif


                            </li>
                          @elseif ($feature == 'Service')
                            <li
                              class="{{ is_array($pFeatures) && in_array($feature, $pFeatures) && $package->number_of_services > 0 ? 'checked' : 'unchecked' }}">
                              {{ __($feature) }}

                              @if (is_null($package->number_of_services) || empty($package->number_of_services))
                                {{-- {{ 0 }} --}}
                              @elseif($package->number_of_services == 999999)
                                {{ __('unlimited') }}
                              @else
                                {{ '(' . $package->number_of_services . ')' }}
                              @endif

                            </li>
                          @elseif ($feature == 'Skill')
                            <li
                              class="{{ is_array($pFeatures) && in_array($feature, $pFeatures) && $package->number_of_skills > 0 ? 'checked' : 'unchecked' }}">
                              {{ __($feature) }}

                              @if (is_null($package->number_of_skills) || empty($package->number_of_skills))
                              @elseif($package->number_of_skills == 999999)
                                {{ __('unlimited') }}
                              @else
                                {{ '(' . $package->number_of_skills . ')' }}
                              @endif
                            </li>
                          @elseif ($feature == 'Experience')
                            <li
                              class="{{ is_array($pFeatures) && in_array($feature, $pFeatures) && $package->number_of_job_expriences > 0 ? 'checked' : 'unchecked' }}">
                              {{ __($feature) }}

                              @if (is_null($package->number_of_skills) || empty($package->number_of_skills))
                              @elseif($package->number_of_skills == 999999)
                                {{ __('unlimited') }}
                              @else
                                {{ '(' . $package->number_of_skills . ')' }}
                              @endif


                            </li>
                          @elseif ($feature == 'Custom Domain')
                            <li
                              class=" {{ is_array($pFeatures) && in_array($feature, $pFeatures) ? 'checked' : 'unchecked' }}">
                              {{ __($feature) }} </li>
                          @elseif ($feature == 'Subdomain')
                            <li
                              class=" {{ is_array($pFeatures) && in_array($feature, $pFeatures) ? 'checked' : 'unchecked' }}">
                              {{ __($feature) }} </li>
                          @elseif ($feature == 'QR Builder')
                            <li
                              class=" {{ is_array($pFeatures) && in_array($feature, $pFeatures) ? 'checked' : 'unchecked' }}">
                              {{ __($feature) }} </li>
                          @elseif ($feature == 'Online CV & Export')
                            <li
                              class=" {{ is_array($pFeatures) && in_array($feature, $pFeatures) ? 'checked' : 'unchecked' }}">
                              {{ __($feature) }} </li>
                          @elseif ($feature == 'Follow/Unfollow')
                            <li
                              class=" {{ is_array($pFeatures) && in_array($feature, $pFeatures) ? 'checked' : 'unchecked' }}">
                              {{ __($feature) }} </li>
                          @elseif ($feature == 'Achievements')
                            <li
                              class=" {{ is_array($pFeatures) && in_array($feature, $pFeatures) ? 'checked' : 'unchecked' }}">
                              {{ __($feature) }} </li>
                          @elseif ($feature == 'Testimonial')
                            <li
                              class=" {{ is_array($pFeatures) && in_array($feature, $pFeatures) ? 'checked' : 'unchecked' }}">
                              {{ __($feature) }} </li>
                          @elseif ($feature == 'Appointment')
                            <li
                              class=" {{ is_array($pFeatures) && in_array($feature, $pFeatures) ? 'checked' : 'unchecked' }}">
                              {{ __($feature) }} </li>
                          @elseif ($feature == 'Google Analytics')
                            <li
                              class=" {{ is_array($pFeatures) && in_array($feature, $pFeatures) ? 'checked' : 'unchecked' }}">
                              {{ __($feature) }} </li>
                          @elseif ($feature == 'Disqus')
                            <li
                              class=" {{ is_array($pFeatures) && in_array($feature, $pFeatures) ? 'checked' : 'unchecked' }}">
                              {{ __($feature) }} </li>
                          @elseif ($feature == 'WhatsApp')
                            <li
                              class=" {{ is_array($pFeatures) && in_array($feature, $pFeatures) ? 'checked' : 'unchecked' }}">
                              {{ __($feature) }} </li>
                          @elseif ($feature == 'Facebook Pixel')
                            <li
                              class=" {{ is_array($pFeatures) && in_array($feature, $pFeatures) ? 'checked' : 'unchecked' }}">
                              {{ __($feature) }} </li>
                          @elseif ($feature == 'Tawk.to')
                            <li
                              class=" {{ is_array($pFeatures) && in_array($feature, $pFeatures) ? 'checked' : 'unchecked' }}">
                              {{ __($feature) }} </li>
                          @endif
                        @endforeach

                        <li
                          class="{{ !empty($package->number_of_languages) && $package->number_of_languages > 0 ? 'checked' : 'unchecked' }}">

                          {{ __('Languages') }}
                          @if ($package->number_of_languages == 999999)
                            {{ __('unlimited') }}
                          @elseif($package->number_of_languages > 0)
                            {{ ' (' . $package->number_of_languages . ')' }}
                          @endif

                        </li>

                        <li
                          class="{{ !empty($package->number_of_education) && $package->number_of_education > 0 ? 'checked' : 'unchecked' }}">

                          {{ __('Education') }}

                          @if ($package->number_of_education == 999999)
                            {{ __('unlimited') }}
                          @elseif($package->number_of_education > 0)
                            {{ ' (' . $package->number_of_education . ')' }}
                          @endif


                        </li>


                      </ul>
                    </div>
                    <div class="pricing-button">
                      @if ($package->is_trial === '1' && $package->price != 0)
                        <a href="{{ route('front.register.view', ['status' => 'trial', 'id' => $package->id]) }}"
                          class="main-btn">{{ __('Trial') }}</a>
                      @endif
                      @if ($package->price == 0)
                        <a href="{{ route('front.register.view', ['status' => 'regular', 'id' => $package->id]) }}"
                          class="main-btn">{{ __('Signup') }}</a>
                      @else
                        <a href="{{ route('front.register.view', ['status' => 'regular', 'id' => $package->id]) }}"
                          class="main-btn">{{ __('Purchase') }}</a>
                      @endif
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        @endforeach

      </div>

    </div>
  </section>
  <!--====== End saas-pricing section ======-->
@endsection
