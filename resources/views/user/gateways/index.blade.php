@extends('user.layout')
@php
    $selLang = \App\Models\User\Language::where([
        ['code', request()->input('language')],
        ['user_id', \Illuminate\Support\Facades\Auth::guard('web')->user()->id],
    ])->first();
    $userDefaultLang = \App\Models\User\Language::where([
        ['user_id', \Illuminate\Support\Facades\Auth::guard('web')->user()->id],
        ['is_default', 1],
    ])->first();

    $userLanguages = \App\Models\User\Language::where(
        'user_id',
        \Illuminate\Support\Facades\Auth::guard('web')->user()->id,
    )->get();
@endphp
@if (!empty($selLang) && $selLang->rtl == 1)
    @section('styles')
        <style>
            form:not(.modal-form) input,
            form:not(.modal-form) textarea,
            form:not(.modal-form) select,
            select[name='userLanguage'] {
                direction: rtl;
            }

            form:not(.modal-form) .note-editor.note-frame .note-editing-area .note-editable {
                direction: rtl;
                text-align: right;
            }
        </style>
    @endsection
@endif
@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Payment Gateways') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('user-dashboard') }}">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Payment Gateways') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">

        <!--flutterwave-->
        <div class="col-lg-4">
            <div class="card">
                <form class="" action="{{ route('user.flutterwave.update') }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-title">{{ __('Flutterwave') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-5 pb-5">
                        <div class="row">
                            <div class="col-lg-12">
                                @csrf
                                @php
                                    $flutterwaveInfo = json_decode(optional($flutterwave)->information, true) ?? [];
                                @endphp
                                <div class="form-group">
                                    <label>{{ __('Flutterwave') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1" class="selectgroup-input"
                                                {{ $flutterwaveInfo ? ($flutterwave->status == 1 ? 'checked' : '') : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0" class="selectgroup-input"
                                                {{ $flutterwaveInfo ? ($flutterwave->status == 0 ? 'checked' : '') : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Flutterwave Public Key') }}</label>
                                    <input class="form-control" name="public_key"
                                        value="{{ $flutterwaveInfo['public_key'] ?? '' }}">
                                    @if ($errors->has('public_key'))
                                        <p class="mb-0 text-danger">{{ $errors->first('public_key') }}</p>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Flutterwave Secret Key') }}</label>
                                    <input class="form-control" name="secret_key"
                                        value="{{ $flutterwaveInfo['secret_key'] ?? '' }}">
                                    @if ($errors->has('secret_key'))
                                        <p class="mb-0 text-danger">{{ $errors->first('secret_key') }}</p>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="form">
                            <div class="form-group from-show-notify row">
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-success">{{ __('Update') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!--razorpay-->
        <div class="col-lg-4">
            <div class="card">
                <form class="" action="{{ route('user.razorpay.update') }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-title">{{ __('Razorpay') }}</div>
                            </div>
                        </div>
                    </div>


                    <div class="card-body pt-5 pb-5">
                        <div class="row">
                            <div class="col-lg-12">
                                @csrf
                                @php
                                    $razorpayInfo = json_decode(optional($razorpay)->information, true) ?? [];
                                @endphp
                                <div class="form-group">
                                    <label>{{ __('Razorpay') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1" class="selectgroup-input"
                                                {{ $razorpayInfo ? ($razorpay->status == 1 ? 'checked' : '') : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0" class="selectgroup-input"
                                                {{ $razorpayInfo ? ($razorpay->status == 0 ? 'checked' : '') : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Razorpay Key') }}</label>
                                    <input class="form-control" name="key" value="{{ $razorpayInfo['key'] ?? '' }}">
                                    @if ($errors->has('key'))
                                        <p class="mb-0 text-danger">{{ $errors->first('key') }}</p>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Razorpay Secret') }}</label>
                                    <input class="form-control" name="secret" value="{{ $razorpayInfo['secret'] ?? '' }}">
                                    @if ($errors->has('secret'))
                                        <p class="mb-0 text-danger">{{ $errors->first('secret') }}</p>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="form">
                            <div class="form-group from-show-notify row">
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-success">{{ __('Update') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Stripe -->
        <div class="col-lg-4">
            <div class="card">
                <form class="" action="{{ route('user.stripe.update') }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-title">{{ __('Stripe') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-5 pb-5">
                        <div class="row">
                            <div class="col-lg-12">
                                @csrf
                                @php
                                    $stripeInfo = json_decode(optional($stripe)->information, true) ?? [];
                                @endphp
                                <div class="form-group">
                                    <label>{{ __('Stripe') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1"
                                                class="selectgroup-input"
                                                {{ $stripeInfo ? ($stripe->status == 1 ? 'checked' : '') : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0"
                                                class="selectgroup-input"
                                                {{ $stripeInfo ? ($stripe->status == 0 ? 'checked' : '') : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Stripe Key') }}</label>
                                    <input class="form-control" name="key" value="{{ $stripeInfo['key'] ?? '' }}">
                                    @if ($errors->has('key'))
                                        <p class="mb-0 text-danger">{{ $errors->first('key') }}</p>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Stripe Secret') }}</label>
                                    <input class="form-control" name="secret"
                                        value="{{ $stripeInfo['secret'] ?? '' }}">
                                    @if ($errors->has('secret'))
                                        <p class="mb-0 text-danger">{{ $errors->first('secret') }}</p>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="form">
                            <div class="form-group from-show-notify row">
                                <div class="col-12 text-center">
                                    <button type="submit" id="displayNotif"
                                        class="btn btn-success">{{ __('Update') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!--xendit-->
        <div class="col-lg-4">
            <div class="card">
                <form action="{{ route('user.zendit.update') }}" id="xenditForm" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-title">{{ __('Xendit') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body pt-5 pb-5">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>{{ __('Xendit Status') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1"
                                                class="selectgroup-input"
                                                {{ isset($xendit) && $xendit->status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0"
                                                class="selectgroup-input"
                                                {{ isset($xendit) && $xendit->status == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                    @if ($errors->has('status'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('status') }}</p>
                                    @endif
                                </div>

                                @php  $xenditInfo = isset($xendit) ? json_decode($xendit->information, true) : null; @endphp

                                <div class="form-group">
                                    <label>{{ __('Secret Key') }}</label>
                                    <input type="text" class="form-control" name="secret_key"
                                        value="{{ @$xenditInfo['secret_key'] }}">
                                    @if ($errors->has('secret_key'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('secret_key') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="form">
                            <div class="form-group from-show-notify row">
                                <div class="col-12 text-center">
                                    <button type="submit" form="xenditForm" class="btn btn-success">
                                        {{ __('Update') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!--mollie-->
        <div class="col-lg-4">
            <div class="card">
                <form class="" action="{{ route('user.mollie.update') }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-title">{{ __('Mollie Payment') }}</div>
                            </div>
                        </div>
                    </div>


                    <div class="card-body pt-5 pb-5">
                        <div class="row">
                            <div class="col-lg-12">
                                @csrf
                                @php
                                    $mollieInfo = json_decode(optional($mollie)->information, true) ?? [];
                                @endphp
                                <div class="form-group">
                                    <label>{{ __('Mollie Payment') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1"
                                                class="selectgroup-input"
                                                {{ $mollieInfo ? ($mollie->status == 1 ? 'checked' : '') : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0"
                                                class="selectgroup-input"
                                                {{ $mollieInfo ? ($mollie->status == 0 ? 'checked' : '') : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Mollie Payment Key') }}</label>
                                    <input class="form-control" name="key" value="{{ $mollieInfo['key'] ?? '' }}">
                                    @if ($errors->has('key'))
                                        <p class="mb-0 text-danger">{{ $errors->first('key') }}</p>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="form">
                            <div class="form-group from-show-notify row">
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-success">{{ __('Update') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!--paystack-->
        <div class="col-lg-4">
            <div class="card">
                <form class="" action="{{ route('user.paystack.update') }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-title">{{ __('Paystack') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-5 pb-5">
                        <div class="row">
                            <div class="col-lg-12">
                                @csrf
                                @php
                                    $paystackInfo = json_decode(optional($paystack)->information, true) ?? [];
                                @endphp
                                <div class="form-group">
                                    <label>{{ __('Paystack') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1"
                                                class="selectgroup-input"
                                                {{ $paystackInfo ? ($paystack->status == 1 ? 'checked' : '') : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0"
                                                class="selectgroup-input"
                                                {{ $paystackInfo ? ($paystack->status == 0 ? 'checked' : '') : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Paystack Secret Key') }}</label>
                                    <input class="form-control" name="key" value="{{ $paystackInfo['key'] ?? '' }}">
                                    @if ($errors->has('key'))
                                        <p class="mb-0 text-danger">{{ $errors->first('key') }}</p>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="form">
                            <div class="form-group from-show-notify row">
                                <div class="col-12 text-center">
                                    <button type="submit" id="displayNotif"
                                        class="btn btn-success">{{ __('Update') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!--Perfect Money-->
        <div class="col-lg-4">
            <div class="card">
                <form id="perfectMoneyForm" action="{{ route('user.perfect_money.update') }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-title">{{ __('Perfect Money') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body pt-5 pb-4">
                        @php
                            $perfect_moneyInfo = isset($perfect_money)
                                ? json_decode($perfect_money->information, true)
                                : null;
                        @endphp
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>{{ __('Perfect Money Status') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1"
                                                class="selectgroup-input"
                                                {{ isset($perfect_money) && $perfect_money->status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0"
                                                class="selectgroup-input"
                                                {{ isset($perfect_money) && $perfect_money->status == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                    @if ($errors->has('status'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('status') }}</p>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Perfect Money Wallet Id') }}</label>
                                    <input type="text" class="form-control" name="perfect_money_wallet_id"
                                        value="{{ @$perfect_moneyInfo['perfect_money_wallet_id'] }}">
                                    @if ($errors->has('perfect_money_wallet_id'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('perfect_money_wallet_id') }}
                                        </p>
                                    @endif

                                    <p class="text-warning mt-1 mb-0">
                                        {{ __('You will get wallet id form here') }}
                                    </p>
                                    <a href="https://prnt.sc/bM3LqLXBduaq"
                                        target="_blank">https://prnt.sc/bM3LqLXBduaq</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="form">
                            <div class="form-group from-show-notify row">
                                <div class="col-12 text-center">
                                    <button type="submit" form="perfectMoneyForm" class="btn btn-success">
                                        {{ __('Update') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Yoco-->
        <div class="col-lg-4">
            <div class="card">
                <form id="yocoForm" action="{{ route('user.yoco.update') }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-title">{{ __('Yoco') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body pt-5 pb-5">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>{{ __('Yoco Status') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1"
                                                class="selectgroup-input"
                                                {{ isset($yoco) && $yoco->status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0"
                                                class="selectgroup-input"
                                                {{ isset($yoco) && $yoco->status == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                    @if ($errors->has('status'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('status') }}</p>
                                    @endif
                                </div>

                                @php $yocoInfo = isset($yoco) ? json_decode($yoco->information, true) : null; @endphp

                                <div class="form-group">
                                    <label>{{ __('Secret Key') }}</label>
                                    <input type="text" class="form-control" name="secret_key"
                                        value="{{ @$yocoInfo['secret_key'] }}">
                                    @if ($errors->has('secret_key'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('secret_key') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="form mt-3">
                            <div class="form-group from-show-notify row">
                                <div class="col-12 text-center">
                                    <button type="submit" form="yocoForm" class="btn btn-success">
                                        {{ __('Update') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!--mercado pago-->
        <div class="col-lg-4">
            <div class="card">
                <form action="" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-title">{{ __('Mercadopago') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">

                        @csrf
                        @php
                            $mercadopagoInfo = json_decode(optional($mercadopago)->information, true) ?? [];
                        @endphp
                        <div class="form-group">
                            <label>{{ __('Mercado Pago') }}</label>
                            <div class="selectgroup w-100">
                                <label class="selectgroup-item">
                                    <input type="radio" name="status" value="1" class="selectgroup-input"
                                        {{ $mercadopagoInfo ? ($mercadopago->status == 1 ? 'checked' : '') : '' }}>
                                    <span class="selectgroup-button">{{ __('Active') }}</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="radio" name="status" value="0" class="selectgroup-input"
                                        {{ $mercadopagoInfo ? ($mercadopago->status == 0 ? 'checked' : '') : '' }}>
                                    <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>{{ __('Mercado Pago Test Mode') }}</label>
                            <div class="selectgroup w-100">
                                <label class="selectgroup-item">
                                    <input type="radio" name="sandbox_check" value="1" class="selectgroup-input"
                                        {{ $mercadopagoInfo ? ($mercadopagoInfo['sandbox_check'] == 1 ? 'checked' : '') : '' }}>
                                    <span class="selectgroup-button">{{ __('Active') }}</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="radio" name="sandbox_check" value="0" class="selectgroup-input"
                                        {{ $mercadopagoInfo ? ($mercadopagoInfo['sandbox_check'] == 0 ? 'checked' : '') : '' }}>
                                    <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>{{ __('Mercadopago Token') }}</label>
                            <input class="form-control" name="token" value="{{ $mercadopagoInfo['token'] ?? '' }}">
                            @if ($errors->has('token'))
                                <p class="mb-0 text-danger">{{ $errors->first('token') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-success">{{ __('Update') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- myfatoorah-->
        <div class="col-lg-4">
            <div class="card">
                <form id="myfatoorahForm" action="{{ route('user.myfatoorah.update') }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-title">{{ __('MyFatoorah') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body pt-5 pb-5">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>{{ __('MyFatoorah Status') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1"
                                                class="selectgroup-input"
                                                {{ isset($myfatoorah) && $myfatoorah->status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0"
                                                class="selectgroup-input"
                                                {{ isset($myfatoorah) && $myfatoorah->status == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                    @if ($errors->has('status'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('status') }}</p>
                                    @endif
                                </div>

                                @php $myfatoorahInfo = isset($myfatoorah) ? json_decode($myfatoorah->information, true) : null; @endphp
                                <div class="form-group">
                                    <label>{{ __('Sandbox Status') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="sandbox_status" value="1"
                                                class="selectgroup-input"
                                                {{ @$myfatoorahInfo['sandbox_status'] == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="sandbox_status" value="0"
                                                class="selectgroup-input"
                                                {{ @$myfatoorahInfo['sandbox_status'] == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                    @if ($errors->has('sandbox_status'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('sandbox_status') }}</p>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Token') }}</label>
                                    <input type="text" class="form-control" name="token"
                                        value="{{ @$myfatoorahInfo['token'] }}">
                                    @if ($errors->has('token'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('token') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="form">
                            <div class="form-group from-show-notify row">
                                <div class="col-12 text-center">
                                    <button type="submit" form="myfatoorahForm" class="btn btn-success">
                                        {{ __('Update') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- toyyibpay-->
        <div class="col-lg-4">
            <div class="card">
                <form id="toyyibpayForm" action="{{ route('user.toyyibpay.update') }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-title">{{ __('Toyyibpay') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body pt-2 pb-1">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>{{ __('Toyyibpay Status') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1"
                                                class="selectgroup-input"
                                                {{ isset($toyyibpay) && $toyyibpay->status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0"
                                                class="selectgroup-input"
                                                {{ isset($toyyibpay) && $toyyibpay->status == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                    @if ($errors->has('status'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('status') }}</p>
                                    @endif
                                </div>
                                @php $toyyibpayInfo = isset($toyyibpay) ? json_decode($toyyibpay->information, true) : null; @endphp
                                <div class="form-group">
                                    <label>{{ __('Toyyibpay Test Mode') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="sandbox_status" value="1"
                                                class="selectgroup-input"
                                                {{ @$toyyibpayInfo['sandbox_status'] == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="sandbox_status" value="0"
                                                class="selectgroup-input"
                                                {{ @$toyyibpayInfo['sandbox_status'] == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                    @if ($errors->has('sandbox_status'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('sandbox_status') }}</p>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Secret Key') }}</label>
                                    <input type="text" class="form-control" name="secret_key"
                                        value="{{ @$toyyibpayInfo['secret_key'] }}">
                                    @if ($errors->has('secret_key'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('secret_key') }}</p>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Category Code') }}</label>
                                    <input type="text" class="form-control" name="category_code"
                                        value="{{ @$toyyibpayInfo['category_code'] }}">
                                    @if ($errors->has('category_code'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('category_code') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="form">
                            <div class="form-group from-show-notify row">
                                <div class="col-12 text-center">
                                    <button type="submit" form="toyyibpayForm" class="btn btn-success">
                                        {{ __('Update') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- instamojo -->
        <div class="col-lg-4">
            <div class="card">
                <form class="" action="{{ route('user.instamojo.update') }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="col-lg-12">
                            <div class="card-title">{{ __('Instamojo') }}</div>
                        </div>
                    </div>


                    <div class="card-body">
                        <div class="col-lg-12">
                            @csrf
                            @php

                                $instamojoInfo = json_decode(optional($instamojo)->information, true) ?? [];
                            @endphp
                            <div class="form-group">
                                <label>{{ __('Instamojo') }}</label>
                                <div class="selectgroup w-100">
                                    <label class="selectgroup-item">
                                        <input type="radio" name="status" value="1" class="selectgroup-input"
                                            {{ $instamojo ? ($instamojo->status == 1 ? 'checked' : '') : '' }}>
                                        <span class="selectgroup-button">{{ __('Active') }}</span>
                                    </label>
                                    <label class="selectgroup-item">
                                        <input type="radio" name="status" value="0" class="selectgroup-input"
                                            {{ $instamojo ? ($instamojo->status == 0 ? 'checked' : '') : '' }}>
                                        <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>{{ __('Test Mode') }}</label>
                                <div class="selectgroup w-100">
                                    <label class="selectgroup-item">
                                        <input type="radio" name="sandbox_check" value="1"
                                            class="selectgroup-input"
                                            {{ $instamojoInfo ? ($instamojoInfo['sandbox_check'] == 1 ? 'checked' : '') : '' }}>
                                        <span class="selectgroup-button">{{ __('Active') }}</span>
                                    </label>
                                    <label class="selectgroup-item">
                                        <input type="radio" name="sandbox_check" value="0"
                                            class="selectgroup-input"
                                            {{ $instamojoInfo ? ($instamojoInfo['sandbox_check'] == 0 ? 'checked' : '') : '' }}>
                                        <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>{{ __('Instamojo API Key') }}</label>
                                <input class="form-control" name="key" value="{{ $instamojoInfo['key'] ?? '' }}">
                                @if ($errors->has('key'))
                                    <p class="mb-0 text-danger">{{ $errors->first('key') }}</p>
                                @endif
                            </div>
                            <div class="form-group">
                                <label>{{ __('Instamojo Auth Token') }}</label>
                                <input class="form-control" name="token" value="{{ $instamojoInfo['token'] ?? '' }}">
                                @if ($errors->has('token'))
                                    <p class="mb-0 text-danger">{{ $errors->first('token') }}</p>
                                @endif
                            </div>

                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-success">{{ __('Update') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- paypal -->
        <div class="col-lg-4">
            <div class="card">
                <form action="{{ route('user.paypal.update') }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-title">{{ __('Paypal') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-5 pb-5">
                        <div class="row">
                            <div class="col-lg-12">
                                @csrf

                                <div class="form-group">
                                    <label>{{ __('Paypal') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1"
                                                class="selectgroup-input"
                                                {{ (optional($paypal)->status ?? null) == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0"
                                                class="selectgroup-input"
                                                {{ (optional($paypal)->status ?? null) == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                </div>
                                @php
                                    $paypalInfo = json_decode(optional($paypal)->information, true) ?? [];
                                @endphp
                                <div class="form-group">
                                    <label>{{ __('Paypal Test Mode') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="sandbox_check" value="1"
                                                class="selectgroup-input"
                                                {{ $paypalInfo ? ($paypalInfo['sandbox_check'] == 1 ? 'checked' : '') : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="sandbox_check" value="0"
                                                class="selectgroup-input"
                                                {{ $paypalInfo ? ($paypalInfo['sandbox_check'] == 0 ? 'checked' : '') : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Paypal Client ID') }}</label>
                                    <input class="form-control" name="client_id"
                                        value="{{ $paypalInfo['client_id'] ?? '' }}">
                                    @if ($errors->has('client_id'))
                                        <p class="mb-0 text-danger">{{ $errors->first('client_id') }}</p>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Paypal Client Secret') }}</label>
                                    <input class="form-control" name="client_secret"
                                        value="{{ $paypalInfo['client_secret'] ?? '' }}">
                                    @if ($errors->has('client_secret'))
                                        <p class="mb-0 text-danger">{{ $errors->first('client_secret') }}</p>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="form">
                            <div class="form-group from-show-notify row">
                                <div class="col-12 text-center">
                                    <button type="submit" id="displayNotif"
                                        class="btn btn-success">{{ __('Update') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- phonepe-->
        <div class="col-lg-4">
            <div class="card">
                <form id="phonepeForm" action="{{ route('user.phonepe.update') }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-title">{{ __('Phonepe') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>{{ __('Phonepe Status') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1"
                                                class="selectgroup-input"
                                                {{ isset($phonepe) && $phonepe->status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0"
                                                class="selectgroup-input"
                                                {{ isset($phonepe) && $phonepe->status == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                    @if ($errors->has('status'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('status') }}</p>
                                    @endif
                                </div>

                                @php $phonepeInfo = isset($phonepe) ? json_decode($phonepe->information, true) : null; @endphp
                                <div class="form-group">
                                    <label>{{ __('Sandbox Status') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="sandbox_status" value="1"
                                                class="selectgroup-input"
                                                {{ @$phonepeInfo['sandbox_status'] == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="sandbox_status" value="0"
                                                class="selectgroup-input"
                                                {{ @$phonepeInfo['sandbox_status'] == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                    @if ($errors->has('sandbox_status'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('sandbox_status') }}</p>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Merchant Id') }}</label>
                                    <input type="text" class="form-control" name="merchant_id"
                                        value="{{ @$phonepeInfo['merchant_id'] }}">
                                    @if ($errors->has('merchant_id'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('merchant_id') }}</p>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Salt Key') }}</label>
                                    <input type="text" class="form-control" name="salt_key"
                                        value="{{ @$phonepeInfo['salt_key'] }}">
                                    @if ($errors->has('salt_key'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('salt_key') }}</p>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Salt Index') }}</label>
                                    <input type="number" class="form-control" name="salt_index"
                                        value="{{ @$phonepeInfo['salt_index'] }}">
                                    @if ($errors->has('salt_index'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('salt_index') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" form="phonepeForm" class="btn btn-success">
                                    {{ __('Update') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- paytabs-->
        <div class="col-lg-4">
            <div class="card">
                <form id="paytabsForm" action="{{ route('user.paytabs.update') }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-title">{{ __('Paytabs') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>{{ __('Paytabs Status') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1"
                                                class="selectgroup-input"
                                                {{ isset($paytabs) && $paytabs->status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0"
                                                class="selectgroup-input"
                                                {{ isset($paytabs) && $paytabs->status == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                    @if ($errors->has('status'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('status') }}</p>
                                    @endif
                                </div>

                                @php $paytabsInfo = isset($paytabs) ? json_decode($paytabs->information, true) : null; @endphp
                                <div class="form-group">
                                    <label>{{ __('Country') }}</label>
                                    <select name="country" id="" class="form-control">
                                        <option value="global" @selected(@$paytabsInfo['country'] == 'global')>Global</option>
                                        <option value="sa" @selected(@$paytabsInfo['country'] == 'sa')>Saudi Arabia</option>
                                        <option value="uae" @selected(@$paytabsInfo['country'] == 'uae')>United Arab Emirates</option>
                                        <option value="egypt" @selected(@$paytabsInfo['country'] == 'egypt')>Egypt</option>
                                        <option value="oman" @selected(@$paytabsInfo['country'] == 'oman')>Oman</option>
                                        <option value="jordan" @selected(@$paytabsInfo['country'] == 'jordan')>Jordan</option>
                                        <option value="iraq" @selected(@$paytabsInfo['country'] == 'iraq')>Iraq</option>
                                    </select>
                                    @if ($errors->has('country'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('server_key') }}</p>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Server Key') }}</label>
                                    <input type="text" class="form-control" name="server_key"
                                        value="{{ @$paytabsInfo['server_key'] }}">
                                    @if ($errors->has('server_key'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('server_key') }}</p>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Profile Id') }}</label>
                                    <input type="text" class="form-control" name="profile_id"
                                        value="{{ @$paytabsInfo['profile_id'] }}">
                                    @if ($errors->has('profile_id'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('profile_id') }}</p>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>{{ __('API Endpoint') }}</label>
                                    <input type="text" class="form-control" name="api_endpoint"
                                        value="{{ @$paytabsInfo['api_endpoint'] }}">
                                    @if ($errors->has('api_endpoint'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('api_endpoint') }}</p>
                                    @endif
                                    <p class="mt-1 mb-0 text-warning">
                                        {{ __("You will get your 'API Endpoit' from PayTabs Dashboard.") }}
                                    </p>
                                    <strong class="text-warning">{{ __('Step 1') }}:</strong>
                                    <a href="https://prnt.sc/McaCbxt75fyi"
                                        target="_blank">https://prnt.sc/McaCbxt75fyi</a>
                                    <br>
                                    <strong class="text-warning"> {{ __('Step 2') }}:</strong>
                                    <a href="https://prnt.sc/DgztAyHVR2o8"
                                        target="_blank">https://prnt.sc/DgztAyHVR2o8</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" form="paytabsForm" class="btn btn-success">
                                    {{ __('Update') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Authorize.Net -->
        <div class="col-lg-4">
            <div class="card">
                <form class="" action="{{ route('user.anet.update') }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-title">{{ __('Authorize.Net') }}</div>
                            </div>
                        </div>
                    </div>


                    <div class="card-body pt-5 pb-5">
                        <div class="row">
                            <div class="col-lg-12">
                                @csrf
                                @php

                                    $anetInfo = json_decode(optional($anet)->information, true) ?? [];
                                @endphp
                                <div class="form-group">
                                    <label>{{ __('Authorize.Net') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1"
                                                class="selectgroup-input"
                                                {{ $anetInfo ? ($anet->status == 1 ? 'checked' : '') : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0"
                                                class="selectgroup-input"
                                                {{ $anetInfo ? ($anet->status == 0 ? 'checked' : '') : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Authorize.Net Test Mode') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="sandbox_check" value="1"
                                                class="selectgroup-input"
                                                {{ $anetInfo ? ($anetInfo['sandbox_check'] == 1 ? 'checked' : '') : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="sandbox_check" value="0"
                                                class="selectgroup-input"
                                                {{ $anetInfo ? ($anetInfo['sandbox_check'] == 0 ? 'checked' : '') : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>{{ __('API Login ID') }}</label>
                                    <input class="form-control" name="login_id"
                                        value="{{ $anetInfo['login_id'] ?? '' }}">
                                    @if ($errors->has('login_id'))
                                        <p class="mb-0 text-danger">{{ $errors->first('login_id') }}</p>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Transaction Key') }}</label>
                                    <input class="form-control" name="transaction_key"
                                        value="{{ $anetInfo['transaction_key'] ?? '' }}">
                                    @if ($errors->has('transaction_key'))
                                        <p class="mb-0 text-danger">{{ $errors->first('transaction_key') }}</p>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Public Client Key') }}</label>
                                    <input class="form-control" name="public_key"
                                        value="{{ $anetInfo['public_key'] ?? '' }}">
                                    @if ($errors->has('public_key'))
                                        <p class="mb-0 text-danger">{{ $errors->first('public_key') }}</p>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="form">
                            <div class="form-group from-show-notify row">
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-success">{{ __('Update') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Iyzico-->
        <div class="col-lg-4">
            <div class="card">
                <form id="iyzicoForm" action="{{ route('user.iyzico.update') }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-title">{{ __('Iyzico') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body pt-5 pb-5">
                        @php
                            $iyzicoInfo = isset($iyzico) ? json_decode($iyzico->information, true) : null;
                        @endphp
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>{{ __('Iyzico Status') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1"
                                                class="selectgroup-input"
                                                {{ isset($iyzico) && $iyzico->status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0"
                                                class="selectgroup-input"
                                                {{ isset($iyzico) && $iyzico->status == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                    @if ($errors->has('status'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('status') }}</p>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Iyzico Test Mode') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="iyzico_mode" value="1"
                                                class="selectgroup-input"
                                                {{ isset($iyzicoInfo) && @$iyzicoInfo['iyzico_mode'] == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="iyzico_mode" value="0"
                                                class="selectgroup-input"
                                                {{ isset($iyzicoInfo) && @$iyzicoInfo['iyzico_mode'] == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                    @if ($errors->has('iyzico_mode'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('iyzico_mode') }}</p>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Iyzico Api Key') }}</label>
                                    <input type="text" class="form-control" name="api_key"
                                        value="{{ !empty($iyzicoInfo['api_key']) ? $iyzicoInfo['api_key'] : null }}">
                                    @if ($errors->has('api_key'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('api_key') }}</p>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Iyzico Secret Key') }}</label>
                                    <input type="text" class="form-control" name="secrect_key"
                                        value="{{ !empty($iyzicoInfo['secrect_key']) ? $iyzicoInfo['secrect_key'] : null }}">
                                    @if ($errors->has('secrect_key'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('secrect_key') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" form="iyzicoForm" class="btn btn-success">
                                    {{ __('Update') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- paytm -->
        <div class="col-lg-4">
            <div class="card">
                <form class="" action="{{ route('user.paytm.update') }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-title">{{ __('Paytm') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-5 pb-5">
                        <div class="row">
                            <div class="col-lg-12">
                                @csrf
                                @php
                                    $paytmInfo = json_decode(optional($paytm)->information, true) ?? [];
                                @endphp
                                <div class="form-group">
                                    <label>{{ __('Paytm') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1"
                                                class="selectgroup-input"
                                                {{ $paytmInfo ? ($paytm->status == 1 ? 'checked' : '') : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0"
                                                class="selectgroup-input"
                                                {{ $paytmInfo ? ($paytm->status == 0 ? 'checked' : '') : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Paytm Environment') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="environment" value="local"
                                                class="selectgroup-input"
                                                {{ $paytmInfo ? ($paytmInfo['environment'] == 'local' ? 'checked' : '') : '' }}>
                                            <span class="selectgroup-button">{{ __('Local') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="environment" value="production"
                                                class="selectgroup-input"
                                                {{ $paytmInfo ? ($paytmInfo['environment'] == 'production' ? 'checked' : '') : '' }}>
                                            <span class="selectgroup-button">{{ __('Production') }}</span>
                                        </label>
                                    </div>
                                    @if ($errors->has('environment'))
                                        <p class="mb-0 text-danger">{{ $errors->first('environment') }}</p>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Paytm Merchant Key') }}</label>
                                    <input class="form-control" name="secret"
                                        value="{{ $paytmInfo['secret'] ?? '' }}">
                                    @if ($errors->has('secret'))
                                        <p class="mb-0 text-danger">{{ $errors->first('secret') }}</p>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Paytm Merchant mid') }}</label>
                                    <input class="form-control" name="merchant"
                                        value="{{ $paytmInfo['merchant'] ?? '' }}">
                                    @if ($errors->has('merchant'))
                                        <p class="mb-0 text-danger">{{ $errors->first('merchant') }}</p>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Paytm Merchant website') }}</label>
                                    <input class="form-control" name="website"
                                        value="{{ $paytmInfo['website'] ?? '' }}">
                                    @if ($errors->has('website'))
                                        <p class="mb-0 text-danger">{{ $errors->first('website') }}</p>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Industry type id') }}</label>
                                    <input class="form-control" name="industry"
                                        value="{{ $paytmInfo['industry'] ?? '' }}">
                                    @if ($errors->has('industry'))
                                        <p class="mb-0 text-danger">{{ $errors->first('industry') }}</p>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="form">
                            <div class="form-group from-show-notify row">
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-success">{{ __('Update') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Midtrans-->
        <div class="col-lg-4">
            <div class="card">
                <form id="midtransForm" action="{{ route('user.midtrans.update') }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-title">{{ __('Midtrans') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        @php
                            $midtransInfo = isset($midtrans) ? json_decode($midtrans->information, true) : null;
                        @endphp
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>{{ __('Midtrans Status') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1"
                                                class="selectgroup-input"
                                                {{ isset($midtrans) && $midtrans->status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0"
                                                class="selectgroup-input"
                                                {{ isset($midtrans) && $midtrans->status == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                    @if ($errors->has('status'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('status') }}</p>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Midtrans Test Mode') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="midtrans_mode" value="1"
                                                class="selectgroup-input"
                                                {{ !empty($midtransInfo) && @$midtransInfo['midtrans_mode'] == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="midtrans_mode" value="0"
                                                class="selectgroup-input"
                                                {{ !empty($midtransInfo) && @$midtransInfo['midtrans_mode'] == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                    @if ($errors->has('midtrans_mode'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('midtrans_mode') }}</p>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Midtrans Server Key') }}</label>
                                    <input type="text" class="form-control" name="server_key"
                                        value="{{ !empty($midtransInfo['server_key']) ? $midtransInfo['server_key'] : null }}">
                                    @if ($errors->has('server_key'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('server_key') }}</p>
                                    @endif
                                </div>
                                <p class="text-warning mb-0">{{ __('Success URL') }} :
                                    {{ url('/') }}/midtrans/bank/notify </p>
                                <p class="text-warning mb-0">{{ __('Cancel URL') }} :
                                    {{ url('/') }}/midtrans/cancel </p>
                                <p class="text-warning mb-0">
                                    {{ __('Set these URLs in Midtrans Dashboard like this') }} :
                                </p>
                                <a href="https://prnt.sc/OiucUCeYJIXo" target="_blank">https://prnt.sc/OiucUCeYJIXo</a>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" form="midtransForm" class="btn btn-success">
                                    {{ __('Update') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
