<?php

declare(strict_types=1);

namespace Diviky\Bright\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS)]
class View
{
    private ?string $name = null;

    private ?string $layout = null;

    public function __construct(?string $name = null, ?string $layout = null)
    {
        $this->name = $name;
        $this->layout = $layout;
    }

    /**
     * Get the value of name.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set the value of name.
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of layout.
     */
    public function getLayout(): ?string
    {
        return $this->layout;
    }

    /**
     * Set the value of layout.
     *
     * @param  mixed  $layout
     */
    public function setLayout($layout): self
    {
        $this->layout = $layout;

        return $this;
    }
}
