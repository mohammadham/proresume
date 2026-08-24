@extends('user.layout')
@section('styles')
  <link rel="stylesheet" href="{{ asset('assets/admin/css/buy_plan.css') }}">
@endsection


@php
  $user = Auth::guard('web')->user();
  $package = \App\Http\Helpers\UserPermissionHelper::currentPackagePermission($user->id);
@endphp


@section('content')

  @php
    $selLang = $userDashboardLang;
    $currentLang = App\Models\Language::where('code', Session::get('userDashboardLang'))->first();
  @endphp
  @if (!empty($selLang) && $selLang->rtl == 1)
    <style>
      .card-pricing2 .pricing-content {
        transform: translate3d(-0px, 0, 0) !important;
      }

      .card-pricing2 .pricing-content {
        text-align: right;
      }

      .card-pricing2 .pricing-content li.disable:before,
      .card-pricing2 .pricing-content li:before {
        left: unset;
        right: -50px;
      }
    </style>
  @endif

  @if (is_null($package))

    @php
      $pendingMemb = \App\Models\Membership::query()
          ->where([['user_id', '=', Auth::id()], ['status', 0]])
          ->whereYear('start_date', '<>', '9999')
          ->orderBy('id', 'DESC')
          ->first();
      $pendingPackage = isset($pendingMemb) ? \App\Models\Package::query()->findOrFail($pendingMemb->package_id) : null;

      $msg1 = __('You have requested a package which needs an action (Approval / Rejection) by Admin');
      $msg2 = __('You will be notified via mail once an action is taken');
    @endphp

    @if ($pendingPackage)
      <div class="alert alert-warning">
        {{$msg1 . '. '.  $msg2}}
      </div>
      <div class="alert alert-warning">
        <strong> {{ __('Pending Package') }}: </strong> {{ $pendingPackage->title }}
        <span class="badge badge-secondary">{{ $pendingPackage->term }}</span>
        <span class="badge badge-warning">{{ __('Decision Pending') }}</span>
      </div>
    @else
      <div class="alert alert-warning">
        {{__('Your membership is expired') . '. ' . __('Please purchase a new package / extend the current package') .'.' }}
      </div>
    @endif
  @else
    <div class="row justify-content-center align-items-center mb-1">
      <div class="col-12">
        <div class="alert border-left border-primary text-dark">
          @if ($package_count >= 2)
            @if ($next_membership->status == 0)
              <strong
                class="text-danger">{{__('You have requested a package which needs an action (Approval / Rejection) by Admin') . '. ' .__('You will be notified via mail once an action is taken') . '.' }}</strong><br>
            @elseif ($next_membership->status == 1)
              <strong
                class="text-danger">{{__('You have another package to activate after the current package expires') . '. '. __('You cannot purchase / extend any package, until the next package is activated') }}
              </strong><br>
            @endif
          @endif

          <strong>{{ __('Current Package') }}: </strong>
          {{__($current_package->title) }}
          <span
            class="badge badge-secondary">{{ __($current_package->term) }}</span>
          @if ($current_membership->is_trial == 1)
            ({{ __('Expire Date') }}:
            {{ Carbon\Carbon::parse($current_membership->expire_date)->format('M-d-Y') }})
            <span class="badge badge-primary">{{ __('Trial') }}</span>
          @else
            ({{ __('Expire Date') }}:
            {{ $current_package->term === 'lifetime' ?  __('Lifetime') : Carbon\Carbon::parse($current_membership->expire_date)->format('M-d-Y') }})
          @endif

          @if ($package_count >= 2)
            <div>
              <strong>{{ __('Next Package To Activate') }}:
              </strong> {{ $next_package->title }} <span class="badge badge-secondary">{{ $next_package->term }}</span>
              @if ($current_package->term != 'lifetime' && $current_membership->is_trial != 1)
                (
                {{ __('Activation Date') }}:
                {{ Carbon\Carbon::parse($next_membership->start_date)->format('M-d-Y') }},
                {{  __('Expire Date') }}:
                {{ $next_package->term === 'lifetime' ?  __('Lifetime') : Carbon\Carbon::parse($next_membership->expire_date)->format('M-d-Y') }})
              @endif
              @if ($next_membership->status == 0)
                <span class="badge badge-warning">{{ __('Decision Pending') }}</span>
              @endif
            </div>
          @endif
        </div>
      </div>
    </div>
  @endif

  <div class="row mb-5 justify-content-center">
    @foreach ($packages as $key => $package)
      <div class="col-md-3 pr-md-0 mb-5">
        <div class="card-pricing2 @if (isset($current_package->id) && $current_package->id === $package->id) card-success @else card-primary @endif">
          <div class="pricing-header">
            <h3 class="fw-bold d-inline-block">
            
              {{ __($package->title) }}
            </h3>
            @if (isset($current_package->id) && $current_package->id === $package->id)
              <h3 class="badge badge-danger d-inline-block float-right ml-2">{{ __('Current') }}</h3>
            @endif
            @if ($package_count >= 2 && $next_package->id == $package->id)
              @if ($next_membership->status == 1)
                <h3 class="badge badge-warning d-inline-block float-right ml-2">
                  {{ __('Next') }}</h3>
              @endif
            @endif
            <span class="sub-title"></span>
          </div>
          <div class="price-value">
            <div class="value">
              <span
                class="amount">{{ $package->price == 0 ?  __('Free') : format_price($package->price) }}</span>
              <span class="month">/{{ __($package->term) }}</span>
            </div>
          </div>

          @php
            $pFeatures = json_decode($package->features);
          @endphp
          <ul class="list pricing-content">
            @php
              $themes = 0;
            @endphp
            @if ($package->themes)
              @php
                $themes = count(json_decode($package->themes, true));
              @endphp
            @endif
            <li class="{{ $themes > 0 ? 'active' : 'disable' }}">

              {{ __('Themes') }}
              @if ($themes == 999999)
                {{ __('unlimited') }}
              @elseif($themes > 0)
                {{ '(' . $themes . ')' }}
              @endif
            </li>


            @foreach ($allPfeatures as $feature)
            @php
                $feature =str_replace(' ', '_', $feature);
            @endphp
              @if ($feature == 'vCard')
                <li
                  class="{{ is_array($pFeatures) && in_array($feature, $pFeatures) && $package->number_of_vcards > 0 ? 'active' : 'disable' }}">
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
                  class="{{ is_array($pFeatures) && in_array($feature, $pFeatures) && $package->number_of_blogs > 0 ? 'active' : 'disable' }}">
                  {{ __($feature) }}

                  @if (is_null($package->number_of_blogs) || empty($package->number_of_blogs))
                  @elseif($package->number_of_blogs == 999999)
                    {{ __('unlimited') }}
                  @else
                    {{ '(' . $package->number_of_blogs . ')' }}
                  @endif

                </li>

                <li class="{{ $package->number_of_blog_categories ? 'active' : 'disable' }}">

                  {{ __('Blog Category') }}
                  @if ($package->number_of_blog_categories == 999999)
                    {{ __('unlimited') }}
                  @elseif($package->number_of_blog_categories > 0)
                    {{ ' (' . $package->number_of_blog_categories . ')' }}
                  @endif
                </li>
              @elseif ($feature == 'Portfolio')
                <li
                  class="{{ is_array($pFeatures) && in_array($feature, $pFeatures) && $package->number_of_portfolios > 0 ? 'active' : 'disable' }}">
                  {{ __($feature) }}

                  @if (is_null($package->number_of_portfolios) || empty($package->number_of_portfolios))
                  @elseif($package->number_of_portfolios == 999999)
                    {{ __('unlimited') }}
                  @else
                    {{ '(' . $package->number_of_portfolios . ')' }}
                  @endif

                </li>


                <li class="{{ $package->number_of_portfolio_categories ? 'active' : 'disable' }}">

                  {{ __('Portfolio Category') }}

                  @if ($package->number_of_portfolio_categories == 999999)
                    {{ __('unlimited') }}
                  @elseif($package->number_of_portfolio_categories > 0)
                    {{ ' (' . $package->number_of_portfolio_categories . ')' }}
                  @endif


                </li>
              @elseif ($feature == 'Service')
                <li
                  class="{{ is_array($pFeatures) && in_array($feature, $pFeatures) && $package->number_of_services > 0 ? 'active' : 'disable' }}">
                  {{ __($feature) }}

                  @if (is_null($package->number_of_services) || empty($package->number_of_services))
                  @elseif($package->number_of_services == 999999)
                    {{ __('unlimited') }}
                  @else
                    {{ '(' . $package->number_of_services . ')' }}
                  @endif

                </li>
              @elseif ($feature == 'Skill')
                <li
                  class="{{ is_array($pFeatures) && in_array($feature, $pFeatures) && $package->number_of_skills > 0 ? 'active' : 'disable' }}">
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
                  class="{{ is_array($pFeatures) && in_array($feature, $pFeatures) && $package->number_of_job_expriences > 0 ? 'active' : 'disable' }}">
                  {{ __($feature) }}

                  @if (is_null($package->number_of_skills) || empty($package->number_of_skills))
                  @elseif($package->number_of_skills == 999999)
                    {{ __('unlimited') }}
                  @else
                    {{ '(' . $package->number_of_skills . ')' }}
                  @endif


                </li>
              @elseif ($feature == 'Custom Domain')
                <li class=" {{ is_array($pFeatures) && in_array($feature, $pFeatures) ? 'active' : 'disable' }}">
                  {{ __($feature) }} </li>
              @elseif ($feature == 'Subdomain')
                <li class=" {{ is_array($pFeatures) && in_array($feature, $pFeatures) ? 'active' : 'disable' }}">
                  {{ __($feature) }} </li>
              @elseif ($feature == 'QR Builder')
                <li class=" {{ is_array($pFeatures) && in_array($feature, $pFeatures) ? 'active' : 'disable' }}">
                  {{ __($feature) }} </li>
              @elseif ($feature == 'Online CV & Export')
                <li class=" {{ is_array($pFeatures) && in_array($feature, $pFeatures) ? 'active' : 'disable' }}">
                  {{ __($feature) }} </li>
              @elseif ($feature == 'Follow/Unfollow')
                <li class=" {{ is_array($pFeatures) && in_array($feature, $pFeatures) ? 'active' : 'disable' }}">
              
                  {{ __($feature) }} </li>
              @elseif ($feature == 'Achievements')
                <li class=" {{ is_array($pFeatures) && in_array($feature, $pFeatures) ? 'active' : 'disable' }}">
                  {{ __($feature) }} </li>
              @elseif ($feature == 'Testimonial')
                <li class=" {{ is_array($pFeatures) && in_array($feature, $pFeatures) ? 'active' : 'disable' }}">
                  {{ __($feature) }} </li>
              @elseif ($feature == 'Appointment')
                <li class=" {{ is_array($pFeatures) && in_array($feature, $pFeatures) ? 'active' : 'disable' }}">
                  {{ __($feature) }} </li>
              @elseif ($feature == 'Google Analytics')
                <li class=" {{ is_array($pFeatures) && in_array($feature, $pFeatures) ? 'active' : 'disable' }}">
                  {{ __($feature) }} </li>
              @elseif ($feature == 'Disqus')
                <li class=" {{ is_array($pFeatures) && in_array($feature, $pFeatures) ? 'active' : 'disable' }}">
              
                  {{ __($feature) }} </li>
              @elseif ($feature == 'WhatsApp')
                <li class=" {{ is_array($pFeatures) && in_array($feature, $pFeatures) ? 'active' : 'disable' }}">
                  {{ __($feature) }} </li>
              @elseif ($feature == 'Facebook Pixel')
                <li class=" {{ is_array($pFeatures) && in_array($feature, $pFeatures) ? 'active' : 'disable' }}">
                  {{ __($feature) }} </li>
              @elseif ($feature == 'Tawk.to')
                <li class=" {{ is_array($pFeatures) && in_array($feature, $pFeatures) ? 'active' : 'disable' }}">
                  {{  __($feature) }} </li>
              @endif
            @endforeach

            <li
              class="{{ !empty($package->number_of_languages) && $package->number_of_languages > 0 ? 'active' : 'disable' }}">

              {{ __('Languages') }}
              @if ($package->number_of_languages == 999999)
                {{ __('unlimited') }}
              @elseif($package->number_of_languages > 0)
                {{ ' (' . $package->number_of_languages . ')' }}
              @endif

            </li>

            <li
              class="{{ !empty($package->number_of_education) && $package->number_of_education > 0 ? 'active' : 'disable' }}">

              {{ __('Education') }}

              @if ($package->number_of_education == 999999)
                {{ __('unlimited') }}
              @elseif($package->number_of_education > 0)
                {{ ' (' . $package->number_of_education . ')' }}
              @endif
            </li>
          </ul>
          @php
            $hasPendingMemb = \App\Http\Helpers\UserPermissionHelper::hasPendingMembership(Auth::id());
          @endphp
          @if ($package_count < 2 && !$hasPendingMemb)
            <div class="px-4">
              @if (isset($current_package->id) && $current_package->id === $package->id)
                @if ($package->term != 'lifetime' || $current_membership->is_trial == 1)
                  <a href="{{ route('user.plan.extend.checkout', $package->id) }}"
                    class="btn btn-success btn-lg w-75 fw-bold mb-3">{{ __('Extend') }}</a>
                @endif
              @else
                <a href="{{ route('user.plan.extend.checkout', $package->id) }}"
                  class="btn btn-primary btn-block btn-lg fw-bold mb-3">{{ __('Buy Now') }}</a>
              @endif
            </div>
          @endif
        </div>
      </div>
    @endforeach
  </div>
@endsection
