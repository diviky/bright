<?php

declare(strict_types=1);

namespace Diviky\Bright\Tests\Database;

use App\Models\User;
use Carbon\Carbon;
use Diviky\Bright\Database\Eloquent\Concerns\Timezone;
use Diviky\Bright\Database\Eloquent\Concerns\TimezoneStorage;
use Diviky\Bright\Services\Resolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(\Diviky\Bright\Tests\TestCase::class, RefreshDatabase::class);

// Test Model for timezone functionality
class TestEvent extends Model
{
    use Timezone, TimezoneStorage;

    protected $table = 'test_events';

    protected $fillable = ['name', 'event_date', 'created_at', 'updated_at', 'system_date'];

    protected $casts = [
        'event_date' => 'datetime',
        'system_date' => 'datetime',
    ];

    // Configure timezone fields
    protected $userTimezoneFields = ['event_date']; // Storage conversion

    protected $timezoneInclude = ['event_date'];    // Retrieval conversion
}

beforeEach(function () {
    // Create test table
    Schema::create('test_events', function ($table) {
        $table->id();
        $table->string('name');
        $table->timestamp('event_date')->nullable();
        $table->timestamp('system_date')->nullable();
        $table->timestamps();
    });

    // Set application timezone to UTC
    config(['app.timezone' => 'UTC']);
});

afterEach(function () {
    Schema::dropIfExists('test_events');
});

describe('Timezone Trait', function () {
    test('converts datetime attributes to user timezone on retrieval', function () {
        // Mock user timezone
        Resolver::resolveTimezone(fn () => 'America/New_York');

        // Create event in UTC
        $event = new TestEvent;
        $event->name = 'Test Event';
        $event->event_date = Carbon::createFromFormat('Y-m-d H:i:s', '2024-01-15 19:00:00', 'UTC');
        $event->save();

        // Retrieve event - should convert to user timezone
        $retrieved = TestEvent::first();

        expect($retrieved->event_date)
            ->toBeInstanceOf(Carbon::class)
            ->and($retrieved->event_date->timezone->getName())
            ->toBe('America/New_York')
            ->and($retrieved->event_date->format('H:i'))
            ->toBe('14:00'); // 19:00 UTC = 14:00 EST
    });

    test('respects timezone include configuration', function () {
        Resolver::resolveTimezone(fn () => 'America/New_York');

        $event = TestEvent::create([
            'name' => 'Test Event',
            'event_date' => '2024-01-15 19:00:00',
            'system_date' => '2024-01-15 19:00:00',
        ]);

        $retrieved = TestEvent::first();

        // event_date should be converted (in timezoneInclude)
        expect($retrieved->event_date->timezone->getName())
            ->toBe('America/New_York');

        // system_date should NOT be converted (not in timezoneInclude)
        expect($retrieved->system_date->timezone->getName())
            ->toBe('UTC');
    });

    test('handles missing user timezone gracefully', function () {
        Resolver::resolveTimezone(fn () => null);

        $event = TestEvent::create([
            'name' => 'Test Event',
            'event_date' => '2024-01-15 19:00:00',
        ]);

        $retrieved = TestEvent::first();

        // Should remain in UTC when no user timezone
        expect($retrieved->event_date->timezone->getName())
            ->toBe('UTC');
    });

    test('can disable timezone conversion temporarily', function () {
        Resolver::resolveTimezone(fn () => 'America/New_York');

        $event = TestEvent::create([
            'name' => 'Test Event',
            'event_date' => '2024-01-15 19:00:00',
        ]);

        // Disable conversion
        $retrieved = TestEvent::first()->withoutTimezoneConversion();

        expect($retrieved->event_date->timezone->getName())
            ->toBe('UTC');
    });

    test('can re-enable timezone conversion', function () {
        Resolver::resolveTimezone(fn () => 'America/New_York');

        $event = TestEvent::create([
            'name' => 'Test Event',
            'event_date' => '2024-01-15 19:00:00',
        ]);

        $retrieved = TestEvent::first()
            ->withoutTimezoneConversion()
            ->withTimezoneConversion();

        expect($retrieved->event_date->timezone->getName())
            ->toBe('America/New_York');
    });

    test('clears timezone cache', function () {
        Resolver::resolveTimezone(fn () => 'America/New_York');

        $event = new TestEvent;
        $event->clearTimezoneCache();

        // Should call Resolver again after cache clear
        $timezone = Resolver::timezone();
        expect($timezone)->toBe('America/New_York');
    });
});

describe('TimezoneStorage Trait', function () {
    test('converts user timezone to system timezone on storage', function () {
        Resolver::resolveTimezone(fn () => 'America/New_York');

        // Create event with user timezone datetime
        $event = TestEvent::create([
            'name' => 'Test Event',
            'event_date' => '2024-01-15 14:00:00', // User timezone (EST)
        ]);

        // Check database value is in UTC
        $dbValue = DB::table('test_events')
            ->where('id', $event->id)
            ->value('event_date');

        $utcDate = Carbon::parse($dbValue, 'UTC');
        expect($utcDate->format('H:i'))->toBe('19:00'); // 14:00 EST = 19:00 UTC
    });

    test('only converts configured user timezone fields', function () {
        Resolver::resolveTimezone(fn () => 'America/New_York');

        $now = Carbon::now('UTC');

        $event = TestEvent::create([
            'name' => 'Test Event',
            'event_date' => '2024-01-15 14:00:00', // Should convert
            'system_date' => $now,                  // Should NOT convert
        ]);

        // Check event_date was converted
        $eventDateDb = DB::table('test_events')
            ->where('id', $event->id)
            ->value('event_date');
        $eventDateUtc = Carbon::parse($eventDateDb, 'UTC');
        expect($eventDateUtc->format('H:i'))->toBe('19:00');

        // Check system_date was NOT converted
        $systemDateDb = DB::table('test_events')
            ->where('id', $event->id)
            ->value('system_date');
        $systemDateUtc = Carbon::parse($systemDateDb, 'UTC');
        expect($systemDateUtc->equalTo($now))->toBeTrue();
    });

    test('handles missing user timezone gracefully on storage', function () {
        Resolver::resolveTimezone(fn () => null);

        $originalTime = '2024-01-15 14:00:00';

        $event = TestEvent::create([
            'name' => 'Test Event',
            'event_date' => $originalTime,
        ]);

        // Without user timezone, should store as-is
        $dbValue = DB::table('test_events')
            ->where('id', $event->id)
            ->value('event_date');

        expect(Carbon::parse($dbValue)->format('Y-m-d H:i:s'))
            ->toBe($originalTime);
    });

    test('can disable timezone storage conversion', function () {
        Resolver::resolveTimezone(fn () => 'America/New_York');

        $event = new TestEvent;
        $event->disableTimezoneStorage();
        $event->name = 'Test Event';
        $event->event_date = '2024-01-15 14:00:00';
        $event->save();

        // Should store as-is when disabled
        $dbValue = DB::table('test_events')
            ->where('id', $event->id)
            ->value('event_date');

        expect(Carbon::parse($dbValue)->format('H:i'))->toBe('14:00');
    });

    test('can enable timezone storage conversion', function () {
        Resolver::resolveTimezone(fn () => 'America/New_York');

        $event = new TestEvent;
        $event->disableTimezoneStorage();
        $event->enableTimezoneStorage();
        $event->name = 'Test Event';
        $event->event_date = '2024-01-15 14:00:00';
        $event->save();

        // Should convert when re-enabled
        $dbValue = DB::table('test_events')
            ->where('id', $event->id)
            ->value('event_date');

        expect(Carbon::parse($dbValue, 'UTC')->format('H:i'))->toBe('19:00');
    });

    test('can dynamically add timezone storage fields', function () {
        Resolver::resolveTimezone(fn () => 'America/New_York');

        $event = new TestEvent;
        $event->addTimezoneStorageFields('system_date');
        $event->name = 'Test Event';
        $event->event_date = '2024-01-15 14:00:00';
        $event->system_date = '2024-01-15 15:00:00';
        $event->save();

        // Both fields should be converted
        $dbRecord = DB::table('test_events')
            ->where('id', $event->id)
            ->first();

        expect(Carbon::parse($dbRecord->event_date, 'UTC')->format('H:i'))->toBe('19:00');
        expect(Carbon::parse($dbRecord->system_date, 'UTC')->format('H:i'))->toBe('20:00');
    });

    test('can remove timezone storage fields', function () {
        Resolver::resolveTimezone(fn () => 'America/New_York');

        $event = new TestEvent;
        $event->removeTimezoneStorageFields('event_date');
        $event->name = 'Test Event';
        $event->event_date = '2024-01-15 14:00:00';
        $event->save();

        // Should NOT convert when removed
        $dbValue = DB::table('test_events')
            ->where('id', $event->id)
            ->value('event_date');

        expect(Carbon::parse($dbValue)->format('H:i'))->toBe('14:00');
    });

    test('can set timezone storage fields', function () {
        Resolver::resolveTimezone(fn () => 'America/New_York');

        $event = new TestEvent;
        $event->setTimezoneStorageFields(['system_date']); // Replace event_date with system_date
        $event->name = 'Test Event';
        $event->event_date = '2024-01-15 14:00:00';
        $event->system_date = '2024-01-15 15:00:00';
        $event->save();

        $dbRecord = DB::table('test_events')
            ->where('id', $event->id)
            ->first();

        // event_date should NOT convert (not in new list)
        expect(Carbon::parse($dbRecord->event_date)->format('H:i'))->toBe('14:00');

        // system_date should convert (in new list)
        expect(Carbon::parse($dbRecord->system_date, 'UTC')->format('H:i'))->toBe('20:00');
    });

    test('checks if timezone storage is enabled', function () {
        $event = new TestEvent;

        expect($event->isTimezoneStorageEnabled())->toBeTrue();

        $event->disableTimezoneStorage();
        expect($event->isTimezoneStorageEnabled())->toBeFalse();

        $event->enableTimezoneStorage();
        expect($event->isTimezoneStorageEnabled())->toBeTrue();
    });

    test('gets current timezone storage fields', function () {
        $event = new TestEvent;

        expect($event->getCurrentTimezoneStorageFields())
            ->toBe(['event_date']);

        $event->addTimezoneStorageFields('system_date');
        expect($event->getCurrentTimezoneStorageFields())
            ->toContain('event_date', 'system_date');
    });

    test('clears timezone storage cache', function () {
        Resolver::resolveTimezone(fn () => 'America/New_York');

        $event = new TestEvent;
        $event->clearTimezoneStorageCache();

        // Should call Resolver again after cache clear
        $timezone = $event->getTimezoneForStorage();
        expect($timezone)->toBe('America/New_York');
    });
});

describe('Bidirectional Timezone Handling', function () {
    test('handles complete user workflow correctly', function () {
        Resolver::resolveTimezone(fn () => 'America/New_York');

        // User creates event in their timezone
        $event = TestEvent::create([
            'name' => 'Conference',
            'event_date' => '2024-01-15 14:00:00', // 2 PM EST
        ]);

        // Database stores in UTC
        $dbValue = DB::table('test_events')
            ->where('id', $event->id)
            ->value('event_date');
        expect(Carbon::parse($dbValue, 'UTC')->format('H:i'))->toBe('19:00'); // 7 PM UTC

        // User retrieves event - shown in their timezone
        $retrieved = TestEvent::find($event->id);
        expect($retrieved->event_date->format('H:i'))->toBe('14:00'); // 2 PM EST
        expect($retrieved->event_date->timezone->getName())->toBe('America/New_York');

        // Update event
        $retrieved->update(['event_date' => '2024-01-15 16:00:00']); // 4 PM EST

        // Check database updated correctly
        $updatedDbValue = DB::table('test_events')
            ->where('id', $event->id)
            ->value('event_date');
        expect(Carbon::parse($updatedDbValue, 'UTC')->format('H:i'))->toBe('21:00'); // 9 PM UTC
    });

    test('handles different user timezones correctly', function () {
        // First user in EST
        Resolver::resolveTimezone(fn () => 'America/New_York');

        $event = TestEvent::create([
            'name' => 'Meeting',
            'event_date' => '2024-01-15 14:00:00', // 2 PM EST
        ]);

        // Second user in PST views same event
        Resolver::resolveTimezone(fn () => 'America/Los_Angeles');

        $retrieved = TestEvent::find($event->id);
        expect($retrieved->event_date->format('H:i'))->toBe('11:00'); // 11 AM PST
        expect($retrieved->event_date->timezone->getName())->toBe('America/Los_Angeles');
    });

    test('handles invalid datetime values gracefully', function () {
        Resolver::resolveTimezone(fn () => 'America/New_York');

        $event = new TestEvent;
        $event->name = 'Test Event';
        $event->event_date = 'invalid-date';

        // Should not throw exception
        expect(fn () => $event->save())->not->toThrow(\Exception::class);
    });
});
