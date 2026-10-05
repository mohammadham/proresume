<?php

use Illuminate\Support\Facades\Route;

// NOTE: the App\Http\Controllers namespace and the 'web' middleware group are both
// already applied by RouteServiceProvider. Re-declaring them here caused a doubled
// namespace (controller not found) and a doubled session/CSRF run (every POST 419).

// ------------------------------------------------------------------
// User panel gateway settings
// ------------------------------------------------------------------
Route::post('/zarinpal/update', 'User\GatewayController@zarinpalUpdate')->name('user.zarinpal.update');
Route::post('/zibal/update', 'User\GatewayController@zibalUpdate')->name('user.zibal.update');
Route::post('/idpay/update', 'User\GatewayController@idpayUpdate')->name('user.idpay.update');
Route::post('/nextpay/update', 'User\GatewayController@nextpayUpdate')->name('user.nextpay.update');
Route::post('/payir/update', 'User\GatewayController@payirUpdate')->name('user.payir.update');
Route::post('/mellat/update', 'User\GatewayController@mellatUpdate')->name('user.mellat.update');

// ------------------------------------------------------------------
// Admin panel gateway settings
// ------------------------------------------------------------------
Route::post('/zarinpal', 'Admin\GatewayController@zarinpalUpdate')->name('admin.zarinpal.update');
Route::post('/zibal', 'Admin\GatewayController@zibalUpdate')->name('admin.zibal.update');
Route::post('/idpay', 'Admin\GatewayController@idpayUpdate')->name('admin.idpay.update');
Route::post('/nextpay', 'Admin\GatewayController@nextpayUpdate')->name('admin.nextpay.update');
Route::post('/payir', 'Admin\GatewayController@payirUpdate')->name('admin.payir.update');
Route::post('/mellat', 'Admin\GatewayController@mellatUpdate')->name('admin.mellat.update');

// ------------------------------------------------------------------
// Gateway callbacks
// ------------------------------------------------------------------
// Every route below is hit by the payment provider, not by a form we render:
// IDPay/NextPay/Pay.ir/Mellat POST the callback server-to-server and no bank can
// present a CSRF token. Leaving VerifyCsrfToken on them meant every one of those
// callbacks answered 419 and the customer never got their membership, so the
// CSRF check is removed from the gateway callbacks only.
$noCsrf = [\App\Http\Middleware\VerifyCsrfToken::class];

// Membership (subscription) callbacks
// ------------------------------------------------------------------
Route::match(['get', 'post'], 'zarinpal/success', 'Payment\ZarinPalController@successPayment')->withoutMiddleware($noCsrf)->name('membership.zarinpal.success');
Route::get('zarinpal/cancel/membership', 'Payment\ZarinPalController@cancelPayment')->withoutMiddleware($noCsrf)->name('membership.zarinpal.cancel');

Route::match(['get', 'post'], 'zibal/success', 'Payment\ZibalController@successPayment')->withoutMiddleware($noCsrf)->name('membership.zibal.success');
Route::get('zibal/cancel/membership', 'Payment\ZibalController@cancelPayment')->withoutMiddleware($noCsrf)->name('membership.zibal.cancel');

Route::match(['get', 'post'], 'idpay/success', 'Payment\IdPayController@success')->withoutMiddleware($noCsrf)->name('membership.idpay.success');
Route::get('idpay/cancel/membership', 'Payment\IdPayController@cancel')->withoutMiddleware($noCsrf)->name('membership.idpay.cancel');

Route::match(['get', 'post'], 'nextpay/success', 'Payment\NextPayController@success')->withoutMiddleware($noCsrf)->name('membership.nextpay.success');
Route::get('nextpay/cancel/membership', 'Payment\NextPayController@cancel')->withoutMiddleware($noCsrf)->name('membership.nextpay.cancel');

Route::match(['get', 'post'], 'payir/success', 'Payment\PayIrController@success')->withoutMiddleware($noCsrf)->name('membership.payir.success');
Route::get('payir/cancel/membership', 'Payment\PayIrController@cancel')->withoutMiddleware($noCsrf)->name('membership.payir.cancel');

Route::match(['get', 'post'], 'mellat/success', 'Payment\MellatController@successPayment')->withoutMiddleware($noCsrf)->name('membership.mellat.success');
Route::get('mellat/cancel/membership', 'Payment\MellatController@cancelPayment')->withoutMiddleware($noCsrf)->name('membership.mellat.cancel');

// ------------------------------------------------------------------
// Appointment / vendor callbacks
// ------------------------------------------------------------------
Route::get('/zarinpal/notify', 'User\Payment\ZarinPalController@successPayment')
    ->withoutMiddleware($noCsrf)
    ->name('customer.appointment.zarinpal.notify');
Route::get('/zarinpal/cancel', 'User\Payment\ZarinPalController@cancelPayment')
    ->withoutMiddleware($noCsrf)
    ->name('customer.appointment.zarinpal.cancel');

Route::get('/zibal/notify', 'User\Payment\ZibalController@successPayment')
    ->withoutMiddleware($noCsrf)
    ->name('customer.appointment.zibal.notify');
Route::get('/zibal/cancel', 'User\Payment\ZibalController@cancelPayment')
    ->withoutMiddleware($noCsrf)
    ->name('customer.appointment.zibal.cancel');

Route::match(['get', 'post'], '/idpay/notify', 'User\Payment\IdPayController@successPayment')
    ->withoutMiddleware($noCsrf)
    ->name('customer.appointment.idpay.notify');
Route::get('/idpay/cancel', 'User\Payment\IdPayController@cancelPayment')
    ->withoutMiddleware($noCsrf)
    ->name('customer.appointment.idpay.cancel');

Route::match(['get', 'post'], '/nextpay/notify', 'User\Payment\NextPayController@successPayment')
    ->withoutMiddleware($noCsrf)
    ->name('customer.appointment.nextpay.notify');
Route::get('/nextpay/cancel', 'User\Payment\NextPayController@cancelPayment')
    ->withoutMiddleware($noCsrf)
    ->name('customer.appointment.nextpay.cancel');

Route::match(['get', 'post'], '/payir/notify', 'User\Payment\PayIrController@successPayment')
    ->withoutMiddleware($noCsrf)
    ->name('customer.appointment.payir.notify');
Route::get('/payir/cancel', 'User\Payment\PayIrController@cancelPayment')
    ->withoutMiddleware($noCsrf)
    ->name('customer.appointment.payir.cancel');

Route::match(['get', 'post'], '/mellat/notify', 'User\Payment\MellatController@successPayment')
    ->withoutMiddleware($noCsrf)
    ->name('customer.appointment.mellat.notify');
Route::get('/mellat/cancel', 'User\Payment\MellatController@cancelPayment')
    ->withoutMiddleware($noCsrf)
    ->name('customer.appointment.mellat.cancel');