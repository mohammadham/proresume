<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProviderController;
use App\Http\Controllers\API\AppointmentController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);

        Route::get('/appointments/slots/{provider}', [AppointmentController::class, 'slots']);
        Route::post('/appointments', [AppointmentController::class, 'store']);
        Route::get('/appointments', [AppointmentController::class, 'myAppointments']);
    });

    Route::get('/providers', [ProviderController::class, 'index']);
    Route::get('/providers/map', [ProviderController::class, 'map']);
    Route::get('/providers/{id}', [ProviderController::class, 'show']);
});
