<?php

namespace Karla\Traits;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

trait Provider
{
    use Database;

    public function directive()
    {
        Blade::directive('form', function ($expression) {
            $expression = substr(substr($expression, 0, -1), 1);
            return '<?php echo config("vajax.' . $expression . '"); ?>';
        });

        Blade::if('view', function ($expression) {
            $expression = $expression ?: 'ajax';
            return !config("vajax." . $expression);;
        });
    }

    public function validates()
    {
        Validator::extend('extension', function ($attribute, $value, $parameters) {
            return in_array($value->getClientOriginalExtension(), $parameters);
        });
    }

    public function macros()
    {
        $parent = $this;

        Builder::macro("toSqlWithBindings", function () {
            $sql = $this->toSql();
            foreach ($this->getBindings() as $binding) {
                $value = is_numeric($binding) ? $binding : "'$binding'";
                $sql = preg_replace('/\?/', $value, $sql, 1);
            }
            return $sql;
        });

        Builder::macro("log", function () {
            if (func_num_args() === 1) {
                $message = func_get_arg(0);
            }
            Log::debug((empty($message) ? "" : $message . ": ") . $this->toSqlWithBindings());

            return $this;
        });

        Builder::macro('ordering', function ($data, $default = []) use ($parent) {
            $parent->ordering($this, $data, $default);

            return $this;
        });

        Builder::macro('filter', function ($data, $alias = null) use ($parent) {
            $parent->filter($this, $data, $alias);

            return $this;
        });
    }
}
