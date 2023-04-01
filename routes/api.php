<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\InfoController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;

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

    Route::group(['middleware' => 'jwt.verify'], function() {
        Route::get('user', [AuthController::class, 'getUser']);
    });

    Route::group(['prefix' => 'catalog'], function () {
        Route::post('check-category', [CatalogController::class, 'checkCategory']);
        Route::get('category/{url}', [CatalogController::class, 'getCategory']);
        Route::get('main-categories', [CatalogController::class, 'getMainCategories']);
        Route::get('menu-categories', [CatalogController::class, 'getMenuCategories']);
        Route::get('child-categories/{parent_id}', [CatalogController::class, 'getChildCategories']);
        Route::get('category-products/{category_id}/{page?}', [CatalogController::class, 'getCategoryProducts']);
        Route::post('category-products', [CatalogController::class, 'getCategoryProductsPost']);
        Route::get('random-products', [CatalogController::class, 'getRandomProducts']);
        Route::post('seller-products', [CatalogController::class, 'getSellerProducts']);
        Route::post('search', [CatalogController::class, 'getSearchResult']);
    });

    Route::group(['prefix' => 'locations'], function () {
        Route::get('get', [LocationController::class, 'getLocations']);
        Route::get('search/{search}', [LocationController::class, 'searchLocations']);
    });

    Route::group(['prefix' => 'info'], function () {
        // Route::get('banners/get', [LocationController::class, 'getBanners']);
        Route::get('banner/{code}', [InfoController::class, 'getBanner']);
        Route::post('send-feedback', [InfoController::class, 'sendFeedback']);
    });

    Route::group(['prefix' => 'product'], function () {
        Route::post('check-product', [ProductController::class, 'checkProduct']);
        Route::post('save', [ProductController::class, 'saveProduct']);
        Route::get('get/{url}', [ProductController::class, 'getProduct']);
        Route::post('increase-views', [ProductController::class, 'increaseProductViews']);
    });

    Route::group(['prefix' => 'profile'], function () {
        Route::post('update', [ProfileController::class, 'updateProfile']);
        Route::post('set-avatar', [ProfileController::class, 'setAvatar']);
        Route::get('products/all/{user_id}/{page?}/{status?}', [ProfileController::class, 'getProfileProducts']);
        Route::get('products/get/{product_id}', [ProfileController::class, 'getProduct'])->middleware('jwt.verify');
        Route::post('products/change-status', [ProfileController::class, 'changeProductStatus'])->middleware('jwt.verify');
        Route::post('products/add-favorite', [ProfileController::class, 'addFavoriteProduct'])->middleware('jwt.verify');
        Route::post('products/del-favorite', [ProfileController::class, 'delFavoriteProduct'])->middleware('jwt.verify');
        Route::get('favorites/all/{user_id}/{page?}', [ProfileController::class, 'getProfileFavorites']);
    });
