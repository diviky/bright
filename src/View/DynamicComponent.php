<?php

namespace Diviky\Bright\View;

use Diviky\Bright\View\Compilers\ComponentTagCompiler;
use Illuminate\Container\Container;
use Illuminate\View\DynamicComponent as ViewDynamicComponent;

class DynamicComponent extends ViewDynamicComponent
{
    /**
     * Get an instance of the Blade tag compiler.
     *
     * @return \Illuminate\View\Compilers\ComponentTagCompiler
     */
    protected function compiler()
    {
        if (!static::$compiler) {
            static::$compiler = new ComponentTagCompiler(
                Container::getInstance()->make('blade.compiler')->getClassComponentAliases(),
                Container::getInstance()->make('blade.compiler')->getClassComponentNamespaces(),
                Container::getInstance()->make('blade.compiler')
            );
        }

        return static::$compiler;
    }
}
