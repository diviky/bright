<?php

namespace Karla\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable as BaseMailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class Mailable extends BaseMailable
{
    use Queueable, SerializesModels;

    protected $theme;
    protected $prefix = 'emails.';

    /**
     * Create a new message instance.
     */
    public function __construct()
    {
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this;
    }

    /**
     * Set the view and view data for the message.
     *
     * @param string $view
     * @param array  $data
     *
     * @return $this
     */
    public function view($view, array $data = [])
    {
        $this->view     = $this->prefix.$view;
        $this->viewData = array_merge($this->viewData, $data);

        return $this;
    }

    /**
     * Set the plain text view for the message.
     *
     * @param string $textView
     * @param array  $data
     *
     * @return $this
     */
    public function text($textView, array $data = [])
    {
        $this->textView = $this->prefix.$textView;
        $this->viewData = array_merge($this->viewData, $data);

        return $this;
    }

    /**
     * Set the Markdown template for the message.
     *
     * @param string $view
     * @param array  $data
     *
     * @return $this
     */
    public function markdown($view, array $data = [])
    {
        $this->markdown = $this->prefix.$view;
        $this->viewData = array_merge($this->viewData, $data);

        return $this;
    }

    public function theme($name)
    {
        $this->theme = $name;

        return $this;
    }

    public function deliver($to = null, $exception = false)
    {
        if ($exception) {
            Mail::to($to)->send($this);

            return true;
        }

        try {
            Mail::to($to)->send($this);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Set the value of prefix.
     *
     * @return self
     */
    public function prefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }
}
