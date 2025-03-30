<?php

declare(strict_types=1);

namespace Diviky\Bright\View\Compilers;

use Illuminate\View\Compilers\ComponentTagCompiler as ComponentTagCompilerBase;

class ComponentTagCompiler extends ComponentTagCompilerBase
{
    public function findClassByComponent($component)
    {
        // Check if any custom resolver can resolve the component
        $resolvedClass = $this->blade->resolveComponent($component);
        if ($resolvedClass) {
            return $resolvedClass;
        }

        // Default behavior if no resolver works
        return parent::findClassByComponent($component);
    }
}
