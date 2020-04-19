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

Route::prefix('auth')->group(function(){
    Route::get('/me', 'AuthController@me');
    Route::post('/login', 'AuthController@login');
    Route::post('/register', 'AuthController@register');
    Route::post('/reset-password', 'AuthController@resetPassword');
});

Route::middleware('auth:api')->group(function () {
    Route::resource('articles', 'ArticlesController')
        ->except(['create', 'edit']);

    Route::resource('carts', 'CartsController')
        ->only(['store']);

    Route::resource('payments', 'PaymentsController')
        ->only(['store']);
});
