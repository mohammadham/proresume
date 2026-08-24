@extends('admin.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Payment Gateways') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}">
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
        <div class="col-lg-4">
            <div class="card">
                <form action="{{ route('admin.paypal.update') }}" method="post">
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
                                            <input type="radio" name="status" value="1" class="selectgroup-input"
                                                {{ $paypal->status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0" class="selectgroup-input"
                                                {{ $paypal->status == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                </div>
                                @php
                                    $paypalInfo = json_decode($paypal->information, true);
                                @endphp
                                <div class="form-group">
                                    <label>{{ __('Paypal') . ' ' . __('Test Mode') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="sandbox_check" value="1"
                                                class="selectgroup-input"
                                                {{ $paypalInfo['sandbox_check'] == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="sandbox_check" value="0"
                                                class="selectgroup-input"
                                                {{ $paypalInfo['sandbox_check'] == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Paypal Client ID') }}</label>
                                    <input class="form-control" name="client_id" value="{{ $paypalInfo['client_id'] }}">
                                    @if ($errors->has('client_id'))
                                        <p class="mb-0 text-danger">{{ $errors->first('client_id') }}</p>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Paypal Client Secret') }}</label>
                                    <input class="form-control" name="client_secret"
                                        value="{{ $paypalInfo['client_secret'] }}">
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
        <div class="col-lg-4">
            <div class="card">
                <form class="" action="{{ route('admin.stripe.update') }}" method="post">
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
                                    $stripeInfo = json_decode($stripe->information, true);
                                    // dd($stripeInfo);
                                @endphp
                                <div class="form-group">
                                    <label>{{ __('Stripe') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1" class="selectgroup-input"
                                                {{ $stripe->status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0" class="selectgroup-input"
                                                {{ $stripe->status == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Stripe') }} {{ __('Key') }}</label>
                                    <input class="form-control" name="key" value="{{ $stripeInfo['key'] }}">
                                    @if ($errors->has('key'))
                                        <p class="mb-0 text-danger">{{ $errors->first('key') }}</p>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Stripe') }} {{ __('Secret') }} </label>
                                    <input class="form-control" name="secret" value="{{ $stripeInfo['secret'] }}">
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
        <div class="col-lg-4">
            <div class="card">
                <form class="" action="{{ route('admin.paytm.update') }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-title">{{ __('Paytm') }} </div>
                            </div>
                        </div>
                    </div>


                    <div class="card-body pt-5 pb-5">
                        <div class="row">
                            <div class="col-lg-12">
                                @csrf
                                @php
                                    $paytmInfo = json_decode($paytm->information, true);
                                @endphp
                                <div class="form-group">
                                    <label>{{ __('Paytm') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1"
                                                class="selectgroup-input" {{ $paytm->status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0"
                                                class="selectgroup-input" {{ $paytm->status == 0 ? 'checked' : '' }}>
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
                                                {{ $paytmInfo['environment'] == 'local' ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Local') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="environment" value="production"
                                                class="selectgroup-input"
                                                {{ $paytmInfo['environment'] == 'production' ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Production') }}</span>
                                        </label>
                                    </div>
                                    @if ($errors->has('environment'))
                                        <p class="mb-0 text-danger">{{ $errors->first('environment') }}</p>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Paytm Merchant Key') }}</label>
                                    <input class="form-control" name="secret" value="{{ $paytmInfo['secret'] }}">
                                    @if ($errors->has('secret'))
                                        <p class="mb-0 text-danger">{{ $errors->first('secret') }}</p>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Paytm Merchant mid') }}</label>
                                    <input class="form-control" name="merchant" value="{{ $paytmInfo['merchant'] }}">
                                    @if ($errors->has('merchant'))
                                        <p class="mb-0 text-danger">{{ $errors->first('merchant') }}</p>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Paytm Merchant website') }}</label>
                                    <input class="form-control" name="website" value="{{ $paytmInfo['website'] }}">
                                    @if ($errors->has('website'))
                                        <p class="mb-0 text-danger">{{ $errors->first('website') }}</p>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Industry type id') }}</label>
                                    <input class="form-control" name="industry" value="{{ $paytmInfo['industry'] }}">
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
        <div class="col-lg-4">
            <div class="card">
                <form class="" action="{{ route('admin.instamojo.update') }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-title">{{ __('Instamojo') }}</div>
                            </div>
                        </div>
                    </div>


                    <div class="card-body pt-5 pb-5">
                        <div class="row">
                            <div class="col-lg-12">
                                @csrf
                                @php
                                    $instamojoInfo = json_decode($instamojo->information, true);
                                @endphp
                                <div class="form-group">
                                    <label>{{ __('Instamojo') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1"
                                                class="selectgroup-input" {{ $instamojo->status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0"
                                                class="selectgroup-input" {{ $instamojo->status == 0 ? 'checked' : '' }}>
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
                                                {{ $instamojoInfo['sandbox_check'] == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="sandbox_check" value="0"
                                                class="selectgroup-input"
                                                {{ $instamojoInfo['sandbox_check'] == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Instamojo') . ' API ' . __('Key') }}</label>
                                    <input class="form-control" name="key" value="{{ $instamojoInfo['key'] }}">
                                    @if ($errors->has('key'))
                                        <p class="mb-0 text-danger">{{ $errors->first('key') }}</p>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Instamojo') . ' ' . __('Auth Token') }}</label>
                                    <input class="form-control" name="token" value="{{ $instamojoInfo['token'] }}">
                                    @if ($errors->has('token'))
                                        <p class="mb-0 text-danger">{{ $errors->first('token') }}</p>
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
        <div class="col-lg-4">
            <div class="card">
                <form class="" action="{{ route('admin.paystack.update') }}" method="post">
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
                                    $paystackInfo = json_decode($paystack->information, true);
                                @endphp
                                <div class="form-group">
                                    <label>{{ __('Paystack') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1"
                                                class="selectgroup-input" {{ $paystack->status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0"
                                                class="selectgroup-input" {{ $paystack->status == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Paystack') . ' ' . __('Secret') . ' ' . __('Key') }}</label>
                                    <input class="form-control" name="key" value="{{ $paystackInfo['key'] }}">
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
        <div class="col-lg-4">
            <div class="card">
                <form class="" action="{{ route('admin.flutterwave.update') }}" method="post">
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
                                    $flutterwaveInfo = json_decode($flutterwave->information, true);
                                @endphp
                                <div class="form-group">
                                    <label>{{ __('Flutterwave') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1"
                                                class="selectgroup-input"
                                                {{ $flutterwave->status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0"
                                                class="selectgroup-input"
                                                {{ $flutterwave->status == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Flutterwave') . ' ' . __('Public Key') }}</label>
                                    <input class="form-control" name="public_key"
                                        value="{{ $flutterwaveInfo['public_key'] }}">
                                    @if ($errors->has('public_key'))
                                        <p class="mb-0 text-danger">{{ $errors->first('public_key') }}</p>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Flutterwave') . ' ' . __('Secret') . ' ' . __('Key') }}</label>
                                    <input class="form-control" name="secret_key"
                                        value="{{ $flutterwaveInfo['secret_key'] }}">
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
        <div class="col-lg-4">
            <div class="card">
                <form class="" action="{{ route('admin.mollie.update') }}" method="post">
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
                                    $mollieInfo = json_decode($mollie->information, true);
                                @endphp
                                <div class="form-group">
                                    <label>{{ __('Mollie Payment') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1"
                                                class="selectgroup-input" {{ $mollie->status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0"
                                                class="selectgroup-input" {{ $mollie->status == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Mollie Payment Key') }}</label>
                                    <input class="form-control" name="key" value="{{ $mollieInfo['key'] }}">
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
        <div class="col-lg-4">
            <div class="card">
                <form class="" action="{{ route('admin.razorpay.update') }}" method="post">
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
                                    $razorpayInfo = json_decode($razorpay->information, true);
                                @endphp
                                <div class="form-group">
                                    <label>{{ __('Razorpay') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1"
                                                class="selectgroup-input" {{ $razorpay->status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0"
                                                class="selectgroup-input" {{ $razorpay->status == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Razorpay') . ' ' . __('Key') }}</label>
                                    <input class="form-control" name="key" value="{{ $razorpayInfo['key'] }}">
                                    @if ($errors->has('key'))
                                        <p class="mb-0 text-danger">{{ $errors->first('key') }}</p>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Razorpay') . ' ' . __('Secret') }}</label>
                                    <input class="form-control" name="secret" value="{{ $razorpayInfo['secret'] }}">
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
        <div class="col-lg-4">
            <div class="card">
                <form class="" action="{{ route('admin.anet.update') }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-title">{{ __('Authorize.net') }}</div>
                            </div>
                        </div>
                    </div>


                    <div class="card-body pt-5 pb-5">
                        <div class="row">
                            <div class="col-lg-12">
                                @csrf
                                @php
                                    $anetInfo = json_decode($anet->information, true);
                                @endphp
                                <div class="form-group">
                                    <label>{{ __('Authorize.net') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1"
                                                class="selectgroup-input" {{ $anet->status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0"
                                                class="selectgroup-input" {{ $anet->status == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Authorize.net') . ' ' . __('Test Mode') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="sandbox_check" value="1"
                                                class="selectgroup-input"
                                                {{ $anetInfo['sandbox_check'] == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="sandbox_check" value="0"
                                                class="selectgroup-input"
                                                {{ $anetInfo['sandbox_check'] == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>{{ __('API Login ID') }}</label>
                                    <input class="form-control" name="login_id" value="{{ $anetInfo['login_id'] }}">
                                    @if ($errors->has('login_id'))
                                        <p class="mb-0 text-danger">{{ $errors->first('login_id') }}</p>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Transaction Key') }}</label>
                                    <input class="form-control" name="transaction_key"
                                        value="{{ $anetInfo['transaction_key'] }}">
                                    @if ($errors->has('transaction_key'))
                                        <p class="mb-0 text-danger">{{ $errors->first('transaction_key') }}</p>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Public Client Key') }}</label>
                                    <input class="form-control" name="public_key"
                                        value="{{ $anetInfo['public_key'] }}">
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
        <div class="col-lg-4">
            <div class="card">
                <form class="" action="{{ route('admin.mercadopago.update') }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-title">{{ __('Mercado Pago') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-5 pb-5">

                        @csrf
                        @php
                            $mercadopagoInfo = json_decode($mercadopago->information, true);
                        @endphp
                        <div class="form-group">
                            <label>{{ __('Mercado Pago') }}</label>
                            <div class="selectgroup w-100">
                                <label class="selectgroup-item">
                                    <input type="radio" name="status" value="1" class="selectgroup-input"
                                        {{ $mercadopago->status == 1 ? 'checked' : '' }}>
                                    <span class="selectgroup-button">{{ __('Active') }}</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="radio" name="status" value="0" class="selectgroup-input"
                                        {{ $mercadopago->status == 0 ? 'checked' : '' }}>
                                    <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>{{ __('Mercado Pago') . ' ' . __('Test Mode') }}</label>
                            <div class="selectgroup w-100">
                                <label class="selectgroup-item">
                                    <input type="radio" name="sandbox_check" value="1" class="selectgroup-input"
                                        {{ $mercadopagoInfo['sandbox_check'] == 1 ? 'checked' : '' }}>
                                    <span class="selectgroup-button">{{ __('Active') }}</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="radio" name="sandbox_check" value="0" class="selectgroup-input"
                                        {{ $mercadopagoInfo['sandbox_check'] == 0 ? 'checked' : '' }}>
                                    <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>{{ __('Mercado Pago') . ' ' . __('Token') }}</label>
                            <input class="form-control" name="token" value="{{ $mercadopagoInfo['token'] }}">
                            @if ($errors->has('token'))
                                <p class="mb-0 text-danger">{{ $errors->first('token') }}</p>
                            @endif
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

        {{-- Midtrans --}}

        <div class="col-lg-4">
            <div class="card">
                <form action="{{ route('admin.update_midtrans_info') }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-title">{{ __('Midtrans') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>{{ __('Midtrans Status') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1"
                                                class="selectgroup-input" {{ @$midtrans->status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0"
                                                class="selectgroup-input" {{ @$midtrans->status == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                    @if ($errors->has('status'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('status') }}</p>
                                    @endif
                                </div>

                                @php $midtransInfo = json_decode(@$midtrans->information, true); @endphp

                                <div class="form-group">
                                    <label>{{ __('Midtrans Test Mode') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="midtrans_mode" value="1"
                                                class="selectgroup-input"
                                                {{ !empty($midtransInfo) && $midtransInfo['midtrans_mode'] == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="midtrans_mode" value="0"
                                                class="selectgroup-input"
                                                {{ !empty($midtransInfo) && $midtransInfo['midtrans_mode'] == 0 ? 'checked' : '' }}>
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

                                <p class="text-warning mb-0">{{ __('Your Success URL') }} :
                                    {{ route('midtrans.bank_notify') }} </p>
                                <p class="text-warning mb-0">{{ __('Your Cancel URL') }} :
                                    {{ route('midtrans.cancel') }} </p>
                                <p class="text-warning mb-0">
                                    <strong></strong>{{ __('Set these URLs in Midtrans Dashboard like this') }} :
                                </p>
                                <a href="https://prnt.sc/OiucUCeYJIXo" target="_blank">https://prnt.sc/OiucUCeYJIXo</a>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-success">
                                    {{ __('Update') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        {{-- Iyzico --}}

        <div class="col-lg-4">
            <div class="card">
                <form action="{{ route('admin.update_iyzico_info') }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-title">{{ __('Iyzico') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>{{ __('Iyzico Status') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1"
                                                class="selectgroup-input" {{ @$iyzico->status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0"
                                                class="selectgroup-input" {{ @$iyzico->status == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                    @if ($errors->has('status'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('status') }}</p>
                                    @endif
                                </div>

                                @php  $iyzicoInfo = json_decode(@$iyzico->information, true); @endphp

                                <div class="form-group">
                                    <label>{{ __('Iyzico Test Mode') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="iyzico_mode" value="1"
                                                class="selectgroup-input"
                                                {{ !empty($iyzicoInfo) && $iyzicoInfo['iyzico_mode'] == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="iyzico_mode" value="0"
                                                class="selectgroup-input"
                                                {{ !empty($iyzicoInfo) && $iyzicoInfo['iyzico_mode'] == 0 ? 'checked' : '' }}>
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
                                    <input type="text" class="form-control" name="secret_key"
                                        value="{{ !empty($iyzicoInfo['secret_key']) ? $iyzicoInfo['secret_key'] : null }}">
                                    @if ($errors->has('secret_key'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('secret_key') }}</p>
                                    @endif
                                </div>
                                <p class="text-warning"><strong>{{ __('Cron Job Command') . ':' }}</strong> <br>
                                    <code class="copy">curl -sS {{ route('cron.check_payment') }}</code>
                                </p>
                                <strong class="text-warning">{{ __('Set the cron job following this video') . ' :' }}
                                </strong>
                                <a href="https://www.awesomescreenshot.com/video/25404126?key=3f7a7fa8cf2391113bb926f43609fa56"
                                    target="_blank">https://www.awesomescreenshot.com/video/25404126?key=3f7a7fa8cf2391113bb926f43609fa56</a>
                                <p class="text-danger">
                                    {{ __("Without cronjob setup, Iyzico payment method won't work") }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-success">
                                    {{ __('Update') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        {{-- paytabs Information --}}
        <div class="col-lg-4">
            <div class="card">
                <form action="{{ route('admin.update_paytabs_info') }}" method="post">
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
                                                {{ $paytabs && $paytabs->status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0"
                                                class="selectgroup-input"
                                                {{ $paytabs && $paytabs->status == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                    @if ($errors->has('status'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('status') }}</p>
                                    @endif
                                </div>
                                @if ($paytabs)
                                    @php $paytabsInfo = json_decode(@$paytabs->information, true); @endphp

                                    <div class="form-group">
                                        <label>{{ __('Country') }}</label>
                                        <select name="country" id="" class="form-control">
                                            <option value="global" @selected(!empty($paytabsInfo['country']) && $paytabsInfo['country'] == 'global')>{{ __('Global') }}
                                            </option>
                                            <option value="sa" @selected(!empty($paytabsInfo['country']) && $paytabsInfo['country'] == 'sa')>{{ __('Saudi Arabia') }}
                                            </option>
                                            <option value="uae" @selected(!empty($paytabsInfo['country']) && $paytabsInfo['country'] == 'uae')>
                                                {{ __('United Arab Emirates') }}</option>
                                            <option value="egypt" @selected(!empty($paytabsInfo['country']) && $paytabsInfo['country'] == 'egypt')>{{ __('Egypt') }}
                                            </option>
                                            <option value="oman" @selected(!empty($paytabsInfo['country']) && $paytabsInfo['country'] == 'oman')>{{ __('Oman') }}
                                            </option>
                                            <option value="jordan" @selected(!empty($paytabsInfo['country']) && $paytabsInfo['country'] == 'jordan')>{{ __('Jordan') }}
                                            </option>
                                            <option value="iraq" @selected(!empty($paytabsInfo['country']) && $paytabsInfo['country'] == 'iraq')>{{ __('Iraq') }}
                                            </option>
                                        </select>
                                        @if ($errors->has('country'))
                                            <p class="mt-1 mb-0 text-danger">{{ $errors->first('server_key') }}</p>
                                        @endif
                                    </div>
                                @endif

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
                                        {{ __('You will get your API Endpoint from PayTabs dashboard') . '.' }}
                                    </p>
                                    <strong class="text-warning">{{ __('Step') . '-1: ' }}</strong> <a
                                        href="https://prnt.sc/McaCbxt75fyi"
                                        target="_blank">https://prnt.sc/McaCbxt75fyi</a><br>
                                    <strong class="text-warning">{{ __('Step') . '-2: ' }}</strong> <a
                                        href="https://prnt.sc/DgztAyHVR2o8"
                                        target="_blank">https://prnt.sc/DgztAyHVR2o8</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-success">
                                    {{ __('Update') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>


        {{-- toyyibpay Information --}}
        <div class="col-lg-4">
            <div class="card">
                <form action="{{ route('admin.update_toyyibpay_info') }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-title">{{ __('Toyyibpay') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>{{ __('Toyyibpay Status') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1"
                                                class="selectgroup-input"
                                                {{ $toyyibpay && $toyyibpay->status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0"
                                                class="selectgroup-input"
                                                {{ $toyyibpay && $toyyibpay->status == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                    @if ($errors->has('status'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('status') }}</p>
                                    @endif
                                </div>
                                @if ($toyyibpay)
                                    @php $toyyibpayInfo = json_decode($toyyibpay->information, true); @endphp

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
                                @endif

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
                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-success">
                                    {{ __('Update') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        {{-- phonepe Information --}}
        <div class="col-lg-4">
            <div class="card">
                <form action="{{ route('admin.update_phonepe_info') }}" method="post">
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
                                                {{ $phonepe && $phonepe->status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0"
                                                class="selectgroup-input"
                                                {{ $phonepe && $phonepe->status == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                    @if ($errors->has('status'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('status') }}</p>
                                    @endif
                                </div>
                                @if ($phonepe)
                                    @php $phonepeInfo = json_decode(@$phonepe->information, true); @endphp

                                    <div class="form-group">
                                        <label>{{ __('Sandbox Status') }}</label>
                                        <div class="selectgroup w-100">
                                            <label class="selectgroup-item">
                                                <input type="radio" name="sandbox_status" value="1"
                                                    class="selectgroup-input"
                                                    {{ isset($phonepeInfo['sandbox_status']) && $phonepeInfo['sandbox_status'] == 1 ? 'checked' : '' }}>
                                                <span class="selectgroup-button">{{ __('Active') }}</span>
                                            </label>
                                            <label class="selectgroup-item">
                                                <input type="radio" name="sandbox_status" value="0"
                                                    class="selectgroup-input"
                                                    {{ isset($phonepeInfo['sandbox_status']) && $phonepeInfo['sandbox_status'] == 0 ? 'checked' : '' }}>
                                                <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                            </label>
                                        </div>
                                        @if ($errors->has('sandbox_status'))
                                            <p class="mt-1 mb-0 text-danger">{{ $errors->first('sandbox_status') }}</p>
                                        @endif
                                    </div>
                                @endif

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
                                <button type="submit" class="btn btn-success">
                                    {{ __('Update') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        {{-- yoco Information --}}
        <div class="col-lg-4">
            <div class="card">
                <form action="{{ route('admin.update_yoco_info') }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-title">{{ __('Yoco') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>{{ __('Yoco Status') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1"
                                                class="selectgroup-input"
                                                {{ $yoco && $yoco->status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0"
                                                class="selectgroup-input"
                                                {{ $yoco && $yoco->status == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                    @if ($errors->has('status'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('status') }}</p>
                                    @endif
                                </div>

                                @php $yocoInfo = json_decode(@$yoco->information, true); @endphp

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
                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-success">
                                    {{ __('Update') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- myfatoorah Information --}}
        <div class="col-lg-4">
            <div class="card">
                <form action="{{ route('admin.update_myfatoorah_info') }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-title">{{ __('MyFatoorah') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>{{ __('MyFatoorah Status') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1"
                                                class="selectgroup-input"
                                                {{ $myfatoorah && $myfatoorah->status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0"
                                                class="selectgroup-input"
                                                {{ $myfatoorah && $myfatoorah->status == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                    @if ($errors->has('status'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('status') }}</p>
                                    @endif
                                </div>
                                @if ($myfatoorah)
                                    @php $myfatoorahInfo = json_decode(@$myfatoorah->information, true); @endphp
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
                                @endif

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
                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-success">
                                    {{ __('Update') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        {{-- xendit Information --}}
        <div class="col-lg-4">
            <div class="card">
                <form action="{{ route('admin.update_xendit_info') }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-title">{{ __('Xendit') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>{{ __('Xendit Status') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1"
                                                class="selectgroup-input"
                                                {{ $xendit && $xendit->status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0"
                                                class="selectgroup-input"
                                                {{ $xendit && $xendit->status == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                    @if ($errors->has('status'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('status') }}</p>
                                    @endif
                                </div>

                                @php $xenditInfo = json_decode(@$xendit->information, true); @endphp

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
                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-success">
                                    {{ __('Update') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>


        {{-- Perfect Money Information --}}
        <div class="col-lg-4">
            <div class="card">
                <form action="{{ route('admin.update_perfect_money_info') }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-title">{{ __('Perfect Money') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>{{ __('Perfect Money Status') }}</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="1"
                                                class="selectgroup-input"
                                                {{ $perfect_money && $perfect_money->status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status" value="0"
                                                class="selectgroup-input"
                                                {{ $perfect_money && $perfect_money->status == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                    @if ($errors->has('status'))
                                        <p class="mt-1 mb-0 text-danger">{{ $errors->first('status') }}</p>
                                    @endif
                                </div>

                                @php $perfect_moneyInfo = json_decode(@$perfect_money->information, true); @endphp

                                <div class="form-group">
                                    <label>{{ __('Perfect Money Wallet Id') }}</label>
                                    <input type="text" class="form-control" name="perfect_money_wallet_id"
                                        value="{{ @$perfect_moneyInfo['perfect_money_wallet_id'] }}">
                                    @if ($errors->has('perfect_money_wallet_id'))
                                        <p class="mt-1 mb-0 text-danger">
                                            {{ $errors->first('perfect_money_wallet_id') }}</p>
                                    @endif

                                    <p class="text-warning mt-1 mb-0">{{ __('You will get wallet id form here') }}
                                    </p>
                                    <a href="https://prnt.sc/bM3LqLXBduaq"
                                        target="_blank">https://prnt.sc/bM3LqLXBduaq</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-success">
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
