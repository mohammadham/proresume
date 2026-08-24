@extends("user.$folder.layout")
@section('styles')
    @if (isset($css_file))
        <link rel="stylesheet" href="{{ $css_file }}">
    @endif
@endsection
@section('tab-title')
    {{ $keywords['Payment'] ?? 'Payment' }}
@endsection
@section('br-title')
    {{ $keywords['Payment'] ?? 'Payment' }}
@endsection
@section('br-link')
    {{ $keywords['Payment'] ?? 'Payment' }}
@endsection
@section('content')
    @if (
        $userBs->theme == 6 ||
            $userBs->theme == 7 ||
            $userBs->theme == 8 ||
            $userBs->theme == 9 ||
            $userBs->theme == 10 ||
            $userBs->theme == 11 ||
            $userBs->theme == 12)
        <!--====== Breadcrumbs Start ======-->
        <section class="breadcrumbs-section">
            <div class="container">
                <div class="row align-items-center justify-content-center">
                    <div class="col-lg-10">
                        <div class="page-title">
                            <h1>{{ $keywords['Checkout'] ?? 'Checkout' }}</h1>
                            <ul class="breadcrumbs-link">
                                <li><a
                                        href="{{ route('front.user.detail.view', getParam()) }}">{{ $keywords['Home'] ?? 'Home' }}</a>
                                </li>
                                <li class="">{{ $keywords['Checkout'] ?? 'Checkout' }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--====== Breadcrumbs End ======-->
    @endif
    @php
        $appointment_summary = Session::get('user_request');
        $dt = Carbon\carbon::parse($appointment_summary['date']);
    @endphp
    <!--====== PROFILE PART START ======-->

    <section class="dashboard-area">
        <div class="container">
            <form action="{{ route('front.user.appointment.checkout', getParam()) }}" method="POST"
                enctype="multipart/form-data" id="my-checkout-form">
                @csrf
                <div class="row">
                    <div class="col-lg-6 ">
                        <div class="card-body shadow-sm p-4 bg-white rounded">
                            <div class="order_wrap_box">
                                <div class="order_payment_box border-0 rounded-3 px-4">
                                    <h4 class="fw-bold mb-4">
                                        {{ $keywords['Appointment_summary'] ?? 'Appointment Summary' }}
                                    </h4>

                                    <div class="form_group py-2 mb-3">
                                        <div class="mb-2">
                                            <strong class="text-dark">{{ $keywords['Name'] ?? 'Name' }}:</strong>
                                            <span class="text-muted ms-1">{{ @$appointment_summary['name'] }}</span>
                                        </div>

                                        <div class="mb-2">
                                            <strong class="text-dark">{{ $keywords['Email'] ?? 'Email' }}:</strong>
                                            <span class="text-muted ms-1">{{ $appointment_summary['email'] }}</span>
                                        </div>

                                        <div class="mb-2">
                                            <strong
                                                class="text-dark">{{ $keywords['Booking_Date'] ?? 'Booking Date' }}:</strong>
                                            <span class="text-muted ms-1">{{ $dt->isoFormat('DD MMMM YYYY') }}</span>
                                        </div>

                                        <div class="mb-2">
                                            <strong
                                                class="text-dark">{{ $keywords['Booking_Time'] ?? 'Booking Time' }}:</strong>
                                            <span class="text-muted ms-1">{{ $appointment_summary['slot'] }}</span>
                                        </div>

                                        @if (!empty($appointment_summary['category_id']))
                                            @php
                                                $catg =
                                                    App\Models\User\Category::where(
                                                        'id',
                                                        $appointment_summary['category_id'],
                                                    )
                                                        ->where('language_id', $userCurrentLang->id)
                                                        ->where('user_id', getUser()->id)
                                                        ->first()->name ?? '';
                                            @endphp
                                            <div class="mb-2">
                                                <strong
                                                    class="text-dark">{{ $keywords['Category'] ?? 'Category' }}:</strong>
                                                <span class="text-muted ms-1">{{ $catg }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 ">
                        <div class="card-body shadow-sm p-4 bg-white rounded">
                            <div class="order_wrap_box">
                                <div class="order_payment_box">
                                    <h4 class="fw-bold mb-4">
                                        {{ $keywords['Payment_Method'] ?? 'Payment Method' }}
                                        </h3>
                                        <div class="form_group">
                                            <div class="mb-2">
                                                <strong
                                                    class="text-dark">{{ $keywords['Total_Fee'] ?? 'Total Fee' }}:</strong>
                                                <span class="text-muted ms-1">
                                                    {{ $userBs->base_currency_symbol_position == 'left' ? $userBs->base_currency_symbol : '' }}
                                                    {{ $total_fee }}
                                                    {{ $userBs->base_currency_symbol_position == 'right' ? $userBs->base_currency_symbol : '' }}
                                                </span>
                                            </div>
                                            <div class="mb-2">
                                                <strong
                                                    class="text-dark">{{ $keywords['Payable_amount'] ?? 'Payable Amount' }}:</strong>
                                                <span class="text-muted ms-1">
                                                    {{ $userBs->base_currency_symbol_position == 'left' ? $userBs->base_currency_symbol : '' }}
                                                    {{ $price }}
                                                    {{ $userBs->base_currency_symbol_position == 'right' ? $userBs->base_currency_symbol : '' }}
                                                    @if (empty($userBs->full_payment))
                                                        ({{ $userBs->advance_percentage }} %
                                                        {{ $keywords['Advance'] ?? __('Advance') }})
                                                    @endif
                                                </span>
                                            </div>

                                            <input type="hidden" value="{{ $total_fee }}" name="total_price">
                                            <input type="hidden" value="{{ $price }}" name="price">
                                            <div class="select-wrapper">
                                                <select name="payment_method" id="payment-gateway"
                                                    class="olima_select form_control anyClass  mt-3 selected-payment-gateway">
                                                    <option value="" selected disabled>
                                                        {{ $keywords['Choose_an_option'] ?? __('Choose an option') }}
                                                    </option>
                                                    @foreach ($payment_methods as $payment_method)
                                                        <option value="{{ $payment_method->name }}"
                                                            {{ old('payment_method') == $payment_method->name ? 'selected' : '' }}>
                                                            {{ $keywords[$payment_method->name] ?? $payment_method->name }}
                                                        </option>
                                                    @endforeach
                                                    @foreach ($offline as $payment_method)
                                                        <option value="{{ $payment_method->name }}"
                                                            {{ old('payment_method') == $payment_method->name ? 'selected' : '' }}>
                                                            {{ $payment_method->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @if ($errors->has('payment_method'))
                                                <span class="method-error text-danger">
                                                    {{ $errors->first('payment_method') }}
                                                </span>
                                            @endif
                                        </div>
                                </div>
                            </div>

                            <!-- toyyibpay -->
                            <div
                                class="mt-4 toyyibpay-element {{ old('payment_method') == 'Toyyibpay' || old('payment_method') == 'My Fatoorah' ? '' : 'd-none' }}">
                                <div class="form-group mb-20">
                                    <input type="text" name="phone_number" value="{{ old('phone_number') }}"
                                        class="form-control" placeholder=" {{ __('Enter Phone Number') }}">
                                    @error('phone_number')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Iyzico payment will be inserted here -->
                            <div class="mt-4 iyzico-element {{ old('payment_method') == 'Iyzico' ? '' : 'd-none' }}">
                                <div class="form-group mb-20 ">
                                    <input type="text" name="identity_number" value="{{ old('identity_number') }}"
                                        class="form-control" placeholder=" {{ __('Identity Number') }}">
                                    @error('identity_number')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="form-group mb-20">
                                    <input type="text" name="iname" value="{{ old('iname') }}" class="form-control"
                                        placeholder=" {{ __('Name') }}">
                                    @error('iname')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="form-group mb-20">
                                    <input type="text" name="iemail" value="{{ old('iemail') }}" class="form-control"
                                        placeholder=" {{ __('Email Address') }}">
                                    @error('iemail')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="form-group mb-20">
                                    <input type="text" name="iphone" value="{{ old('iphone') }}" class="form-control"
                                        placeholder=" {{ __('Phone') }}">
                                    @error('iphone')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="form-group mb-20">
                                    <input type="text" name="iaddress" value="{{ old('iaddress') }}"
                                        class="form-control" placeholder=" {{ __('Address') }}">
                                    @error('iaddress')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="form-group mb-20">
                                    <input type="text" name="zip_code" value="{{ old('zip_code') }}"
                                        class="form-control" placeholder=" {{ __('Zip Code') }}">
                                    @error('zip_code')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="form-group mb-20">
                                    <input type="text" name="icity" value="{{ old('icity') }}"
                                        class="form-control" placeholder=" {{ __('City') }}">
                                    @error('icity')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <input type="text" name="icountry" value="{{ old('icountry') }}"
                                        class="form-control" placeholder=" {{ __('Country') }}">
                                    @error('icountry')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- START: Stripe Card Details Form --}}
                            <div id="stripe-element" style="margin-top: 10px;" class="mb-2 ">
                                <!-- A Stripe Element will be inserted here. -->
                            </div>
                            <!-- Used to display form errors -->
                            <div id="stripe-errors" class="pb-2 " role="alert"></div>
                            {{-- END: Stripe Card Details Form --}}

                            {{-- START: Authorize.net Card Details Form --}}
                            <div class="row gateway-details py-3" id="tab-anet" style="display: none;">
                                <div class="col-lg-6">
                                    <div class="form_group mb-3">
                                        <input class="form-control" type="text" id="anetCardNumber"
                                            placeholder="{{ __('Card Number') }}" disabled />
                                    </div>
                                </div>
                                <div class="col-lg-6 mb-3">
                                    <div class="form_group">
                                        <input class="form-control" type="text" id="anetExpMonth"
                                            placeholder="{{ __('Expire Month') }}" disabled />
                                    </div>
                                </div>
                                <div class="col-lg-6 ">
                                    <div class="form_group">
                                        <input class="form-control" type="text" id="anetExpYear"
                                            placeholder="{{ __('Expire Year') }}" disabled />
                                    </div>
                                </div>
                                <div class="col-lg-6 ">
                                    <div class="form_group">
                                        <input class="form-control" type="text" id="anetCardCode"
                                            placeholder="{{ __('Card Code') }}" disabled />
                                    </div>
                                </div>
                                <input type="hidden" name="opaqueDataValue" id="opaqueDataValue" disabled />
                                <input type="hidden" name="opaqueDataDescriptor" id="opaqueDataDescriptor" disabled />
                                <ul id="anetErrors"></ul>
                            </div>
                            {{-- END: Authorize.net Card Details Form --}}

                            <div>
                                <div id="instructions"></div>
                                <div class="form-element mb-2 payment_rec d-none">
                                    <label>{{ $keywords['Receipt'] ?? __('Receipt') }}<span>*</span></label><br>
                                    <input type="file" name="receipt" value="1" class="file-input"
                                        id="has_receipt">

                                    <p class="mb-0 text-warning">**
                                        {{ $keywords['Receipt_image_must_be'] ?? __('Receipt image must be') }} .jpg /
                                        .jpeg / .png</p>
                                    @if ($errors->has('receipt'))
                                        <span class="error">
                                            <strong>{{ $errors->first('receipt') }}</strong>
                                        </span>
                                    @endif
                                </div>
                                <input type="hidden" name="is_receipt" value="{{ old('is_receipt', 0) }}"
                                    id="is_receipt">
                            </div>
                            @if ($userBs->theme == 10 || $userBs->theme == 9)
                                <div class="form-group mt-10">
                                    <button class="btn btn-md btn-primary w-100"
                                        type="submit">{{ $keywords['Confirm'] ?? __('Confirm') }}</button>
                                </div>
                            @else
                                <div class="text-center">
                                    <button
                                        class="mt-30 w-100 @if ($userBs->theme == 1 || $userBs->theme == 2) template-btn @else main-btn @endif"
                                        type="submit">{{ $keywords['Confirm'] ?? __('Confirm') }}
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>

    </section>
    <!--====== PROFILE PART ENDS ======-->
@endsection
@section('scripts')
    <script src="https://js.stripe.com/v3/"></script>

    <script>
        "use strict";
        let instructionText = "{{ $keywords['Instructions'] ?? __('Instructions') }}"
        let shortDesText = "{{ $keywords['Short_Description'] ?? __('Short Description') }}"
        let receiptError = "{{ session('receipt_error') }}";
        let requiredError = "{{ $errors->has('receipt') }}";
        let finalInstruction;

        // Check if finalInstruction is stored in session storage
        let storedInstruction = sessionStorage.getItem('finalInstruction');
        console.log(storedInstruction);

        if (storedInstruction && (receiptError || requiredError)) {
            $("#instructions").html(storedInstruction);
            $('#instructions').fadeIn();
            $('.payment_rec').removeClass('d-none');
        }

        $("#payment-gateway").on('change', function() {

            // To clear all items in session storage
            sessionStorage.removeItem('finalInstruction');
            $('.payment_rec').addClass('d-none');
            $('.error').addClass('d-none');
            $('.method-error').addClass('d-none');
            $('#instructions').fadeOut();
            console.log(finalInstruction)
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
            $("input[name='receipt']").removeAttr("required").val("").removeClass("is-invalid");


            if (paymentMethod == 'Stripe') {
                $('#stripe-element').removeClass('d-none');
            } else {
                $('#stripe-element').addClass('d-none');
            }
            if (paymentMethod == 'Authorize.net') {
                $("#tab-anet").show();
                $("#tab-anet input").removeAttr('disabled');
            }

            if (paymentMethod == 'Iyzico') {
                $('.iyzico-element').removeClass('d-none');
                $('#stripe-element').addClass('d-none');
            } else {
                $('.iyzico-element').addClass('d-none');
            }
            if (paymentMethod == 'Toyyibpay' || paymentMethod == 'My Fatoorah') {
                $('.toyyibpay-element').removeClass('d-none');
                $('#stripe-element').addClass('d-none');
            } else {
                $('.toyyibpay-element').addClass('d-none');
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
                        console.log(data.is_receipt)

                        function nl2br(str) {
                            return str.replace(/(?:\r\n|\r|\n)/g, '<br>');
                        }

                        function nl2bri(str) {
                            return str.replace(/(?:\r\n|\r|\n)/g, '<br>');
                        }

                        let instruction = $("#instructions");
                        let instructions =
                            `<div class="gateway-desc"><strong>${instructionText} : </strong>  ${nl2bri(data.instructions)}</div>`;
                        if (data.description != null) {
                            var description =
                                `<div class="gateway-desc"><strong>${shortDesText} : </strong>
                                    <p> ${nl2br(data.description)}</p></div>`;
                        } else {
                            var description = `<div></div>`;
                        }

                        if (data.is_receipt == 1) {
                            $('.payment_rec').removeClass('d-none')
                            $("#is_receipt").val(1);
                            $("#is_receipt").attr('required', true);
                            $("#has_receipt").attr('required', true);
                            finalInstruction = instructions + description;
                            instruction.html(finalInstruction);
                            // Store finalInstruction in session storage
                            sessionStorage.setItem('finalInstruction', finalInstruction);
                        } else {
                            $("#is_receipt").val(0);
                            $("#is_receipt").attr('required', false);
                            $("#has_receipt").attr('required', false);
                            $('.payment_rec').addClass('d-none')
                            finalInstruction = instructions + description;
                            instruction.html(finalInstruction);
                            // Store finalInstruction in session storage
                            sessionStorage.setItem('finalInstruction', finalInstruction);
                        }
                        $('#instructions').fadeIn();
                    },
                    error: function(data) {}
                })
            } else {
                $('#instructions').fadeOut();
            }
            // Check if there are validation errors and show the modal
            @if ($errors->has('receipt') || session('receipt_error'))

                $(document).ready(function() {
                    $("#is_receipt input[name='is_receipt']").val("{{ old('is_receipt') }}");
                });
            @endif
        });
    </script>


    @php
        $anet = \App\Models\User\UserPaymentGateway::where('user_id', getUser()->id)
            ->where('keyword', 'authorize.net')
            ->first();
        if ($anet) {
            $anerInfo = $anet->convertAutoData() ?? [];
            $anetTest = $anerInfo['sandbox_check'] ?? '';
        } else {
            $anerInfo = [];
            $anetTest = '';
        }
        $anerInfo = $anet->convertAutoData();
        $anetTest = $anerInfo['sandbox_check'] ?? '';

        if ($anetTest == 1) {
            $anetSrc = 'https://jstest.authorize.net/v1/Accept.js';
        } else {
            $anetSrc = 'https://js.authorize.net/v1/Accept.js';
        }
    @endphp

    @php
        $stripe_key = \App\Models\User\UserPaymentGateway::where('user_id', getUser()->id)
            ->where('keyword', 'stripe')
            ->first()->information;
        $stripe_key = json_decode($stripe_key);
        $stripe_key = $stripe_key->key ?? '';
        $stripeError = __('Your card number is incomplete');
        $anetCardError = __('Please provide valid credit card number');
        $anetYearError = __('Please provide valid expiration year');
        $anetMonthError = __('Please provide valid expiration month');
        $anetExpirationDateError = __('Expiration date must be in the future');
        $anetCvvInvalidError = __('Please provide valid CVV');

    @endphp

    <script>
        let stripe_key = "{{ $stripe_key }}";
        let anit_public_key = "{{ $anerInfo['public_key'] ?? '' }}";
        let login_id = "{{ $anerInfo['login_id'] ?? '' }}";
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
