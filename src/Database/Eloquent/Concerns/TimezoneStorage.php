<?php

namespace Diviky\Bright\Database\Eloquent\Concerns;

use Carbon\Carbon;
use Diviky\Bright\Services\Resolver;

trait TimezoneStorage
{
    /**
     * Cache for user timezone
     *
     * @var string|null
     */
    protected $timezoneStorageCache;

    /**
     * Enable/disable timezone storage conversion for this model instance
     *
     * @var bool
     */
    protected $timezoneStorageEnabled = true;

    /**
     * Fields that should be converted from user timezone to system timezone on storage
     *
     * @var array
     */
    protected $timezoneFields = [];

    /**
     * Override setAttribute to convert user timezone to system timezone for specified fields
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        // Only process if storage conversion is enabled and value is processable
        if ($this->shouldConvertTimezoneOnStorage($key, $value)) {
            $value = $this->convertFromTimezone($value);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Check if attribute should be converted from user timezone to system timezone on storage
     */
    protected function shouldConvertTimezoneOnStorage(string $key, mixed $value): bool
    {
        // Check if timezone storage conversion is disabled
        if (!$this->timezoneStorageEnabled) {
            return false;
        }

        // Check if this attribute is in the timezone storage fields
        if (!in_array($key, $this->getTimezoneFields())) {
            return false;
        }

        // Only convert if value is a datetime-like value
        if (!$this->isDateTimeValue($value)) {
            return false;
        }

        // Only convert if user timezone is available
        if (!$this->getTimezoneForStorage()) {
            return false;
        }

        return true;
    }

    /**
     * Convert datetime from user timezone to system timezone (UTC)
     */
    protected function convertFromTimezone(mixed $value): Carbon
    {
        $userTimezone = $this->getTimezoneForStorage();
        $systemTimezone = config('app.timezone', 'UTC');

        if (!$userTimezone) {
            // No user timezone available, return as-is
            return $this->toCarbonInstance($value);
        }

        // Convert to Carbon instance
        $carbonDate = $this->toCarbonInstance($value);

        // Create a new Carbon instance in the user timezone
        // This treats the input as a "naive" datetime in the user's timezone
        $userDate = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $carbonDate->format('Y-m-d H:i:s'),
            $userTimezone
        );

        // Convert to system timezone for storage
        return $userDate->setTimezone($systemTimezone);
    }

    /**
     * Convert value to Carbon instance
     */
    protected function toCarbonInstance(mixed $value): Carbon
    {
        if ($value instanceof Carbon) {
            return $value->clone();
        }

        if (is_string($value)) {
            // Handle various datetime formats including "Fri, Aug 29, 2025 11:15 AM"
            try {
                return Carbon::parse($value);
            } catch (\Exception $e) {
                // If parsing fails, try with createFromFormat for specific formats
                $formats = [
                    'D, M j, Y g:i A',  // "Fri, Aug 29, 2025 11:15 AM"
                    'Y-m-d H:i:s',      // "2025-08-29 11:15:00"
                    'Y-m-d\TH:i:s',     // "2025-08-29T11:15:00"
                    'd/m/Y H:i',        // "29/08/2025 11:15"
                    'm/d/Y g:i A',      // "08/29/2025 11:15 AM"
                ];

                foreach ($formats as $format) {
                    try {
                        return Carbon::createFromFormat($format, $value);
                    } catch (\Exception $e) {
                        continue;
                    }
                }

                // If all fails, throw original exception
                throw new \InvalidArgumentException("Unable to parse datetime: {$value}");
            }
        }

        // Handle other types (timestamp, etc.)
        return Carbon::parse($value);
    }

    /**
     * Check if value is a datetime-like value
     */
    protected function isDateTimeValue(mixed $value): bool
    {
        if ($value instanceof Carbon) {
            return true;
        }

        if (is_string($value)) {
            try {
                Carbon::parse($value);

                return true;
            } catch (\Exception $e) {
                return false;
            }
        }

        return false;
    }

    /**
     * Get user timezone for storage operations
     */
    protected function getTimezoneForStorage(): ?string
    {
        if ($this->timezoneStorageCache === null) {
            $this->timezoneStorageCache = Resolver::timezone();
        }

        return $this->timezoneStorageCache;
    }

    /**
     * Get the list of fields that should be converted from user timezone on storage
     */
    protected function getTimezoneFields(): array
    {
        // Check if userTimezoneFields is defined (this is the standard property name)
        if (property_exists($this, 'userTimezoneFields') && !empty($this->userTimezoneFields)) {
            return $this->userTimezoneFields;
        }

        // Fallback to timezoneFields
        return $this->timezoneFields;
    }

    /**
     * Enable timezone storage conversion for this model instance
     *
     * @return $this
     */
    public function enableTimezoneStorage()
    {
        $this->timezoneStorageEnabled = true;

        return $this;
    }

    /**
     * Disable timezone storage conversion for this model instance
     *
     * @return $this
     */
    public function disableTimezoneStorage()
    {
        $this->timezoneStorageEnabled = false;

        return $this;
    }

    /**
     * Add fields to timezone storage conversion
     *
     * @param  string|array  $fields
     * @return $this
     */
    public function addTimezoneFields($fields)
    {
        $fields = is_array($fields) ? $fields : [$fields];
        $this->timezoneFields = array_unique(
            array_merge($this->timezoneFields, $fields)
        );

        return $this;
    }

    /**
     * Remove fields from timezone storage conversion
     *
     * @param  string|array  $fields
     * @return $this
     */
    public function removeTimezoneFields($fields)
    {
        $fields = is_array($fields) ? $fields : [$fields];
        $this->timezoneFields = array_diff($this->timezoneFields, $fields);

        return $this;
    }

    /**
     * Set timezone storage fields (replaces existing)
     *
     * @return $this
     */
    public function setTimezoneFields(array $fields)
    {
        $this->timezoneFields = $fields;

        return $this;
    }

    /**
     * Clear timezone storage cache (useful for testing or when timezone changes)
     *
     * @return $this
     */
    public function clearTimezoneStorageCache()
    {
        $this->timezoneStorageCache = null;

        return $this;
    }

    /**
     * Check if timezone storage is enabled
     */
    public function isTimezoneStorageEnabled(): bool
    {
        return $this->timezoneStorageEnabled;
    }

    /**
     * Get current timezone storage fields
     */
    public function getCurrentTimezoneFields(): array
    {
        return $this->getTimezoneFields();
    }
}
