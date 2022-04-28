# An extension to laravel for quick develpment

## Install

```
    composer require diviky/bright
```

## Setup

```
    php artisan bright:setup
```

Query builder needs to overwrite with bright

```php
sed -i '' 's/use Illuminate\\Database\\Query\\Builder/use Diviky\\Bright\\Database\\Query\\Builder/g' vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php

```

```php
    php artisan vendor:publish --tag="bright-config"
    php artisan vendor:publish --tag="bright-assets"
    php artisan vendor:publish --tag="bright-views"
    php artisan vendor:publish --tag="bright-migrations"

    //Copy webpack and some other files
    php artisan vendor:publish --tag="bright-setup"
```

```
    bower install selectize --save
    npm install jquery --save
    npm install popper.js --save
    npm install bootstrap --save
```

add in kernal.php route middleware

```php
// $middleware
    \Diviky\Bright\Http\Middleware\PreflightResponse::class,

```

### Sorting task

```html
<tbody ajax-content class="table_sortable_body">
    ...
    <td sortable>
        <i class="fa fa-arrows-v fa-lg"></i>
        <input type="hidden" name="sorting[{{ $row->id }}]" value="{{ $row->ordering }}" />
    </td>
</tbody>
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

$filters[] = ['filter[name]' => 'bright']; // $query->where('name', '=', 'bright')
$filters[] = ['filter[first_name|last_name]' => 'bright']; // $query->where('first_name', '=', 'bright')->orWhere()
$filters[] = ['lfilter[name]' => 'bright']; // $query->where('name', 'like', '%bright%')
$filters[] = ['rfilter[name]' => 'bright']; // $query->where('name', 'like', 'bright%')
$filters[] = ['efilter[name]' => 'bright']; // $query->where('name', 'like', '%bright')

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

##### Get Trashed && Non Trashed

Get non deleted items

```php

$rows = DB::table('users')
    ->withOutTrashed()
    ->get();

```

Get only deleted items

```php

$rows = DB::table('users')
    ->onlyTrashed()
    ->get();

```

##### Raw Expressions

```php

$rows = DB::table('orders')
    ->groupByRaw(['username']);
    ->groupByRaw('price * ? as price_with_tax', [1.0825]);
    ->get()
```

```php

$rows = DB::table('orders')
    ->selectRaw(['max(price)', 'order_id']);
    ->groupByRaw('price * ? as price_with_tax', [1.0825]);
    ->get()
```

```php

$rows = DB::table('orders')
    ->selectRaw(['max(price)', 'order_id']);
    ->whereBetweenRaw('max(price)', [1.0825, 2]);
    ->get()
```

##### Ordering

```php

$rows = DB::table('orders')
    ->ordering($data, ['order_id' => 'desc']);
    ->groupByRaw('price * ? as price_with_tax', [1.0825]);
    ->get()
```

##### Timestamps

Set the timestamps 'created_at`and`updated_at`for insert and`updated_at` for update

```php
    $result = DB::table('orders')
        ->timestamps()
        ->insert($values)

```

```php
    $result = DB::table('orders')
        ->timestamps()
        ->update($values)

```

```php
    $result = DB::table('orders')
        ->timestamps(false)
        ->update($values)

```

```html
<div class="form-group">
    <label class="form-label">Sent By Employee</label>
    <select name="sent_by" class="form-control" data-select-ajax="{{ url('search/employee') }}">
        <option value="">Search Employee</option>
    </select>
</div>
```

## Flatten Relations

Return single model with merged attributes from relations

```php

// except the relations from merge
$model = $model->flatten($except);

// Take some keys
$model = $model->some(['id']);

// Take except
$model = $model->except(['id']);

// Append keys to attributes
$model = $model->merge(['id' => 1]);

// Apped relation keys to attributes
$model = $model->concat(['relation.id']);

// combination of merge and contact
$model = $model->combine(['relation.id']);
```

# Database Filter

`filter` method used to filter the database columns in query builder. it accepts `requets` object as `array`.

Avaliable filters

`filter[]` uses the `$builder->where($column, $value)`. uses array key as column name and value as value. ex: `filter[column]='value'`

`lfilter[]` uses the `$builder->where($column, '%'.$value.'%')` with like match. uses array key as column name and value as value. ex: `lfilter[column]='value'`

use the `|` notation to filter or condition. ex: `filter[comments|title]=xxx`
use the `:` notation to filter with relation table. ex: `filter[posts:title]=xxx`
use the `.` notation to filter the table alias in join query. ex: `filter[comments.title]=xxx`
use the `scope[]` to filter the model scopes. ex: `scope[status]=1` will run `$builder->status(1)`
use `parse[]` to DSL Parser for a filter query langague.
Example queries in this language:

-   `price = 100`
-   `price != 100`
-   `price > 100`
-   `price < 100`
-   `price <= 100`
-   `price >= 100`
-   `name =~ "brig%"`
-   `price > 100 AND active = 1`
-   `status = "pending" OR status = "approved"`
-   `product.price > 100 AND category.id = 7`
-   `name =~ "Foo%"`
-   `created_at > "2017-01-01" and created_at < "2017-01-31"`
-   `status = 1 AND (name = "PHP Rocks" or name = "I ♥ PHP")`

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
