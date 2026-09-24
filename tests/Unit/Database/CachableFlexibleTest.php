<?php

declare(strict_types=1);

use Diviky\Bright\Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(TestCase::class);

beforeEach(function (): void {
    config(['database.connections.sqlite.bright.db_cache' => true]);

    Schema::dropIfExists('flexible_cache_probe');
    Schema::create('flexible_cache_probe', function ($table): void {
        $table->id();
        $table->string('label');
    });

    DB::table('flexible_cache_probe')->insert(['label' => 'alpha']);

    Cache::flush();
});

afterEach(function (): void {
    Schema::dropIfExists('flexible_cache_probe');
});

it('uses default flexible ttl when remember flexible receives null or empty ttl', function (): void {
    $defaults = DB::table('flexible_cache_probe')->rememberFlexible(null, 'flex-default-key');
    $empty = DB::table('flexible_cache_probe')->rememberFlexible([], 'flex-empty-key');

    expect($defaults->getCacheFlexibleTtl())->toBe([600, 1200])
        ->and($empty->getCacheFlexibleTtl())->toBe([600, 1200]);
});

it('derives stale ttl from fresh when only the fresh window is provided', function (): void {
    $builder = DB::table('flexible_cache_probe')->rememberFlexible([180], 'flex-partial-key');

    expect($builder->getCacheFlexibleTtl())->toBe([180, 360]);
});

it('stores flexible ttl and key via remember flexible', function (): void {
    $builder = DB::table('flexible_cache_probe')
        ->rememberFlexible([120, 300], 'flex-key');

    expect($builder->getCacheFlexibleTtl())->toBe([120, 300])
        ->and($builder->getCacheKeyName())->toBe('flex-key')
        ->and($builder->getCacheTime())->toBeNull();
});

it('enables memo with remember flexible memo', function (): void {
    $builder = DB::table('flexible_cache_probe')
        ->rememberFlexibleMemo([60, 120], 'flex-memo-key');

    expect($builder->usesCacheMemo())->toBeTrue()
        ->and($builder->getCacheFlexibleTtl())->toBe([60, 120]);
});

it('clears flexible settings when dont remember is called', function (): void {
    $builder = DB::table('flexible_cache_probe')
        ->rememberFlexible([10, 20], 'flex-key')
        ->dontRemember();

    expect($builder->getCacheFlexibleTtl())->toBeNull();
});

it('resolves cached rows through the flexible cache driver when available', function (): void {
    if (! method_exists(Cache::store('array'), 'flexible')) {
        $this->markTestSkipped('Cache::flexible() requires Laravel 13 or newer.');
    }

    $rows = DB::table('flexible_cache_probe')
        ->rememberFlexible([300, 600], 'flexible-probe')
        ->get();

    expect($rows)->toHaveCount(1)
        ->and($rows->first()->label)->toBe('alpha');
});
