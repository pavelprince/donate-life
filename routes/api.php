<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// public route
Route::controller(AuthController::class)->group(function () {
    Route::prefix('otp')->group(function () {
        Route::post('generate', 'generate')->name('otp.generate');
        Route::post('verification', 'verification')->name('otp.verification');
    });
});

Route::group(['middleware'=> 'api'], function ($routes) {
    Route::post('/register', [\App\Http\Controllers\Api\UserController::class, 'register']);
    Route::post('/login', [\App\Http\Controllers\Api\UserController::class, 'login']);
});


// private route
Route::group(['middleware' => ['auth:api']], function () {
    Route::get('/home', [\App\Http\Controllers\Api\HomeController::class, 'index']);
});
