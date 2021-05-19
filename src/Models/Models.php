<?php

namespace Diviky\Bright\Models;

class Models
{
    /**
     * Users table.
     *
     * @return \Illuminate\Database\Eloquent\Model::class
     */
    public static function user()
    {
        return config('auth.providers.users.model', User::class);
    }

    /**
     * Password History table.
     *
     * @return \Illuminate\Database\Eloquent\Model::class
     */
    public static function passwordHistory()
    {
        return config('bright.models.passwod_history', PasswordHistory::class);
    }

    /**
     *  @return \Illuminate\Database\Eloquent\Model::class
     */
    public static function branding()
    {
        return config('bright.models.branding', Branding::class);
    }

    /**
     *  @return \Illuminate\Database\Eloquent\Model::class
     */
    public static function options(): string
    {
        return config('bright.models.options', Options::class);
    }

    /**
     *  @return \Illuminate\Database\Eloquent\Model::class
     */
    public static function meta()
    {
        return config('bright.models.meta', Meta::class);
    }

    /**
     *  @return \Illuminate\Database\Eloquent\Model::class
     */
    public static function metaValues()
    {
        return config('bright.models.meta_values', MetaValues::class);
    }

    /**
     *  @return \Illuminate\Database\Eloquent\Model::class
     */
    public static function emailLogs()
    {
        return config('bright.models.email_logs', EmailLogs::class);
    }

    /**
     *  @return \Illuminate\Database\Eloquent\Model::class
     */
    public static function users()
    {
        return config('bright.models.user_users', UserUsers::class);
    }

    /**
     * @param mixed $name
     *
     *  @return \Illuminate\Database\Eloquent\Model::class
     */
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

        return $name;
    }

    /**
     *  @return \Illuminate\Database\Eloquent\Model::class
     */
    public static function activation()
    {
        return config('bright.models.email_logs', Activation::class);
    }
}
