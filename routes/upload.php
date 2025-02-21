<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

return function (string $prefix = '', string $as = ''): void {
    Route::group([
        'namespace' => '\Diviky\Bright\Http\Controllers',
        'prefix' => $prefix,
        'as' => $as,
    ], function (): void {
        Route::post('upload/signed', 'Upload\Controller@signed')->name('upload.signed');
        Route::match(['post', 'put'], 'upload/files', 'Upload\Controller@upload')->name('upload.files');
        Route::match(['post', 'put'], 'store/files', 'Upload\Controller@store')->name('store.files');
        Route::delete('upload/revert', 'Upload\Controller@revert')->name('upload.revert');
    });
};
