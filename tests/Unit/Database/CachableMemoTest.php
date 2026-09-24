<?php

declare(strict_types=1);

use Diviky\Bright\Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(TestCase::class);

beforeEach(function (): void {
    config(['database.connections.sqlite.bright.db_cache' => true]);

    Schema::dropIfExists('memo_cache_probe');
    Schema::create('memo_cache_probe', function ($table): void {
        $table->id();
        $table->string('label');
    });

    DB::table('memo_cache_probe')->insert(['label' => 'alpha']);

    Cache::flush();
});

afterEach(function (): void {
    Schema::dropIfExists('memo_cache_probe');
});

it('enables memo and remember settings via remember memo', function (): void {
    $builder = DB::table('memo_cache_probe')->rememberMemo(120, 'memo-key');

    expect($builder->usesCacheMemo())->toBeTrue()
        ->and($builder->getCacheTime())->toBe(120)
        ->and($builder->getCacheKeyName())->toBe('memo-key');
});

it('exposes cache memo state on the query builder', function (): void {
    $builder = DB::table('memo_cache_probe')->cacheMemo();

    expect($builder->usesCacheMemo())->toBeTrue();

    $builder->cacheMemo(false);

    expect($builder->usesCacheMemo())->toBeFalse();
});

it('resolves the memoized cache repository when cache memo is enabled', function (): void {
    if (! method_exists(Cache::getFacadeRoot(), 'memo')) {
        $this->markTestSkipped('Cache::memo() requires Laravel 13 or newer.');
    }

    $builder = DB::table('memo_cache_probe')->cacheMemo();

    $method = new ReflectionMethod($builder, 'getCacheDriver');
    $method->setAccessible(true);

    expect($method->invoke($builder))->toBe(Cache::memo());
});

it('disables cache memo when dont remember is called', function (): void {
    $builder = DB::table('memo_cache_probe')
        ->cacheMemo()
        ->dontRemember();

    expect($builder->usesCacheMemo())->toBeFalse();
});
