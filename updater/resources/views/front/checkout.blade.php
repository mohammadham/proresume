@extends('front.layout')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/front/css/checkout.css') }}">
@endsection

@section('pagename')
    - {{ __('Checkout') }}
@endsection

@section('meta-description', !empty($seo) ? $seo->checkout_meta_description : '')
@section('meta-keywords', !empty($seo) ? $seo->checkout_meta_keywords : '')

@section('breadcrumb-title')
    {{ __('Checkout') }}
@endsection
@section('breadcrumb-link')
    {{ __('Checkout') }}
@endsection

@php
    $stripe_key = App\Models\PaymentGateway::where('keyword', 'stripe')->first()->information;
    $stripe_key = json_decode($stripe_key);
    $stripe_key = $stripe_key->key;
@endphp


@section('content')
    <!--====== Start saas_checkout ======-->
    <section class="saas_checkout pt-150 pb-110">
        <div class="container">
            <form action="{{ route('front.membership.checkout') }}" method="POST" enctype="multipart/form-data"
                id="my-checkout-form">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="section_title mb-30">
                            <h3>{{ __('Billing Details') }}</h3>
                        </div>
                        <div class="billing_form mb-40">
                            @csrf
                            <div class="row">
                                <input type="hidden" name="username" value="{{ $username }}">
                                <input type="hidden" name="password" value="{{ $password }}">
                                <input type="hidden" name="package_type" value="{{ $status }}">
                                <input type="hidden" name="email" value="{{ $email }}">
                                <input type="hidden" name="price"
                                    value="{{ $status == 'trial' ? 0 : $package->price }}">
                                <input type="hidden" name="package_id" value="{{ $id }}">
                                <input type="hidden" name="payment_method" id="payment"
                                    value="{{ old('payment_method') }}">
                                <input type="hidden" name="trial_days" id="trial_days" value="{{ $package->trial_days }}">
                                <input type="hidden" name="start_date"
                                    value="{{ \Carbon\Carbon::today()->format('d-m-Y') }}">
                                @if ($status === 'trial')
                                    <input type="hidden" name="expire_date"
                                        value="{{ \Carbon\Carbon::today()->addDay($package->trial_days)->format('d-m-Y') }}">
                                @else
                                    @if ($package->term === 'daily')
                                        <input type="hidden" name="expire_date"
                                            value="{{ \Carbon\Carbon::today()->addDay()->format('d-m-Y') }}">
                                    @elseif($package->term === 'weekly')
                                        <input type="hidden" name="expire_date"
                                            value="{{ \Carbon\Carbon::today()->addWeek()->format('d-m-Y') }}">
                                    @elseif($package->term === 'monthly')
                                        <input type="hidden" name="expire_date"
                                            value="{{ \Carbon\Carbon::today()->addMonth()->format('d-m-Y') }}">
                                    @elseif($package->term === 'lifetime')
                                        <input type="hidden" name="expire_date"
                                            value="{{ \Carbon\Carbon::maxValue()->format('d-m-Y') }}">
                                    @else
                                        <input type="hidden" name="expire_date"
                                            value="{{ \Carbon\Carbon::today()->addYear()->format('d-m-Y') }}">
                                    @endif
                                @endif

                                <div class="col-lg-6">
                                    <div class="form_group">
                                        <label for="first_name">{{ __('First Name') }}*</label>
                                        <input id="first_name" type="text" class="form_control" name="first_name"
                                            placeholder="{{ __('First Name') }}" value="{{ old('first_name') }}" required>
                                        @if ($errors->has('first_name'))
                                            <span class="error">
                                                <strong>{{ $errors->first('first_name') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form_group">
                                        <label for="last_name">{{ __('Last Name') }}*</label>
                                        <input id="last_name" type="text" class="form_control" name="last_name"
                                            placeholder="{{ __('Last Name') }}" value="{{ old('last_name') }}" required>
                                        @if ($errors->has('last_name'))
                                            <span class="error">
                                                <strong>{{ $errors->first('last_name') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="form_group">
                                        <label for="phone">{{ __('Phone Number') }}*</label>
                                        <input id="phone" type="text" class="form_control" name="phone"
                                            placeholder="{{ __('Phone Number') }}" value="{{ old('phone') }}" required>
                                        @if ($errors->has('phone'))
                                            <span class="error">
                                                <strong>{{ $errors->first('phone') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form_group">
                                        <label for="email">{{ __('Email Address') }}*</label>
                                        <input id="email" type="email" class="form_control" name="email"
                                            value="{{ $email }}" disabled>
                                        @if ($errors->has('email'))
                                            <span class="error">
                                                <strong>{{ $errors->first('email') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form_group">
                                        <label for="company_name">{{ __('Company Name') }}</label>
                                        <input id="company_name" type="text" class="form_control" name="company_name"
                                            placeholder="{{ __('Company Name') }}" value="{{ old('company_name') }}">
                                        @if ($errors->has('company_name'))
                                            <span class="error">
                                                <strong>{{ $errors->first('company_name') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="form_group">
                                        <label for="address">{{ __('Street Address') }}</label>
                                        <input id="address" type="text" class="form_control" name="address"
                                            placeholder="{{ __('Street Address') }}" value="{{ old('address') }}">
                                        @if ($errors->has('address'))
                                            <span class="error">
                                                <strong>{{ $errors->first('address') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form_group">
                                        <label for="city">{{ __('City') }} *</label>
                                        <input id="city" type="text" class="form_control" name="city"
                                            placeholder="{{ __('City') }}" value="{{ old('city') }}" required>
                                        @if ($errors->has('city'))
                                            <span class="error">
                                                <strong>{{ $errors->first('city') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form_group">
                                        <label for="district">{{ __('State') }}</label>
                                        <input id="district" type="text" class="form_control" name="district"
                                            placeholder="{{ __('State') }}" value="{{ old('district') }}">
                                        @if ($errors->has('district'))
                                            <span class="error">
                                                <strong>{{ $errors->first('district') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form_group">
                                        <label for="post_code">{{ __('Postcode/Zip') }}</label>
                                        <input id="post_code" type="text" class="form_control" name="post_code"
                                            placeholder="{{ __('Post Code') }}" value="{{ old('post_code') }}">
                                        @if ($errors->has('post_code'))
                                            <span class="error">
                                                <strong>{{ $errors->first('post_code') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form_group">
                                        <label for="country">{{ __('Country') }} *</label>
                                        <input id="country" type="text" class="form_control" name="country"
                                            placeholder="{{ __('Country') }}" value="{{ old('country') }}" required>
                                        @if ($errors->has('country'))
                                            <span class="error">
                                                <strong>{{ $errors->first('country') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="order_wrap_box mb-40">
                            <div id="couponReload">
                                <input type="hidden" name="price"
                                    value="{{ $status == 'trial' ? 0 : $package->price - $cAmount }}">
                                <div class="order_product_info mb-30 mt-30">
                                    <h3>{{ __('Package Summary') }}</h3>
                                    <div class="order_list_info">
                                        <ul>

                                            <li>{{ __('Package') }} <span>{{ __($package->title) }}</span></li>
                                            <li>{{ __('Start Date') }}
                                                <span>{{ \Carbon\Carbon::today()->format('d-m-Y') }}</span>
                                            </li>
                                            @if ($status === 'trial')
                                                <li>
                                                    {{ __('Expiry Date') }}
                                                    <span>
                                                        {{ \Carbon\Carbon::today()->addDay($package->trial_days)->format('d-m-Y') }}
                                                    </span>
                                                </li>
                                            @else
                                                <li>
                                                    {{ __('Expiry Date') }}
                                                    <span>
                                                        @if ($package->term === 'daily')
                                                            {{ \Carbon\Carbon::today()->addDay()->format('d-m-Y') }}
                                                        @elseif($package->term === 'weekly')
                                                            {{ \Carbon\Carbon::today()->addWeek()->format('d-m-Y') }}
                                                        @elseif($package->term === 'monthly')
                                                            {{ \Carbon\Carbon::today()->addMonth()->format('d-m-Y') }}
                                                        @elseif($package->term === 'lifetime')
                                                            {{ __('Lifetime') }}
                                                        @else
                                                            {{ \Carbon\Carbon::today()->addYear()->format('d-m-Y') }}
                                                        @endif
                                                    </span>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                    <div class="order_list_bottom">
                                        <ul>
                                            <li><span>{{ __('Total') }}</span></li>
                                            <li>
                                                <span class="price">
                                                    @if ($status === 'trial')
                                                        {{ __('Free') }} ({{ $package->trial_days . ' days' }})
                                                    @elseif($package->price == 0)
                                                        {{ __('Free') }}
                                                    @else
                                                        {{ format_price($package->price) }}
                                                    @endif
                                                </span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                @if (session()->has('coupon'))
                                    <li>
                                        <span>{{ __('Package Price') }}</span>
                                        <span class="price">
                                            @if ($status === 'trial')
                                                {{ __('Free') }} ({{ $package->trial_days . ' days' }})
                                            @elseif($package->price == 0)
                                                {{ __('Free') }}
                                            @else
                                                {{ format_price($package->price) }}
                                            @endif
                                        </span>
                                    </li>
                                    <li>
                                        <span>{{ __('Discount') }}</span>
                                        <span class="price text-success">
                                            - {{ format_price($cAmount) }}
                                        </span>
                                    </li>
                                    <li class="border-0">
                                        <span>{{ __('Total') }}</span>
                                        <span class="price">
                                            @if ($status === 'trial')
                                                {{ __('Free') }} ({{ $package->trial_days . ' days' }})
                                            @elseif($package->price == 0)
                                                {{ __('Free') }}
                                            @else
                                                {{ format_price($package->price - $cAmount) }}
                                            @endif
                                        </span>
                                    </li>
                                @endif
                                @if ($package->price > 0 && $status != 'trial')
                                    @if (!session()->has('coupon'))
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="input-group mb-3">
                                                    <input type="text" class="form-control form-control-lg"
                                                        name="coupon" placeholder="{{ __('Enter Coupon Code Here') }}">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text coupon-apply"
                                                            id="basic-addon2">{{ __('Apply') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="alert alert-success">
                                            {{ __('Coupon already applied') }}
                                        </div>
                                    @endif
                                @endif
                                <div class="order_payment_box">
                                    @if ($package->price - $cAmount == 0 || $status == 'trial')
                                    @else
                                        <h3>{{ __('Payment Method') }}</h3>
                                        <div class="form_group">
                                            <select id="payment-gateway" class="selected-payment-gateway">
                                                <option value="" selected disabled>{{ __('Choose an option') }}
                                                </option>

                                                @foreach ($payment_methods as $payment_method)
                                                    <option value="{{ $payment_method->name }}"
                                                        {{ old('payment_method') == $payment_method->name ? 'selected' : '' }}>
                                                        {{ __($payment_method->name) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @if ($errors->has('payment_method'))
                                                <span class="method-error">
                                                    <strong>{{ $errors->first('payment_method') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                            {{-- START: Stripe Card Details Form --}}
                            <div id="stripe-element" style="margin-top: 70px;" class="mb-2 ">
                                <!-- A Stripe Element will be inserted here. -->
                            </div>

                            <!-- Used to display form errors -->
                            <div id="stripe-errors" class="pb-2 " role="alert"></div>
                            {{-- END: Stripe Card Details Form --}}


                            {{-- START: Authorize.net Card Details Form --}}
                            <div class="row gateway-details py-3" id="tab-anet" style="display: none;">
                                <div class="col-lg-6">
                                    <div class="form_group mb-3">
                                        <input class="form_control" type="text" id="anetCardNumber"
                                            placeholder="{{ __('Card Number') }}" disabled />
                                    </div>
                                </div>
                                <div class="col-lg-6 mb-3">
                                    <div class="form_group">
                                        <input class="form_control" type="text" id="anetExpMonth"
                                            placeholder="{{ __('Expire Month') }}" disabled />
                                    </div>
                                </div>
                                <div class="col-lg-6 ">
                                    <div class="form_group">
                                        <input class="form_control" type="text" id="anetExpYear"
                                            placeholder="{{ __('Expire Year') }}" disabled />
                                    </div>
                                </div>
                                <div class="col-lg-6 ">
                                    <div class="form_group">
                                        <input class="form_control" type="text" id="anetCardCode"
                                            placeholder="{{ __('Card Code') }}" disabled />
                                    </div>
                                </div>
                                <input type="hidden" name="opaqueDataValue" id="opaqueDataValue" disabled />
                                <input type="hidden" name="opaqueDataDescriptor" id="opaqueDataDescriptor" disabled />
                                <ul id="anetErrors" style="display: none;"></ul>
                            </div>
                            {{-- END: Authorize.net Card Details Form --}}

                            {{-- START: Offline Gateways Information & Receipt Area --}}
                            <div>
                                <div id="instructions"></div>
                                <input type="hidden" name="is_receipt" value="0" id="is_receipt">
                                @if ($errors->has('receipt'))
                                    <span class="error">
                                        <strong>{{ $errors->first('receipt') }}</strong>
                                    </span>
                                @endif
                            </div>
                            {{-- END: Offline Gateways Information & Receipt Area --}}

                            <div class="text-center">
                                <button id="confirmBtn" class="main-btn"
                                    style="margin-top: 20px; width: 100%;" type="submit">{{ __('Confirm') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
    <!--====== End saas_checkout ======-->
    @php
        $couponApplied =  __('Coupon applied successfully') ;
        $Receipt =  __('Receipt') ;
        $ReceiptMsg =  __('Receipt image must be') ;
    @endphp
@endsection

@section('scripts')

    <script src="https://js.stripe.com/v3/"></script>    
    <script>
        "use strict";
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            function applyCoupon() {
                let fd = new FormData();
                let coupon = $("input[name='coupon']").val();
                fd.append('coupon', coupon);
                fd.append('package_id', {{ $package->id }});
                $.ajax({
                    url: "{{ route('front.membership.coupon') }}",
                    type: 'POST',
                    data: fd,
                    contentType: false,
                    processData: false,
                    success: function(data) {
                        if (data == 'success') {
                            $("#couponReload").load(location.href + " #couponReload", function() {
                                $('select').niceSelect();
                            });
                            toastr['success']($couponApplied);
                            
                        } else {
                            toastr['warning'](data);
                        }
                    }
                })
            }

            $("input[name='coupon']").on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();

                    applyCoupon();

                }
            });

            $(".coupon-apply").on('click', function() {
                applyCoupon();
            });

            $(document).on('change', "#payment-gateway", function() {
                let offline = @php echo json_encode($offline) @endphp;
                let data = [];
                offline.map(({
                    id,
                    name
                }) => {
                    data.push(name);
                });
                let paymentMethod = $("#payment-gateway").val();
                
                $("input[name='payment_method']").val(paymentMethod);

                $(".gateway-details").hide();
                $(".gateway-details input").attr('disabled', true);

                

                if (paymentMethod == 'Authorize.net') {
                    $("#tab-anet").show();
                    $("#tab-anet input").removeAttr('disabled');
                }
                if (paymentMethod == 'Stripe') {
                    $('#stripe-element').removeClass('d-none');
                } else {
                    $('#stripe-element').addClass('d-none');
                }

                if (data.indexOf(paymentMethod) != -1) {
                    let formData = new FormData();
                    formData.append('name', paymentMethod);
                    $.ajax({
                        url: '{{ route('front.payment.instructions') }}',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: 'POST',
                        contentType: false,
                        processData: false,
                        cache: false,
                        data: formData,
                        success: function(data) {
                            let instruction = $("#instructions");
                            let instructions =
                                `<div class="gateway-desc">${data.instructions}</div>`;
                            if (data.description != null) {
                                var description =
                                    `<div class="gateway-desc"><p>${data.description}</p></div>`;
                            } else {
                                var description = `<div></div>`;
                            }
                            let receipt = `<div class="form-element mb-2">
                                              <label>${{!! json_encode($Receipt) !!}}<span>*</span></label><br>
                                              <input type="file" name="receipt" value="" class="file-input" required>
                                              <p class="mb-0 text-warning">** ${{!! json_encode($ReceiptMsg) !!}} .jpg / .jpeg / .png</p>
                                           </div>`;
                            if (data.is_receipt == 1) {
                                $("#is_receipt").val(1);
                                let finalInstruction = instructions + description + receipt;
                                instruction.html(finalInstruction);
                            } else {
                                $("#is_receipt").val(0);
                                let finalInstruction = instructions + description;
                                instruction.html(finalInstruction);
                            }
                            $('#instructions').fadeIn();
                        },
                        error: function(data) {}
                    })
                } else {
                    $('#instructions').fadeOut();
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            "use strict";
            $(document).on('change', "#payment-gateway", function() {
                let offline = @php echo json_encode($offline) @endphp;
                let data = [];
                offline.map(({
                    id,
                    name
                }) => {
                    data.push(name);
                });
                let paymentMethod = $("#payment-gateway").val();
                $("input[name='payment_method']").val(paymentMethod);

                $(".gateway-details").hide();
                $(".gateway-details input").attr('disabled', true);

                if (paymentMethod == 'Authorize.net') {
                    $("#tab-anet").show();
                    $("#tab-anet input").removeAttr('disabled');
                }

                if (data.indexOf(paymentMethod) != -1) {
                    let formData = new FormData();
                    formData.append('name', paymentMethod);
                    $.ajax({
                        url: '{{ route('front.payment.instructions') }}',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: 'POST',
                        contentType: false,
                        processData: false,
                        cache: false,
                        data: formData,
                        success: function(data) {
                            let instruction = $("#instructions");
                            let instructions =
                                `<div class="gateway-desc">${data.instructions}</div>`;
                            if (data.description != null) {
                                var description =
                                    `<div class="gateway-desc"><p>${data.description}</p></div>`;
                            } else {
                                var description = `<div></div>`;
                            }
                            let receipt = `<div class="form-element mb-2">
                                              <label>${{!! json_encode($Receipt) !!}}<span>*</span></label><br>
                                              <input type="file" name="receipt" value="" class="file-input" required>
                                              <p class="mb-0 text-warning">** ${{!! json_encode($ReceiptMsg) !!}} .jpg / .jpeg / .png</p>
                                           </div>`;
                            if (data.is_receipt == 1) {
                                $("#is_receipt").val(1);
                                let finalInstruction = instructions + description + receipt;
                                instruction.html(finalInstruction);
                                $("input[name='receipt']").attr('required', 'required');
                            } else {
                                $("#is_receipt").val(0);
                                let finalInstruction = instructions + description;
                                instruction.html(finalInstruction);
                            }
                            $('#instructions').fadeIn();
                        },
                        error: function(data) {}
                    })
                } else {
                    $('#instructions').fadeOut();
                    $("#is_receipt").val(0);
                }
            });
        });
    </script>


    {{-- START: Authorize.net Scripts --}}
    @php
        $anet = App\Models\PaymentGateway::find(20);
        $anerInfo = $anet->convertAutoData();
        $anetTest = $anerInfo['sandbox_check'];

        if ($anetTest == 1) {
            $anetSrc = 'https://jstest.authorize.net/v1/Accept.js';
        } else {
            $anetSrc = 'https://js.authorize.net/v1/Accept.js';
        }

        $stripeError = __('Your card number is incomplete');
        $anetCardError = __('Please provide valid credit card number');
        $anetYearError = __('Please provide valid expiration year');
        $anetMonthError = __('Please provide valid expiration month');
        $anetExpirationDateError = __('Expiration date must be in the future');
        $anetCvvInvalidError = __('Please provide valid CVV');
    @endphp
    <script>
     
        let stripe_key = "{{ $stripe_key }}";
        let anit_public_key = "{{ $anerInfo['public_key'] }}";
        let login_id = "{{ $anerInfo['login_id'] }}";
        let stripeError = "{{ $stripeError }}";
        let anetCardError = "{{ $anetCardError }}";
        let anetYearError = "{{ $anetYearError }}";
        let anetMonthError = "{{ $anetMonthError }}";
        let anetExpirationDateError = "{{ $anetExpirationDateError }}";
        let anetCvvInvalidError = "{{ $anetCvvInvalidError }}";
    </script>
    <script type="text/javascript" src="{{ $anetSrc }}" charset="utf-8"></script>
   
    <script src="{{ asset('assets/front/js/stripe.js') }}"></script>
    {{-- END: Authorize.net Scripts --}}

    <script>
        @if (old('payment_method') == 'Stripe')
            $(document).ready(function() {
                $('#stripe-element').removeClass('d-none');
            })
        @endif
    </script>
@endsection
