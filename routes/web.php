<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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
    'middleware' => ['web'],
    'namespace' => '\Diviky\Bright\Http\Controllers',
], function (): void {
    Route::get('signed/{disk}/{path}', function (string $disk, string $path) {
        return Storage::disk($disk)->download($path);
    })->middleware('signed')->name('signed.url');
});
