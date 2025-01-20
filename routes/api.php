<?php

use App\Http\Controllers\Api\AuthController;
//use App\Http\Controllers\Api\CategoryController;
// use App\Http\Controllers\Api\ShipmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Api\AddressController as AddressController;
// use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\AddressController as AddressController;
use App\Http\Controllers\Api\ClientController;

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


Route::post('login', [AuthController::class, 'login'])->name("api.login");
Route::post('register', [AuthController::class, 'register'])->name("api.register");

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});




Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name("api.logout");
    Route::post('changePassword', [AuthController::class, 'changePassword'])->name("api.password.change");
    Route::get('filer/{var}',[ClientController::class, 'filer']);
    //client
    Route::group([  'prefix' => 'client','middleware' => 'client'], function () {

        Route::get('/',  [\App\Http\Controllers\Api\HomeController::class, 'index']);
        Route::group(['as'  => 'addresses.' , 'prefix'  => 'addresses'],function(){
            Route::post('share_address',[AddressController::class,'moveAddress']);
        });

        Route::apiResource('addresses', AddressController::class);
        Route::get('categories',  [\App\Http\Controllers\Api\CategoryController::class, 'index']);
        Route::get('subcategories',  [\App\Http\Controllers\Api\SubcategoryController::class, 'index']);
        //Route::apiResource('services', \App\Http\Controllers\Api\ServiceController::class);
        Route::group(['as'  => 'shipments.','prefix'    => 'shipments'],function(){
            Route::post('qr_code',[ \App\Http\Controllers\Api\ShipmentController::class,'qrCode']);
        });
        Route::apiResource('shipments', \App\Http\Controllers\Api\ShipmentController::class);
        Route::apiResource('shipments-images', \App\Http\Controllers\Api\ShipmentImageController::class)->except(['update','edit']);
        Route::apiResource('clients', ClientController::class);
        Route::apiResource('prohibitions', \App\Http\Controllers\Api\ProhibitionController::class);
        Route::apiResource('restrictions', \App\Http\Controllers\Api\RestrictionController::class);

    });
    Route::apiResource('notifications', \App\Http\Controllers\Api\NotificationController::class);

});

