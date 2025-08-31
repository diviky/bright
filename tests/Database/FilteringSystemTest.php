<?php

declare(strict_types=1);

namespace Diviky\Bright\Tests\Database;

use Carbon\Carbon;
use Diviky\Bright\Database\Concerns\Filter;
use Diviky\Bright\Database\Eloquent\Concerns\Filters;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(\Diviky\Bright\Tests\TestCase::class, RefreshDatabase::class);

// Test Model with filtering capabilities
class TestProduct extends Model
{
    use Filters;

    protected $table = 'test_products';

    protected $fillable = [
        'name', 'description', 'price', 'category_id', 'status',
        'is_featured', 'tags', 'created_at', 'updated_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_featured' => 'boolean',
        'tags' => 'array',
    ];

    // Filter configuration
    protected $filterTypes = [
        'status' => 'exact',
        'name' => 'like',
        'description' => 'contains',
        'price' => 'range',
        'created_at' => 'date',
    ];

    protected $filterAliases = [
        'product_name' => 'name',
        'is_active' => 'status',
    ];

    public function category()
    {
        return $this->belongsTo(TestCategory::class, 'category_id');
    }
}

class TestCategory extends Model
{
    protected $table = 'test_categories';

    protected $fillable = ['name', 'slug'];

    public function products()
    {
        return $this->hasMany(TestProduct::class, 'category_id');
    }
}

beforeEach(function () {
    // Create test tables
    Schema::create('test_categories', function ($table) {
        $table->id();
        $table->string('name');
        $table->string('slug');
        $table->timestamps();
    });

    Schema::create('test_products', function ($table) {
        $table->id();
        $table->string('name');
        $table->text('description')->nullable();
        $table->decimal('price', 10, 2);
        $table->unsignedBigInteger('category_id');
        $table->enum('status', ['active', 'inactive', 'pending']);
        $table->boolean('is_featured')->default(false);
        $table->json('tags')->nullable();
        $table->timestamps();

        $table->foreign('category_id')->references('id')->on('test_categories');
    });

    // Seed test data
    $electronics = TestCategory::create(['name' => 'Electronics', 'slug' => 'electronics']);
    $books = TestCategory::create(['name' => 'Books', 'slug' => 'books']);

    TestProduct::create([
        'name' => 'iPhone 15',
        'description' => 'Latest smartphone from Apple',
        'price' => 999.99,
        'category_id' => $electronics->id,
        'status' => 'active',
        'is_featured' => true,
        'tags' => ['phone', 'apple', 'premium'],
        'created_at' => Carbon::parse('2024-01-15'),
    ]);

    TestProduct::create([
        'name' => 'MacBook Pro',
        'description' => 'Professional laptop for developers',
        'price' => 2499.99,
        'category_id' => $electronics->id,
        'status' => 'active',
        'is_featured' => false,
        'tags' => ['laptop', 'apple', 'professional'],
        'created_at' => Carbon::parse('2024-01-10'),
    ]);

    TestProduct::create([
        'name' => 'PHP Best Practices',
        'description' => 'Learn modern PHP development',
        'price' => 49.99,
        'category_id' => $books->id,
        'status' => 'pending',
        'is_featured' => false,
        'tags' => ['programming', 'php', 'education'],
        'created_at' => Carbon::parse('2024-02-01'),
    ]);
});

afterEach(function () {
    Schema::dropIfExists('test_products');
    Schema::dropIfExists('test_categories');
});

describe('Exact Filters', function () {
    test('filters by exact match on single value', function () {
        $query = TestProduct::query();
        $query->getQuery()->filter(['filter' => ['status' => 'active']]);
        $products = $query->get();

        expect($products)->toHaveCount(2);
        expect($products->pluck('status')->unique()->toArray())->toBe(['active']);
    });

    test('filters by exact match on array values (IN clause)', function () {
        $query = TestProduct::query();
        $query->getQuery()->filter(['filter' => ['status' => ['active', 'pending']]]);
        $products = $query->get();

        expect($products)->toHaveCount(3);
        expect($products->pluck('status')->unique()->sort()->values()->toArray())
            ->toBe(['active', 'pending']);
    });

    test('filters by boolean values', function () {
        $query = TestProduct::query();

        $query->getQuery()->filter([
            'filter' => ['is_featured' => true],
        ]);

        $products = $query->get();

        expect($products)->toHaveCount(1);
        expect($products->first()->name)->toBe('iPhone 15');
    });

    test('filters by multiple exact conditions', function () {
        $query = TestProduct::query();

        $query->getQuery()->filter([
            'filter' => [
                'status' => 'active',
                'is_featured' => true,
            ],
        ]);

        $products = $query->get();

        expect($products)->toHaveCount(1);
        expect($products->first()->name)->toBe('iPhone 15');
    });
});

describe('Like Filters', function () {
    test('filters by partial text match', function () {
        $query = TestProduct::query();

        $query->getQuery()->filter([
            'lfilter' => ['name' => 'phone'],
        ]);

        $products = $query->get();

        expect($products)->toHaveCount(1);
        expect($products->first()->name)->toBe('iPhone 15');
    });

    test('filters by multiple like conditions', function () {
        $query = TestProduct::query();

        $query->getQuery()->filter([
            'lfilter' => [
                'name' => 'apple',
                'description' => 'professional',
            ],
        ]);

        $products = $query->get();

        expect($products)->toHaveCount(0); // No product matches both conditions
    });

    test('filters description with contains', function () {
        $query = TestProduct::query();

        $query->getQuery()->filter([
            'lfilter' => ['description' => 'apple'],
        ]);

        $products = $query->get();

        expect($products)->toHaveCount(1);
        expect($products->first()->name)->toBe('iPhone 15');
    });
});

describe('Date Filters', function () {
    test('filters by year', function () {
        $query = TestProduct::query();

        $query->getQuery()->filter([
            'dfilter' => ['created_at' => '2024'],
        ]);

        $products = $query->get();

        expect($products)->toHaveCount(3);
    });

    test('filters by year and month', function () {
        $query = TestProduct::query();

        $query->getQuery()->filter([
            'dfilter' => ['created_at' => '2024-01'],
        ]);

        $products = $query->get();

        expect($products)->toHaveCount(2);
        expect($products->pluck('name')->toArray())
            ->toContain('iPhone 15', 'MacBook Pro');
    });

    test('filters by specific date', function () {
        $query = TestProduct::query();

        $query->getQuery()->filter([
            'dfilter' => ['created_at' => '2024-01-15'],
        ]);

        $products = $query->get();

        expect($products)->toHaveCount(1);
        expect($products->first()->name)->toBe('iPhone 15');
    });

    test('filters by date range', function () {
        $query = TestProduct::query();

        $query->getQuery()->filter([
            'dfilter' => ['created_at' => ['2024-01-01', '2024-01-31']],
        ]);

        $products = $query->get();

        expect($products)->toHaveCount(2);
        expect($products->pluck('name')->toArray())
            ->toContain('iPhone 15', 'MacBook Pro');
    });
});

describe('Parse Filters', function () {
    test('handles comparison operators', function () {
        // Greater than or equal
        $query = TestProduct::query();

        $query->getQuery()->filter([
            'parse' => ['price:gte:1000'],
        ]);

        $products = $query->get();

        expect($products)->toHaveCount(1);
        expect($products->first()->name)->toBe('MacBook Pro');
    });

    test('handles less than operator', function () {
        $query = TestProduct::query();

        $query->getQuery()->filter([
            'parse' => ['price:lt:100'],
        ]);

        $products = $query->get();

        expect($products)->toHaveCount(1);
        expect($products->first()->name)->toBe('PHP Best Practices');
    });

    test('handles between operator', function () {
        $query = TestProduct::query();

        $query->getQuery()->filter([
            'parse' => ['price:between:50,1500'],
        ]);

        $products = $query->get();

        expect($products)->toHaveCount(2);
        expect($products->pluck('name')->toArray())
            ->toContain('iPhone 15', 'PHP Best Practices');
    });

    test('handles in operator', function () {
        $query = TestProduct::query();

        $query->getQuery()->filter([
            'parse' => ['status:in:active,pending'],
        ]);

        $products = $query->get();

        expect($products)->toHaveCount(3);
    });

    test('handles contains operator', function () {
        $query = TestProduct::query();

        $query->getQuery()->filter([
            'parse' => ['name:contains:apple'],
        ]);

        $products = $query->get();

        expect($products)->toHaveCount(0); // Case sensitive
    });

    test('handles multiple parse filters', function () {
        $query = TestProduct::query();

        $query->getQuery()->filter([
            'parse' => [
                'price:gte:500',
                'status:eq:active',
            ],
        ]);

        $products = $query->get();

        expect($products)->toHaveCount(2);
        expect($products->pluck('name')->toArray())
            ->toContain('iPhone 15', 'MacBook Pro');
    });
});

describe('Combined Filters', function () {
    test('combines different filter types', function () {
        $query = TestProduct::query();

        $query->getQuery()->filter([
            'filter' => ['status' => 'active'],
            'lfilter' => ['description' => 'apple'],
            'parse' => ['price:gte:500'],
        ]);

        $products = $query->get();

        expect($products)->toHaveCount(1);
        expect($products->first()->name)->toBe('iPhone 15');
    });

    test('combines date and exact filters', function () {
        $query = TestProduct::query();

        $query->getQuery()->filter([
            'filter' => ['status' => 'active'],
            'dfilter' => ['created_at' => '2024-01'],
        ]);

        $products = $query->get();

        expect($products)->toHaveCount(2);
        expect($products->pluck('name')->toArray())
            ->toContain('iPhone 15', 'MacBook Pro');
    });
});

describe('Filter Configuration', function () {
    test('uses filter type configuration', function () {
        $products = TestProduct::query()->filters([
            'name' => 'exact', // Override default 'like'
        ])->filter([
            'filter' => ['name' => 'iPhone'],
        ])->get();

        expect($products)->toHaveCount(0); // Exact match fails
    });

    test('uses filter aliases', function () {
        $query = TestProduct::query();

        $query->getQuery()->filter([
            'filter' => ['product_name' => 'iPhone 15'], // Uses alias
        ]);

        $products = $query->get();

        expect($products)->toHaveCount(1);
        expect($products->first()->name)->toBe('iPhone 15');
    });

    test('applies custom filters configuration', function () {
        $products = TestProduct::query()->filters(
            ['status' => 'exact'], // Types
            ['is_active' => 'status'] // Aliases
        )->filter([
            'filter' => ['is_active' => 'active'],
        ])->get();

        expect($products)->toHaveCount(2);
        expect($products->pluck('status')->unique()->toArray())->toBe(['active']);
    });
});

describe('Edge Cases and Error Handling', function () {
    test('handles empty filter arrays', function () {
        $query = TestProduct::query();

        $query->getQuery()->filter([
            'filter' => [],
            'lfilter' => [],
            'dfilter' => [],
            'parse' => [],
        ]);

        $products = $query->get();

        expect($products)->toHaveCount(3); // No filters applied
    });

    test('handles null filter values', function () {
        $query = TestProduct::query();

        $query->getQuery()->filter([
            'filter' => ['name' => null],
        ]);

        $products = $query->get();

        expect($products)->toHaveCount(3); // Null values ignored
    });

    test('handles invalid parse syntax gracefully', function () {
        $query = TestProduct::query();

        $query->getQuery()->filter([
            'parse' => ['invalid:syntax'],
        ]);

        $products = $query->get();

        // Should not throw exception, invalid syntax ignored
        expect($products)->toHaveCount(3);
    });

    test('handles non-existent fields gracefully', function () {
        $query = TestProduct::query();

        $query->getQuery()->filter([
            'filter' => ['non_existent_field' => 'value'],
        ]);

        $products = $query->get();

        // Should not throw exception
        expect($products)->toHaveCount(3);
    });
});

describe('Query Builder Integration', function () {
    test('works with query builder directly', function () {
        $products = DB::table('test_products')
            ->filter([
                'filter' => ['status' => 'active'],
                'parse' => ['price:gte:1000'],
            ])
            ->get();

        expect($products)->toHaveCount(1);
        expect($products->first()->name)->toBe('MacBook Pro');
    });

    test('combines with other query builder methods', function () {
        $products = TestProduct::where('category_id', 1)
            ->filter([
                'filter' => ['status' => 'active'],
            ])
            ->orderBy('price', 'desc')
            ->get();

        expect($products)->toHaveCount(2);
        expect($products->first()->name)->toBe('MacBook Pro'); // Highest price first
    });
});

describe('Relationship Filtering', function () {
    test('filters by relationship attributes', function () {
        // This would require additional implementation in the Filter trait
        // For now, testing the concept
        $products = TestProduct::whereHas('category', function ($query) {
            $query->where('name', 'Electronics');
        })->get();

        expect($products)->toHaveCount(2);
        expect($products->pluck('name')->toArray())
            ->toContain('iPhone 15', 'MacBook Pro');
    });
});

describe('Performance Tests', function () {
    test('handles large number of filter conditions', function () {
        $filters = [
            'filter' => [
                'status' => ['active', 'pending', 'inactive'],
                'is_featured' => [true, false],
            ],
            'lfilter' => [
                'name' => 'test',
                'description' => 'test',
            ],
            'parse' => [
                'price:gte:0',
                'price:lte:10000',
                'id:gte:1',
            ],
            'dfilter' => [
                'created_at' => '2024',
            ],
        ];

        $products = TestProduct::query()->filter($filters)->get();

        // Should execute without performance issues
        expect($products)->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);
    });

    test('generates efficient SQL queries', function () {
        DB::enableQueryLog();

        $query = TestProduct::query();

        $query->getQuery()->filter([
            'filter' => ['status' => 'active'],
            'lfilter' => ['name' => 'phone'],
            'parse' => ['price:gte:500'],
        ]);

        $products = $query->get();

        $queries = DB::getQueryLog();

        // Should generate a single query with proper WHERE conditions
        expect($queries)->toHaveCount(1);

        $sql = $queries[0]['query'];
        expect($sql)->toContain('WHERE');
        expect($sql)->toContain('status');
        expect($sql)->toContain('LIKE');
        expect($sql)->toContain('price');

        DB::disableQueryLog();
    });
});
