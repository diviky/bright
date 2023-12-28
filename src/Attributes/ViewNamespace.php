<?php

declare(strict_types=1);

namespace Diviky\Bright\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
class ViewNamespace
{
    private string $namespace;

    public function __construct(string $namespace)
    {
        $this->namespace = $namespace;
    }

    public function getViewName(string $name): string
    {
        return $this->namespace . '::' . $name;
    }

    /**
     * Get the value of namespace.
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * Set the value of namespace.
     *
     * @param  mixed  $namespace
     */
    public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;

        return $this;
    }
}
