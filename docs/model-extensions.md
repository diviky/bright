# Model Extensions

The Bright package extends Laravel's Eloquent Model with additional functionality through various traits. The main model class is `Diviky\Bright\Database\Eloquent\Model` which extends Laravel's base model.

## Overview

```php
use Diviky\Bright\Database\Eloquent\Model;

class User extends Model
{
    // Your model automatically inherits all Bright extensions
}

// Or use individual traits in your existing models
use Diviky\Bright\Database\Eloquent\Concerns\Timezone;
use Diviky\Bright\Database\Eloquent\Concerns\Relations;

class User extends \Illuminate\Database\Eloquent\Model
{
    use Timezone, Relations;
}
```

## Available Traits

The enhanced Model includes the following functionality through traits:

### 1. Array to Object Conversion (`ArrayToObject` trait)

Convert models to objects with enhanced functionality.

```php
// Convert model to object
$user = User::first();
$userObject = $user->toObject();

// The object provides enhanced access patterns
echo $userObject->name; // Dynamic property access
echo $userObject->profile->bio; // Nested property access
```

**Methods:**
- `toObject()` - Convert model instance to enhanced object

### 2. Model Caching (`Cachable` trait)

Enhanced caching capabilities for models and their relationships.

```php
// Cache model queries
$users = User::remember(3600)->where('active', true)->get();

// Cache with tags
$posts = Post::with('user')
    ->rememberForever('all-posts', ['posts', 'users'])
    ->get();

// Model-level cache configuration
class User extends Model
{
    protected $rememberFor = 3600; // Cache all queries for 1 hour
    protected $rememberCacheTag = ['users']; // Default cache tags
}
```

**Properties:**
- `$rememberFor` - Default cache time for all queries
- `$rememberCacheTag` - Default cache tags

### 3. Database Connection Management (`Connection` trait)

Dynamic database connection handling.

```php
// Use specific connection for model
$users = User::on('mysql-read')->where('active', true)->get();

// Switch connections dynamically
$user = new User();
$user->setConnection('mysql-write');
$user->save();

// Configure connection per model
class AnalyticsData extends Model
{
    protected $connection = 'analytics';
}
```

**Methods:**
- `on($connection)` - Use specific database connection
- `setConnection($connection)` - Change model connection

### 4. Enhanced Eloquent Features (`Eloquent` trait)

Additional Eloquent functionality and optimizations.

```php
// Enhanced model creation with validation
$user = User::createValidated([
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

// Batch operations
User::batchInsert([
    ['name' => 'User 1', 'email' => 'user1@example.com'],
    ['name' => 'User 2', 'email' => 'user2@example.com'],
]);

// Enhanced model updates
$user->updateValidated(['email' => 'newemail@example.com']);
```

**Methods:**
- `createValidated(array $attributes)` - Create with validation
- `updateValidated(array $attributes)` - Update with validation
- `batchInsert(array $records)` - Batch insert records

### 5. Event System (`HasEvents` trait)

Enhanced event handling for model operations.

```php
// Define model events
class User extends Model
{
    protected $events = [
        'creating' => 'handleUserCreating',
        'created' => 'handleUserCreated',
        'updating' => 'handleUserUpdating',
    ];
    
    public function handleUserCreating($model)
    {
        // Execute before creating user
        $model->uuid = Str::uuid();
    }
    
    public function handleUserCreated($model)
    {
        // Execute after creating user
        event(new UserCreated($model));
    }
}

// Register global events
User::globalEvent('created', function ($user) {
    // Execute for all user creation
});
```

**Properties:**
- `$events` - Define model event handlers

**Methods:**
- `globalEvent($event, callable $callback)` - Register global event handlers
- `trigger($event, array $data = [])` - Trigger custom events

### 6. Enhanced Timestamps (`HasTimestamps` trait)

Advanced timestamp management with timezone support.

```php
// Configure timestamp fields
class Post extends Model
{
    use HasTimestamps;
    
    protected $timestamps = true;
    protected $dateFormat = 'Y-m-d H:i:s.u'; // Microsecond precision
    
    // Additional timestamp fields
    protected $timestampFields = [
        'published_at',
        'archived_at',
        'last_accessed_at'
    ];
}

// Automatic timestamp updates
$post = new Post();
$post->title = 'New Post';
$post->save(); // Automatically sets created_at, updated_at

// Touch related models
$post->touchRelated(['user', 'category']);
```

**Properties:**
- `$timestampFields` - Additional fields to treat as timestamps
- `$dateFormat` - Custom date format

**Methods:**
- `touchRelated(array $relations)` - Update timestamps on related models
- `touchQuietly()` - Update timestamps without triggering events

### 7. Nanoid Primary Keys (`Nanoids` trait)

Use Nanoids as primary keys instead of auto-incrementing integers.

```php
// Enable Nanoids for model
class User extends Model
{
    use Nanoids;
    
    protected int $nanoidSize = 21; // Custom size (default: 18)
}

// Model automatically generates Nanoid for primary key
$user = User::create(['name' => 'John Doe']);
echo $user->id; // Output: V1StGXR8_Z5jdHi6B-myT
```

**Properties:**
- `$nanoidSize` - Size of generated Nanoid (default: 18)

**Features:**
- Automatically generates URL-safe unique IDs
- Non-incrementing, non-sequential
- More secure than auto-incrementing IDs

### 8. Enhanced Relationships (`Relations` trait)

Advanced relationship handling and manipulation.

```php
// Flatten relationship attributes
$user = User::with('profile', 'posts')->first();
$flattened = $user->flatten(); // Merges profile and posts attributes

// Exclude specific relations from flattening
$flattened = $user->flatten(['posts']); // Only flatten profile

// Collapse relationships (alias for flatten)
$collapsed = $user->collapse();

// Flat relationships with custom behavior
$user->flat(['posts'], ['profile.avatar']);

// Set nested relationship attributes
$user->setNested('profile.bio', 'New bio text');
$user->setNested('posts.0.title', 'Updated first post title');

// Get nested relationship attributes
$bio = $user->getNested('profile.bio');
$firstPostTitle = $user->getNested('posts.0.title');

// Check if nested attribute exists
if ($user->hasNested('profile.avatar.url')) {
    // Process avatar
}
```

**Methods:**
- `flatten(array $except = [], array $exclude = [])` - Flatten relationships into model
- `flat(array $except = [], array $exclude = [])` - Alternative flatten method
- `collapse(array $except = [], array $exclude = [])` - Alias for flatten
- `setNested($key, $value)` - Set nested relationship attributes
- `getNested($key, $default = null)` - Get nested relationship attributes
- `hasNested($key)` - Check if nested attribute exists

### 9. Timezone Handling (`Timezone` & `TimezoneStorage` traits)

Comprehensive timezone support for datetime attributes.

```php
// Configure timezone conversion
class Event extends Model
{
    use Timezone, TimezoneStorage;
    
    // Fields that should be converted to user timezone on retrieval
    protected $timezoneInclude = ['event_date', 'deadline'];
    
    // Fields that should be converted from user timezone on storage
    protected $userTimezoneFields = ['event_date', 'deadline'];
    
    protected $casts = [
        'event_date' => 'datetime',
        'deadline' => 'datetime',
    ];
}

// Automatic conversion
$event = Event::create([
    'name' => 'Conference',
    'event_date' => '2024-01-15 14:00:00', // User timezone → UTC in database
]);

// Retrieval automatically converts to user timezone
$event = Event::first();
echo $event->event_date; // Displayed in user's timezone

// Disable conversion temporarily
$event->withoutTimezoneConversion()->save();
$event->disableTimezoneStorage()->update($data);
```

For detailed timezone documentation, see [timezone-handling.md](timezone-handling.md).

## Usage Examples

### Complete Model with All Features

```php
use Diviky\Bright\Database\Eloquent\Model;
use Diviky\Bright\Database\Eloquent\Concerns\Nanoids;

class User extends Model
{
    use Nanoids;
    
    protected $fillable = [
        'name',
        'email',
        'profile_data',
        'preferences'
    ];
    
    protected $casts = [
        'profile_data' => 'array',
        'preferences' => 'object',
        'email_verified_at' => 'datetime',
    ];
    
    // Cache configuration
    protected $rememberFor = 3600;
    protected $rememberCacheTag = ['users'];
    
    // Timezone configuration
    protected $timezoneInclude = ['created_at', 'updated_at', 'email_verified_at'];
    
    // Event handlers
    protected $events = [
        'creating' => 'generateUsername',
        'created' => 'sendWelcomeEmail',
        'updating' => 'validateUpdate',
    ];
    
    public function generateUsername($model)
    {
        if (!$model->username) {
            $model->username = Str::slug($model->name) . '-' . Str::random(4);
        }
    }
    
    public function sendWelcomeEmail($model)
    {
        // Send welcome email
        Mail::to($model->email)->send(new WelcomeEmail($model));
    }
    
    public function validateUpdate($model)
    {
        // Custom validation logic
    }
    
    // Relationships
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
}
```

### Working with Enhanced Features

```php
// Create user with automatic features
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'profile_data' => [
        'bio' => 'Software Developer',
        'location' => 'New York'
    ]
]);

// User ID is automatically generated Nanoid
echo $user->id; // V1StGXR8_Z5jdHi6B-myT

// Load with relationships and cache
$userWithRelations = User::with('posts', 'profile')
    ->remember(1800, 'user-' . $user->id, ['users', 'posts'])
    ->find($user->id);

// Flatten relationships
$flatUser = $userWithRelations->flatten();
echo $flatUser->bio; // From profile relationship

// Convert to enhanced object
$userObject = $user->toObject();
echo $userObject->profile_data->bio;

// Work with nested attributes
$user->setNested('profile.social.twitter', '@johndoe');
$twitterHandle = $user->getNested('profile.social.twitter');

// Batch operations
User::batchInsert([
    ['name' => 'User 1', 'email' => 'user1@example.com'],
    ['name' => 'User 2', 'email' => 'user2@example.com'],
]);
```

### Advanced Caching Patterns

```php
// Model with automatic caching
class Product extends Model
{
    protected $rememberFor = 7200; // 2 hours
    protected $rememberCacheTag = ['products'];
    
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}

// All queries automatically cached
$products = Product::active()->get(); // Cached for 2 hours

// Cache with relationships
$products = Product::with('category')
    ->active()
    ->get(); // Both products and categories cached

// Clear specific cache
Product::flushCache('products');
```

### Relationship Flattening Examples

```php
// User with multiple relationships
$user = User::with(['profile', 'posts.comments', 'roles'])
    ->first();

// Access nested data normally
echo $user->profile->bio;
echo $user->posts[0]->comments[0]->body;

// Flatten for easier access
$flatUser = $user->flatten();
echo $flatUser->bio; // Direct access to profile.bio
echo $flatUser->phone; // Direct access to profile.phone

// Flatten with exceptions
$partialFlat = $user->flatten(['posts']); // Don't flatten posts
echo $partialFlat->bio; // Works
echo $partialFlat->posts[0]->title; // Still nested

// Set nested values
$user->setNested('profile.settings.theme', 'dark');
$user->setNested('posts.0.meta.featured', true);
$user->save(); // Saves changes to related models
```

## Performance Considerations

1. **Caching**: Configure appropriate cache times and tags for your use case
2. **Nanoids**: Slightly slower than auto-increment IDs but more secure
3. **Relationship Flattening**: Use sparingly on large datasets
4. **Timezone Conversion**: Only enable for fields that need it
5. **Events**: Be careful with event handlers that perform heavy operations

## Configuration

Configure features through model properties or environment variables:

```php
// Model-level configuration
class User extends Model
{
    // Caching
    protected $rememberFor = 3600;
    protected $rememberCacheTag = ['users'];
    
    // Timezone
    protected $timezoneInclude = ['created_at', 'updated_at'];
    protected $userTimezoneFields = ['appointment_date'];
    
    // Nanoids
    protected int $nanoidSize = 21;
    
    // Events
    protected $events = [
        'created' => 'handleCreated',
        'updated' => 'handleUpdated',
    ];
}

// Global configuration in config/database.php
'eloquent' => [
    'cache' => [
        'default_ttl' => 3600,
        'driver' => 'redis'
    ],
    'nanoid' => [
        'default_size' => 18,
        'alphabet' => 'custom_alphabet'
    ]
]
```

These Model extensions provide powerful functionality while maintaining Laravel's elegant syntax and adding performance optimizations.
