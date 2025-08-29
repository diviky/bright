# Database Concerns Overview

The Bright package provides a comprehensive set of database concerns (traits) that extend Laravel's database functionality. These concerns can be used independently or combined to create powerful database operations.

## Architecture

Database concerns are organized into two main categories:

1. **Query Builder Concerns** (`Database/Concerns/`) - Extend the base Query Builder
2. **Eloquent Concerns** (`Database/Eloquent/Concerns/`) - Extend Eloquent Models

## Available Database Concerns

### Core Query Building

#### 1. BuildsQueries
Provides enhanced query building methods for memory-efficient processing.

```php
use Diviky\Bright\Database\Concerns\BuildsQueries;

class CustomBuilder extends QueryBuilder
{
    use BuildsQueries;
}

// Usage
$results = DB::table('users')
    ->lazyMap(1000, function ($user) {
        return $user->transform();
    });

$users = DB::table('users')->selectIterator(500);
foreach ($users as $user) {
    // Memory-efficient processing
}
```

**Key Methods:**
- `lazyMap($chunkSize, ?callable $callback)` - Lazy processing with mapping
- `flatChunk($chunkSize, ?callable $callback)` - Flatten chunk processing  
- `selectIterator($chunkSize)` - Iterator-based processing

#### 2. Build
Enhanced query building utilities and optimizations.

```php
use Diviky\Bright\Database\Concerns\Build;

// Advanced query construction
$query = DB::table('orders')
    ->build()
    ->optimizeForLargeDataset()
    ->addConditionalWhere($conditions);
```

#### 3. Raw
Enhanced raw SQL capabilities with automatic column wrapping.

```php
use Diviky\Bright\Database\Concerns\Raw;

// Automatic wrapping in group by
$stats = DB::table('orders')
    ->groupByRaw(['user_id', 'DATE(created_at)'])
    ->get();

// Enhanced raw where with wrapping
$users = DB::table('users')
    ->whereRaw('users.score > ?', [100])
    ->get();
```

**Key Methods:**
- `groupByRaw($sql, array $bindings = [])` - Enhanced group by with wrapping
- `whereRaw($sql, $bindings, $boolean = 'and')` - Enhanced raw where
- `joinRaw($table, $bindings = [])` - Enhanced raw joins

### Performance & Optimization

#### 4. Cachable
Comprehensive query result caching with tags and drivers.

```php
use Diviky\Bright\Database\Concerns\Cachable;

// Basic caching
$users = DB::table('users')
    ->remember(3600)
    ->where('active', true)
    ->get();

// Advanced caching with tags
$posts = DB::table('posts')
    ->rememberForever('all-posts', ['posts', 'users'])
    ->get();

// Custom cache driver
$settings = DB::table('settings')
    ->cacheDriver('redis')
    ->remember(7200, 'app-settings')
    ->get();
```

**Key Methods:**
- `remember($seconds, $key = null)` - Cache query results
- `rememberForever($key = null, array $tags = [])` - Permanent cache with tags
- `rememberWithKey($key, $seconds)` - Cache with specific key
- `cacheDriver($driver)` - Set cache driver
- `cacheTags(array $tags)` - Set cache tags
- `flushCache($key = null)` - Clear cached results

#### 5. Async
Execute database queries asynchronously using Laravel's queue system.

```php
use Diviky\Bright\Database\Concerns\Async;

// Execute query asynchronously
$users = DB::table('users')
    ->where('active', true)
    ->async('user-queries', 'redis')
    ->get();

// Configure async settings
$results = DB::table('logs')
    ->async('log-processing', 'high-priority')
    ->chunk(1000, $callback);
```

**Key Methods:**
- `async($name = null, $queue = null, $connection = null)` - Execute asynchronously
- `asyncConfig()` - Get async configuration

### Data Management

#### 6. Paging
Enhanced pagination with multi-table support and complex scenarios.

```php
use Diviky\Bright\Database\Concerns\Paging;

// Multi-table pagination
$results = DB::table('users')
    ->join('posts', 'users.id', '=', 'posts.user_id')
    ->tables(['posts', 'comments'])
    ->complexPaginate(15);

// Pagination metadata
$metadata = DB::table('users')->paginationMeta($results);
```

**Key Methods:**
- `complexPaginate($perPage, $columns, $pageName, $page)` - Multi-table pagination
- `tables(array $tables)` - Specify tables for pagination
- `paginationMeta($paginator)` - Get pagination metadata
- `getTableTotals()` - Get totals for multiple tables

#### 7. Remove
Enhanced delete operations with conditions and batch processing.

```php
use Diviky\Bright\Database\Concerns\Remove;

// Conditional removal
$deleted = DB::table('logs')
    ->removeWhere(['level' => 'debug'])
    ->execute();

// Batch removal
$deleted = DB::table('temp_data')
    ->removeInBatches(1000)
    ->where('processed', true)
    ->execute();
```

**Key Methods:**
- `removeWhere(array $conditions)` - Conditional removal
- `removeInBatches($size)` - Batch deletion
- `removeWithConfirmation(array $conditions)` - Safe deletion

#### 8. SoftDeletes
Query builder soft delete support.

```php
use Diviky\Bright\Database\Concerns\SoftDeletes;

// Include soft deleted records
$allUsers = DB::table('users')->withTrashed()->get();

// Only soft deleted records
$deletedUsers = DB::table('users')->onlyTrashed()->get();

// Restore soft deleted records
$restored = DB::table('users')->restore();
```

**Key Methods:**
- `withTrashed()` - Include soft deleted records
- `onlyTrashed()` - Only soft deleted records
- `restore()` - Restore soft deleted records

### Data Operations

#### 9. Timestamps
Automatic timestamp handling in query builder operations.

```php
use Diviky\Bright\Database\Concerns\Timestamps;

// Auto-add timestamps on insert
DB::table('posts')
    ->insertWithTimestamps([
        'title' => 'New Post',
        'content' => 'Post content'
    ]);

// Auto-update timestamps on update
DB::table('posts')
    ->updateWithTimestamps(['title' => 'Updated Title']);
```

**Key Methods:**
- `insertWithTimestamps(array $values)` - Insert with automatic timestamps
- `updateWithTimestamps(array $values)` - Update with automatic timestamps

#### 10. Ordering
Enhanced sorting and ordering capabilities.

```php
use Diviky\Bright\Database\Concerns\Ordering;

// Dynamic ordering
$users = DB::table('users')
    ->orderByColumn('name', 'asc')
    ->orderByColumn('created_at', 'desc')
    ->get();

// Custom ordering logic
$products = DB::table('products')
    ->orderByCustom('priority', ['high', 'medium', 'low'])
    ->get();

// Conditional ordering
$query = DB::table('posts')
    ->orderByIf($condition, 'created_at', 'desc');
```

**Key Methods:**
- `orderByColumn($column, $direction)` - Enhanced column ordering
- `orderByCustom($column, array $values)` - Custom value ordering
- `orderByIf($condition, $column, $direction)` - Conditional ordering

### System Integration

#### 11. Config
Dynamic configuration and connection management.

```php
use Diviky\Bright\Database\Concerns\Config;

// Use specific database connection
$users = DB::table('users')
    ->connection('mysql-read')
    ->get();

// Apply configuration dynamically
$query = DB::table('logs')
    ->config(['timeout' => 30])
    ->get();
```

**Key Methods:**
- `connection($name)` - Set database connection
- `config(array $options)` - Apply dynamic configuration

#### 12. Eloquent
Bridge query builder with Eloquent features.

```php
use Diviky\Bright\Database\Concerns\Eloquent;

// Use Eloquent-like methods in query builder
$users = DB::table('users')
    ->whereModel(User::class, 'role', 'admin')
    ->withModel(User::class)
    ->get();
```

**Key Methods:**
- `whereModel($model, $column, $value)` - Apply model-based conditions
- `withModel($model)` - Associate query with model
- `applyScope($scope)` - Apply Eloquent scopes

#### 13. Eventable
Comprehensive event handling for query operations.

```php
use Diviky\Bright\Database\Concerns\Eventable;

// Listen for query events
DB::table('users')->before('select', function ($query) {
    logger('Executing query: ' . $query->toSql());
});

DB::table('orders')->after('insert', function ($query, $result) {
    event(new OrderCreated($result));
});

// Custom events
DB::table('users')
    ->trigger('user_accessed', ['user_id' => auth()->id()])
    ->first();
```

**Key Methods:**
- `before($event, callable $callback)` - Register before event listener
- `after($event, callable $callback)` - Register after event listener
- `trigger($event, array $data = [])` - Trigger custom event

### Data Export

#### 14. Outfile
Export query results to various file formats.

```php
use Diviky\Bright\Database\Concerns\Outfile;

// Export to CSV
DB::table('users')
    ->where('active', true)
    ->outfile('/tmp/active_users.csv', 'csv')
    ->get();

// Export to JSON
DB::table('orders')
    ->outfile('/tmp/orders.json', 'json')
    ->get();

// Custom export format
DB::table('products')
    ->outfile('/tmp/products.txt', 'custom', function ($row) {
        return $row->name . '|' . $row->price . "\n";
    })
    ->get();
```

**Key Methods:**
- `outfile($path, $format = 'csv', ?callable $formatter = null)` - Export to file
- Support for CSV, JSON, XML, and custom formats

#### 15. Filter
Advanced filtering system for complex database queries.

```php
use Diviky\Bright\Database\Concerns\Filter;

// Comprehensive filtering
$users = DB::table('users')->filter([
    'filter' => ['status' => 'active'],
    'lfilter' => ['name' => 'john'],
    'dfilter' => ['created_at' => '2024-01'],
    'parse' => ['age:gte:18', 'score:lt:100']
])->get();

// Configure filter behavior
$results = DB::table('products')
    ->filters(['category' => 'exact'], ['product_name' => 'name'])
    ->filter($requestData)
    ->get();
```

For detailed filtering documentation, see [database-filtering-system.md](database-filtering-system.md).

## Eloquent Model Concerns

### Model Enhancement

#### 1. ArrayToObject
Convert models to enhanced objects with dynamic property access.

```php
use Diviky\Bright\Database\Eloquent\Concerns\ArrayToObject;

class User extends Model
{
    use ArrayToObject;
}

$user = User::first();
$userObject = $user->toObject();
echo $userObject->name; // Dynamic access
```

#### 2. Cachable
Model-level caching configuration.

```php
use Diviky\Bright\Database\Eloquent\Concerns\Cachable;

class User extends Model
{
    use Cachable;
    
    protected $rememberFor = 3600;
    protected $rememberCacheTag = ['users'];
}
```

#### 3. Connection
Dynamic database connection handling for models.

```php
use Diviky\Bright\Database\Eloquent\Concerns\Connection;

class User extends Model
{
    use Connection;
}

$users = User::on('mysql-read')->get();
```

### Data Management

#### 4. Nanoids
Use Nanoids as primary keys instead of auto-incrementing integers.

```php
use Diviky\Bright\Database\Eloquent\Concerns\Nanoids;

class User extends Model
{
    use Nanoids;
    
    protected int $nanoidSize = 21;
}

$user = User::create(['name' => 'John']);
echo $user->id; // V1StGXR8_Z5jdHi6B-myT
```

#### 5. Relations
Enhanced relationship handling and manipulation.

```php
use Diviky\Bright\Database\Eloquent\Concerns\Relations;

class User extends Model
{
    use Relations;
}

$user = User::with('profile', 'posts')->first();
$flattened = $user->flatten(); // Merge relationship attributes

// Access nested data
$user->setNested('profile.bio', 'New bio');
$bio = $user->getNested('profile.bio');
```

#### 6. Timezone & TimezoneStorage
Comprehensive timezone support for datetime attributes.

```php
use Diviky\Bright\Database\Eloquent\Concerns\Timezone;
use Diviky\Bright\Database\Eloquent\Concerns\TimezoneStorage;

class Event extends Model
{
    use Timezone, TimezoneStorage;
    
    protected $timezoneInclude = ['event_date'];
    protected $userTimezoneFields = ['event_date'];
}
```

For detailed timezone documentation, see [timezone-handling.md](timezone-handling.md).

## Usage Patterns

### Combining Multiple Concerns

```php
use Diviky\Bright\Database\Query\Builder as BaseBuilder;

class EnhancedQueryBuilder extends BaseBuilder
{
    use BuildsQueries,
        Cachable,
        Filter,
        Async,
        Paging,
        Eventable;
}

// Usage with multiple features
$results = DB::table('orders')
    ->filter($filters)
    ->remember(3600, 'filtered-orders', ['orders'])
    ->complexPaginate(20)
    ->async('order-processing');
```

### Custom Model with All Features

```php
use Diviky\Bright\Database\Eloquent\Model as BaseModel;

class AdvancedUser extends BaseModel
{
    use Nanoids,
        Relations,
        Timezone,
        TimezoneStorage;
    
    protected int $nanoidSize = 21;
    protected $rememberFor = 1800;
    protected $timezoneInclude = ['created_at', 'last_login'];
    protected $userTimezoneFields = ['appointment_date'];
    
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}

// Usage
$user = AdvancedUser::with('posts')
    ->remember(3600)
    ->filter($filters)
    ->first();

$flatUser = $user->flatten();
$userObject = $user->toObject();
```

## Performance Considerations

1. **Caching**: Use appropriate cache strategies based on data access patterns
2. **Async Processing**: Ideal for heavy operations that don't need immediate results
3. **Chunking**: Use lazy methods for memory-efficient large dataset processing
4. **Filtering**: Optimize filters with proper database indexing
5. **Events**: Be cautious with event handlers that perform heavy operations

## Configuration

Many concerns can be configured globally or per-model:

```php
// config/database.php
'eloquent' => [
    'cache' => [
        'default_ttl' => 3600,
        'driver' => 'redis',
        'tags' => true
    ],
    'async' => [
        'enable' => true,
        'queue' => 'database',
        'connection' => 'redis'
    ],
    'nanoid' => [
        'default_size' => 18
    ]
]
```

## Error Handling

The concerns provide comprehensive error handling:

```php
try {
    $results = DB::table('users')
        ->filter($filters)
        ->remember(3600)
        ->get();
} catch (\Diviky\Bright\Database\Exceptions\InvalidFilterValue $e) {
    // Handle filter errors
} catch (\Exception $e) {
    // Handle other errors
}
```

These database concerns provide a powerful foundation for building robust, high-performance database operations while maintaining Laravel's elegant syntax.
