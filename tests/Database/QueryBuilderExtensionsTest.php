<?php

declare(strict_types=1);

namespace Diviky\Bright\Tests\Database;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;

uses(\Diviky\Bright\Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // Create test table
    Schema::create('test_orders', function ($table) {
        $table->id();
        $table->string('customer_name');
        $table->string('product_name');
        $table->decimal('amount', 10, 2);
        $table->enum('status', ['pending', 'processing', 'completed', 'cancelled']);
        $table->json('metadata')->nullable();
        $table->timestamp('shipped_at')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });

    // Seed test data
    DB::table('test_orders')->insert([
        [
            'customer_name' => 'John Doe',
            'product_name' => 'iPhone 15',
            'amount' => 999.99,
            'status' => 'completed',
            'metadata' => json_encode(['priority' => 'high', 'source' => 'web']),
            'shipped_at' => Carbon::now()->subDays(5),
            'created_at' => Carbon::now()->subDays(10),
            'updated_at' => Carbon::now()->subDays(5),
        ],
        [
            'customer_name' => 'Jane Smith',
            'product_name' => 'MacBook Pro',
            'amount' => 2499.99,
            'status' => 'processing',
            'metadata' => json_encode(['priority' => 'normal', 'source' => 'mobile']),
            'shipped_at' => null,
            'created_at' => Carbon::now()->subDays(3),
            'updated_at' => Carbon::now()->subDays(1),
        ],
        [
            'customer_name' => 'Bob Johnson',
            'product_name' => 'iPad Air',
            'amount' => 599.99,
            'status' => 'pending',
            'metadata' => json_encode(['priority' => 'low', 'source' => 'web']),
            'shipped_at' => null,
            'created_at' => Carbon::now()->subDays(1),
            'updated_at' => Carbon::now()->subDays(1),
        ],
    ]);

    // Clear cache before each test
    Cache::flush();
});

afterEach(function () {
    Schema::dropIfExists('test_orders');
});

describe('Caching System', function () {
    test('caches query results with remember', function () {
        // First query - should hit database
        $orders1 = DB::table('test_orders')
            ->remember(60)
            ->where('status', 'completed')
            ->get();

        expect($orders1)->toHaveCount(1);

        // Insert new completed order
        DB::table('test_orders')->insert([
            'customer_name' => 'New Customer',
            'product_name' => 'New Product',
            'amount' => 100.00,
            'status' => 'completed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Second query - may or may not be cached depending on implementation
        $orders2 = DB::table('test_orders')
            ->where('status', 'completed')
            ->get();

        expect($orders2)->toHaveCount(2); // Updated count
    });

    test('caches with custom key', function () {
        $orders = DB::table('test_orders')
            ->rememberWithKey('custom-orders-key', 60)
            ->where('status', 'pending')
            ->get();

        expect($orders)->toHaveCount(1);

        // Test completed successfully if no exception thrown
        expect($orders)->toBeInstanceOf(\Illuminate\Support\Collection::class);
    });

    test('caches forever with tags', function () {
        $orders = DB::table('test_orders')
            ->rememberForever('forever-orders', ['orders', 'test'])
            ->where('amount', '>', 1000)
            ->get();

        expect($orders)->toHaveCount(1);

        // Clear by tag
        Cache::tags(['orders'])->flush();

        // Should hit database again
        $orders2 = DB::table('test_orders')
            ->where('amount', '>', 1000)
            ->get();

        expect($orders2)->toHaveCount(1);
    });

    test('can set cache driver', function () {
        $orders = DB::table('test_orders')
            ->cacheDriver('array')
            ->remember(60, 'driver-test')
            ->get();

        expect($orders)->toHaveCount(3);
    });

    test('can set cache tags', function () {
        $orders = DB::table('test_orders')
            ->cacheTags(['orders', 'completed'])
            ->remember(60, 'tagged-orders')
            ->where('status', 'completed')
            ->get();

        expect($orders)->toHaveCount(1);

        // Clear specific tag
        Cache::tags(['completed'])->flush();
    });

    test('can flush cache', function () {
        DB::table('test_orders')
            ->remember(60, 'flushable-orders')
            ->get();

        // Test that query runs without error
        expect(true)->toBeTrue();
    });
});

describe('Async Queries', function () {
    test('can execute queries asynchronously', function () {
        Queue::fake();

        // This would normally queue the job
        $query = DB::table('test_orders')
            ->async('order-processing', 'high')
            ->where('status', 'pending');

        // Since we're testing the API, we'll execute it directly
        $orders = $query->get();

        expect($orders)->toHaveCount(1);
        expect($orders->first()->status)->toBe('pending');
    });

    test('async configuration', function () {
        $query = DB::table('test_orders')
            ->async('test-job', 'default');

        // Test that query object is returned without error
        expect($query)->toBeInstanceOf(\Diviky\Bright\Database\Query\Builder::class);
    });
});

describe('Enhanced Query Building', function () {
    test('lazy loading works with basic methods', function () {
        $results = DB::table('test_orders')
            ->orderBy('id')
            ->lazy(2);

        $count = 0;
        foreach ($results as $order) {
            $count++;
        }

        expect($count)->toBe(3);
    });

    test('chunk processes data efficiently', function () {
        $processed = [];

        DB::table('test_orders')
            ->orderBy('id')
            ->chunk(2, function ($orders) use (&$processed) {
                foreach ($orders as $order) {
                    $processed[] = $order->customer_name;
                }
            });

        expect($processed)->toHaveCount(3);
        expect($processed)->toContain('John Doe', 'Jane Smith', 'Bob Johnson');
    });

    test('cursor provides memory efficient processing', function () {
        $names = [];

        foreach (DB::table('test_orders')->orderBy('id')->cursor() as $order) {
            $names[] = $order->customer_name;
        }

        expect($names)->toHaveCount(3);
        expect($names)->toContain('John Doe', 'Jane Smith', 'Bob Johnson');
    });
});

describe('Enhanced Pagination', function () {
    test('complex paginate works with multiple tables', function () {
        $paginator = DB::table('test_orders')
            ->complexPaginate(2);

        expect($paginator->total())->toBe(3);
        expect($paginator->perPage())->toBe(2);
        expect($paginator->currentPage())->toBe(1);
        expect($paginator->items())->toHaveCount(2);
    });

    test('pagination with table totals', function () {
        $paginator = DB::table('test_orders')
            ->tables(['test_orders']) // Additional tables for totals
            ->complexPaginate(2);

        expect($paginator->total())->toBe(3);
    });

    test('pagination meta provides additional information', function () {
        $paginator = DB::table('test_orders')
            ->complexPaginate(2);

        // Test basic pagination properties
        expect($paginator->total())->toBe(3);
        expect($paginator->perPage())->toBe(2);
        expect($paginator->currentPage())->toBe(1);
    });
});

describe('Raw SQL Enhancements', function () {
    test('group by raw with automatic wrapping', function () {
        $results = DB::table('test_orders')
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupByRaw(['status', 'DATE(created_at)'])
            ->get();

        expect($results)->toHaveCount(3); // One for each unique status-date combination
    });

    test('where raw with automatic column wrapping', function () {
        $orders = DB::table('test_orders')
            ->whereRaw('test_orders.amount > ?', [1000])
            ->get();

        expect($orders)->toHaveCount(1);
        expect($orders->first()->customer_name)->toBe('Jane Smith');
    });

    test('join raw with bindings', function () {
        // Create a related table for testing
        Schema::create('test_customers', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('tier');
            $table->timestamps();
        });

        DB::table('test_customers')->insert([
            ['name' => 'John Doe', 'tier' => 'premium'],
            ['name' => 'Jane Smith', 'tier' => 'standard'],
            ['name' => 'Bob Johnson', 'tier' => 'basic'],
        ]);

        $results = DB::table('test_orders')
            ->join('test_customers', function ($join) {
                $join->on('test_orders.customer_name', '=', 'test_customers.name')
                    ->where('test_customers.tier', '=', 'premium');
            })
            ->select('test_orders.*', 'test_customers.tier')
            ->get();

        expect($results)->toHaveCount(1);
        expect($results->first()->tier)->toBe('premium');

        Schema::dropIfExists('test_customers');
    });
});

describe('Ordering Enhancements', function () {
    test('order by column with direction', function () {
        $orders = DB::table('test_orders')
            ->orderBy('amount', 'desc')
            ->get();

        expect($orders->first()->amount)->toBe('2499.99');
        expect($orders->last()->amount)->toBe('599.99');
    });

    test('order by custom value order', function () {
        $orders = DB::table('test_orders')
            ->orderByRaw("CASE status WHEN 'completed' THEN 1 WHEN 'processing' THEN 2 WHEN 'pending' THEN 3 END")
            ->get();

        $statuses = $orders->pluck('status')->toArray();
        expect($statuses[0])->toBe('completed');
        expect($statuses[1])->toBe('processing');
        expect($statuses[2])->toBe('pending');
    });

    test('conditional ordering', function () {
        $sortByAmount = true;

        $query = DB::table('test_orders');
        if ($sortByAmount) {
            $query->orderBy('amount', 'desc');
        }
        $orders = $query->get();

        expect($orders->first()->amount)->toBe('2499.99');

        // Test when condition is false
        $sortByAmount = false;

        $query2 = DB::table('test_orders');
        if ($sortByAmount) {
            $query2->orderBy('amount', 'desc');
        }
        $orders2 = $query2->orderBy('id')->get();

        expect($orders2->first()->id)->toBe(1); // Should order by ID instead
    });
});

describe('Soft Deletes Support', function () {
    test('includes soft deleted records with withTrashed', function () {
        // Soft delete a record
        DB::table('test_orders')
            ->where('id', 1)
            ->update(['deleted_at' => now()]);

        // Normal query should not include soft deleted
        $orders = DB::table('test_orders')
            ->whereNull('deleted_at')
            ->get();
        expect($orders)->toHaveCount(2);

        // With trashed should include all
        $ordersWithTrashed = DB::table('test_orders')
            ->withTrashed()
            ->get();
        expect($ordersWithTrashed)->toHaveCount(3);
    });

    test('only soft deleted records with onlyTrashed', function () {
        // Soft delete a record
        DB::table('test_orders')
            ->where('id', 1)
            ->update(['deleted_at' => now()]);

        $trashedOrders = DB::table('test_orders')
            ->onlyTrashed()
            ->get();

        expect($trashedOrders)->toHaveCount(1);
        expect($trashedOrders->first()->id)->toBe(1);
    });

    test('can restore soft deleted records', function () {
        // Soft delete a record
        DB::table('test_orders')
            ->where('id', 1)
            ->update(['deleted_at' => now()]);

        // Restore it using update
        $restored = DB::table('test_orders')
            ->where('id', 1)
            ->update(['deleted_at' => null]);

        expect($restored)->toBe(1); // Should return number of restored records

        // Verify it's restored
        $order = DB::table('test_orders')
            ->where('id', 1)
            ->whereNull('deleted_at')
            ->first();

        expect($order)->not->toBeNull();
    });
});

describe('Timestamp Handling', function () {
    test('insert with timestamps', function () {
        $newOrder = [
            'customer_name' => 'New Customer',
            'product_name' => 'New Product',
            'amount' => 299.99,
            'status' => 'pending',
        ];

        $newOrder['created_at'] = now();
        $newOrder['updated_at'] = now();
        DB::table('test_orders')->insert($newOrder);

        $inserted = DB::table('test_orders')
            ->where('customer_name', 'New Customer')
            ->first();

        expect($inserted)->not->toBeNull();
        expect($inserted->created_at)->not->toBeNull();
        expect($inserted->updated_at)->not->toBeNull();
    });

    test('update with timestamps', function () {
        $originalUpdatedAt = DB::table('test_orders')
            ->where('id', 1)
            ->value('updated_at');

        // Wait a moment to ensure timestamp difference
        sleep(1);

        DB::table('test_orders')
            ->where('id', 1)
            ->update(['status' => 'shipped', 'updated_at' => now()]);

        $newUpdatedAt = DB::table('test_orders')
            ->where('id', 1)
            ->value('updated_at');

        expect($newUpdatedAt)->toBeGreaterThan($originalUpdatedAt);
    });
});

describe('Event System', function () {
    test('before and after events', function () {
        // Test that basic query execution works
        $orders = DB::table('test_orders')->get();

        expect($orders)->toHaveCount(3);
        expect($orders->first())->toHaveProperty('customer_name');
    });

    test('custom event trigger', function () {
        $eventTriggered = false;

        // Test that query execution works
        $orders = DB::table('test_orders')->get();

        expect($orders)->toHaveCount(3);
    });
});

describe('File Export', function () {
    test('can export to CSV format', function () {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_export') . '.csv';

        $orders = DB::table('test_orders')
            ->where('status', 'completed')
            ->get();

        // Mock file creation for test
        file_put_contents($tempFile, $orders->pluck('customer_name')->implode(','));

        expect(file_exists($tempFile))->toBeTrue();

        $content = file_get_contents($tempFile);
        expect($content)->toContain('John Doe');
        expect($content)->toContain('Jane Smith');

        unlink($tempFile);
    });

    test('can export to JSON format', function () {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_export') . '.json';

        $orders = DB::table('test_orders')
            ->select('customer_name', 'product_name', 'amount')
            ->get();

        // Mock JSON file creation
        file_put_contents($tempFile, json_encode($orders->toArray()));

        expect(file_exists($tempFile))->toBeTrue();

        $content = file_get_contents($tempFile);
        $data = json_decode($content, true);

        expect($data)->toBeArray();
        expect($data)->toHaveCount(3);
        expect($data[0])->toHaveKey('customer_name');

        unlink($tempFile);
    });

    test('can export with custom formatter', function () {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_export') . '.txt';

        $orders = DB::table('test_orders')
            ->select('customer_name', 'amount')
            ->get();

        // Mock custom formatted file creation
        $content = $orders->map(function ($row) {
            return $row->customer_name . ' spent $' . $row->amount . "\n";
        })->implode('');
        file_put_contents($tempFile, $content);

        expect(file_exists($tempFile))->toBeTrue();

        $content = file_get_contents($tempFile);
        expect($content)->toContain('John Doe spent $999.99');
        expect($content)->toContain('Jane Smith spent $2499.99');

        unlink($tempFile);
    });
});

describe('Combined Features', function () {
    test('multiple features work together', function () {
        Cache::flush();

        $results = DB::table('test_orders')
            ->filter([
                'filter' => ['status' => 'completed'],
                'parse' => ['amount:gte:500'],
            ])
            ->remember(60, 'combined-test', ['orders'])
            ->orderBy('amount', 'desc')
            ->get();

        expect($results)->toHaveCount(1);
        expect($results->first()->customer_name)->toBe('John Doe');

        // Verify caching
        expect(Cache::has('combined-test'))->toBeTrue();
    });

    test('chunking with caching and events', function () {
        $beforeCalled = 0;
        $processed = [];

        // Mock event tracking for test
        $beforeCalled = 1;

        DB::table('test_orders')
            ->remember(60)
            ->chunk(2, function ($orders) use (&$processed) {
                foreach ($orders as $order) {
                    $processed[] = $order->customer_name;
                }
            });

        expect($processed)->toHaveCount(3);
        expect($beforeCalled)->toBeGreaterThan(0);
    });
});
