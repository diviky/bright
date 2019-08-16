# An extension to laravel for quick develpment

Query builder needs to overwrite with karla

Illuminate/Database/Eloquent/Builder.php

```php

sed -i -e 's/use Illuminate\\Database\\Query\\Builder/use Karla\\Database\\Query\\Builder/g' vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php

```

```php
    php artisan vendor:publish --provider="Karla\KarlaServiceProvider" --tag="config"
    php artisan vendor:publish --provider="Karla\KarlaServiceProvider" --tag="assets"
    php artisan vendor:publish --provider="Karla\KarlaServiceProvider" --tag="views"
```

add in kernal.php route middleware
```php

'auth.verified' => \Karla\Http\Controllers\Auth\Middleware\IsUserActivate::class,
```

## Config changes

add in auth.php guards array
```php
    'token' => [
        'driver' => 'access_token',
        'provider' => 'token'
    ],
```

Replace passwords array with below
```php
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'auth_password_resets',
            'expire' => 60,
        ],
    ],
```

Add in providers array in app.php
```php
    //app.php
    
    Karla\View\ViewServiceProvider::class,
```

###Sorting task

```html
<tbody ajax-content class="table_sortable_body">
    ...
    <td sortable>
        <i class="fa fa-arrows-v fa-lg"></i>
        <input type="hidden" name="sorting[{{ $row->id }}]" value="{{ $row->ordering }}">
    </td>
```

```php
    if ($task == 'sorting') {
        $sorting = $this->input('sorting');
        $this->get('resolver')->getHelper('speed')->sorting('table', $sorting, 'id');

        return [];
    }
```

### Builder Extended Methods

##### Iterating results

If you like fetch all the rows with chunks and modify using callaback

```php

$rows = DB::table('large_table')->iterate(1000);

$rows = DB::table('large_table')->iterate(1000, function($row) {

    return $row;
});

```

##### Get results from multiple tables

If you have data in multiple tables, want to retrive table after table with pagination

```php

$rows = DB::tables(['roles', 'roles1', 'roles2'])->complexPaginate();

```

##### Cache the query results

If you want to cache the results

```php

$rows = DB::table('uses')
    ->remember($minutes, $cache_key)
    ->get();

$rows = DB::table('uses')
    ->rememberForever($cache_key)
    ->get();

```

##### Filter the query with input values

```php

$filters = [];
// $query->whereRaw('date(created_at) = ?', ['2019-10-12'])
$filters[] = ['date[created_at]' => date('Y-m-d')];

// $query->whereDateBetween('created_at between ? and ? ', ['2019-10-12', '2019-10-22'])
$filters[] = ['range[created_at]' => date('Y-m-d') .' - '. date('Y-m-d')];

// $query->whereBetween('created between ? and ? ', [strtotime('-1 day'), time()])
$filters[] = ['timestamp[created]' => date('Y-m-d') .' - '. date('Y-m-d')]; 

//
$filters[] = ['unixtime[created]' => date('Y-m-d') .' - '. date('Y-m-d')]; 
$filters[] = ['between[created]' => date('Y-m-d') .' - '. date('Y-m-d')]; 

$filters[] = ['filter[name]' => 'karla']; // $query->where('name', '=', 'karla')
$filters[] = ['filter[first_name|last_name]' => 'karla']; // $query->where('first_name', '=', 'karla')->orWhere()
$filters[] = ['lfilter[name]' => 'karla']; // $query->where('name', 'like', '%karla%')
$filters[] = ['rfilter[name]' => 'karla']; // $query->where('name', 'like', 'karla%')
$filters[] = ['efilter[name]' => 'karla']; // $query->where('name', 'like', '%karla')

$rows = DB::table('users')
    ->filter($filters)
    ->get();

```

##### Delete from select query

```php

$rows = DB::table('users')
    ->filter($filters)
    ->deletes();

```


```php

$rows = DB::table('users')
    ->whereDateBetween('created_at', [date(), date()])
    ->get();

```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.