# Query Builder Extensions

The Bright package extends Laravel's Query Builder with additional functionality through various traits. The main builder class is `Diviky\Bright\Database\Query\Builder` which extends Laravel's base query builder.

## Overview

```php
use Diviky\Bright\Database\Query\Builder;

// The extended builder is automatically used when you use DB facade
// or when working with Eloquent models that use Bright's extensions
```

## Available Traits

The Query Builder includes the following functionality through traits:

### 1. Async Queries (`Async` trait)

Execute database queries asynchronously using Laravel's queue system.

```php
// Execute query asynchronously
$users = DB::table('users')
    ->where('active', true)
    ->async('user-queries', 'redis', 'user-sync')
    ->get();

// Configure async with specific queue settings
$results = DB::table('logs')
    ->where('level', 'error')
    ->async('log-processing', 'high-priority')
    ->select(['id', 'message', 'created_at'])
    ->get();
```

**Methods:**
- `async($name = null, $queue = null, $connection = null)` - Execute query asynchronously
- `asyncConfig()` - Get async configuration settings

### 2. Enhanced Building (`Build` trait)

Additional query building utilities and optimizations.

```php
// Enhanced query building with optimization
$query = DB::table('users')
    ->build()
    ->optimizeForLargeDataset()
    ->select(['id', 'name', 'email']);

// Build complex queries programmatically
$builder = DB::table('orders')
    ->build()
    ->addConditionalWhere($conditions)
    ->addDynamicJoins($relations);
```

**Methods:**
- `build()` - Enhanced building mode
- Various optimization and building utilities

### 3. Enhanced Query Building (`BuildsQueries` trait)

Additional query building methods for complex scenarios.

```php
// Lazy mapping with custom callback
$results = DB::table('users')
    ->where('active', true)
    ->lazyMap(1000, function ($user) {
        return (object) ['id' => $user->id, 'processed' => true];
    });

// Flat chunk processing
DB::table('users')
    ->where('status', 'pending')
    ->flatChunk(500, function ($user) {
        // Process each user
    });

// Iterator-based processing
foreach (DB::table('users')->selectIterator(1000) as $user) {
    // Memory-efficient processing
}
```

**Methods:**
- `lazyMap($chunkSize = 1000, ?callable $callback = null)` - Lazy processing with mapping
- `flatChunk($chunkSize = 1000, ?callable $callback = null)` - Flatten chunk processing
- `selectIterator($chunkSize = 1000)` - Iterator-based query processing

### 4. Caching (`Cachable` trait)

Comprehensive query result caching with tags and drivers.

```php
// Basic query caching
$users = DB::table('users')
    ->where('active', true)
    ->remember(3600) // Cache for 1 hour
    ->get();

// Advanced caching with tags
$results = DB::table('posts')
    ->join('users', 'posts.user_id', '=', 'users.id')
    ->rememberForever('all-posts', ['posts', 'users'])
    ->get();

// Custom cache driver
$data = DB::table('settings')
    ->cacheDriver('redis')
    ->remember(7200, 'app-settings')
    ->get();

// Cache with custom key
$users = DB::table('users')
    ->where('role', 'admin')
    ->rememberWithKey('admin-users-' . auth()->id(), 1800)
    ->get();
```

**Methods:**
- `remember($seconds, $key = null)` - Cache query results
- `rememberForever($key = null, array $tags = [])` - Cache permanently with tags
- `rememberWithKey($key, $seconds)` - Cache with specific key
- `cacheDriver($driver)` - Set cache driver
- `cacheTags(array $tags)` - Set cache tags
- `flushCache($key = null)` - Clear cached results

### 5. Configuration (`Config` trait)

Dynamic configuration and connection management.

```php
// Use specific database connection
$users = DB::table('users')
    ->connection('mysql-read')
    ->where('active', true)
    ->get();

// Apply configuration dynamically
$query = DB::table('logs')
    ->config(['timeout' => 30, 'memory_limit' => '512M'])
    ->where('date', today())
    ->get();
```

**Methods:**
- `connection($name)` - Set database connection
- `config(array $options)` - Apply dynamic configuration

### 6. Eloquent Integration (`Eloquent` trait)

Bridge query builder with Eloquent features.

```php
// Use Eloquent-like methods in query builder
$users = DB::table('users')
    ->whereModel(User::class, 'role', 'admin')
    ->withModel(User::class)
    ->get();

// Apply model scopes to query builder
$activeUsers = DB::table('users')
    ->applyScope('active')
    ->select(['id', 'name', 'email'])
    ->get();
```

**Methods:**
- `whereModel($model, $column, $value)` - Apply model-based where conditions
- `withModel($model)` - Associate query with model
- `applyScope($scope)` - Apply Eloquent scopes

### 7. Event System (`Eventable` trait)

Comprehensive event handling for query operations.

```php
// Listen for query events
DB::table('users')->before('select', function ($query) {
    logger('Executing user query: ' . $query->toSql());
});

DB::table('orders')->after('insert', function ($query, $result) {
    // Trigger post-insert actions
    event(new OrderCreated($result));
});

// Custom events
DB::table('users')
    ->trigger('user_accessed', ['user_id' => auth()->id()])
    ->where('id', 1)
    ->first();
```

**Methods:**
- `before($event, callable $callback)` - Register before event listener
- `after($event, callable $callback)` - Register after event listener
- `trigger($event, array $data = [])` - Trigger custom event

### 8. Advanced Filtering (`Filter` trait)

Comprehensive filtering system for complex queries.

```php
// Apply filters from request data
$users = DB::table('users')->filter([
    'filter' => ['status' => 'active', 'role' => 'admin'],
    'lfilter' => ['name' => 'john'], // Like filter
    'dfilter' => ['created_at' => '2024-01'], // Date filter
    'parse' => ['age:gte:18', 'score:lt:100'] // Advanced parsing
])->get();

// Configure filter behavior
$results = DB::table('products')
    ->filters(
        ['category' => 'exact', 'name' => 'like'],
        ['product_name' => 'name', 'is_available' => 'status']
    )
    ->filter($requestData)
    ->get();

// Advanced date filtering
$orders = DB::table('orders')
    ->filterDate(['created_at' => ['2024-01-01', '2024-12-31']])
    ->get();

// Range filtering
$products = DB::table('products')
    ->filterRange(['price' => [100, 500]])
    ->get();

// Relationship filtering
$users = DB::table('users')
    ->filterRelation('posts', ['status' => 'published'])
    ->get();
```

**Methods:**
- `filter(array $data = [])` - Apply comprehensive filters
- `filters(array $types = [], array $aliases = [])` - Configure filter behavior
- `filterExact(array $filters)` - Apply exact match filters
- `filterContains(array $filters)` - Apply contains/like filters
- `filterDate(array $filters)` - Apply date-based filters
- `filterRange(array $filters)` - Apply range filters
- `filterRelation($relation, array $filters)` - Filter by relationship

### 9. Ordering (`Ordering` trait)

Enhanced sorting and ordering capabilities.

```php
// Dynamic ordering with multiple criteria
$users = DB::table('users')
    ->orderByColumn('name', 'asc')
    ->orderByColumn('created_at', 'desc')
    ->get();

// Custom ordering logic
$products = DB::table('products')
    ->orderByCustom('priority', ['high', 'medium', 'low'])
    ->orderBy('name')
    ->get();

// Conditional ordering
$query = DB::table('posts')
    ->orderByIf($sortByDate, 'created_at', 'desc')
    ->orderByIf($sortByViews, 'view_count', 'desc');
```

**Methods:**
- `orderByColumn($column, $direction = 'asc')` - Enhanced column ordering
- `orderByCustom($column, array $values)` - Custom value ordering
- `orderByIf($condition, $column, $direction = 'asc')` - Conditional ordering

### 10. File Output (`Outfile` trait)

Export query results to files.

```php
// Export to CSV
DB::table('users')
    ->where('active', true)
    ->outfile('/tmp/active_users.csv', 'csv')
    ->get();

// Export to JSON
DB::table('orders')
    ->where('status', 'completed')
    ->outfile('/tmp/completed_orders.json', 'json')
    ->get();

// Custom export format
DB::table('products')
    ->outfile('/tmp/products.txt', 'custom', function ($row) {
        return $row->name . '|' . $row->price . "\n";
    })
    ->get();
```

**Methods:**
- `outfile($path, $format = 'csv', ?callable $formatter = null)` - Export results to file
- Various format support (CSV, JSON, XML, custom)

### 11. Enhanced Pagination (`Paging` trait)

Advanced pagination with multi-table support.

```php
// Complex pagination across multiple tables
$results = DB::table('users')
    ->join('posts', 'users.id', '=', 'posts.user_id')
    ->tables(['posts', 'comments'])
    ->complexPaginate(15);

// Get pagination metadata
$metadata = DB::table('users')->paginationMeta($results);

// Multi-table totals
$totals = DB::table('users')->getTableTotals();
```

**Methods:**
- `complexPaginate($perPage = 15, $columns = ['*'], $pageName = 'page', $page = null)` - Multi-table pagination
- `tables(array $tables)` - Specify tables for pagination
- `paginationMeta($paginator)` - Get pagination metadata

### 12. Raw SQL Enhancements (`Raw` trait)

Enhanced raw SQL capabilities with automatic wrapping.

```php
// Enhanced group by with automatic wrapping
$stats = DB::table('orders')
    ->select('user_id', DB::raw('COUNT(*) as order_count'))
    ->groupByRaw(['user_id', 'DATE(created_at)'])
    ->get();

// Raw where with automatic column wrapping
$users = DB::table('users')
    ->whereRaw('users.score > ?', [100])
    ->get();

// Enhanced join raw
$results = DB::table('users')
    ->joinRaw('posts ON users.id = posts.user_id AND posts.status = ?', ['published'])
    ->get();
```

**Methods:**
- `groupByRaw($sql, array $bindings = [])` - Enhanced group by with wrapping
- `whereRaw($sql, $bindings, $boolean = 'and')` - Enhanced raw where
- `joinRaw($table, $bindings = [])` - Enhanced raw joins

### 13. Record Removal (`Remove` trait)

Enhanced delete operations with conditions.

```php
// Conditional removal
$deleted = DB::table('logs')
    ->removeWhere(['level' => 'debug', 'created_at' => '<=' => now()->subDays(30)])
    ->execute();

// Batch removal with limits
$deleted = DB::table('temp_data')
    ->removeInBatches(1000)
    ->where('processed', true)
    ->execute();

// Safe removal with confirmation
$deleted = DB::table('users')
    ->removeWithConfirmation(['status' => 'inactive'])
    ->execute();
```

**Methods:**
- `removeWhere(array $conditions)` - Conditional removal
- `removeInBatches($size = 1000)` - Batch deletion
- `removeWithConfirmation(array $conditions)` - Safe deletion

### 14. Soft Deletes (`SoftDeletes` trait)

Query builder soft delete support.

```php
// Include soft deleted records
$allUsers = DB::table('users')
    ->withTrashed()
    ->get();

// Only soft deleted records
$deletedUsers = DB::table('users')
    ->onlyTrashed()
    ->get();

// Restore soft deleted records
$restored = DB::table('users')
    ->whereIn('id', [1, 2, 3])
    ->restore();
```

**Methods:**
- `withTrashed()` - Include soft deleted records
- `onlyTrashed()` - Only soft deleted records
- `restore()` - Restore soft deleted records

### 15. Timestamps (`Timestamps` trait)

Automatic timestamp handling in query builder.

```php
// Auto-add timestamps on insert
DB::table('posts')
    ->insertWithTimestamps([
        'title' => 'New Post',
        'content' => 'Post content'
    ]);

// Auto-update timestamps on update
DB::table('posts')
    ->where('id', 1)
    ->updateWithTimestamps(['title' => 'Updated Title']);
```

**Methods:**
- `insertWithTimestamps(array $values)` - Insert with automatic timestamps
- `updateWithTimestamps(array $values)` - Update with automatic timestamps

## Usage Examples

### Complete Example with Multiple Features

```php
// Complex query with multiple enhancements
$results = DB::table('orders')
    ->join('users', 'orders.user_id', '=', 'users.id')
    ->join('products', 'orders.product_id', '=', 'products.id')
    ->select([
        'orders.id',
        'users.name as user_name',
        'products.name as product_name',
        'orders.total',
        'orders.created_at'
    ])
    ->filter([
        'filter' => ['orders.status' => 'completed'],
        'dfilter' => ['orders.created_at' => '2024-01'],
        'parse' => ['orders.total:gte:100']
    ])
    ->remember(1800, 'completed-orders-2024', ['orders', 'users'])
    ->orderByColumn('orders.total', 'desc')
    ->complexPaginate(20);

// Export results to file
DB::table('orders')
    ->filter($filters)
    ->outfile('/tmp/order_report.csv', 'csv')
    ->get();
```

### Async Processing with Events

```php
// Process large dataset asynchronously
DB::table('user_analytics')
    ->before('select', function ($query) {
        logger('Starting analytics processing');
    })
    ->where('processed', false)
    ->async('analytics-processing', 'high-priority')
    ->chunk(1000, function ($records) {
        foreach ($records as $record) {
            // Process analytics
        }
    });
```

## Performance Considerations

1. **Caching**: Use appropriate cache drivers and tags for optimal performance
2. **Async Queries**: Ideal for long-running operations that don't need immediate results
3. **Chunking**: Use `flatChunk()` and `lazyMap()` for memory-efficient processing
4. **Filtering**: Define filter types to optimize query performance
5. **Pagination**: Use `complexPaginate()` for multi-table scenarios

## Configuration

Many features can be configured through environment variables or config files:

```php
// config/database.php
'connections' => [
    'mysql' => [
        // ... standard config
        'async' => [
            'enable' => true,
            'queue' => 'database',
            'connection' => 'redis'
        ],
        'cache' => [
            'driver' => 'redis',
            'default_ttl' => 3600
        ]
    ]
]
```

This enhanced Query Builder provides powerful tools for complex database operations while maintaining Laravel's elegant syntax and adding performance optimizations.
