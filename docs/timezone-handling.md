# Timezone Handling Traits

This package provides two complementary traits for comprehensive timezone handling in Laravel models:

1. **`Timezone`** - Converts database timestamps to user timezone for display
2. **`TimezoneStorage`** - Converts user-selected datetimes from user timezone to system timezone for storage

## Quick Start

```php
<?php

namespace App\Models;

use Diviky\Bright\Database\Eloquent\Concerns\Timezone;
use Diviky\Bright\Database\Eloquent\Concerns\TimezoneStorage;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use Timezone, TimezoneStorage;

    // Define which fields are user-selected and should be converted on storage
    protected $timezoneFields = [
        'event_date',
        'registration_deadline',
        'start_time',
        'end_time'
    ];

    // Regular Laravel casts still apply
    protected $casts = [
        'event_date' => 'datetime',
        'registration_deadline' => 'datetime',
        'start_time' => 'datetime', 
        'end_time' => 'datetime',
    ];
}
```

## How It Works

### Data Flow

```
Frontend (User TZ) → Laravel (User TZ → UTC) → Database (UTC)
                                     ↓
Frontend (User TZ) ← Laravel (UTC → User TZ) ← Database (UTC)
```

### Storage Flow (TimezoneStorage trait)
1. User selects datetime in their timezone (e.g., "2024-01-15 14:00" in "America/New_York")
2. `setAttribute()` detects this is a user timezone field
3. Converts to system timezone (UTC): "2024-01-15 19:00"
4. Stores in database as UTC

### Retrieval Flow (Timezone trait)
1. Database returns UTC datetime: "2024-01-15 19:00"
2. `getAttribute()` detects this is a timezone-convertible field
3. Converts to user timezone: "2024-01-15 14:00 EST"
4. Returns to user in their timezone

## Configuration Options

### Model-Level Configuration

```php
class Event extends Model
{
    use Timezone, TimezoneStorage;

    // Fields that should be converted from user timezone on storage
    protected $timezoneFields = ['event_date', 'deadline'];

    // Control which fields get timezone conversion on retrieval
    protected $timezoneInclude = ['event_date', 'deadline'];
    // OR exclude specific fields
    protected $timezoneExclude = ['created_at', 'updated_at'];
    
    // Disable timezone conversion entirely for this model
    protected $timezoneConversion = false;
}
```

### Runtime Configuration

```php
// Temporarily disable storage conversion
$event = new Event();
$event->disableTimezoneStorage();
$event->event_date = '2024-01-15 14:00:00'; // Stored as-is

// Add fields dynamically  
$event->addTimezoneStorageFields(['new_field', 'another_field']);

// Remove fields dynamically
$event->removeTimezoneStorageFields('field_to_remove');

// Replace all fields
$event->setTimezoneStorageFields(['only_this_field']);

// Disable retrieval conversion
$event->withoutTimezoneConversion();
```

## Usage Examples

### Basic CRUD Operations

```php
// Creating with user timezone data
$event = Event::create([
    'name' => 'Conference',
    'event_date' => '2024-01-15 14:00:00', // User's timezone
    'created_at' => now(), // System timezone (no conversion)
]);

// Reading with automatic conversion to user timezone
$event = Event::find(1);
echo $event->event_date; // Displayed in user's timezone

// Updating user-selected dates
$event->update([
    'event_date' => '2024-01-16 15:00:00', // Converted from user timezone
]);
```

### Conditional Conversion

```php
// Only convert specific instances
$event = Event::find(1);
$event->addTimezoneStorageFields('special_date');
$event->special_date = $userSelectedDate;

// Or disable for system operations
$event->disableTimezoneStorage();
$event->processed_at = now(); // Stored in system timezone
```

### API Endpoints

```php
// Controller handling user timezone data
class EventController extends Controller
{
    public function store(Request $request)
    {
        // Data from frontend comes in user timezone
        $event = Event::create($request->validated());
        // Automatically converted to UTC for storage
        
        return response()->json($event);
        // Automatically converted back to user timezone for response
    }
}
```

### Form Handling

```php
// Form Request with user timezone data
class CreateEventRequest extends FormRequest
{
    public function rules()
    {
        return [
            'event_date' => 'required|date|after:now',
            // Validation happens on user timezone values
        ];
    }
}
```

## Safety Features

### Automatic Fallbacks

1. **No User Timezone**: If user timezone is unavailable, no conversion occurs
2. **Invalid Dates**: Non-datetime values are ignored
3. **Disabled Conversion**: Conversion can be disabled at model or instance level
4. **Field Filtering**: Only specified fields are converted

### Error Prevention

```php
// Check if timezone storage is working
if ($event->isTimezoneStorageEnabled()) {
    // Conversion is active
}

// Check current convertible fields
$fields = $event->getCurrentTimezoneStorageFields();

// Clear caches if timezone changes
$event->clearTimezoneCache();
$event->clearTimezoneStorageCache();
```

## Best Practices

### 1. Define Fields Clearly
```php
// ✅ Good: Explicit field definition
protected $timezoneFields = [
    'appointment_time',
    'deadline',
    'user_selected_date'
];

// ❌ Avoid: Converting system timestamps
// Don't include: created_at, updated_at, processed_at
```

### 2. Consistent Configuration
```php
// ✅ Good: Same fields for storage and retrieval
protected $timezoneFields = ['event_date'];
protected $timezoneInclude = ['event_date'];
```

### 3. Handle Missing Timezone Gracefully
```php
// The traits automatically handle cases where:
// - User is not authenticated
// - User timezone is not set
// - Resolver::timezone() returns null
```

### 4. Test with Different Timezones
```php
// In tests, set specific timezones
config(['app.timezone' => 'UTC']);
// Mock user timezone
$this->mockUserTimezone('America/New_York');
```

## Troubleshooting

### Common Issues

**Issue**: Dates are stored in user timezone instead of UTC
**Solution**: Check that `timezoneFields` includes the field and user timezone is available

**Issue**: Dates are not converted on display  
**Solution**: Ensure the `Timezone` trait is used and field is not in `timezoneExclude`

**Issue**: API returns wrong timezone
**Solution**: Verify `Resolver::timezone()` returns correct user timezone

### Debugging

```php
// Check if conversion should happen
$shouldConvert = $event->shouldConvertTimezoneOnStorage('event_date', $value);

// Check user timezone
$timezone = $event->getTimezoneForStorage();

// Check field configuration
$fields = $event->getCurrentTimezoneStorageFields();
```

## Integration with Existing Code

### Gradual Adoption
1. Add traits to models
2. Define `timezoneFields` for user-input dates only
3. Test with existing data
4. Gradually expand to more fields

### Backward Compatibility
- Existing data remains unchanged
- Only new/updated records get converted
- Can disable conversion if needed

## Advanced Usage

### Custom Timezone Resolution

If you need custom timezone resolution logic, you can override the `getTimezoneForStorage()` method:

```php
class Event extends Model
{
    use Timezone, TimezoneStorage;

    protected function getTimezoneForStorage(): ?string
    {
        // Custom logic to determine user timezone
        return auth()->user()?->timezone ?? config('app.timezone');
    }
}
```

### Model-Specific Timezone

```php
class Event extends Model
{
    use Timezone, TimezoneStorage;

    // Override to use event-specific timezone
    protected function convertFromTimezone(mixed $value): Carbon
    {
        $eventTimezone = $this->timezone ?? $this->getTimezoneForStorage();
        $systemTimezone = config('app.timezone', 'UTC');

        $carbonDate = $this->toCarbonInstance($value);
        
        if ($eventTimezone) {
            $carbonDate = $carbonDate->setTimezone($eventTimezone);
        }

        return $carbonDate->setTimezone($systemTimezone);
    }
}
```

### Bulk Operations

For bulk operations where timezone conversion might be expensive:

```php
// Disable conversion for bulk operations
Event::disableTimezoneStorage()
    ->insert($bulkData);

// Or use raw queries for performance
DB::table('events')->insert($utcConvertedData);
```

This solution provides complete timezone handling while maintaining flexibility and safety.
