# An extension to laravel for quick develpment


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

add in kernal.php route middleware
```php

'auth.verified' => \App\Http\Controllers\Auth\Middlewares\IsUserActivate::class,
```

add in auth.php guards array
```php
    'token' => [
        'driver' => 'access_token',
        'provider' => 'token'
    ],
```

```php
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'auth_password_resets',
            'expire' => 60,
        ],
    ],
```

```php
    //app.php
    
    Karla\View\ViewServiceProvider::class,
```