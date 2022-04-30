<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('health/ping', '\Diviky\Bright\Http\Controllers\Health\Controller@ping');
Route::get('health/database', '\Diviky\Bright\Http\Controllers\Health\Controller@livenessDatabase');
Route::get('health/backend', '\Diviky\Bright\Http\Controllers\Health\Controller@livenessBackend');
Route::get('health/readiness', '\Diviky\Bright\Http\Controllers\Health\Controller@readiness');
Route::get('health/cache', '\Diviky\Bright\Http\Controllers\Health\Controller@livenessCache');
