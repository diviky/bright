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

Route::group([
    'middleware' => ['web', 'guest', 'firewall'],
    'namespace'  => 'Karla\Http\Controllers',
], function () {

    Route::get('account/sniff/{id}', 'Account\Controller@sniff');
    // Authentication Routes...
    Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
    Route::post('login', 'Auth\LoginController@login');

    // Registration Routes...
    Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
    Route::post('register', 'Auth\RegisterController@register');

    // Password Reset Routes...
    Route::any('password/reset', 'Auth\ForgotPasswordController@reset')->name('password.reset');
    Route::any('password/verify', 'Auth\ForgotPasswordController@verify')->name('password.verify');
    Route::any('password/resend', 'Auth\ForgotPasswordController@resend')->name('password.resend');
    Route::any('password/change', 'Auth\ForgotPasswordController@change')->name('password.change');
});

Route::group([
    'middleware' => ['web', 'auth'],
    'namespace'  => 'Karla\Http\Controllers',
], function () {
    Route::any('activate', 'Auth\ActivationController@activate')->name('user.activate');
    Route::any('resend', 'Auth\ActivationController@resend')->name('activate.resend');
    Route::any('logout', 'Auth\LoginController@logout')->name('logout');

    Route::any('account', 'Account\Controller@index');
    Route::any('account/password', 'Account\Controller@password');
    Route::get('account/search', 'Account\Controller@search');
});
