<?php

declare(strict_types=1);

namespace Diviky\Bright\Tests\Database;

use Diviky\Bright\Database\Batch;
use Diviky\Bright\Database\Eloquent\Concerns\Batch as BatchConcern;
use Diviky\Bright\Database\Eloquent\Model;
use Diviky\Bright\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(TestCase::class, RefreshDatabase::class);

class BulkLoadTestOrder extends Model
{
    use BatchConcern;

    protected $table = 'test_orders';

    protected $fillable = [
        'customer_name',
        'product_name',
        'amount',
        'status',
        'priority',
        'notes',
        'shipped_at',
    ];
}

beforeEach(function (): void {
    Schema::create('test_orders', function ($table): void {
        $table->id();
        $table->string('customer_name');
        $table->string('product_name');
        $table->decimal('amount', 10, 2);
        $table->string('status')->default('pending');
        $table->string('priority')->default('normal');
        $table->text('notes')->nullable();
        $table->timestamp('shipped_at')->nullable();
        $table->timestamps();
    });
});

it('uses chunked inserts when bulk load is disabled', function (): void {
    Config::set('bright.bulk_load', false);
    DB::enableQueryLog();

    $batch = BulkLoadTestOrder::query()->batch(true);

    $batch->add([
        'customer_name' => 'ProxySQL Customer',
        'product_name' => 'Fallback Insert',
        'amount' => 42.00,
        'status' => 'pending',
    ]);

    expect($batch->commit())->toBeTrue()
        ->and(BulkLoadTestOrder::where('customer_name', 'ProxySQL Customer')->exists())->toBeTrue();

    expect(collect(DB::getQueryLog())->contains(
        fn (array $query): bool => str_starts_with(strtolower(trim($query['query'])), 'insert')
    ))->toBeTrue();

    DB::disableQueryLog();
});

it('does not run load data local infile when bulk load is disabled', function (): void {
    Config::set('bright.bulk_load', false);

    DB::enableQueryLog();

    $batch = new Batch(BulkLoadTestOrder::query());
    $batch->bulk(true);
    $batch->add([
        'customer_name' => 'No Bulk Load',
        'product_name' => 'Chunked',
        'amount' => 10.00,
        'status' => 'pending',
    ]);

    expect($batch->commit())->toBeTrue();

    $queries = collect(DB::getQueryLog())->pluck('query');

    expect($queries->contains(
        fn (string $query): bool => str_contains(strtolower($query), 'load data local infile')
    ))->toBeFalse();

    DB::disableQueryLog();
});
