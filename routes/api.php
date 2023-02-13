<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

    Route::post('register', [AuthController::class, 'register']);
    Route::post('complete', [AuthController::class, 'complete']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('verify/{token}', [AuthController::class, 'verifyEmail']);
    Route::post('password/forgot', [AuthController::class, 'forgotPassword']);
    Route::get('password/reset/{token}', [AuthController::class, 'resetPassword']);
    Route::get('password/check/{token}', [AuthController::class, 'checkPasswordResetToken']);
    Route::post('password/change', [AuthController::class, 'changePassword']);

    Route::group(['middleware'=>'jwt.verify'], function() {
        Route::get('user', [AuthController::class, 'getUser']);
    });

    Route::group(['prefix' => 'catalog'], function () {
        Route::get('category/{url}', [CatalogController::class, 'getCategory']);
        Route::get('main-categories', [CatalogController::class, 'getMainCategories']);

    });
