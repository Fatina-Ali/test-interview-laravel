<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('index');
});

Route::fallback(function (){
    return redirect()->route('login');
});

Route::group(['as'  => 'delete_account.','prefix'   => 'delete_account'],function(){
    Route::get('deleted', function () {
        return view('backend.client.deleted');
    })->name('deleted.confirmation');
    Route::group(['middleware' => ['auth','client']], function () {
        Route::get('/', [\App\Http\Controllers\ClientController::class, 'deleteAccount']);
        Route::delete('delete', [\App\Http\Controllers\ClientController::class, 'destroy'])->name('delete');
    });
});


require_once __DIR__.'/auth.php';
require_once __DIR__.'/admin.php';
require_once __DIR__.'/profile.php';
require_once __DIR__.'/user.php';
require_once __DIR__.'/notifications.php';
require_once __DIR__.'/socialite.php';


