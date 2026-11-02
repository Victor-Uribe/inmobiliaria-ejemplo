<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PropertyController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/share/property/{property}', [PropertyController::class, 'share'])
    ->name('property.share');
