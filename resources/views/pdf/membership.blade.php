<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ rtlAwareText( __('Invoice'), $isRtl) }}</title>
    <link rel="stylesheet" href="{{ asset('assets/front/css/membership-pdf.css') }}">

</head>

<body>

    <div class="main">
        <table class="heading">
            <tr>
                <td>
                    @if ($bs->logo)
                        <img loading="lazy" src="{{ asset('assets/front/img/' . $bs->logo) }}" height="40"
                            class="d-inline-block">
                    @else
                        <img loading="lazy" src="{{ asset('assets/admin/img/noimage.jpg') }}" height="40"
                            class="d-inline-block">
                    @endif
                </td>

                <td class="text-right strong invoice-heading">{{ rtlAwareText( __('INVOICE'), $isRtl)  }}</td>
            </tr>
        </table>
        <div class="header">
            <div class="ml-20">
                <table class="text-left">
                    <tr>
                        <td class="strong small gry-color">{{ rtlAwareText(__('Bill to'), $isRtl) }}:</td>
                    </tr>
                    <tr>
                        <td class="strong">
                            {{ rtlAwareText(ucfirst($member['first_name']) . ' ' . ucfirst($member['last_name']), $isRtl) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="gry-color small"><strong>{{ rtlAwareText( __('Username'), $isRtl) }}: </strong>{{ $member['username'] }}
                        </td>
                    </tr>
                    <tr>
                        <td class="gry-color small"><strong>{{ rtlAwareText( __('Email'), $isRtl) }}: </strong> {{ $member['email'] }}</td>
                    </tr>
                    <tr>
                        <td class="gry-color small"><strong>{{ rtlAwareText( __('Phone'), $isRtl) }}: </strong> {{ $phone }}</td>
                    </tr>
                </table>
            </div>
            <div class="order-details">
                <table class="text-right">
                    <tr>
                        <td class="strong">{{ rtlAwareText( __('Order Details'), $isRtl) }}:</td>
                    </tr>
                    <tr>
                        <td class="gry-color small"><strong>{{ rtlAwareText( __('Order ID'), $isRtl) }}:</strong> #{{ $order_id }}</td>
                    </tr>
                    <tr>
                        <td class="gry-color small"><strong>{{ rtlAwareText( __('Order Price'), $isRtl)}}:</strong>
                            {{ $amount == 0 ? rtlAwareText( __('Free'), $isRtl) : $amount }}</td>
                    </tr>
                    <tr>
                        <td class="gry-color small"><strong>{{ rtlAwareText( __('Payment Method'), $isRtl) }}:</strong>
                            {{ rtlAwareText( __($request['payment_method']), $isRtl) }}</td>
                    </tr>
                    <tr>
                        <td class="gry-color small"><strong>{{ rtlAwareText( __('Payment Status'), $isRtl) }}:</strong>{{rtlAwareText( __('Completed'), $isRtl) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="gry-color small"><strong>{{rtlAwareText( __('Order Date'), $isRtl)  }}:</strong>
                            {{ \Illuminate\Support\Carbon::now()->format('d/m/Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="package-info">
            <table class="padding text-left small border-bottom">
                <thead>
                    <tr class="gry-color info-titles">
                        <th width="20%">{{rtlAwareText( __('Package Title'), $isRtl) }}</th>
                        <th width="20%">{{rtlAwareText( __('Start Date'), $isRtl)}}</th>
                        <th width="20%">{{ rtlAwareText( __('Expire Date'), $isRtl) }}</th>
                        <th width="20%">{{ rtlAwareText( __('Currency'), $isRtl) }}</th>
                        <th width="20%">{{ rtlAwareText( __('Price'), $isRtl)}}</th>
                    </tr>
                </thead>
                <tbody class="strong">

                    <tr class="text-center">
                        <td>{{ rtlAwareText( __($package_title), $isRtl)  }}</td>
                        <td>{{ $request['start_date'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($request['expire_date'])->format('Y') == '9999' ? rtlAwareText( __('Lifetime'), $isRtl)  : $request['expire_date'] }}
                        </td>
                        <td>{{ $base_currency_text }}</td>
                        <td>
                            {{ $amount == 0 ? rtlAwareText( __('Free'), $isRtl) : $amount }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <table class="mt-80">
            <tr>
                <td class="text-right regards">{{ rtlAwareText( __('Thanks & Regards'), $isRtl) . ',' }}</td>
            </tr>
            <tr>
                <td class="text-right strong regards">{{ rtlAwareText( __($bs->website_title), $isRtl) }}</td>
            </tr>
        </table>
    </div>

</body>

</html>
