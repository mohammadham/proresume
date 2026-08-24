@extends('user.layout')

@section('content')
    @if ($message = Session::get('error'))
        <div class="alert alert-danger alert-block">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <strong>{{ $message }}</strong>
        </div>
    @endif
    @if (!empty($membership) && ($membership->package->term == 'lifetime' || $membership->is_trial == 1))
        <div class="alert bg-warning alert-warning text-white text-center">
            <h3>{{ __('If you purchase this package') }} <strong class="text-dark">({{ __($package->title) }})</strong>,
                {{ __('then your current package') }} <strong class="text-dark">({{ __($membership->package->title) }}
                    @if ($membership->is_trial == 1)
                        <span class="badge badge-secondary">{{ __('Trial') }}</span>
                    @endif)
                </strong> {{ __('will be replaced immediately') }}</h3>
        </div>
    @endif
    <div class="row justify-content-center align-items-center mb-1">
        <div class="col-md-1 pl-md-0">
        </div>
        <div class="col-md-6 pl-md-0 pr-md-0">
            <div class="card card-pricing card-pricing-focus card-secondary">
                <form id="my-checkout-form" action="{{ route('user.plan.checkout') }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="package_id" value="{{ $package->id }}">
                    <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                    <input type="hidden" name="payment_method" id="payment" value="{{ old('payment_method') }}">
                    <div class="card-header">
                        <h4 class="card-title">{{ __($package->title) }}</h4>
                        <div class="card-price">
                            <span class="price">{{ $package->price == 0 ?  __('Free') : format_price($package->price) }}</span>
                            <span class="text">/{{  __($package->term) }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="specification-list">
                            <li>
                                <span class="name-specification">{{  __('Membership') }}</span>
                                <span class="status-specification">{{  __('Yes') }}</span>
                            </li>
                            <li>
                                <span class="name-specification">{{ __('Start Date') }}</span>
                                @if (
                                    (!empty($previousPackage) && $previousPackage->term == 'lifetime') ||
                                        (!empty($membership) && $membership->is_trial == 1))
                                    <input type="hidden" name="start_date"
                                        value="{{ \Carbon\Carbon::today()->format('d-m-Y') }}">
                                    <span
                                        class="status-specification">{{ \Carbon\Carbon::today()->format('d-m-Y') }}</span>
                                @else
                                    <input type="hidden" name="start_date"
                                        value="{{ \Carbon\Carbon::parse($membership->expire_date ?? \Carbon\Carbon::yesterday())->addDay()->format('d-m-Y') }}">
                                    <span
                                        class="status-specification">{{ \Carbon\Carbon::parse($membership->expire_date ?? \Carbon\Carbon::yesterday())->addDay()->format('d-m-Y') }}</span>
                                @endif
                            </li>
                            <li>
                                <span class="name-specification">{{  __('Expire Date') }}</span>
                                <span class="status-specification">
                                    @if ($package->term == 'monthly')
                                        @if (
                                            (!empty($previousPackage) && $previousPackage->term == 'lifetime') ||
                                                (!empty($membership) && $membership->is_trial == 1))
                                            {{ \Carbon\Carbon::parse(now())->addMonth()->format('d-m-Y') }}
                                            <input type="hidden" name="expire_date"
                                                value="{{ \Carbon\Carbon::parse(now())->addMonth()->format('d-m-Y') }}">
                                        @else
                                            {{ \Carbon\Carbon::parse($membership->expire_date ?? \Carbon\Carbon::yesterday())->addDay()->addMonth()->format('d-m-Y') }}
                                            <input type="hidden" name="expire_date"
                                                value="{{ \Carbon\Carbon::parse($membership->expire_date ?? \Carbon\Carbon::yesterday())->addDay()->addMonth()->format('d-m-Y') }}">
                                        @endif
                                    @elseif($package->term == 'lifetime')
                                        {{ __('Lifetime') }}
                                        <input type="hidden" name="expire_date"
                                            value="{{ \Carbon\Carbon::maxValue()->format('d-m-Y') }}">
                                    @else
                                        @if (
                                            (!empty($previousPackage) && $previousPackage->term == 'lifetime') ||
                                                (!empty($membership) && $membership->is_trial == 1))
                                            {{ \Carbon\Carbon::parse(now())->addYear()->format('d-m-Y') }}
                                            <input type="hidden" name="expire_date"
                                                value="{{ \Carbon\Carbon::parse(now())->addYear()->format('d-m-Y') }}">
                                        @else
                                            {{ \Carbon\Carbon::parse($membership->expire_date ?? \Carbon\Carbon::yesterday())->addDay()->addYear()->format('d-m-Y') }}
                                            <input type="hidden" name="expire_date"
                                                value="{{ \Carbon\Carbon::parse($membership->expire_date ?? \Carbon\Carbon::yesterday())->addDay()->addYear()->format('d-m-Y') }}">
                                        @endif
                                    @endif
                                </span>
                            </li>
                            <li>
                                <span class="name-specification">{{ __('Total Cost') }}</span>
                                <input type="hidden" name="price" value="{{ $package->price }}">
                                <span class="status-specification">
                                    {{ $package->price == 0 ? __('Free') : format_price($package->price) }}
                                </span>
                            </li>
                            @if ($package->price != 0)
                                <li>
                                    <div class="form-group px-0">
                                        <label
                                            class="text-white">{{ __('Payment Method') }}</label>
                                        <select name="payment_method"
                                            class="form-control input-solid selected-payment-gateway" id="payment-gateway"
                                            required>
                                            <option value="" disabled selected>
                                                {{ __('Select a Payment Method') }}
                                            </option>
                                            @foreach ($payment_methods as $payment_method)
                                                @php
                                                    $paymentName = str_replace(' ', '_', $payment_method->name);
                                                @endphp
                                                <option value="{{ $payment_method->name }}"
                                                    {{ old('payment_method') == $payment_method->name ? 'selected' : '' }}>
                                                    {{ __($payment_method->name) }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('payment_method'))
                                            <span class="method-error">
                                                <strong
                                                    class="text-warning">{{ $errors->first('payment_method') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </li>
                            @endif

                            {{-- START: Stripe Card Details Form --}}
                            <div id="stripe-element" style="margin-top: 70px;" class="mb-2 ">
                                <!-- A Stripe Element will be inserted here. -->
                            </div>
                            <!-- Used to display form errors -->
                            <div id="stripe-errors" class="pb-2 " role="alert"></div>
                            {{-- END: Stripe Card Details Form --}}

                            {{-- START: Authorize.net Card Details Form --}}
                            <div class="row gateway-details pt-3" id="tab-anet" style="display: none;">
                                <div class="col-lg-6">
                                    <div class="form-group mb-3">
                                        <input class="form-control" type="text" id="anetCardNumber"
                                            placeholder="{{ __('Card Number') }}" disabled />
                                    </div>
                                </div>
                                <div class="col-lg-6 mb-3">
                                    <div class="form-group">
                                        <input class="form-control" type="text" id="anetExpMonth"
                                            placeholder="{{ __('Expire Month') }}" disabled />
                                    </div>
                                </div>
                                <div class="col-lg-6 ">
                                    <div class="form-group">
                                        <input class="form-control" type="text" id="anetExpYear"
                                            placeholder="{{ __('Expire Year') }}" disabled />
                                    </div>
                                </div>
                                <div class="col-lg-6 ">
                                    <div class="form-group">
                                        <input class="form-control" type="text" id="anetCardCode"
                                            placeholder="{{ __('Card Code') }}" disabled />
                                    </div>
                                </div>
                                <input type="hidden" name="opaqueDataValue" id="opaqueDataValue" disabled />
                                <input type="hidden" name="opaqueDataDescriptor" id="opaqueDataDescriptor" disabled />
                                <ul id="anetErrors" style="display: none;"></ul>
                            </div>
                            {{-- END: Authorize.net Card Details Form --}}

                            <div id="instructions" class="text-left"></div>
                            @if ($errors->has('receipt'))
                                <span class="error">
                                    <strong>{{ $errors->first('receipt') }}</strong>
                                </span>
                            @endif
                            <input type="hidden" name="is_receipt" value="{{ old('is_receipt', 0) }}" id="is_receipt">
                        </ul>

                    </div>
                    <div class="card-footer">
                        <button class="btn btn-light btn-block"
                            type="submit"><b>{{ __('Checkout Now') }}</b></button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-1 pr-md-0"></div>
    </div>
@endsection

@section('scripts')
    @php
        $anet = App\Models\PaymentGateway::find(20);
        $anerInfo = $anet->convertAutoData();
        $anetTest = $anerInfo['sandbox_check'];

        if ($anetTest == 1) {
            $anetSrc = 'https://jstest.authorize.net/v1/Accept.js';
        } else {
            $anetSrc = 'https://js.authorize.net/v1/Accept.js';
        }

        $stripe_key = App\Models\PaymentGateway::where('keyword', 'stripe')->first()->information;
        $stripe_key = json_decode($stripe_key);
        $stripe_key = $stripe_key->key;

        $stripeError = __('Your card number is incomplete');
        $anetCardError = __('Please provide valid credit card number');
        $anetYearError = __('Please provide valid expiration year');
        $anetMonthError = __('Please provide valid expiration month');
        $anetExpirationDateError = __('Expiration date must be in the future');
        $anetCvvInvalidError = __('Please provide valid CVV');
        $receiptImage = __('Receipt image must be');
        $receiptText = __('Receipt');
    @endphp
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        $(document).ready(function() {
            "use strict";
            let finalInstruction;
            let receiptError = "{{ session('receipt_error') }}"; 
            let requiredError = "{{ $errors->has('receipt') }}"; 
             let receiptImage = "{{ $receiptImage }}";
             let receiptText = "{{ $receiptText }}";
            // Check if finalInstruction is stored in session storage
            let storedInstruction = sessionStorage.getItem('finalInstruction');
            console.log(storedInstruction);
            console.log('receiptError: ' + receiptError);
            if (storedInstruction && (receiptError || requiredError)) {
                $("#instructions").html(storedInstruction);
                $('#instructions').fadeIn();
            }
            $("#payment-gateway").on('change', function() {

                // To clear all items in session storage
                sessionStorage.removeItem('finalInstruction');
                $('.error').addClass('d-none');
                $('.method-error').addClass('d-none');
                $('#instructions').fadeOut();

                let offline = @php echo json_encode($offline) @endphp;
                let data = [];
                offline.map(({
                    id,
                    name
                }) => {
                    data.push(name);
                });
                let paymentMethod = $("#payment-gateway").val();

                $(".gateway-details").hide();
                $(".gateway-details input").attr('disabled', true);
                // Reset receipt input when switching payment methods
                $("input[name='receipt']").removeAttr("required").val("").removeClass("is-invalid");

                if (paymentMethod == 'Stripe') {
                    $('#stripe-element').removeClass('d-none');
                } else {
                    $('#stripe-element').addClass('d-none');
                    $("#stripe-errors").empty();
                }

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
                                              <label class="text-white">${receiptText}<span>*</span></label><br>
                                              <input type="file" name="receipt" value="" class="file-input" required>
                                              <p class="mb-0 text-warning">** ${receiptImage} .jpg / .jpeg / .png</p>
                                           </div>`;
                            if (data.is_receipt == 1) {
                                $("#is_receipt").val(1);
                                finalInstruction = instructions + description + receipt;
                                instruction.html(finalInstruction);
                                sessionStorage.setItem('finalInstruction', finalInstruction);
                                $("input[name='receipt']").attr('required', 'required');

                            } else {
                                $("#is_receipt").val(0);
                                finalInstruction = instructions + description;
                                instruction.html(finalInstruction);
                                sessionStorage.setItem('finalInstruction', finalInstruction);
                                
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
            // Check if there are validation errors and show the modal
            @if ($errors->has('receipt') || session('receipt_error'))

                $(document).ready(function() {
                    $("#is_receipt input[name='is_receipt']").val("{{ old('is_receipt') }}");
                });
            @endif
        });
    </script>
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
    <script>
        @if (old('payment_method') == 'Stripe')
            $(document).ready(function() {
                $('#stripe-element').removeClass('d-none');
            })
        @endif
    </script>
@endsection
