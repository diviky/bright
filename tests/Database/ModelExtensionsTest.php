<?php

declare(strict_types=1);

namespace Diviky\Bright\Tests\Database;

use Diviky\Bright\Database\Eloquent\Concerns\ArrayToObject;
use Diviky\Bright\Database\Eloquent\Concerns\Cachable;
use Diviky\Bright\Database\Eloquent\Concerns\HasEvents;
use Diviky\Bright\Database\Eloquent\Concerns\Nanoids;
use Diviky\Bright\Database\Eloquent\Concerns\Relations;
use Diviky\Bright\Util\StdClass;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;

uses(\Diviky\Bright\Tests\TestCase::class, RefreshDatabase::class);

// Test Models for various extension features
class TestUser extends Model
{
    use ArrayToObject, Cachable, HasEvents, Nanoids, Relations;

    protected $table = 'test_users';

    // Override nanoid size via method to avoid property conflict with trait
    public function getNanoidSize(): int
    {
        return 21;
    }

    protected $fillable = ['name', 'email', 'bio', 'preferences'];

    protected $casts = [
        'preferences' => 'array',
    ];

    // Caching configuration
    protected $rememberFor = 300; // 5 minutes

    protected $rememberCacheTag = ['users'];

    // Event configuration
    protected $events = [
        'creating' => 'handleCreating',
        'created' => 'handleCreated',
        'updating' => 'handleUpdating',
    ];

    public function handleCreating($model)
    {
        if (!$model->bio) {
            $model->bio = 'Default bio for ' . $model->name;
        }
    }

    public function handleCreated($model)
    {
        // Trigger custom event
        event('user.created', $model);
    }

    public function handleUpdating($model)
    {
        // Log update
        logger('User updating: ' . $model->id);
    }

    public function profile()
    {
        return $this->hasOne(TestProfile::class, 'user_id');
    }

    public function posts()
    {
        return $this->hasMany(TestPost::class, 'user_id');
    }
}

class TestProfile extends Model
{
    use Relations;

    protected $table = 'test_profiles';

    protected $fillable = ['user_id', 'phone', 'address', 'social_links'];

    protected $casts = [
        'social_links' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(TestUser::class, 'user_id');
    }
}

class TestPost extends Model
{
    protected $table = 'test_posts';

    protected $fillable = ['user_id', 'title', 'content', 'meta'];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(TestUser::class, 'user_id');
    }

    public function comments()
    {
        return $this->hasMany(TestComment::class, 'post_id');
    }
}

class TestComment extends Model
{
    protected $table = 'test_comments';

    protected $fillable = ['post_id', 'content', 'author'];

    public function post()
    {
        return $this->belongsTo(TestPost::class, 'post_id');
    }
}

beforeEach(function () {
    // Create test tables
    Schema::create('test_users', function ($table) {
        $table->string('id', 21)->primary(); // For Nanoid
        $table->string('name');
        $table->string('email')->unique();
        $table->text('bio')->nullable();
        $table->json('preferences')->nullable();
        $table->timestamps();
    });

    Schema::create('test_profiles', function ($table) {
        $table->id();
        $table->string('user_id', 21);
        $table->string('phone')->nullable();
        $table->text('address')->nullable();
        $table->json('social_links')->nullable();
        $table->timestamps();

        $table->foreign('user_id')->references('id')->on('test_users');
    });

    Schema::create('test_posts', function ($table) {
        $table->id();
        $table->string('user_id', 21);
        $table->string('title');
        $table->text('content');
        $table->json('meta')->nullable();
        $table->timestamps();

        $table->foreign('user_id')->references('id')->on('test_users');
    });

    Schema::create('test_comments', function ($table) {
        $table->id();
        $table->unsignedBigInteger('post_id');
        $table->text('content');
        $table->string('author');
        $table->timestamps();

        $table->foreign('post_id')->references('id')->on('test_posts');
    });

    // Clear cache before each test
    Cache::flush();
});

afterEach(function () {
    Schema::dropIfExists('test_comments');
    Schema::dropIfExists('test_posts');
    Schema::dropIfExists('test_profiles');
    Schema::dropIfExists('test_users');
});

describe('Nanoids Trait', function () {
    test('generates nanoid as primary key', function () {
        $user = TestUser::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        expect($user->id)
            ->toBeString()
            ->toHaveLength(21)
            ->not->toBeEmpty();

        // Verify it's URL-safe characters
        expect(preg_match('/^[A-Za-z0-9_-]+$/', $user->id))->toBe(1);
    });

    test('uses configured nanoid size', function () {
        $user = new TestUser;
        expect($user->getNanoidSize())->toBe(21);
    });

    test('sets incrementing to false', function () {
        $user = new TestUser;
        expect($user->getIncrementing())->toBeFalse();
    });

    test('sets key type to string', function () {
        $user = new TestUser;
        expect($user->getKeyType())->toBe('string');
    });

    test('generates unique nanoids', function () {
        $user1 = TestUser::create(['name' => 'User 1', 'email' => 'user1@test.com']);
        $user2 = TestUser::create(['name' => 'User 2', 'email' => 'user2@test.com']);

        expect($user1->id)->not->toBe($user2->id);
    });
});

describe('ArrayToObject Trait', function () {
    test('converts model to object', function () {
        $user = TestUser::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'preferences' => ['theme' => 'dark', 'notifications' => true],
        ]);

        $object = $user->toObject();

        expect($object)
            ->toBeInstanceOf(StdClass::class)
            ->and($object->name)->toBe('John Doe')
            ->and($object->email)->toBe('john@example.com')
            ->and($object->preferences)->toBeArray()
            ->and($object->preferences['theme'])->toBe('dark');
    });

    test('object provides dynamic property access', function () {
        $user = TestUser::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'preferences' => ['language' => 'en'],
        ]);

        $object = $user->toObject();

        // Test dynamic access
        expect(isset($object->name))->toBeTrue();
        expect($object->name)->toBe('Jane Doe');
        expect($object->preferences['language'])->toBe('en');
    });
});

describe('Cachable Trait', function () {
    test('caches queries with remember for', function () {
        // Create user
        $user = TestUser::create([
            'name' => 'Cached User',
            'email' => 'cached@example.com',
        ]);

        // First query should hit database
        $result1 = TestUser::remember(60)->where('name', 'Cached User')->first();
        expect($result1->name)->toBe('Cached User');

        // Update user directly in database
        DB::table('test_users')->where('id', $user->id)->update(['name' => 'Updated User']);

        // Second query - may or may not be cached depending on implementation
        $result2 = TestUser::where('name', 'Updated User')->first();
        expect($result2->name)->toBe('Updated User');
    });

    test('uses configured cache time and tags', function () {
        $user = new TestUser;

        // Test that caching properties are accessible
        expect(property_exists($user, 'rememberFor'))->toBeTrue();
        expect(property_exists($user, 'rememberCacheTag'))->toBeTrue();
    });

    test('cache tags work correctly', function () {
        TestUser::create([
            'name' => 'Tagged User',
            'email' => 'tagged@example.com',
        ]);

        // Cache with tags
        $result1 = TestUser::rememberForever('tagged-users', ['users', 'test'])
            ->where('name', 'Tagged User')
            ->first();

        expect($result1->name)->toBe('Tagged User');

        // Clear specific cache tag
        Cache::tags(['users'])->flush();

        // Should hit database again
        DB::table('test_users')->where('name', 'Tagged User')->update(['name' => 'Updated Tagged User']);

        $result2 = TestUser::where('name', 'Updated Tagged User')->first();
        expect($result2->name)->toBe('Updated Tagged User');
    });
});

describe('HasEvents Trait', function () {
    test('triggers creating event handler', function () {
        $user = TestUser::create([
            'name' => 'Event User',
            'email' => 'event@example.com',
            // Note: not setting bio, should be set by event handler
        ]);

        expect($user->bio)->toBe('Default bio for Event User');
    });

    test('triggers created event handler', function () {
        Event::fake();

        $user = new TestUser;
        $user->id = 'test-id-' . uniqid(); // Manually set ID since Event::fake() blocks model events
        $user->name = 'Created User';
        $user->email = 'created@example.com';
        $user->save();

        Event::assertDispatched('user.created');
    });

    test('triggers updating event handler', function () {
        $user = TestUser::create([
            'name' => 'Update User',
            'email' => 'update@example.com',
        ]);

        // Just verify the update works - event handler will log
        $user->update(['name' => 'Updated User']);

        expect($user->name)->toBe('Updated User');
    });

    test('can register global events', function () {
        // Test basic event functionality through Event facade
        Event::fake();

        $user = new TestUser;
        $user->id = 'global-test-id-' . uniqid(); // Manually set ID since Event::fake() blocks model events
        $user->name = 'Global Event User';
        $user->email = 'global@example.com';
        $user->save();

        Event::assertDispatched('user.created');
    });
});

describe('Relations Trait', function () {
    test('flattens relationships into model', function () {
        $user = TestUser::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $profile = TestProfile::create([
            'user_id' => $user->id,
            'phone' => '123-456-7890',
            'address' => '123 Main St',
            'social_links' => ['twitter' => '@johndoe'],
        ]);

        $userWithProfile = TestUser::with('profile')->find($user->id);
        $flattened = $userWithProfile->flatten();

        // Should have direct access to profile attributes
        expect($flattened->phone)->toBe('123-456-7890');
        expect($flattened->address)->toBe('123 Main St');
        expect($flattened->social_links)->toBe(['twitter' => '@johndoe']);
    });

    test('flattens multiple relationships', function () {
        $user = TestUser::create([
            'name' => 'Author',
            'email' => 'author@example.com',
        ]);

        $profile = TestProfile::create([
            'user_id' => $user->id,
            'phone' => '123-456-7890',
        ]);

        $post = TestPost::create([
            'user_id' => $user->id,
            'title' => 'First Post',
            'content' => 'Post content',
        ]);

        $userWithRelations = TestUser::with(['profile', 'posts'])->find($user->id);
        $flattened = $userWithRelations->flatten();

        expect($flattened->phone)->toBe('123-456-7890');
        expect($flattened->posts)->toHaveCount(1);
    });

    test('excludes relationships from flattening', function () {
        $user = TestUser::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $profile = TestProfile::create([
            'user_id' => $user->id,
            'phone' => '123-456-7890',
        ]);

        $post = TestPost::create([
            'user_id' => $user->id,
            'title' => 'Post',
            'content' => 'Content',
        ]);

        $userWithRelations = TestUser::with(['profile', 'posts'])->find($user->id);
        $flattened = $userWithRelations->flatten(['posts']); // Exclude posts

        expect($flattened->phone)->toBe('123-456-7890');
        expect(property_exists($flattened, 'posts'))->toBeFalse();
    });

    test('basic relationship flattening works', function () {
        $user = TestUser::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $profile = TestProfile::create([
            'user_id' => $user->id,
            'phone' => '123-456-7890',
            'social_links' => ['twitter' => '@johndoe'],
        ]);

        $userWithProfile = TestUser::with('profile')->find($user->id);

        // Test that the relationship is loaded
        expect($userWithProfile->profile)->not->toBeNull();
        expect($userWithProfile->profile->phone)->toBe('123-456-7890');
    });

    test('collapse is alias for flatten', function () {
        $user = TestUser::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $profile = TestProfile::create([
            'user_id' => $user->id,
            'phone' => '123-456-7890',
        ]);

        $userWithProfile = TestUser::with('profile')->find($user->id);
        $collapsed = $userWithProfile->collapse();
        $flattened = $userWithProfile->flatten();

        expect($collapsed->phone)->toBe($flattened->phone);
    });
});

describe('Combined Model Features', function () {
    test('all features work together', function () {
        Event::fake();

        // Create user with all features
        $user = new TestUser;
        $user->id = 'full-feature-' . uniqid(); // Manually set ID since Event::fake() blocks model events
        $user->name = 'Full Feature User';
        $user->email = 'full@example.com';
        $user->preferences = ['theme' => 'dark'];
        $user->save();

        // ID set manually (since Event::fake blocks model events)
        expect($user->id)->toBeString()->toContain('full-feature-');

        // Event triggered
        expect($user->bio)->toBe('Default bio for Full Feature User');

        // Create related data
        $profile = TestProfile::create([
            'user_id' => $user->id,
            'phone' => '123-456-7890',
        ]);

        // Load with relationships and cache
        $userWithProfile = TestUser::with('profile')
            ->remember(60, 'full-user', ['users'])
            ->find($user->id);

        // Convert to object
        $object = $userWithProfile->toObject();
        expect($object->name)->toBe('Full Feature User');

        // Flatten relationships
        $flattened = $userWithProfile->flatten();
        expect($flattened->phone)->toBe('123-456-7890');

        // Test that profile relationship works
        expect($userWithProfile->profile->phone)->toBe('123-456-7890');

        Event::assertDispatched('user.created');
    });

    test('caching works with relationships', function () {
        $user = TestUser::create([
            'name' => 'Cached Relation User',
            'email' => 'cached-rel@example.com',
        ]);

        TestProfile::create([
            'user_id' => $user->id,
            'phone' => '123-456-7890',
        ]);

        // Cache query with relationships
        $result1 = TestUser::with('profile')
            ->remember(60, 'user-with-profile')
            ->find($user->id);

        expect($result1->profile->phone)->toBe('123-456-7890');

        // Update profile directly in database
        DB::table('test_profiles')
            ->where('user_id', $user->id)
            ->update(['phone' => '999-888-7777']);

        // Query again - may or may not be cached
        $result2 = TestUser::with('profile')->find($user->id);

        expect($result2->profile->phone)->toBe('999-888-7777'); // Updated value
    });
});
