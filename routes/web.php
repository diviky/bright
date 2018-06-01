<?php

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

Route::group(['middleware' => ['web', 'guest']], function () {
    // Authentication Routes...
    Route::get('login', '\Karla\Http\Controllers\Auth\LoginController@showLoginForm')->name('login');
    Route::post('login', '\Karla\Http\Controllers\Auth\LoginController@login');

    // Registration Routes...
    Route::get('register', '\Karla\Http\Controllers\Auth\RegisterController@showRegistrationForm')->name('register');
    Route::post('register', '\Karla\Http\Controllers\Auth\RegisterController@register');

    // Password Reset Routes...
    Route::any('password/reset', '\Karla\Http\Controllers\Auth\ForgotPasswordController@reset')->name('password.reset');
    Route::any('password/verify', '\Karla\Http\Controllers\Auth\ForgotPasswordController@verify')->name('password.verify');
    Route::any('password/resend', '\Karla\Http\Controllers\Auth\ForgotPasswordController@resend')->name('password.resend');
    Route::any('password/change', '\Karla\Http\Controllers\Auth\ForgotPasswordController@change')->name('password.change');
});

Route::group(['middleware' => ['web', 'auth']], function () {
    Route::any('activate', '\Karla\Http\Controllers\Auth\ActivationController@activate')->name('user.activate');
    Route::any('resend', '\Karla\Http\Controllers\Auth\ActivationController@resend')->name('activate.resend');

    Route::group(['middleware' => ['auth.verified']], function () {
        Route::any('logout', '\Karla\Http\Controllers\Auth\LoginController@logout')->name('logout');

        Route::any('/', 'Users\Controller@index')->name('home');
    });
});
