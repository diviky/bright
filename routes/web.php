<?php

declare(strict_types=1);

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

return function (string $prefix = '', string $as = ''): void {
    Route::group([
        'middleware' => ['web', 'guest'],
        'namespace' => '\Diviky\Bright\Http\Controllers',
        'prefix' => $prefix,
        'as' => $as
    ], function (): void {
        Route::get('account/sniff/{id}', 'Account\Controller@sniff');
        // Authentication Routes...
        Route::get('login', 'Auth\LoginController@showLoginForm');
        Route::post('login', 'Auth\LoginController@login');

        // Registration Routes...
        Route::get('register', 'Auth\RegisterController@showRegistrationForm');
        Route::post('register', 'Auth\RegisterController@register');

        Route::group(['middleware' => ['throttle:3,5']], function (): void {
            Route::post('password/resend', 'Auth\ForgotPasswordController@resend');
            Route::post('password/reset', 'Auth\ForgotPasswordController@reset');
        });

        // Password Reset Routes...
        Route::get('password/reset', 'Auth\ForgotPasswordController@reset');
        Route::get('password/verify', 'Auth\ForgotPasswordController@verify');
        Route::post('password/verify', 'Auth\ForgotPasswordController@verify');
        Route::get('password/change', 'Auth\ForgotPasswordController@change');
        Route::post('password/change', 'Auth\ForgotPasswordController@change');

        Route::get('social/connect/{provider}', 'Socialite\Controller@connect');
        Route::get('social/connect/{provider}/callback', 'Socialite\Controller@callback');
    });

    Route::group([
        'middleware' => ['web', 'auth'],
        'namespace' => '\Diviky\Bright\Http\Controllers',
        'prefix' => $prefix,
        'as' => $as
    ], function (): void {
        Route::get('activate', 'Auth\ActivationController@activate')->name('user.activate');
        Route::any('logout', 'Auth\LoginController@logout');

        Route::group(['middleware' => ['throttle:3,5']], function (): void {
            Route::post('resend', 'Auth\ActivationController@resend');
            Route::post('activate', 'Auth\ActivationController@activate');
        });

        Route::any('account', 'Account\Controller@index');
        Route::any('account/password', 'Account\Controller@password');
        Route::get('account/search', 'Account\Controller@search');
        Route::post('account/token/refresh', 'Account\Controller@token');
    });
};
