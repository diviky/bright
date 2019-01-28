<?php

namespace Karla\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable as BaseMailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class Mailable extends BaseMailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $theme;
    protected $prefix = 'emails.';

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

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
        $this->view     = $this->prefix . $view;
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
        $this->textView = $this->prefix . $textView;
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
        $this->markdown = $this->prefix . $view;
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
        //$to = $this->format($to);

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

    /**
     * Set the recipients of the message.
     *
     * All recipients are stored internally as [['name' => ?, 'address' => ?]]
     *
     * @param  object|array|string  $address
     * @param  string|null  $name
     * @param  string  $property
     * @return $this
     */
    protected function setAddress($address, $name = null, $property = 'to')
    {
        $address = $this->format($address, $name);

        foreach ($this->addressesToArray($address, $name) as $recipient) {
            $recipient = $this->normalizeRecipient($recipient);

            $this->{$property}[] = [
                'name'    => $recipient->name ?? null,
                'address' => $recipient->email,
            ];
        }

        return $this;
    }

    /**
     * Convert the given recipient arguments to an array.
     *
     * @param  object|array|string  $address
     * @param  string|null  $name
     * @return array
     */
    protected function addressesToArray($address, $name)
    {
        if (!is_array($address) && !$address instanceof Collection) {
            $address = is_string($name) ? [['name' => $name, 'email' => $address]] : [$address];
        }

        return $address;
    }

    protected function format($address, $from = null)
    {
        if (is_array($address)) {
            return array_map('trim', $address);
        }

        $addresses = [];
        if (preg_match('/>[^\S]*;/', $address)) {
            $address = explode(';', $address);
            foreach ($address as $v) {
                $v     = explode('<', $v);
                $email = ($v[1]) ? rtrim(trim($v[1]), '>') : $v[0];
                $name  = ($v[1]) ? $v[0] : $from;

                $addresses[] = ['email' => trim($email), 'name' => trim($name)];
            }
        } elseif (strstr($address, '|')) {
            $delim   = (strstr($address, ',')) ? ',' : ';';
            $address = explode('|', $address);
            foreach ($address as $v) {
                $v     = explode($delim, $v);
                $email = $v[1] ?: $v[0];
                $name  = $v[1] ?: $from;

                $addresses[] = ['email' => trim($email), 'name' => trim($name)];
            }
        } else {
            $address = preg_split("/[,|\n]/", $address);
            foreach ($address as $v) {
                $v     = explode(';', $v);
                $email = $v[1] ?: $v[0];
                $name  = $v[1] ?: $from;

                $addresses[] = ['email' => trim($email), 'name' => trim($name)];
            }
        }

        return $addresses;
    }
}
