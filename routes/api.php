<?php

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

Route::group([
    'middleware' => ['api', 'auth:credentials'],
    'prefix'     => 'api/v1',
    'namespace'  => 'Karla\Http\Controllers',
], function () {
    Route::post('login', 'Auth\Api@login');
});

Route::group([
    'middleware' => ['api'],
    'namespace'  => 'Karla\Http\Controllers',
    'prefix'     => 'api/v1',
], function () {
    // Password Reset Routes...
    Route::post('password/reset', 'Auth\Api@reset');
    Route::get('password/resend/{id}', 'Auth\Api@resend');
    Route::post('password/verify/{id}', 'Auth\Api@verify');
});

Route::group([
    'middleware' => ['api', 'rest'],
    'namespace'  => 'Karla\Http\Controllers',
    'prefix'     => 'api/v1',
], function () {
    Route::post('password/change', 'Auth\Api@change');
});
