<?php

use Illuminate\Support\Facades\Route;

Route::namespace('App\Http\Controllers')
    ->middleware('web')
    ->group(function () {

        // Admin gateway update routes
        Route::post('/zarinpal/update', 'User\GatewayController@zarinpalUpdate')->name('user.zarinpal.update');
        Route::post('/zibal/update', 'User\GatewayController@zibalUpdate')->name('user.zibal.update');
        Route::post('/idpay/update', 'User\GatewayController@idpayUpdate')->name('user.idpay.update');
        Route::post('/nextpay/update', 'User\GatewayController@nextpayUpdate')->name('user.nextpay.update');
        Route::post('/payir/update', 'User\GatewayController@payirUpdate')->name('user.payir.update');
        Route::post('/mellat/update', 'User\GatewayController@mellatUpdate')->name('user.mellat.update');

        // Admin gateway update routes (POST from admin panel)
        Route::post('/zarinpal', 'Admin\GatewayController@zarinpalUpdate')->name('admin.zarinpal.update');
        Route::post('/zibal', 'Admin\GatewayController@zibalUpdate')->name('admin.zibal.update');
        Route::post('/idpay', 'Admin\GatewayController@idpayUpdate')->name('admin.idpay.update');
        Route::post('/nextpay', 'Admin\GatewayController@nextpayUpdate')->name('admin.nextpay.update');
        Route::post('/payir', 'Admin\GatewayController@payirUpdate')->name('admin.payir.update');
        Route::post('/mellat', 'Admin\GatewayController@mellatUpdate')->name('admin.mellat.update');

        // ZarinPal routes
        Route::match(['get','post'], 'zarinpal/success', 'Payment\ZarinPalController@successPayment')->name('membership.zarinpal.success');
        Route::get('zarinpal/cancel', 'Payment\ZarinPalController@cancelPayment')->name('membership.zarinpal.cancel');

        // Zibal routes
        Route::match(['get','post'], 'zibal/success', 'Payment\ZibalController@successPayment')->name('membership.zibal.success');
        Route::get('zibal/cancel', 'Payment\ZibalController@cancelPayment')->name('membership.zibal.cancel');

        // IDPay routes (callback is POST per official docs)
        Route::match(['get','post'], 'idpay/success', 'Payment\IdPayController@success')->name('membership.idpay.success');
        Route::get('idpay/cancel', 'Payment\IdPayController@cancel')->name('membership.idpay.cancel');

        // NextPay routes (callback is POST per official docs)
        Route::match(['get','post'], 'nextpay/success', 'Payment\NextPayController@success')->name('membership.nextpay.success');
        Route::get('nextpay/cancel', 'Payment\NextPayController@cancel')->name('membership.nextpay.cancel');

        // Pay.ir routes (callback is POST per official docs)
        Route::match(['get','post'], 'payir/success', 'Payment\PayIrController@success')->name('membership.payir.success');
        Route::get('payir/cancel', 'Payment\PayIrController@cancel')->name('membership.payir.cancel');

        // Mellat routes
        Route::match(['get','post'], 'mellat/success', 'Payment\MellatController@successPayment')->name('membership.mellat.success');
        Route::get('mellat/cancel', 'Payment\MellatController@cancelPayment')->name('membership.mellat.cancel');

        // Appointment/Vendor callback routes
        Route::get('/zarinpal/notify', 'User\Payment\ZarinPalController@successPayment')
            ->name('customer.appointment.zarinpal.notify');
        Route::get('/zarinpal/cancel', 'User\Payment\ZarinPalController@cancelPayment')
            ->name('customer.appointment.zarinpal.cancel');

        Route::get('/zibal/notify', 'User\Payment\ZibalController@successPayment')
            ->name('customer.appointment.zibal.notify');
        Route::get('/zibal/cancel', 'User\Payment\ZibalController@cancelPayment')
            ->name('customer.appointment.zibal.cancel');

        Route::match(['get','post'], '/idpay/notify', 'User\Payment\IdPayController@successPayment')
            ->name('customer.appointment.idpay.notify');
        Route::get('/idpay/cancel', 'User\Payment\IdPayController@cancelPayment')
            ->name('customer.appointment.idpay.cancel');

        Route::match(['get','post'], '/nextpay/notify', 'User\Payment\NextPayController@successPayment')
            ->name('customer.appointment.nextpay.notify');
        Route::get('/nextpay/cancel', 'User\Payment\NextPayController@cancelPayment')
            ->name('customer.appointment.nextpay.cancel');

        Route::match(['get','post'], '/payir/notify', 'User\Payment\PayIrController@successPayment')
            ->name('customer.appointment.payir.notify');
        Route::get('/payir/cancel', 'User\Payment\PayIrController@cancelPayment')
            ->name('customer.appointment.payir.cancel');

        // Mellat callback routes (POST from bank)
        Route::match(['get','post'], '/mellat/notify', 'User\Payment\MellatController@successPayment')
            ->name('customer.appointment.mellat.notify');
        Route::get('/mellat/cancel', 'User\Payment\MellatController@cancelPayment')
            ->name('customer.appointment.mellat.cancel');
    });
