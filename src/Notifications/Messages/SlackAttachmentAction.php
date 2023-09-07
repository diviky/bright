<?php

declare(strict_types=1);

namespace Diviky\Bright\Notifications\Messages;

class SlackAttachmentAction
{
    /**
     * The title field of the attachment field.
     *
     * @var string
     */
    protected $text;

    /**
     * Action url.
     *
     * @var string
     */
    protected $url;

    /**
     * Action style.
     *
     * @var string
     */
    protected $style;

    /**
     * Action nae.
     *
     * @var string
     */
    protected $name;

    /**
     * Action value.
     *
     * @var string
     */
    protected $value;

    /**
     * @var array
     */
    protected $confirm;

    /**
     * Action.
     *
     * @var array
     */
    protected $action = [];

    /**
     * The content of the attachment field.
     *
     * @var string
     */
    protected $type = 'button';

    /**
     * Set the title of the field.
     *
     * @param mixed $text
     *
     * @return $this
     */
    public function text($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Action name.
     *
     * @param string $name
     */
    public function name($name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Action value.
     *
     * @param string $value
     */
    public function value($value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Action.
     *
     * @param array $action
     */
    public function action($action = []): self
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Action url.
     *
     * @param string $url
     */
    public function url($url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Action confirm button.
     *
     * @param array $confirm
     */
    public function confirm($confirm = []): self
    {
        $this->confirm = $confirm;

        return $this;
    }

    /**
     * Action type.
     *
     * @param string $type
     */
    public function type($type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Action style.
     *
     * @param string $style
     */
    public function style($style): self
    {
        $this->style = $style;

        return $this;
    }

    /**
     * Get the array representation of the attachment field.
     *
     * @return array
     */
    public function toArray()
    {
        $values = [
            'type' => $this->type,
            'text' => $this->text,
            'name' => $this->name,
            'value' => $this->value,
            'url' => $this->url,
            'style' => $this->style,
            'confirm' => $this->confirm,
        ];

        $values = \array_merge($values, $this->action);

        return \array_filter($values);
    }
}
