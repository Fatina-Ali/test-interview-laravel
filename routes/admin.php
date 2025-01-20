<?php

use App\Http\Controllers\User\AdminController;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'auth.role:admin'])
    ->prefix('admin')
    ->name('admin-')
    ->controller(AdminController::class)->group(function (){

    // vendors

    Route::post('activate_vendor', 'vendorActivate')->name('activate-vendor');
    Route::post('remove_vendor', 'userRemove')->name('vendor-remove');

    // fallback
    Route::fallback(function (){
        return redirect('/admin/dashboard');
    })->name('brand-fallback');
});


Route::middleware(['auth'])->group(function (){

    Route::resource('restrictions', \App\Http\Controllers\RestrictionController::class);

    Route::resource('prohibitions', \App\Http\Controllers\ProhibitionController::class);

    Route::resource('countries', \App\Http\Controllers\CountryController::class);

    Route::resource('shipment', \App\Http\Controllers\ShipmentController::class)->except(['create','store']);

    Route::resource('advertisements', \App\Http\Controllers\AdvertisementController::class);
});

