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
        'as' => $as,
    ], function (): void {
        // Authentication Routes...
        Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
        Route::post('login', 'Auth\LoginController@login')->name('login.post');

        // Registration Routes...
        Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
        Route::post('register', 'Auth\RegisterController@register')->name('register.post');

        Route::group(['prefix' => 'password', 'as' => 'password.'], function (): void {
            Route::group(['middleware' => ['throttle:3,5']], function (): void {
                Route::post('resend', 'Auth\ForgotPasswordController@resend')->name('resend');
                Route::post('reset', 'Auth\ForgotPasswordController@reset')->name('reset.post');
            });

            // Password Reset Routes...
            Route::get('reset', 'Auth\ForgotPasswordController@reset')->name('reset');
            Route::get('verify', 'Auth\ForgotPasswordController@verify')->name('verify');
            Route::post('verify', 'Auth\ForgotPasswordController@verify')->name('verify.post');
            Route::get('change', 'Auth\ForgotPasswordController@change')->name('change');
            Route::post('change', 'Auth\ForgotPasswordController@change')->name('change.post');
        });

        Route::group(['prefix' => 'social/connect', 'as' => 'social.'], function (): void {
            Route::get('{provider}', 'Socialite\Controller@connect')->name('connect');
            Route::get('{provider}/callback', 'Socialite\Controller@callback')->name('callback');
        });
    });

    Route::group([
        'middleware' => ['web', 'auth'],
        'namespace' => '\Diviky\Bright\Http\Controllers',
        'prefix' => $prefix,
        'as' => $as,
    ], function (): void {
        Route::match(['get', 'post'], 'account/sniff/{id?}', 'Account\Controller@sniff')->name('sniff');

        Route::get('activate', 'Auth\ActivationController@activate')->name('activate');
        Route::any('logout', 'Auth\LoginController@logout')->name('logout');

        Route::group(['middleware' => ['throttle:3,5'], 'as' => 'activation.'], function (): void {
            Route::post('resend', 'Auth\ActivationController@resend')->name('resend');
            Route::post('activate', 'Auth\ActivationController@activate')->name('activate');
        });

        Route::group(['prefix' => 'account', 'as' => 'account.'], function (): void {
            Route::any('/', 'Account\Controller@index')->name('index');
            Route::any('password', 'Account\Controller@password')->name('password');
            Route::get('search', 'Account\Controller@search')->name('search');
            Route::post('token/refresh', 'Account\Controller@token')->name('refresh');
        });
    });
};
