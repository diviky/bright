<?php

namespace Diviky\Bright\Database\Eloquent\Concerns;

use Carbon\Carbon;
use Diviky\Bright\Services\Resolver;

trait Timezone
{
    /**
     * Cache for user timezone
     *
     * @var string|null
     */
    protected $userTimezoneCache;

    /**
     * Override getAttribute to apply timezone conversion to date/datetime attributes
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        // Only process if timezone conversion is enabled and value is a Carbon instance
        if (isset($value) && $value instanceof Carbon && $this->shouldConvertTimezone($key)) {
            return $this->convertToUserTimezone($value);
        }

        return $value;
    }

    /**
     * Override getAttributes to apply timezone conversion to all date/datetime attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        $attributes = parent::getAttributes();

        // Apply timezone conversion to all applicable attributes
        foreach ($attributes as $key => $value) {
            if (isset($value) && $value instanceof Carbon && $this->shouldConvertTimezone($key)) {
                $attributes[$key] = $this->convertToUserTimezone($value);
            }
        }

        return $attributes;
    }

    /**
     * Check if attribute should be timezone converted
     */
    protected function shouldConvertTimezone(string $key): bool
    {
        // Check if timezone conversion is globally disabled for this model
        if (property_exists($this, 'timezoneConversion') && $this->timezoneConversion === false) {
            return false;
        }

        // Check if this attribute is explicitly excluded
        if (property_exists($this, 'timezoneExclude') && in_array($key, $this->timezoneExclude)) {
            return false;
        }

        // If timezoneInclude is defined, only include specified attributes
        if (property_exists($this, 'timezoneInclude') && !in_array($key, $this->timezoneInclude)) {
            return false;
        }

        // If we get here, conversion is allowed
        return true;
    }

    /**
     * Convert Carbon instance to user timezone
     */
    protected function convertToUserTimezone(Carbon $date): Carbon
    {
        $userTimezone = $this->getUserTimezone();

        if (!$userTimezone) {
            return $date;
        }

        // Clone the date to avoid modifying the original
        return $date->clone()->setTimezone($userTimezone);
    }

    /**
     * Get user timezone using Resolver
     */
    protected function getUserTimezone(): ?string
    {
        if ($this->userTimezoneCache === null) {
            $this->userTimezoneCache = Resolver::timezone();
        }

        return $this->userTimezoneCache;
    }

    /**
     * Temporarily disable timezone conversion for this model instance
     *
     * @return $this
     */
    public function withoutTimezoneConversion()
    {
        $this->timezoneConversion = false;

        return $this;
    }

    /**
     * Re-enable timezone conversion for this model instance
     *
     * @return $this
     */
    public function withTimezoneConversion()
    {
        $this->timezoneConversion = true;

        return $this;
    }

    /**
     * Clear timezone caches (useful for testing or when timezone changes)
     *
     * @return $this
     */
    public function clearTimezoneCache()
    {
        $this->userTimezoneCache = null;

        return $this;
    }
}
