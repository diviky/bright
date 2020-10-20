<?php

namespace Diviky\Bright\Traits;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

trait Provider
{
    public function directive()
    {
        Blade::directive('form', function ($expression) {
            $expression = \substr(\substr($expression, 0, -1), 1);

            return '<?php echo config("vajax.' . $expression . '"); ?>';
        });

        Blade::if('view', function ($expression) {
            $expression = $expression ?: 'ajax';

            return !config('vajax.' . $expression);
        });

        Blade::directive('dispatch', function ($expression) {
            if (!\is_array($expression)) {
                $expression = ['url' => $expression, 'method' => 'GET', 'params' => []];
            }

            //$request = Request::create('/people');
            //$response = app()->handle($request)->getContent();;
            //$response = Route::dispatchToRoute($request)->getContent();

            return '<?php echo "dispatched"; ?>';
        });
    }

    public function validates()
    {
        Validator::extend('extension', function ($attribute, $value, $parameters) {
            $ext = $value->getClientOriginalExtension();

            return \in_array(\strtolower($ext), $parameters);
        }, 'The :attribute must be a file of type: :values.');

        Validator::replacer('extension', function ($message, $attribute, $rule, $parameters) {
            return \str_replace([':attribute', ':values'], [$attribute, \implode(',', $parameters)], $message);
        });
    }

    public function macros()
    {
        $parent = $this;

        Builder::macro('log', function () {
            if (1 === \func_num_args()) {
                $message = \func_get_arg(0);
            }
            Log::debug((empty($message) ? '' : $message . ': ') . $this->toQuery());

            return $this;
        });
    }
}
