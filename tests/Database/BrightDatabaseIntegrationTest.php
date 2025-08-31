<?php

declare(strict_types=1);

namespace Diviky\Bright\Tests\Database;

use Carbon\Carbon;
use Diviky\Bright\Database\Eloquent\Model as BrightModel;
use Diviky\Bright\Models\User;
use Diviky\Bright\Services\Resolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;

uses(\Diviky\Bright\Tests\TestCase::class, RefreshDatabase::class);

// Comprehensive test model showcasing all Bright features
class EventManagementSystem extends BrightModel
{
    protected $table = 'events';

    protected $fillable = [
        'name', 'description', 'event_date', 'registration_deadline',
        'max_attendees', 'price', 'status', 'category_id', 'organizer_id',
        'location', 'tags', 'metadata',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'registration_deadline' => 'datetime',
        'price' => 'decimal:2',
        'max_attendees' => 'integer',
        'tags' => 'array',
        'metadata' => 'array',
    ];

    // Nanoid configuration
    protected int $nanoidSize = 21;

    // Cache configuration
    protected $rememberFor = 1800; // 30 minutes

    protected $rememberCacheTag = ['events'];

    // Timezone configuration
    protected $timezoneInclude = ['event_date', 'registration_deadline'];

    protected $userTimezoneFields = ['event_date', 'registration_deadline'];

    // Filter configuration
    protected $filterTypes = [
        'status' => 'exact',
        'name' => 'like',
        'description' => 'contains',
        'price' => 'range',
        'event_date' => 'date',
    ];

    protected $filterAliases = [
        'event_name' => 'name',
        'is_active' => 'status',
    ];

    // Event handlers
    protected $events = [
        'creating' => 'handleCreating',
        'created' => 'handleCreated',
        'updating' => 'handleUpdating',
    ];

    public function handleCreating($model)
    {
        if (!$model->status) {
            $model->status = 'draft';
        }
        if (!$model->organizer_id) {
            $model->organizer_id = auth()->id() ?? 1;
        }
    }

    public function handleCreated($model)
    {
        event('event.created', $model);
        Cache::tags(['events'])->flush();
    }

    public function handleUpdating($model)
    {
        if ($model->isDirty('status') && $model->status === 'published') {
            event('event.published', $model);
        }
    }

    // Relationships
    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function category()
    {
        return $this->belongsTo(EventCategory::class, 'category_id');
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class, 'event_id');
    }

    public function reviews()
    {
        return $this->hasMany(EventReview::class, 'event_id');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>', now());
    }

    public function scopePremium($query)
    {
        return $query->where('price', '>', 100);
    }
}

class EventCategory extends BrightModel
{
    protected $table = 'event_categories';

    protected $fillable = ['name', 'slug', 'description', 'color'];

    public function events()
    {
        return $this->hasMany(EventManagementSystem::class, 'category_id');
    }
}

class EventRegistration extends BrightModel
{
    protected $table = 'event_registrations';

    protected $fillable = [
        'event_id', 'user_id', 'attendee_name', 'attendee_email',
        'registration_date', 'status', 'payment_status', 'amount_paid',
    ];

    protected $casts = [
        'registration_date' => 'datetime',
        'amount_paid' => 'decimal:2',
    ];

    public function event()
    {
        return $this->belongsTo(EventManagementSystem::class, 'event_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

class EventReview extends BrightModel
{
    protected $table = 'event_reviews';

    protected $fillable = [
        'event_id', 'user_id', 'rating', 'review_text', 'is_verified',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_verified' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(EventManagementSystem::class, 'event_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

beforeEach(function () {
    // Create comprehensive database schema
    Schema::create('users', function ($table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('password');
        $table->timestamps();
    });

    Schema::create('event_categories', function ($table) {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->text('description')->nullable();
        $table->string('color', 7)->default('#3498db');
        $table->timestamps();
    });

    Schema::create('events', function ($table) {
        $table->string('id', 21)->primary(); // Nanoid
        $table->string('name');
        $table->text('description')->nullable();
        $table->timestamp('event_date');
        $table->timestamp('registration_deadline');
        $table->integer('max_attendees')->default(100);
        $table->decimal('price', 10, 2)->default(0);
        $table->enum('status', ['draft', 'published', 'cancelled', 'completed'])->default('draft');
        $table->unsignedBigInteger('category_id');
        $table->unsignedBigInteger('organizer_id');
        $table->string('location');
        $table->json('tags')->nullable();
        $table->json('metadata')->nullable();
        $table->timestamps();

        $table->foreign('category_id')->references('id')->on('event_categories');
        $table->foreign('organizer_id')->references('id')->on('users');
        $table->index(['status', 'event_date']);
        $table->index(['category_id', 'status']);
    });

    \Schema::create('event_registrations', function ($table) {
        $table->id();
        $table->string('event_id', 21);
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('attendee_name');
        $table->string('attendee_email');
        $table->timestamp('registration_date');
        $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
        $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid');
        $table->decimal('amount_paid', 10, 2)->default(0);
        $table->timestamps();

        $table->foreign('event_id')->references('id')->on('events');
        $table->foreign('user_id')->references('id')->on('users');
        $table->unique(['event_id', 'attendee_email']);
    });

    \Schema::create('event_reviews', function ($table) {
        $table->id();
        $table->string('event_id', 21);
        $table->unsignedBigInteger('user_id');
        $table->integer('rating')->min(1)->max(5);
        $table->text('review_text')->nullable();
        $table->boolean('is_verified')->default(false);
        $table->timestamps();

        $table->foreign('event_id')->references('id')->on('events');
        $table->foreign('user_id')->references('id')->on('users');
        $table->unique(['event_id', 'user_id']);
    });

    // Seed comprehensive test data
    $user = User::create([
        'name' => 'Test Organizer',
        'email' => 'organizer@test.com',
        'password' => bcrypt('password'),
    ]);

    EventCategory::create([
        'name' => 'Technology',
        'slug' => 'technology',
        'description' => 'Tech events',
    ]);

    $event = new EventManagementSystem;
    $event->id = (new \Hidehalo\Nanoid\Client)->generateId(21);
    $event->name = 'Sample Event';
    $event->description = 'A sample event for testing';
    $event->category_id = 1;
    $event->organizer_id = $user->id;
    $event->event_date = '2024-06-01 10:00:00';
    $event->registration_deadline = '2024-05-25 23:59:59';
    $event->location = 'Test Venue';
    $event->max_attendees = 100;
    $event->price = 50.00;
    $event->status = 'published';
    $event->save();
});

afterEach(function () {
    \Schema::dropIfExists('event_reviews');
    \Schema::dropIfExists('event_registrations');
    \Schema::dropIfExists('events');
    \Schema::dropIfExists('event_categories');
    \Schema::dropIfExists('users');
});

function seedTestData()
{
    // Create users
    $organizer1 = User::create([
        'name' => 'Tech Conference Organizer',
        'email' => 'organizer@techconf.com',
        'password' => bcrypt('password'),
    ]);

    $organizer2 = User::create([
        'name' => 'Workshop Leader',
        'email' => 'leader@workshops.com',
        'password' => bcrypt('password'),
    ]);

    $attendee1 = User::create([
        'name' => 'John Developer',
        'email' => 'john@developer.com',
        'password' => bcrypt('password'),
    ]);

    // Create categories
    $techCategory = EventCategory::create([
        'name' => 'Technology',
        'slug' => 'technology',
        'description' => 'Tech conferences and workshops',
        'color' => '#2563eb',
    ]);

    $businessCategory = EventCategory::create([
        'name' => 'Business',
        'slug' => 'business',
        'description' => 'Business and entrepreneurship events',
        'color' => '#059669',
    ]);

    // Mock user timezone
    Resolver::shouldReceive('timezone')
        ->andReturn('America/New_York');

    // Create events
    $event1 = EventManagementSystem::create([
        'name' => 'Laravel Advanced Workshop',
        'description' => 'Deep dive into Laravel advanced features',
        'event_date' => '2024-03-15 14:00:00', // User timezone (EST)
        'registration_deadline' => '2024-03-10 23:59:59',
        'max_attendees' => 50,
        'price' => 199.99,
        'status' => 'published',
        'category_id' => $techCategory->id,
        'organizer_id' => $organizer1->id,
        'location' => 'San Francisco, CA',
        'tags' => ['laravel', 'php', 'workshop', 'advanced'],
        'metadata' => [
            'difficulty' => 'advanced',
            'prerequisites' => ['PHP', 'Laravel basics'],
            'materials_provided' => true,
        ],
    ]);

    $event2 = EventManagementSystem::create([
        'name' => 'Startup Pitch Competition',
        'description' => 'Present your startup idea to investors',
        'event_date' => '2024-04-20 18:00:00',
        'registration_deadline' => '2024-04-15 17:00:00',
        'max_attendees' => 100,
        'price' => 25.00,
        'status' => 'published',
        'category_id' => $businessCategory->id,
        'organizer_id' => $organizer2->id,
        'location' => 'New York, NY',
        'tags' => ['startup', 'pitch', 'competition', 'investment'],
        'metadata' => [
            'prize_pool' => 10000,
            'judges_count' => 5,
            'presentation_time' => 5,
        ],
    ]);

    $event3 = EventManagementSystem::create([
        'name' => 'React Masterclass',
        'description' => 'Master React development with hooks and context',
        'event_date' => '2024-05-10 10:00:00',
        'registration_deadline' => '2024-05-05 23:59:59',
        'max_attendees' => 75,
        'price' => 299.99,
        'status' => 'draft',
        'category_id' => $techCategory->id,
        'organizer_id' => $organizer1->id,
        'location' => 'Austin, TX',
        'tags' => ['react', 'javascript', 'hooks', 'frontend'],
        'metadata' => [
            'difficulty' => 'intermediate',
            'duration_hours' => 8,
            'certification' => true,
        ],
    ]);

    // Create registrations
    EventRegistration::create([
        'event_id' => $event1->id,
        'user_id' => $attendee1->id,
        'attendee_name' => 'John Developer',
        'attendee_email' => 'john@developer.com',
        'registration_date' => now()->subDays(5),
        'status' => 'confirmed',
        'payment_status' => 'paid',
        'amount_paid' => 199.99,
    ]);

    EventRegistration::create([
        'event_id' => $event2->id,
        'attendee_name' => 'Jane Entrepreneur',
        'attendee_email' => 'jane@startup.com',
        'registration_date' => now()->subDays(2),
        'status' => 'pending',
        'payment_status' => 'unpaid',
        'amount_paid' => 0,
    ]);

    // Create reviews
    EventReview::create([
        'event_id' => $event1->id,
        'user_id' => $attendee1->id,
        'rating' => 5,
        'review_text' => 'Excellent workshop! Learned so much about Laravel.',
        'is_verified' => true,
    ]);
}

describe('Complete Event Management System Integration', function () {
    test('creates event with all features working together', function () {
        Event::fake();
        Cache::flush();

        $category = EventCategory::first();

        // Create event with all Bright features
        $event = EventManagementSystem::create([
            'name' => 'Full Stack Development Bootcamp',
            'description' => 'Complete web development training',
            'event_date' => '2024-06-15 09:00:00', // User timezone
            'registration_deadline' => '2024-06-10 23:59:59',
            'max_attendees' => 30,
            'price' => 599.99,
            'category_id' => $category->id,
            'location' => 'Seattle, WA',
            'tags' => ['fullstack', 'bootcamp', 'web', 'training'],
            'metadata' => ['duration_weeks' => 12, 'job_placement' => true],
        ]);

        // Verify Nanoid generation
        expect($event->id)->toBeString()->toHaveLength(21);

        // Verify event handlers triggered
        expect($event->status)->toBe('draft'); // Set by creating handler
        expect($event->organizer_id)->toBe(1); // Set by creating handler

        // Verify timezone storage conversion
        $dbValue = \DB::table('events')->where('id', $event->id)->value('event_date');
        $utcDate = Carbon::parse($dbValue, 'UTC');
        expect($utcDate->format('H:i'))->toBe('14:00'); // 09:00 EST = 14:00 UTC

        // Verify custom event dispatched
        Event::assertDispatched('event.created');
    });

    test('comprehensive filtering and search functionality', function () {
        // Complex filtering across multiple criteria
        $events = EventManagementSystem::filter([
            'filter' => [
                'status' => ['published', 'draft'],
                'category_id' => EventCategory::where('slug', 'technology')->first()->id,
            ],
            'lfilter' => [
                'name' => 'workshop',
                'description' => 'laravel',
            ],
            'parse' => [
                'price:gte:100',
                'max_attendees:lt:100',
            ],
            'dfilter' => [
                'event_date' => '2024',
            ],
        ])->get();

        expect($events)->toHaveCount(2); // Laravel Advanced Workshop + React Masterclass
        expect($events->pluck('name')->toArray())
            ->toContain('Laravel Advanced Workshop', 'React Masterclass');
    });

    test('advanced querying with relationships and caching', function () {
        Cache::flush();

        // Complex query with relationships, filtering, and caching
        $results = EventManagementSystem::with(['category', 'organizer', 'registrations', 'reviews'])
            ->published()
            ->filter([
                'parse' => ['price:between:50,300'],
            ])
            ->remember(1800, 'premium-events', ['events', 'categories'])
            ->orderByCustom('price', [199.99, 25.00]) // Custom ordering
            ->complexPaginate(10);

        expect($results->total())->toBe(2);

        // Verify relationships loaded
        $firstEvent = $results->items()[0];
        expect($firstEvent->relationLoaded('category'))->toBeTrue();
        expect($firstEvent->relationLoaded('organizer'))->toBeTrue();
        expect($firstEvent->category->name)->not->toBeEmpty();

        // Verify caching
        expect(Cache::has('premium-events'))->toBeTrue();

        // Test relationship flattening
        $flattened = $firstEvent->flatten();
        expect($flattened->category_name ?? $flattened->name)->not->toBeEmpty();
    });

    test('batch operations with event processing', function () {
        Event::fake();

        // Batch create multiple events
        $batchEvents = [];
        for ($i = 1; $i <= 10; $i++) {
            $batchEvents[] = [
                'id' => \Hidehalo\Nanoid\Client::generateId(21),
                'name' => "Batch Event $i",
                'description' => "Description for batch event $i",
                'event_date' => Carbon::now()->addDays($i * 7),
                'registration_deadline' => Carbon::now()->addDays(($i * 7) - 3),
                'max_attendees' => 50 + ($i * 10),
                'price' => 99.99 + ($i * 25),
                'status' => $i % 2 === 0 ? 'published' : 'draft',
                'category_id' => EventCategory::first()->id,
                'organizer_id' => User::first()->id,
                'location' => "City $i",
                'tags' => json_encode(['batch', "event$i"]),
                'metadata' => json_encode(['batch_number' => $i]),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        EventManagementSystem::batch()->insert($batchEvents);

        // Verify batch creation
        $totalEvents = EventManagementSystem::count();
        expect($totalEvents)->toBe(13); // 3 original + 10 batch

        // Filter batch-created events
        $batchCreated = EventManagementSystem::filter([
            'lfilter' => ['name' => 'Batch Event'],
            'filter' => ['status' => 'published'],
        ])->get();

        expect($batchCreated)->toHaveCount(5); // Even numbered events
    });

    test('async processing with complex operations', function () {
        Queue::fake();

        // Process events asynchronously with filtering and relationships
        $query = EventManagementSystem::with(['registrations', 'reviews'])
            ->filter([
                'filter' => ['status' => 'published'],
                'parse' => ['price:gte:100'],
            ])
            ->async('premium-event-processing', 'high-priority');

        $events = $query->get();

        expect($events)->toHaveCount(1); // Laravel Advanced Workshop
        expect($events->first()->registrations)->not->toBeEmpty();
    });

    test('timezone handling in real-world scenario', function () {
        // Test different user timezones
        Resolver::shouldReceive('timezone')
            ->andReturn('America/Los_Angeles'); // PST

        $event = EventManagementSystem::find(EventManagementSystem::first()->id);

        // Should display in PST (3 hours behind EST)
        $eventDate = $event->event_date;
        expect($eventDate->timezone->getName())->toBe('America/Los_Angeles');
        expect($eventDate->format('H:i'))->toBe('11:00'); // 14:00 EST = 11:00 PST

        // Update event from PST timezone
        $event->update([
            'event_date' => '2024-03-15 15:00:00', // 3 PM PST
        ]);

        // Check database value is correct UTC
        $dbValue = \DB::table('events')->where('id', $event->id)->value('event_date');
        $utcDate = Carbon::parse($dbValue, 'UTC');
        expect($utcDate->format('H:i'))->toBe('23:00'); // 15:00 PST = 23:00 UTC
    });

    test('performance optimization features', function () {
        // Test memory-efficient processing
        $processedCount = 0;
        $eventData = [];

        EventManagementSystem::with('category')
            ->lazyMap(2, function ($event) use (&$processedCount, &$eventData) {
                $processedCount++;
                $eventData[] = [
                    'id' => $event->id,
                    'name' => $event->name,
                    'category' => $event->category->name,
                    'price' => $event->price,
                    'attendees_ratio' => $event->registrations->count() / $event->max_attendees,
                ];

                return $eventData[count($eventData) - 1];
            })
            ->collect();

        expect($processedCount)->toBe(3);
        expect($eventData)->toHaveCount(3);
        expect($eventData[0])->toHaveKey('attendees_ratio');
    });

    test('comprehensive reporting and analytics', function () {
        Cache::flush();

        // Generate complex analytics report
        $report = EventManagementSystem::with(['category', 'registrations', 'reviews'])
            ->filter([
                'filter' => ['status' => 'published'],
                'dfilter' => ['event_date' => '2024'],
            ])
            ->remember(3600, 'events-report', ['events', 'analytics'])
            ->get()
            ->map(function ($event) {
                return [
                    'event_id' => $event->id,
                    'name' => $event->name,
                    'category' => $event->category->name,
                    'price' => $event->price,
                    'registrations_count' => $event->registrations->count(),
                    'capacity_utilization' => $event->registrations->count() / $event->max_attendees,
                    'average_rating' => $event->reviews->avg('rating'),
                    'revenue' => $event->registrations->where('payment_status', 'paid')->sum('amount_paid'),
                    'conversion_rate' => $event->registrations->where('status', 'confirmed')->count() /
                                      max(1, $event->registrations->count()),
                ];
            });

        expect($report)->toHaveCount(2);
        expect($report[0])->toHaveKey('capacity_utilization');
        expect($report[0])->toHaveKey('revenue');
        expect($report[0]['average_rating'])->toBe(5.0);

        // Verify caching
        expect(Cache::has('events-report'))->toBeTrue();
    });

    test('file export functionality', function () {
        $tempFile = tempnam(sys_get_temp_dir(), 'events_export') . '.csv';

        // Export events with comprehensive data
        EventManagementSystem::with(['category', 'organizer'])
            ->published()
            ->outfile($tempFile, 'csv')
            ->get();

        expect(file_exists($tempFile))->toBeTrue();

        $content = file_get_contents($tempFile);
        expect($content)->toContain('Laravel Advanced Workshop');
        expect($content)->toContain('Startup Pitch Competition');

        unlink($tempFile);
    });

    test('real-time event updates and notifications', function () {
        Event::fake();

        $event = EventManagementSystem::first();

        // Update event status to published (should trigger notification)
        $event->update(['status' => 'published']);

        Event::assertDispatched('event.published');

        // Test cache invalidation
        expect(Cache::tags(['events'])->get('test-key'))->toBeNull();
    });

    test('complete CRUD operations with all features', function () {
        Event::fake();
        Cache::flush();

        // CREATE with all features
        $event = EventManagementSystem::create([
            'name' => 'CRUD Test Event',
            'description' => 'Testing complete CRUD operations',
            'event_date' => '2024-07-01 16:00:00',
            'registration_deadline' => '2024-06-25 23:59:59',
            'max_attendees' => 40,
            'price' => 149.99,
            'category_id' => EventCategory::first()->id,
            'location' => 'Portland, OR',
            'tags' => ['crud', 'test', 'demo'],
            'metadata' => ['test_mode' => true],
        ]);

        // Verify creation with all features
        expect($event->id)->toHaveLength(21); // Nanoid
        expect($event->status)->toBe('draft'); // Event handler
        Event::assertDispatched('event.created');

        // READ with caching and relationships
        $retrieved = EventManagementSystem::with(['category', 'organizer'])
            ->remember(60, "event-{$event->id}")
            ->find($event->id);

        expect($retrieved->name)->toBe('CRUD Test Event');
        expect($retrieved->relationLoaded('category'))->toBeTrue();

        // UPDATE with timezone handling
        $retrieved->update([
            'status' => 'published',
            'event_date' => '2024-07-01 17:00:00', // New time in user timezone
        ]);

        Event::assertDispatched('event.published');

        // Verify timezone conversion
        $dbDate = \DB::table('events')->where('id', $event->id)->value('event_date');
        expect(Carbon::parse($dbDate, 'UTC')->format('H:i'))->toBe('22:00'); // 17:00 EST = 22:00 UTC

        // DELETE with cascade effects
        $eventId = $event->id;
        $event->delete();

        expect(EventManagementSystem::find($eventId))->toBeNull();
    });
});

describe('Error Handling and Edge Cases', function () {
    test('handles missing relationships gracefully', function () {
        $event = EventManagementSystem::create([
            'name' => 'Orphaned Event',
            'description' => 'Event without proper relationships',
            'event_date' => '2024-08-01 10:00:00',
            'registration_deadline' => '2024-07-25 23:59:59',
            'max_attendees' => 50,
            'price' => 99.99,
            'category_id' => 9999, // Non-existent category
            'location' => 'Virtual',
        ]);

        // Should not throw exceptions
        expect($event->exists)->toBeTrue();

        // Accessing missing relationship should handle gracefully
        $eventWithCategory = EventManagementSystem::with('category')->find($event->id);
        expect($eventWithCategory->category)->toBeNull();
    });

    test('handles large dataset operations efficiently', function () {
        // Create large dataset
        $largeDataset = [];
        for ($i = 1; $i <= 100; $i++) {
            $largeDataset[] = [
                'id' => \Hidehalo\Nanoid\Client::generateId(21),
                'name' => "Large Dataset Event $i",
                'description' => "Description $i",
                'event_date' => Carbon::now()->addDays($i),
                'registration_deadline' => Carbon::now()->addDays($i - 3),
                'max_attendees' => 100,
                'price' => 50.00,
                'status' => 'published',
                'category_id' => EventCategory::first()->id,
                'organizer_id' => User::first()->id,
                'location' => "Location $i",
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Should handle large batch operations
        expect(fn () => EventManagementSystem::batch()->insert($largeDataset))
            ->not->toThrow(\Exception::class);

        // Memory-efficient processing
        $processedCount = 0;
        EventManagementSystem::lazyMap(25, function ($event) use (&$processedCount) {
            $processedCount++;

            return ['id' => $event->id];
        })->collect();

        expect($processedCount)->toBe(103); // 3 original + 100 new
    });

    test('concurrent access and cache consistency', function () {
        Cache::flush();

        $event = EventManagementSystem::first();

        // Simulate concurrent cache access
        $cached1 = EventManagementSystem::remember(60, 'concurrent-test')->find($event->id);
        $cached2 = EventManagementSystem::remember(60, 'concurrent-test')->find($event->id);

        expect($cached1->id)->toBe($cached2->id);
        expect($cached1->name)->toBe($cached2->name);

        // Cache invalidation should work
        Cache::forget('concurrent-test');
        $fresh = EventManagementSystem::find($event->id);
        expect($fresh->id)->toBe($event->id);
    });
});

describe('Performance Benchmarks', function () {
    test('query performance with all features enabled', function () {
        $startTime = microtime(true);

        // Complex query with all features
        $results = EventManagementSystem::with(['category', 'organizer', 'registrations.user', 'reviews'])
            ->filter([
                'filter' => ['status' => ['published', 'draft']],
                'lfilter' => ['name' => 'workshop'],
                'parse' => ['price:gte:50'],
            ])
            ->remember(60, 'performance-test')
            ->orderByColumn('price', 'desc')
            ->complexPaginate(10);

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        expect($results)->not->toBeNull();
        expect($executionTime)->toBeLessThan(1.0); // Should execute in under 1 second

        // Memory usage should be reasonable
        $memoryUsage = memory_get_usage();
        expect($memoryUsage)->toBeLessThan(50 * 1024 * 1024); // Less than 50MB
    });
});
