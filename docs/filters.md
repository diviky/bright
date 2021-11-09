# Database Filter

`filter` method used to filter the database columns in query builder. it accepts `requets` object as `array`.

Avaliable filters

`filter[]` uses the `$builder->where($column, $value)`. uses array key as column name and value as value. ex: `filter[column]='value'`

`lfilter[]` uses the `$builder->where($column, '%'.$value.'%')` with like match. uses array key as column name and value as value. ex: `lfilter[column]='value'`

use the `|` notation to filter the relations. ex: `filter[comments|title]=xxx`
use the `.` notation to filter the table alias in join query. ex: `filter[comments.title]=xxx`
use the `scope[]` to filter the model scopes. ex: `scope[status]=1` will run `$builder->status(1)`
