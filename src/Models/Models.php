<?php

namespace Diviky\Bright\Models;

use App\Models\User;

class Models
{
    public static function user()
    {
        return config('auth.providers.users.model', User::class);
    }

    public static function passwordHistory()
    {
        return config('bright.models.passwod_history', PasswordHistory::class);
    }

    public static function branding()
    {
        return config('bright.models.branding', Branding::class);
    }

    public static function options()
    {
        return config('bright.models.options', Options::class);
    }

    public static function meta()
    {
        return config('bright.models.meta', Meta::class);
    }

    public static function metaValues()
    {
        return config('bright.models.meta_values', MetaValues::class);
    }

    public static function emailLogs()
    {
        return config('bright.models.email_logs', EmailLogs::class);
    }

    public static function users()
    {
        return config('bright.models.user_users', UserUsers::class);
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

    public static function activation()
    {
        return config('bright.models.email_logs', Activation::class);
    }
}
