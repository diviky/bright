<?php

use Carbon\Carbon;
use Diviky\Bright\Database\Eloquent\Concerns\Async;
use Diviky\Bright\Database\Eloquent\Concerns\Batch;
use Diviky\Bright\Database\Eloquent\Concerns\BuildsQueries;
use Diviky\Bright\Database\Eloquent\Concerns\Eventable;
use Diviky\Bright\Database\Eloquent\Concerns\Filters;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

// Test Model with Eloquent Builder Extensions
class TestOrder extends Model
{
    use Async, Batch, BuildsQueries, Eventable, Filters;

    protected $table = 'test_orders';
    protected $fillable = [
        'customer_name', 'product_name', 'amount', 'status', 
        'priority', 'notes', 'shipped_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'shipped_at' => 'datetime',
    ];

    // Event handlers
    protected $events = [
        'creating' => 'handleCreating',
        'created' => 'handleCreated',
    ];

    public function handleCreating($model)
    {
        if (!$model->priority) {
            $model->priority = 'normal';
        }
    }

    public function handleCreated($model)
    {
        event('order.created', $model);
    }

    public function customer()
    {
        return $this->belongsTo(TestCustomer::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(TestOrderItem::class, 'order_id');
    }

    // Query scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeHighValue($query)
    {
        return $query->where('amount', '>', 1000);
    }
}

class TestCustomer extends Model
{
    protected $table = 'test_customers';
    protected $fillable = ['name', 'email', 'tier'];

    public function orders()
    {
        return $this->hasMany(TestOrder::class, 'customer_id');
    }
}

class TestOrderItem extends Model
{
    protected $table = 'test_order_items';
    protected $fillable = ['order_id', 'product_name', 'quantity', 'price'];

    public function order()
    {
        return $this->belongsTo(TestOrder::class, 'order_id');
    }
}

beforeEach(function () {
    // Create test tables
    \Schema::create('test_customers', function ($table) {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->enum('tier', ['basic', 'premium', 'enterprise']);
        $table->timestamps();
    });

    \Schema::create('test_orders', function ($table) {
        $table->id();
        $table->unsignedBigInteger('customer_id')->nullable();
        $table->string('customer_name');
        $table->string('product_name');
        $table->decimal('amount', 10, 2);
        $table->enum('status', ['pending', 'processing', 'completed', 'cancelled']);
        $table->enum('priority', ['low', 'normal', 'high'])->default('normal');
        $table->text('notes')->nullable();
        $table->timestamp('shipped_at')->nullable();
        $table->timestamps();
        
        $table->foreign('customer_id')->references('id')->on('test_customers');
    });

    \Schema::create('test_order_items', function ($table) {
        $table->id();
        $table->unsignedBigInteger('order_id');
        $table->string('product_name');
        $table->integer('quantity');
        $table->decimal('price', 10, 2);
        $table->timestamps();
        
        $table->foreign('order_id')->references('id')->on('test_orders');
    });

    // Seed test data
    $customer1 = TestCustomer::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'tier' => 'premium',
    ]);

    $customer2 = TestCustomer::create([
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'tier' => 'basic',
    ]);

    $order1 = TestOrder::create([
        'customer_id' => $customer1->id,
        'customer_name' => 'John Doe',
        'product_name' => 'iPhone 15',
        'amount' => 999.99,
        'status' => 'completed',
        'priority' => 'high',
        'shipped_at' => Carbon::now()->subDays(5),
    ]);

    $order2 = TestOrder::create([
        'customer_id' => $customer2->id,
        'customer_name' => 'Jane Smith',
        'product_name' => 'MacBook Pro',
        'amount' => 2499.99,
        'status' => 'processing',
        'shipped_at' => null,
    ]);

    $order3 = TestOrder::create([
        'customer_id' => $customer1->id,
        'customer_name' => 'John Doe',
        'product_name' => 'iPad Air',
        'amount' => 599.99,
        'status' => 'pending',
        'priority' => 'low',
    ]);

    TestOrderItem::create([
        'order_id' => $order1->id,
        'product_name' => 'iPhone 15',
        'quantity' => 1,
        'price' => 999.99,
    ]);

    TestOrderItem::create([
        'order_id' => $order2->id,
        'product_name' => 'MacBook Pro',
        'quantity' => 1,
        'price' => 2499.99,
    ]);

    // Clear cache and queues
    Cache::flush();
    Queue::purge();
});

afterEach(function () {
    \Schema::dropIfExists('test_order_items');
    \Schema::dropIfExists('test_orders');
    \Schema::dropIfExists('test_customers');
});

describe('Async Queries', function () {
    test('can execute eloquent queries asynchronously', function () {
        Queue::fake();

        // Queue async query
        $query = TestOrder::where('status', 'completed')
            ->async('order-processing', 'high');

        // Execute the query (normally would be queued)
        $orders = $query->get();

        expect($orders)->toHaveCount(1);
        expect($orders->first()->status)->toBe('completed');
    });

    test('async works with scopes', function () {
        Queue::fake();

        $orders = TestOrder::completed()
            ->async('completed-orders')
            ->get();

        expect($orders)->toHaveCount(1);
        expect($orders->first()->customer_name)->toBe('John Doe');
    });

    test('async works with relationships', function () {
        Queue::fake();

        $orders = TestOrder::with('items')
            ->async('orders-with-items')
            ->get();

        expect($orders)->toHaveCount(3);
        expect($orders->first()->items)->not->toBeEmpty();
    });
});

describe('Batch Operations', function () {
    test('can perform batch insert operations', function () {
        $newOrders = [
            [
                'customer_name' => 'Alice Brown',
                'product_name' => 'AirPods',
                'amount' => 199.99,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'customer_name' => 'Bob Wilson',
                'product_name' => 'Apple Watch',
                'amount' => 399.99,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        TestOrder::batch()->insert($newOrders);

        $totalOrders = TestOrder::count();
        expect($totalOrders)->toBe(5); // 3 existing + 2 new
    });

    test('can perform batch update operations', function () {
        TestOrder::where('status', 'pending')
            ->batch()
            ->update(['priority' => 'high']);

        $pendingOrders = TestOrder::where('status', 'pending')->get();
        expect($pendingOrders->every(fn($order) => $order->priority === 'high'))->toBeTrue();
    });

    test('batch operations are more efficient', function () {
        \DB::enableQueryLog();

        // Create multiple orders using batch
        $newOrders = [];
        for ($i = 0; $i < 10; $i++) {
            $newOrders[] = [
                'customer_name' => "Customer $i",
                'product_name' => "Product $i",
                'amount' => 100.00 + $i,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        TestOrder::batch()->insert($newOrders);

        $queries = \DB::getQueryLog();
        
        // Should use fewer queries than individual inserts
        expect(count($queries))->toBeLessThan(10);

        \DB::disableQueryLog();
    });
});

describe('Enhanced Query Building', function () {
    test('lazy map processes eloquent collections with callback', function () {
        $processedCount = 0;
        
        $results = TestOrder::lazyMap(2, function ($order) use (&$processedCount) {
            $processedCount++;
            return [
                'id' => $order->id,
                'customer' => $order->customer_name,
                'total_with_tax' => $order->amount * 1.1,
                'processed' => true,
            ];
        });

        $collection = $results->collect();
        
        expect($collection)->toHaveCount(3);
        expect($processedCount)->toBe(3);
        expect($collection->first()['processed'])->toBeTrue();
    });

    test('flat chunk processes eloquent models efficiently', function () {
        $processed = [];
        
        TestOrder::flatChunk(2, function ($order) use (&$processed) {
            $processed[] = [
                'name' => $order->customer_name,
                'amount' => $order->amount,
            ];
        });

        expect($processed)->toHaveCount(3);
        expect($processed[0]['name'])->toBe('John Doe');
    });

    test('select iterator works with eloquent models', function () {
        $customerNames = [];
        
        foreach (TestOrder::selectIterator(1) as $order) {
            $customerNames[] = $order->customer_name;
        }

        expect($customerNames)->toHaveCount(3);
        expect($customerNames)->toContain('John Doe', 'Jane Smith');
    });

    test('enhanced building works with relationships', function () {
        $processed = [];
        
        TestOrder::with('items')
            ->flatChunk(2, function ($order) use (&$processed) {
                $processed[] = [
                    'order_id' => $order->id,
                    'item_count' => $order->items->count(),
                ];
            });

        expect($processed)->toHaveCount(3);
        expect($processed[0]['item_count'])->toBeGreaterThan(0);
    });
});

describe('Event System', function () {
    test('triggers eloquent model events', function () {
        Event::fake();

        $order = TestOrder::create([
            'customer_name' => 'Event Test',
            'product_name' => 'Test Product',
            'amount' => 150.00,
            'status' => 'pending',
            // priority should be set by event handler
        ]);

        expect($order->priority)->toBe('normal'); // Set by creating event

        Event::assertDispatched('order.created');
    });

    test('can register before and after events on queries', function () {
        $beforeCalled = false;
        $afterCalled = false;

        TestOrder::before('select', function ($query) use (&$beforeCalled) {
            $beforeCalled = true;
        });

        TestOrder::after('select', function ($query, $result) use (&$afterCalled) {
            $afterCalled = true;
        });

        TestOrder::completed()->get();

        expect($beforeCalled)->toBeTrue();
        expect($afterCalled)->toBeTrue();
    });

    test('can trigger custom events', function () {
        $eventTriggered = false;
        $eventData = null;

        TestOrder::on('custom.event', function ($data) use (&$eventTriggered, &$eventData) {
            $eventTriggered = true;
            $eventData = $data;
        });

        TestOrder::trigger('custom.event', ['test' => 'data'])
            ->where('status', 'completed')
            ->get();

        expect($eventTriggered)->toBeTrue();
        expect($eventData['test'])->toBe('data');
    });
});

describe('Advanced Filtering', function () {
    test('applies filters to eloquent queries', function () {
        $orders = TestOrder::filter([
            'filter' => ['status' => 'completed'],
            'parse' => ['amount:gte:500'],
        ])->get();

        expect($orders)->toHaveCount(1);
        expect($orders->first()->customer_name)->toBe('John Doe');
    });

    test('combines filters with eloquent scopes', function () {
        $orders = TestOrder::completed()
            ->filter([
                'parse' => ['amount:gte:500'],
            ])
            ->get();

        expect($orders)->toHaveCount(1);
        expect($orders->first()->status)->toBe('completed');
    });

    test('filters work with relationships', function () {
        $orders = TestOrder::with('items')
            ->filter([
                'filter' => ['status' => ['completed', 'processing']],
            ])
            ->get();

        expect($orders)->toHaveCount(2);
        expect($orders->every(fn($order) => $order->items->isNotEmpty()))->toBeTrue();
    });

    test('complex filtering with multiple conditions', function () {
        $orders = TestOrder::filter([
            'filter' => ['priority' => ['normal', 'high']],
            'parse' => ['amount:between:500,1500'],
            'lfilter' => ['customer_name' => 'john'],
        ])->get();

        expect($orders)->toHaveCount(1);
        expect($orders->first()->customer_name)->toBe('John Doe');
        expect($orders->first()->product_name)->toBe('iPhone 15');
    });
});

describe('Pagination Enhancement', function () {
    test('complex paginate works with eloquent models', function () {
        $paginator = TestOrder::with('items')
            ->complexPaginate(2);

        expect($paginator->total())->toBe(3);
        expect($paginator->perPage())->toBe(2);
        expect($paginator->items())->toHaveCount(2);
        
        // Check that relationships are loaded
        expect($paginator->items()[0]->relationLoaded('items'))->toBeTrue();
    });

    test('pagination works with filtering', function () {
        $paginator = TestOrder::filter([
            'filter' => ['status' => ['completed', 'processing']],
        ])->complexPaginate(1);

        expect($paginator->total())->toBe(2);
        expect($paginator->items())->toHaveCount(1);
    });

    test('pagination with scopes', function () {
        $paginator = TestOrder::highValue()
            ->complexPaginate(1);

        expect($paginator->total())->toBe(1); // Only MacBook Pro > 1000
        expect($paginator->items()[0]->product_name)->toBe('MacBook Pro');
    });
});

describe('Caching Integration', function () {
    test('eloquent queries inherit cache settings', function () {
        // Set cache time on query
        $orders1 = TestOrder::remember(60)
            ->where('status', 'completed')
            ->get();

        expect($orders1)->toHaveCount(1);

        // Create new completed order
        TestOrder::create([
            'customer_name' => 'Cache Test',
            'product_name' => 'Test Product',
            'amount' => 200.00,
            'status' => 'completed',
        ]);

        // Should return cached result (old count)
        $orders2 = TestOrder::remember(60)
            ->where('status', 'completed')
            ->get();

        expect($orders2)->toHaveCount(1); // Should be cached
    });

    test('relationships inherit cache settings', function () {
        $order = TestOrder::with('items')
            ->remember(60)
            ->first();

        expect($order->relationLoaded('items'))->toBeTrue();
        expect($order->items)->not->toBeEmpty();
    });
});

describe('Combined Advanced Features', function () {
    test('multiple extensions work together seamlessly', function () {
        Event::fake();
        Queue::fake();

        $results = TestOrder::with('items')
            ->filter([
                'filter' => ['status' => ['completed', 'processing']],
                'parse' => ['amount:gte:500'],
            ])
            ->before('select', function ($query) {
                // Log query execution
            })
            ->remember(60, 'complex-orders', ['orders'])
            ->async('complex-processing', 'high')
            ->complexPaginate(5);

        expect($results->total())->toBe(2);
        expect($results->items())->toHaveCount(2);

        // Verify caching
        expect(Cache::has('complex-orders'))->toBeTrue();
    });

    test('batch operations with events and filtering', function () {
        Event::fake();

        // Create multiple orders that will trigger events
        $newOrders = [];
        for ($i = 0; $i < 5; $i++) {
            $newOrders[] = [
                'customer_name' => "Batch Customer $i",
                'product_name' => "Batch Product $i",
                'amount' => 100.00 + ($i * 50),
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        TestOrder::batch()->insert($newOrders);

        // Filter the batch-created orders
        $batchOrders = TestOrder::filter([
            'lfilter' => ['customer_name' => 'Batch'],
            'parse' => ['amount:gte:150'],
        ])->get();

        expect($batchOrders)->toHaveCount(4); // 150, 200, 250, 300
    });

    test('lazy processing with relationships and caching', function () {
        Cache::flush();

        $processed = [];
        
        TestOrder::with('items')
            ->remember(60)
            ->lazyMap(2, function ($order) use (&$processed) {
                $processed[] = [
                    'order_id' => $order->id,
                    'customer' => $order->customer_name,
                    'item_count' => $order->items->count(),
                    'total_value' => $order->amount,
                ];
                return $processed[count($processed) - 1];
            })
            ->collect();

        expect($processed)->toHaveCount(3);
        expect($processed[0]['item_count'])->toBeGreaterThan(0);
        
        // Verify caching worked
        $cacheKeys = array_keys(Cache::getStore()->getMemcached()->getAllKeys() ?: []);
        expect($cacheKeys)->not->toBeEmpty();
    });
});

describe('Error Handling and Edge Cases', function () {
    test('handles empty results gracefully', function () {
        $orders = TestOrder::filter([
            'filter' => ['status' => 'nonexistent'],
        ])->get();

        expect($orders)->toBeInstanceOf(Collection::class);
        expect($orders)->toHaveCount(0);
    });

    test('handles malformed filter syntax gracefully', function () {
        $orders = TestOrder::filter([
            'parse' => ['invalid:syntax:too:many:colons'],
        ])->get();

        // Should not throw exception
        expect($orders)->toBeInstanceOf(Collection::class);
    });

    test('async operations handle failures gracefully', function () {
        Queue::fake();

        // This should not throw exception
        $query = TestOrder::async('failing-job')
            ->where('nonexistent_column', 'value');

        expect($query)->toBeInstanceOf(\Illuminate\Database\Eloquent\Builder::class);
    });

    test('batch operations handle large datasets', function () {
        $largeDataset = [];
        for ($i = 0; $i < 100; $i++) {
            $largeDataset[] = [
                'customer_name' => "Customer $i",
                'product_name' => "Product $i",
                'amount' => rand(10, 1000),
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Should handle large batch insert without issues
        expect(fn() => TestOrder::batch()->insert($largeDataset))
            ->not->toThrow(\Exception::class);

        expect(TestOrder::count())->toBe(103); // 3 existing + 100 new
    });
});

describe('Performance Tests', function () {
    test('lazy processing is memory efficient', function () {
        // Create many orders for memory testing
        $orders = [];
        for ($i = 0; $i < 50; $i++) {
            $orders[] = [
                'customer_name' => "Memory Test $i",
                'product_name' => "Product $i",
                'amount' => 100.00,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        TestOrder::batch()->insert($orders);

        $memoryBefore = memory_get_usage();
        
        // Process using lazy map
        $processedCount = 0;
        TestOrder::lazyMap(10, function ($order) use (&$processedCount) {
            $processedCount++;
            return ['id' => $order->id];
        })->collect();

        $memoryAfter = memory_get_usage();
        
        expect($processedCount)->toBe(53); // 3 original + 50 new
        
        // Memory usage should be reasonable (this is a rough check)
        $memoryDiff = $memoryAfter - $memoryBefore;
        expect($memoryDiff)->toBeLessThan(10 * 1024 * 1024); // Less than 10MB
    });
});

describe('Integration with Standard Eloquent', function () {
    test('extensions do not break standard eloquent features', function () {
        // Standard Eloquent operations should still work
        $order = TestOrder::create([
            'customer_name' => 'Standard Test',
            'product_name' => 'Standard Product',
            'amount' => 299.99,
            'status' => 'pending',
        ]);

        expect($order->exists)->toBeTrue();
        expect($order->wasRecentlyCreated)->toBeTrue();

        // Standard relationships
        $item = TestOrderItem::create([
            'order_id' => $order->id,
            'product_name' => 'Test Item',
            'quantity' => 2,
            'price' => 149.99,
        ]);

        $order->load('items');
        expect($order->items)->toHaveCount(1);

        // Standard updates
        $order->update(['status' => 'processing']);
        expect($order->fresh()->status)->toBe('processing');

        // Standard deletion
        $order->delete();
        expect(TestOrder::find($order->id))->toBeNull();
    });
});
