<?php

namespace Diviky\Bright\View\Compilers;

use Closure;
use Illuminate\Support\Traits\Macroable;
use Illuminate\View\Compilers\BladeCompiler as BaseBladeCompiler;

class BladeCompiler extends BaseBladeCompiler
{
    use Macroable;

    protected static array $missingComponentResolvers = [];

    /**
     * Register a resolver for missing components.
     */
    public static function resolveMissingComponent(Closure $resolver): void
    {
        static::$missingComponentResolvers[] = $resolver;
    }

    public function resolveComponent($name)
    {
        foreach (static::$missingComponentResolvers as $resolver) {
            $resolved = call_user_func($resolver, $name);
            if ($resolved && class_exists($resolved)) {
                return $resolved;
            }
        }

        return null;
    }

    /**
     * Compile the component tags.
     *
     * @param  string  $value
     * @return string
     */
    protected function compileComponentTags($value)
    {
        if (!$this->compilesComponentTags) {
            return $value;
        }

        return (new ComponentTagCompiler(
            $this->classComponentAliases, $this->classComponentNamespaces, $this
        ))->compile($value);
    }
}
