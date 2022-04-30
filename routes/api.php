<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

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
    'middleware' => ['api'],
    'namespace' => '\Diviky\Bright\Http\Controllers',
    'prefix' => 'api/v1/auth',
], function (): void {
    Route::group([
        'middleware' => ['auth:credentials'],
    ], function (): void {
        Route::post('login', 'Auth\Api@login');
    });

    Route::group([
        'middleware' => ['rest'],
    ], function (): void {
        Route::post('password/change', 'Auth\Api@change');
        Route::post('account/token/refresh', 'Account\Controller@token');
    });
    // Password Reset Routes...
    Route::post('password/reset', 'Auth\Api@reset');
    Route::get('password/resend/{id}', 'Auth\Api@resend');
    Route::post('password/verify/{id}', 'Auth\Api@verify');
});
