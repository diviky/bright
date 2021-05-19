<?php

namespace Diviky\Bright\Notifications\Messages;

class SlackAttachmentAction
{
    /**
     * The title field of the attachment field.
     *
     * @var string
     */
    protected $text;

    protected $url;

    protected $style;

    protected $name;

    protected $value;

    protected $confirm;

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
     * @param string $title
     * @param mixed  $text
     *
     * @return $this
     */
    public function text($text)
    {
        $this->text = $text;

        return $this;
    }

    public function name($name): static
    {
        $this->name = $name;

        return $this;
    }

    public function value($value): static
    {
        $this->value = $value;

        return $this;
    }

    public function action($action = []): static
    {
        $this->action = $action;

        return $this;
    }

    public function url($url): static
    {
        $this->url = $url;

        return $this;
    }

    public function confirm($confirm = []): static
    {
        $this->confirm = $confirm;

        return $this;
    }

    public function type($type): static
    {
        $this->type = $type;

        return $this;
    }

    public function style($style): static
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
            'type'    => $this->type,
            'text'    => $this->text,
            'name'    => $this->name,
            'value'   => $this->value,
            'url'     => $this->url,
            'style'   => $this->style,
            'confirm' => $this->confirm,
        ];

        $values = \array_merge($values, $this->action);

        return \array_filter($values);
    }
}
