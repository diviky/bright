# Database Extensions Documentation Index

This directory contains comprehensive documentation for all database extensions provided by the Bright package. These extensions enhance Laravel's database functionality with powerful features for modern applications.

## Documentation Files

### Core Components

#### 1. [Eloquent Builder Extensions](eloquent-builder-extensions.md)
Enhanced Eloquent query builder with advanced features:
- **Async Queries** - Execute queries asynchronously using queues
- **Batch Operations** - Efficient bulk insert, update, and delete operations
- **Advanced Filtering** - Comprehensive filtering system with multiple filter types
- **Enhanced Pagination** - Multi-table pagination with complex scenarios
- **Event System** - Rich event handling for query operations
- **Lazy Processing** - Memory-efficient processing with chunking and iteration

#### 2. [Query Builder Extensions](query-builder-extensions.md)
Extended Query Builder with 15+ traits providing:
- **Caching System** - Advanced result caching with tags and drivers
- **Async Processing** - Background query execution
- **File Export** - Export results to CSV, JSON, XML formats
- **Enhanced Filtering** - Complex filtering with parse language support
- **Performance Optimizations** - Chunking, lazy loading, and memory management
- **Raw SQL Enhancements** - Improved raw query handling with auto-wrapping

#### 3. [Model Extensions](model-extensions.md)
Eloquent Model enhancements through various concerns:
- **Nanoid Primary Keys** - Secure, non-sequential primary keys
- **Timezone Handling** - Automatic timezone conversion for date/time fields
- **Enhanced Relationships** - Relationship flattening and nested attribute access
- **Model Caching** - Built-in caching for models and relationships
- **Array to Object Conversion** - Enhanced object access patterns
- **Event System** - Advanced model event handling

### Specialized Systems

#### 4. [Database Filtering System](database-filtering-system.md)
Comprehensive filtering framework supporting:
- **Multiple Filter Types** - Exact, like, date, range, and parse filters
- **Relationship Filtering** - Filter by related model attributes
- **Advanced Query Language** - Parse complex filter expressions
- **Custom Filters** - Create custom filter classes for business logic
- **Security Features** - Built-in validation and SQL injection prevention
- **Performance Optimization** - Efficient query generation and indexing support

#### 5. [Timezone Handling](timezone-handling.md)
Complete timezone management solution:
- **Bidirectional Conversion** - User timezone ↔ Database (UTC) conversion
- **Selective Field Handling** - Configure which fields need timezone conversion
- **User Context Awareness** - Automatic user timezone detection
- **Safety Features** - Graceful fallbacks when timezone info unavailable
- **Flexible Configuration** - Model-level and runtime configuration options

#### 6. [Database Concerns Overview](database-concerns-overview.md)
Comprehensive reference for all database traits and concerns:
- **Query Builder Concerns** - 15+ traits for query enhancement
- **Eloquent Model Concerns** - Model-specific functionality traits
- **Usage Patterns** - How to combine multiple concerns effectively
- **Performance Considerations** - Best practices for optimal performance
- **Configuration Options** - Global and per-model configuration

## Quick Start Guide

### Basic Setup

```php
// Use the enhanced model
use Diviky\Bright\Database\Eloquent\Model;

class User extends Model
{
    // Automatically includes all Bright extensions
}

// Or use individual traits
use Diviky\Bright\Database\Eloquent\Concerns\Timezone;
use Diviky\Bright\Database\Eloquent\Concerns\Relations;

class User extends \Illuminate\Database\Eloquent\Model
{
    use Timezone, Relations;
}
```

### Enhanced Query Builder

```php
// Query Builder automatically enhanced when using Bright models
$users = User::where('active', true)
    ->filter($requestFilters)
    ->remember(3600)
    ->complexPaginate(20);

// Direct Query Builder usage
$results = DB::table('users')
    ->filter(['filter' => ['status' => 'active']])
    ->async('user-processing')
    ->get();
```

### Common Patterns

```php
// Complex filtering with caching and async processing
$results = User::with('posts')
    ->filter([
        'filter' => ['status' => 'active'],
        'lfilter' => ['name' => $searchTerm],
        'dfilter' => ['created_at' => '2024-01'],
        'parse' => ['age:gte:18', 'score:between:80,100']
    ])
    ->remember(1800, 'filtered-users', ['users', 'posts'])
    ->async('user-analytics', 'high-priority')
    ->complexPaginate(25);

// Model with timezone and relationships
$event = Event::create([
    'name' => 'Conference',
    'event_date' => '2024-01-15 14:00:00', // User timezone → UTC
]);

$eventData = $event->load('attendees')
    ->flatten() // Merge relationship attributes
    ->toObject(); // Convert to enhanced object
```

## Feature Matrix

| Feature | Query Builder | Eloquent Builder | Model | Description |
|---------|:-------------:|:----------------:|:-----:|-------------|
| **Async Queries** | ✅ | ✅ | ❌ | Execute queries in background |
| **Caching** | ✅ | ✅ | ✅ | Result and model caching |
| **Filtering** | ✅ | ✅ | ❌ | Advanced filtering system |
| **Pagination** | ✅ | ✅ | ❌ | Multi-table pagination |
| **Events** | ✅ | ✅ | ✅ | Event handling system |
| **Chunking** | ✅ | ✅ | ❌ | Memory-efficient processing |
| **File Export** | ✅ | ❌ | ❌ | Export to various formats |
| **Timezone** | ❌ | ❌ | ✅ | Automatic timezone conversion |
| **Nanoids** | ❌ | ❌ | ✅ | Secure primary keys |
| **Relations** | ❌ | ❌ | ✅ | Enhanced relationship handling |
| **Soft Deletes** | ✅ | ✅ | ✅ | Query builder soft deletes |
| **Timestamps** | ✅ | ✅ | ✅ | Automatic timestamp handling |

## Architecture Overview

```
Bright Database Extensions
├── Query Builder Extensions (Database/Query/Builder.php)
│   ├── Core Concerns (Database/Concerns/)
│   │   ├── Async, Cachable, Filter, Paging
│   │   ├── BuildsQueries, Raw, Ordering
│   │   └── Remove, SoftDeletes, Timestamps
│   └── Features: Filtering, Caching, Export, Events
│
├── Eloquent Builder Extensions (Database/Eloquent/Builder.php)
│   ├── Eloquent Concerns (Database/Eloquent/Concerns/)
│   │   ├── Async, Batch, Filters, Eventable
│   │   └── BuildsQueries, Cachable
│   └── Features: Enhanced Eloquent Operations
│
└── Model Extensions (Database/Eloquent/Model.php)
    ├── Model Concerns (Database/Eloquent/Concerns/)
    │   ├── Timezone, TimezoneStorage, Relations
    │   ├── Nanoids, ArrayToObject, Cachable
    │   └── Connection, HasEvents, HasTimestamps
    └── Features: Enhanced Model Functionality
```

## Performance Guidelines

### Best Practices

1. **Caching Strategy**
   - Use appropriate cache TTL for your data patterns
   - Leverage cache tags for efficient invalidation
   - Consider Redis for high-performance caching

2. **Async Processing**
   - Use for heavy operations that don't need immediate results
   - Configure appropriate queue drivers and workers
   - Monitor queue performance and failed jobs

3. **Filtering Optimization**
   - Ensure filtered columns have database indexes
   - Use exact filters when possible (faster than like filters)
   - Consider full-text search for complex text searches

4. **Memory Management**
   - Use lazy methods (`lazyMap`, `flatChunk`) for large datasets
   - Implement proper chunking for bulk operations
   - Monitor memory usage in production

5. **Database Optimization**
   - Index frequently filtered columns
   - Use appropriate database connections (read/write separation)
   - Monitor query performance and optimize slow queries

### Common Pitfalls

1. **Over-caching** - Don't cache frequently changing data
2. **N+1 Queries** - Use eager loading with relationships
3. **Memory Leaks** - Be careful with large dataset processing
4. **Event Overhead** - Minimize heavy operations in event handlers
5. **Filter Complexity** - Balance flexibility with performance

## Migration Guide

### From Standard Laravel

```php
// Before (Standard Laravel)
class User extends \Illuminate\Database\Eloquent\Model
{
    // Basic model
}

$users = User::where('active', true)->paginate(20);

// After (With Bright Extensions)
use Diviky\Bright\Database\Eloquent\Model;

class User extends Model
{
    use Timezone, Relations;
    
    protected $rememberFor = 3600;
    protected $timezoneInclude = ['created_at'];
}

$users = User::filter($filters)
    ->remember(3600)
    ->complexPaginate(20);
```

### Gradual Adoption

1. **Start with Models** - Extend from `Diviky\Bright\Database\Eloquent\Model`
2. **Add Filtering** - Implement the filtering system for search/filter functionality
3. **Enable Caching** - Add caching for frequently accessed data
4. **Implement Timezone** - Add timezone handling for datetime fields
5. **Add Async** - Use async processing for heavy operations

## Support and Troubleshooting

### Common Issues

1. **Cache Not Working** - Check cache driver configuration and permissions
2. **Async Jobs Failing** - Verify queue worker setup and job configuration
3. **Filter Errors** - Validate filter syntax and field names
4. **Timezone Issues** - Ensure user timezone resolution is working
5. **Memory Issues** - Use appropriate chunking for large datasets

### Debug Mode

```php
// Enable query logging
DB::enableQueryLog();

// Check executed queries
$queries = DB::getQueryLog();

// Debug specific features
$user = User::filter($filters)
    ->debug() // Enable debug mode
    ->get();
```

### Configuration Check

```php
// Verify configuration
$config = config('database.eloquent');
$asyncConfig = config('database.connections.mysql.async');
$cacheConfig = config('database.connections.mysql.cache');
```

This documentation provides comprehensive coverage of all database extensions available in the Bright package. Each file contains detailed examples and usage patterns for specific functionality.
