<?php

declare(strict_types=1);

namespace Diviky\Bright\Concerns;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;

trait Provider
{
    public function directive(): void
    {
        Blade::directive('form', function ($expression) {
            $expression = \substr(\substr($expression, 0, -1), 1);

            return '<?php echo config("vajax.' . $expression . '"); ?>';
        });

        Blade::if('view', function ($expression) {
            $expression = $expression ?: 'ajax';

            return !config('vajax.' . $expression);
        });
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    public function validates(): void
    {
        Validator::extend('extension', function ($attribute, $value, $parameters) {
            $ext = $value->getClientOriginalExtension();

            return \in_array(\strtolower($ext), $parameters);
        }, 'The :attribute must be a file of type: :values.');

        Validator::replacer('extension', function ($message, $attribute, $rule, $parameters) {
            return \str_replace([':attribute', ':values'], [$attribute, \implode(',', $parameters)], $message);
        });
    }

    public function macros(): void {}
}
