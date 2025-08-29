# Database Filtering System

The Bright package provides a comprehensive filtering system that allows for complex database queries through a simple, standardized interface. This system supports multiple filter types, relationship filtering, and advanced query parsing.

## Overview

The filtering system works through the `Filter` trait which can be used in both Query Builder and Eloquent Builder contexts.

```php
// Basic filtering
$users = User::filter([
    'filter' => ['status' => 'active'],
    'lfilter' => ['name' => 'john'],
    'dfilter' => ['created_at' => '2024-01'],
])->get();

// Query Builder filtering
$users = DB::table('users')->filter([
    'filter' => ['status' => 'active'],
    'parse' => ['age:gte:18', 'score:between:80,100']
])->get();
```

## Filter Types

The system supports multiple filter types, each optimized for different use cases:

### 1. Exact Filters (`filter`)

Apply exact match conditions to database columns.

```php
$users = User::filter([
    'filter' => [
        'status' => 'active',
        'role' => 'admin',
        'department_id' => 5,
        'is_verified' => true,
    ]
])->get();

// Generates: WHERE status = 'active' AND role = 'admin' AND department_id = 5 AND is_verified = 1
```

**Array Values (IN clause):**
```php
$users = User::filter([
    'filter' => [
        'status' => ['active', 'pending'], // IN clause
        'role' => ['admin', 'manager', 'supervisor'],
    ]
])->get();

// Generates: WHERE status IN ('active', 'pending') AND role IN ('admin', 'manager', 'supervisor')
```

### 2. Like Filters (`lfilter`)

Apply LIKE/contains conditions for partial text matching.

```php
$users = User::filter([
    'lfilter' => [
        'name' => 'john',           // %john%
        'email' => '@company.com',  // %@company.com%
        'bio' => 'developer',       // %developer%
    ]
])->get();

// Generates: WHERE name LIKE '%john%' AND email LIKE '%@company.com%' AND bio LIKE '%developer%'
```

### 3. Date Filters (`dfilter`)

Specialized filtering for date and datetime columns.

```php
$orders = Order::filter([
    'dfilter' => [
        'created_at' => '2024-01',                    // Month: 2024-01-01 to 2024-01-31
        'updated_at' => '2024',                       // Year: 2024-01-01 to 2024-12-31
        'shipped_at' => ['2024-01-01', '2024-01-31'], // Range: between dates
        'delivered_at' => '2024-01-15',               // Specific date
    ]
])->get();
```

**Date Format Support:**
- `YYYY` - Full year (e.g., `2024`)
- `YYYY-MM` - Year and month (e.g., `2024-01`)
- `YYYY-MM-DD` - Specific date (e.g., `2024-01-15`)
- `[start, end]` - Date range array

### 4. Dynamic Match Filters (`dfilter` with operators)

Advanced filtering with custom operators and relationships.

```php
$products = Product::filter([
    'dfilter' => [
        'price' => ['operator' => '>=', 'value' => 100],
        'category.name' => ['operator' => 'like', 'value' => 'electronics'],
        'tags.slug' => ['operator' => 'in', 'value' => ['tech', 'gadgets']],
    ]
])->get();
```

### 5. Parse Filters (`parse`)

Advanced query language for complex filtering conditions.

```php
$users = User::filter([
    'parse' => [
        'age:gte:18',              // age >= 18
        'score:lt:100',            // score < 100
        'salary:between:50000,80000', // salary BETWEEN 50000 AND 80000
        'status:in:active,pending', // status IN ('active', 'pending')
        'name:contains:john',      // name LIKE '%john%'
        'email:starts:admin',      // email LIKE 'admin%'
        'phone:ends:1234',         // phone LIKE '%1234'
    ]
])->get();
```

## Parse Filter Operators

The parse filter supports various operators for complex conditions:

### Comparison Operators
```php
'age:eq:25'        // age = 25 (equal)
'age:ne:25'        // age != 25 (not equal)
'age:gt:18'        // age > 18 (greater than)
'age:gte:18'       // age >= 18 (greater than or equal)
'age:lt:65'        // age < 65 (less than)
'age:lte:65'       // age <= 65 (less than or equal)
```

### Range Operators
```php
'salary:between:30000,80000'  // salary BETWEEN 30000 AND 80000
'age:not_between:13,17'       // age NOT BETWEEN 13 AND 17
```

### Array Operators
```php
'status:in:active,pending'         // status IN ('active', 'pending')
'status:not_in:inactive,banned'    // status NOT IN ('inactive', 'banned')
```

### String Operators
```php
'name:contains:john'     // name LIKE '%john%'
'name:not_contains:test' // name NOT LIKE '%test%'
'email:starts:admin'     // email LIKE 'admin%'
'email:ends:@company'    // email LIKE '%@company'
```

### Null Operators
```php
'deleted_at:null'       // deleted_at IS NULL
'deleted_at:not_null'   // deleted_at IS NOT NULL
```

## Filter Configuration

### Filter Types Configuration

Define how different fields should be filtered:

```php
$users = User::filters([
    'status' => 'exact',      // Always exact match
    'name' => 'like',         // Always LIKE match
    'email' => 'contains',    // Always contains match
    'age' => 'range',         // Range filtering
    'created_at' => 'date',   // Date filtering
], [
    // Aliases
    'is_active' => 'status',
    'user_name' => 'name',
    'email_address' => 'email'
])->filter($requestData)->get();
```

### Model-Level Configuration

Configure filtering behavior at the model level:

```php
class User extends Model
{
    protected $filterTypes = [
        'status' => 'exact',
        'name' => 'like',
        'email' => 'contains',
        'age' => 'range',
        'created_at' => 'date',
    ];
    
    protected $filterAliases = [
        'is_active' => 'status',
        'user_name' => 'name',
        'full_name' => 'name',
    ];
    
    protected $filterDefaults = [
        'status' => 'active', // Default value if not provided
    ];
}
```

## Relationship Filtering

Filter records based on related model attributes:

```php
// Filter users by their posts
$users = User::filter([
    'relations' => [
        'posts' => [
            'status' => 'published',
            'created_at' => '2024-01'
        ]
    ]
])->get();

// Filter with relationship counts
$users = User::filter([
    'relations' => [
        'posts' => [
            'count:gte:5' // Users with 5 or more posts
        ]
    ]
])->get();

// Nested relationship filtering
$users = User::filter([
    'relations' => [
        'posts.comments' => [
            'status' => 'approved'
        ]
    ]
])->get();
```

## Custom Filters

Create custom filter classes for complex filtering logic:

```php
// Create custom filter
class StatusFilter implements \Diviky\Bright\Database\Filters\Filter
{
    public function __invoke(Builder $query, $value, string $property): void
    {
        if ($value === 'active') {
            $query->where('status', 'active')
                  ->where('is_verified', true);
        } elseif ($value === 'inactive') {
            $query->where(function ($q) {
                $q->where('status', 'inactive')
                  ->orWhere('is_verified', false);
            });
        }
    }
}

// Register custom filter
User::addCustomFilter('status', new StatusFilter());

// Use custom filter
$users = User::filter(['filter' => ['status' => 'active']])->get();
```

## Advanced Usage Examples

### API Endpoint with Comprehensive Filtering

```php
class UserController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'filter' => 'array',
            'lfilter' => 'array',
            'dfilter' => 'array',
            'parse' => 'array',
            'relations' => 'array',
        ]);
        
        $users = User::with(['profile', 'posts'])
            ->filters([
                'status' => 'exact',
                'name' => 'like',
                'email' => 'contains',
                'created_at' => 'date',
            ], [
                'is_active' => 'status',
                'user_name' => 'name',
            ])
            ->filter($filters)
            ->paginate(20);
            
        return response()->json($users);
    }
}
```

### Complex Business Logic Filtering

```php
$complexFilter = [
    'filter' => [
        'status' => ['active', 'pending'],
        'department_id' => [1, 2, 3],
    ],
    'lfilter' => [
        'name' => $searchTerm,
        'skills' => $skillSearch,
    ],
    'dfilter' => [
        'hire_date' => [$startDate, $endDate],
        'last_login' => '2024-01', // This month
    ],
    'parse' => [
        'salary:gte:50000',
        'age:between:25,65',
        'performance_score:gt:7.5',
    ],
    'relations' => [
        'projects' => [
            'status' => 'active',
            'count:gte:2' // At least 2 active projects
        ],
        'reviews' => [
            'rating:gte:4',
            'created_at' => '2024'
        ]
    ]
];

$employees = Employee::with(['projects', 'reviews', 'department'])
    ->filter($complexFilter)
    ->orderBy('hire_date', 'desc')
    ->paginate(25);
```

### Frontend Integration

```javascript
// Frontend filter object
const filters = {
    filter: {
        status: 'active',
        role: ['admin', 'manager']
    },
    lfilter: {
        name: searchInput.value,
        email: emailFilter.value
    },
    dfilter: {
        created_at: [startDate, endDate]
    },
    parse: [
        `age:gte:${minAge}`,
        `salary:between:${minSalary},${maxSalary}`
    ]
};

// Send to API
fetch('/api/users', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(filters)
});
```

## Performance Considerations

1. **Index Optimization**: Ensure filtered columns have appropriate database indexes
2. **Relationship Filtering**: Use eager loading to avoid N+1 queries
3. **Parse Filters**: More flexible but potentially slower than direct filters
4. **Date Filtering**: Optimize date range queries with proper indexing
5. **Like Filters**: Use sparingly on large datasets, consider full-text search for better performance

## Error Handling

The filtering system provides comprehensive error handling:

```php
try {
    $users = User::filter($filters)->get();
} catch (\Diviky\Bright\Database\Exceptions\InvalidFilterValue $e) {
    // Handle invalid filter values
    return response()->json(['error' => 'Invalid filter value'], 400);
} catch (\Diviky\Bright\Database\Filters\Ql\ParserException $e) {
    // Handle parse filter syntax errors
    return response()->json(['error' => 'Invalid filter syntax'], 400);
}
```

## Security Considerations

1. **Validation**: Always validate filter inputs from user requests
2. **Whitelisting**: Use filter types configuration to restrict allowed filters
3. **Aliases**: Use aliases to hide internal column names
4. **Sanitization**: The system automatically sanitizes inputs to prevent SQL injection
5. **Relationship Security**: Be careful with relationship filtering to avoid data leaks

The filtering system provides a powerful, flexible way to handle complex database queries while maintaining security and performance.
