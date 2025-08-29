# Eloquent Builder Extensions

The Bright package extends Laravel's Eloquent Builder with additional functionality through various traits. The main builder class is `Diviky\Bright\Database\Eloquent\Builder` which extends Laravel's base builder.

## Overview

```php
use Diviky\Bright\Database\Eloquent\Builder;

// The extended builder is automatically used when you use Bright's Model class
// or extend from Diviky\Bright\Database\Eloquent\Model
```

## Available Traits

The Eloquent Builder includes the following functionality through traits:

### 1. Async Queries (`Async` trait)

Execute database queries asynchronously using Laravel's queue system.

```php
// Execute query asynchronously
$users = User::where('active', true)
    ->async('user-queries', 'redis', 'user-sync')
    ->get();

// Configure async settings
$users = User::where('status', 'pending')
    ->async()
    ->get();
```

**Methods:**
- `async($name = null, $queue = null, $connection = null)` - Execute query asynchronously

### 2. Batch Operations (`Batch` trait)

Handle batch database operations efficiently.

```php
// Batch insert operations
User::batch()->insert([
    ['name' => 'John', 'email' => 'john@example.com'],
    ['name' => 'Jane', 'email' => 'jane@example.com'],
]);

// Batch update operations
User::whereIn('id', [1, 2, 3])
    ->batch()
    ->update(['status' => 'active']);
```

**Methods:**
- `batch()` - Enable batch mode for operations
- Optimized insert, update, and delete operations

### 3. Enhanced Query Building (`BuildsQueries` trait)

Additional query building methods for complex scenarios.

```php
// Lazy mapping with custom callback
$results = User::where('active', true)
    ->lazyMap(1000, function ($user) {
        return $user->transform();
    });

// Flat chunk processing
User::where('status', 'pending')
    ->flatChunk(500, function ($user) {
        $user->process();
    });

// Iterator-based processing
foreach (User::selectIterator(1000) as $user) {
    // Process each user
}
```

**Methods:**
- `lazyMap($chunkSize = 1000, ?callable $callback = null)` - Lazy processing with mapping
- `flatChunk($chunkSize = 1000, ?callable $callback = null)` - Flatten chunk processing
- `selectIterator($chunkSize = 1000)` - Iterator-based query processing

### 4. Event System (`Eventable` trait)

Enhanced event handling for Eloquent operations.

```php
// Listen for query events
User::before('select', function ($query) {
    // Execute before select
});

User::after('update', function ($query, $result) {
    // Execute after update
});

// Trigger custom events
User::trigger('custom_event', ['data' => $value]);
```

**Methods:**
- `before($event, callable $callback)` - Register before event listener
- `after($event, callable $callback)` - Register after event listener  
- `trigger($event, array $data = [])` - Trigger custom event

### 5. Advanced Filtering (`Filters` trait)

Comprehensive filtering system for complex queries.

```php
// Apply filters from request data
$users = User::filter([
    'filter' => ['status' => 'active', 'role' => 'admin'],
    'lfilter' => ['name' => 'john'], // Like filter
    'dfilter' => ['created_at' => '2024-01'], // Date filter
    'parse' => ['age:gte:18', 'score:lt:100'] // Advanced parsing
])->get();

// Configure filter types and aliases
$users = User::filters(
    ['status' => 'exact', 'name' => 'like'],
    ['user_name' => 'name', 'is_active' => 'status']
)->filter($requestData)->get();
```

**Methods:**
- `filter(array $data = [])` - Apply comprehensive filters
- `filters(array $types = [], array $aliases = [])` - Configure filter behavior
- `filterExact(array $filters)` - Apply exact match filters
- `filterContains(array $filters)` - Apply contains/like filters
- `filterMatch(array $filters, array $data)` - Apply advanced matching
- `filterParse(array $filters)` - Parse complex filter expressions

### 6. Pagination (`Paging` trait)

Enhanced pagination with multi-table support.

```php
// Complex pagination across multiple tables
$results = User::join('posts', 'users.id', '=', 'posts.user_id')
    ->tables(['posts', 'comments'])
    ->complexPaginate(15);

// Get pagination metadata
$metadata = User::paginationMeta($results);

// Multi-table totals
$totals = User::getTableTotals();
```

**Methods:**
- `complexPaginate($perPage = 15, $columns = ['*'], $pageName = 'page', $page = null)` - Multi-table pagination
- `tables(array $tables)` - Specify tables for pagination
- `paginationMeta($paginator)` - Get pagination metadata
- `getTableTotals()` - Get totals for multiple tables

## Caching Integration

The builder integrates with Laravel's caching system through the base query builder's caching functionality.

```php
// Cache queries automatically when cache time is set
$users = User::where('active', true)
    ->remember(3600) // Cache for 1 hour
    ->get();

// Relations automatically inherit cache settings
$user = User::with('posts')->remember(1800)->first();
// Posts relation will also be cached for 1800 seconds
```

## Usage Examples

### Complete Filtering Example

```php
// Handle complex filtering from API request
$filters = [
    'filter' => [
        'status' => 'active',
        'role' => ['admin', 'manager'], // Array values
    ],
    'lfilter' => [
        'name' => 'john', // Contains search
        'email' => '@company.com'
    ],
    'dfilter' => [
        'created_at' => '2024-01', // Date range
        'updated_at' => ['2024-01-01', '2024-12-31']
    ],
    'parse' => [
        'age:gte:18', // Greater than or equal
        'score:between:80,100', // Between values
        'status:in:active,pending' // In array
    ]
];

$users = User::filter($filters)
    ->paginate(20);
```

### Async Processing with Events

```php
// Process large datasets asynchronously with events
User::before('select', function ($query) {
    logger('Starting user query processing');
})
->where('status', 'pending')
->async('user-processing', 'high-priority')
->chunk(1000, function ($users) {
    foreach ($users as $user) {
        $user->process();
    }
});
```

### Batch Operations with Filtering

```php
// Update multiple records with filtering
User::filter(['filter' => ['role' => 'user']])
    ->batch()
    ->update(['status' => 'verified']);

// Batch insert with validation
$userData = collect($csvData)->map(function ($row) {
    return [
        'name' => $row['name'],
        'email' => $row['email'],
        'created_at' => now(),
        'updated_at' => now(),
    ];
});

User::batch()->insert($userData->toArray());
```

## Performance Considerations

1. **Async Queries**: Use for long-running operations that don't need immediate results
2. **Batch Operations**: More efficient for bulk insert/update operations
3. **Lazy Loading**: Use `lazyMap()` and `flatChunk()` for memory-efficient processing
4. **Caching**: Leverage automatic cache inheritance for relations
5. **Filtering**: Define filter types to optimize query performance

## Configuration

The builder respects various configuration options:

```php
// In your model or service provider
protected $filterTypes = [
    'status' => 'exact',
    'name' => 'like',
    'email' => 'contains',
    'age' => 'range'
];

protected $filterAliases = [
    'is_active' => 'status',
    'user_name' => 'name',
    'email_address' => 'email'
];
```

This enhanced Eloquent Builder provides powerful tools for complex database operations while maintaining Laravel's elegant syntax.
