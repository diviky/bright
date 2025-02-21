<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

return function (string $prefix = ''): void {
    Route::group([
        'namespace' => '\Diviky\Bright\Http\Controllers',
        'prefix' => $prefix . '/health',
    ], function (): void {
        Route::get('ping', 'Health\Controller@ping');
        Route::get('database', 'Health\Controller@livenessDatabase');
        Route::get('backend', 'Health\Controller@livenessBackend');
        Route::get('readiness', 'Health\Controller@readiness');
        Route::get('cache', 'Health\Controller@livenessCache');
    });
};
