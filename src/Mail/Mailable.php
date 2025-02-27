<?php

declare(strict_types=1);

namespace Diviky\Bright\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable as BaseMailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class Mailable extends BaseMailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * View prefix.
     *
     * @var string
     */
    protected $prefix = 'emails.';

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
     * @param  string  $view
     * @return $this
     */
    #[\Override]
    public function view($view, array $data = [])
    {
        $this->view = $this->prefix . $view;
        $this->viewData = \array_merge($this->viewData, $data);

        return $this;
    }

    /**
     * Set the plain text view for the message.
     *
     * @param  string  $textView
     * @return $this
     */
    #[\Override]
    public function text($textView, array $data = [])
    {
        $this->textView = $this->prefix . $textView;
        $this->viewData = \array_merge($this->viewData, $data);

        return $this;
    }

    /**
     * Set the Markdown template for the message.
     *
     * @param  string  $view
     * @return $this
     */
    #[\Override]
    public function markdown($view, array $data = [])
    {
        $this->markdown = $this->prefix . $view;
        $this->viewData = \array_merge($this->viewData, $data);

        return $this;
    }

    /**
     * Set the view theme.
     *
     * @param  string  $name
     */
    public function theme($name): self
    {
        $this->theme = $name;

        return $this;
    }

    /**
     * Send email.
     *
     * @param  mixed  $to
     * @param  bool  $exception
     */
    public function deliver($to = null, $exception = false): bool
    {
        // $to = $this->format($to);

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
     * @param  mixed  $prefix
     * @return self
     */
    public function prefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    #[\Override]
    protected function setAddress($address, $name = null, $property = 'to')
    {
        $address = $this->format($address, $name);

        return parent::setAddress($address, $name, $property);
    }

    /**
     * Format the given email to proper name and address.
     *
     * @param  mixed  $address
     * @return array
     */
    protected function format($address, ?string $from = null)
    {
        if (\is_array($address)) {
            return \array_map('trim', $address);
        }

        if (!\is_string($address)) {
            return $address;
        }

        $addresses = [];
        if (\preg_match('/>[^\S]*;/', $address)) {
            $address = \explode(';', $address);
            foreach ($address as $v) {
                $v = \explode('<', $v);
                $email = isset($v[1]) ? \rtrim(\trim($v[1]), '>') : $v[0];
                $name = isset($v[1]) ? $v[0] : $from;

                $addresses[] = ['email' => \trim($email), 'name' => $name];
            }
        } elseif (\strstr($address, '|')) {
            $delim = (\strstr($address, ',')) ? ',' : ';';
            $address = \explode('|', $address);
            foreach ($address as $v) {
                $v = \explode($delim, $v);
                $email = isset($v[1]) ? $v[1] : $v[0];
                $name = isset($v[1]) ? $v[1] : $from;

                $addresses[] = ['email' => \trim($email), 'name' => $name];
            }
        } else {
            $address = \preg_split("/[,|\n]/", $address);
            if (is_array($address)) {
                foreach ($address as $v) {
                    $v = \explode(';', $v);
                    $email = isset($v[1]) ? $v[1] : $v[0];
                    $name = isset($v[1]) ? $v[1] : $from;

                    $addresses[] = ['email' => \trim($email), 'name' => $name];
                }
            }
        }

        return $addresses;
    }
}
