<?php

namespace Karla\Models;

use App\User;
use Karla\Models\Branding;
use Karla\Models\EmailLogs;
use Karla\Models\Meta;
use Karla\Models\MetaValues;
use Karla\Models\Options;
use Karla\Models\PasswordHistory;
use Karla\Models\UserUsers;

class Models
{
    public static function user()
    {
        return User::class;
    }

    public static function passwordHistory()
    {
        return config('karla.models.passwod_history', PasswordHistory::class);
    }

    public static function branding()
    {
        return config('karla.models.branding', Branding::class);
    }

    public static function options()
    {
        return config('karla.models.options', Options::class);
    }

    public static function meta()
    {
        return config('karla.models.meta', Meta::class);
    }

    public static function metaValues()
    {
        return config('karla.models.meta_values', MetaValues::class);
    }

    public static function emailLogs()
    {
        return config('karla.models.email_logs', EmailLogs::class);
    }

    public static function users()
    {
        return config('karla.models.user_users', UserUsers::class);
    }

    public static function table($name)
    {
        if ('app_options' == $name) {
            return static::options();
        }

        if ('app_meta' == $name) {
            return static::meta();
        }

        if ('app_meta_values' == $name) {
            return static::metaValues();
        }
    }
}
