<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\PropertyController;

use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\PropertyImageController;
use App\Http\Controllers\Api\TestimonialController;
use App\Http\Controllers\Api\UserController;

use App\Http\Controllers\Api\PropertyBuyerController;
use App\Http\Controllers\Api\PropertyPaymentController;


Route::get('/test', function () {
    return response()->json([
        'message' => 'API funcionando correctamente'
    ]);
});


Route::prefix('public')->group(function () {
    Route::get('/properties', [PropertyController::class, 'publicIndex']);
    Route::get('/properties/featured', [PropertyController::class, 'publicFeatured']);
    Route::get('/properties/{slug}', [PropertyController::class, 'publicShow']);
    Route::post('/leads', [LeadController::class, 'store']);
    Route::get('/testimonials', [TestimonialController::class, 'publicIndex']);
    Route::get('/banners', [BannerController::class, 'publicIndex']);
    Route::post('/leads', [LeadController::class, 'store']);
});

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/auth/forgot-password', [PasswordResetController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [PasswordResetController::class, 'resetPassword']);

    Route::middleware('auth:api')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });
});

Route::middleware('auth:api')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::apiResource('/properties', PropertyController::class);
    Route::post('/properties/{property}/images', [PropertyImageController::class, 'store']);
    Route::patch('/property-images/{image}/main', [PropertyImageController::class, 'setMain']);
    Route::delete('/property-images/{image}', [PropertyImageController::class, 'destroy']);

    Route::apiResource('/testimonials', TestimonialController::class)
        ->middleware('role:super-admin,admin');

    Route::apiResource('/banners', BannerController::class)
        ->middleware('role:super-admin,admin');

    Route::apiResource('/users', UserController::class)
        ->middleware('role:super-admin,admin');

    Route::get('/leads', [LeadController::class, 'index'])
        ->middleware('role:super-admin,admin');

    Route::patch('/leads/{lead}', [LeadController::class, 'update'])
        ->middleware('role:super-admin,admin');

    Route::post('/properties/{property}/buyer', [PropertyBuyerController::class, 'storeOrUpdate']);

    Route::get('/properties/{property}/payments', [PropertyPaymentController::class, 'index']);
    Route::post('/properties/{property}/payments', [PropertyPaymentController::class, 'store']);

    Route::delete('/property-payments/{payment}', [PropertyPaymentController::class, 'destroy']);

});
