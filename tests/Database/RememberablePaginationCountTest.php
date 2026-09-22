<?php

declare(strict_types=1);

use Diviky\Bright\Database\Eloquent\Builder as BrightEloquentBuilder;
use Diviky\Bright\Database\Eloquent\Model;
use Diviky\Bright\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(TestCase::class, RefreshDatabase::class);

class PaginationCountTestModel extends Model
{
    protected $table = 'pagination_count_test';

    protected $guarded = [];
}

/**
 * @return int Number of COUNT(*) queries executed inside the callback.
 */
function countPaginationCountQueries(callable $callback): int
{
    $count = 0;

    DB::listen(function ($query) use (&$count): void {
        if (preg_match('/^\s*select\s+count\(/i', $query->sql)) {
            $count++;
        }
    });

    $callback();

    return $count;
}

beforeEach(function (): void {
    BrightEloquentBuilder::automaticallyRememberCount(false);

    Schema::create('pagination_count_test', function ($table): void {
        $table->id();
        $table->string('status');
        $table->timestamps();
    });

    PaginationCountTestModel::create(['status' => 'open']);
    PaginationCountTestModel::create(['status' => 'open']);
    PaginationCountTestModel::create(['status' => 'closed']);

    Cache::flush();
});

afterEach(function (): void {
    Schema::dropIfExists('pagination_count_test');
    BrightEloquentBuilder::automaticallyRememberCount(false);
});

it('caches pagination count when automaticallyRememberCount is enabled', function (): void {
    BrightEloquentBuilder::automaticallyRememberCount(true, 60);

    $counts = countPaginationCountQueries(function (): void {
        PaginationCountTestModel::query()->where('status', 'open')->paginate(1);
        PaginationCountTestModel::query()->where('status', 'open')->paginate(1);
    });

    expect($counts)->toBe(1);
});

it('runs count on every paginate when automaticallyRememberCount is disabled', function (): void {
    $counts = countPaginationCountQueries(function (): void {
        PaginationCountTestModel::query()->where('status', 'open')->paginate(1);
        PaginationCountTestModel::query()->where('status', 'open')->paginate(1);
    });

    expect($counts)->toBe(2);
});

it('skips count cache when dontRememberCount is used', function (): void {
    BrightEloquentBuilder::automaticallyRememberCount(true, 60);

    $counts = countPaginationCountQueries(function (): void {
        PaginationCountTestModel::query()->where('status', 'open')->dontRememberCount()->paginate(1);
        PaginationCountTestModel::query()->where('status', 'open')->dontRememberCount()->paginate(1);
    });

    expect($counts)->toBe(2);
});

it('caches pagination count for an explicit rememberCount chain when global is off', function (): void {
    $counts = countPaginationCountQueries(function (): void {
        PaginationCountTestModel::query()->where('status', 'open')->rememberCount(60)->paginate(1);
        PaginationCountTestModel::query()->where('status', 'open')->rememberCount(60)->paginate(1);
    });

    expect($counts)->toBe(1);
});

it('does not use count cache when an explicit total is passed to paginate', function (): void {
    BrightEloquentBuilder::automaticallyRememberCount(true, 60);

    $counts = countPaginationCountQueries(function (): void {
        PaginationCountTestModel::query()->where('status', 'open')->paginate(1, ['*'], 'page', 1, 2);
        PaginationCountTestModel::query()->where('status', 'open')->paginate(1, ['*'], 'page', 1, 2);
    });

    expect($counts)->toBe(0);
});

it('recomputes count when filters change even with the same named rememberCount key', function (): void {
    BrightEloquentBuilder::automaticallyRememberCount(true, 60);

    $openTotal = PaginationCountTestModel::query()
        ->where('status', 'open')
        ->rememberCount(null, 'test.messages.index')
        ->paginate(10)
        ->total();

    $closedTotal = PaginationCountTestModel::query()
        ->where('status', 'closed')
        ->rememberCount(null, 'test.messages.index')
        ->paginate(10)
        ->total();

    expect($openTotal)->toBe(2)
        ->and($closedTotal)->toBe(1);
});
