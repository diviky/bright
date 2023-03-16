<?php

declare(strict_types=1);

namespace Diviky\Bright\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Request variables handle.
 *
 * @author sankar <sankar.suda@gmail.com>
 */
trait HttpTrait
{
    /**
     * Request object.
     *
     * @return \Illuminate\Http\Request
     */
    public function request()
    {
        return $this->get('request');
    }

    /**
     * Retrieve an input item from the request.
     *
     * @param string                     $key
     * @param null|array|bool|int|string $default
     *
     * @return mixed
     */
    public function input($key = null, $default = null)
    {
        return $this->request()->input($key, $default);
    }

    /**
     * Retrieve an input item from the request.
     *
     * @return array
     */
    public function all()
    {
        return $this->request()->all();
    }

    /**
     * Retrieve a query string item from the request.
     *
     * @param string            $key
     * @param null|array|string $default
     *
     * @return null|array|string
     */
    public function query($key = null, $default = null)
    {
        return $this->request()->query($key, $default);
    }

    /**
     * Retrieve a cookie from the request.
     *
     * @param string            $key
     * @param null|array|string $default
     *
     * @return null|array|string
     */
    public function cookie($key = null, $default = null)
    {
        return $this->request()->cookie($key, $default);
    }

    /**
     * Retrieve a file from the request.
     *
     * @param null|string $key
     * @param mixed       $default
     *
     * @return null|array|\Illuminate\Http\UploadedFile|\Illuminate\Http\UploadedFile[]
     */
    public function files($key = null, $default = null)
    {
        return $this->request()->file($key, $default);
    }

    /**
     * Retrieve a request payload item from the request.
     *
     * @param null|string       $key
     * @param null|array|string $default
     *
     * @return null|array|string
     */
    public function post($key = null, $default = null)
    {
        return $this->request()->post($key, $default);
    }

    /**
     * Retrieve a header from the request.
     *
     * @param null|string       $key
     * @param null|array|string $default
     *
     * @return null|array|string
     */
    public function header($key = null, $default = null)
    {
        return $this->request()->header($key, $default);
    }

    /**
     * Retrieve a server variable from the request.
     *
     * @param null|string       $key
     * @param null|array|string $default
     *
     * @return null|array|string
     */
    public function server($key = null, $default = null)
    {
        return $this->request()->server($key, $default);
    }

    /**
     * Get the input method.
     *
     * @param string $type
     *
     * @return bool|string
     */
    public function method($type = null)
    {
        $method = $this->request()->getMethod();

        if ($type) {
            return \strtoupper($type) == $method ? true : false;
        }

        return $method;
    }

    /**
     * Checks if the request method is of specified type.
     *
     * @param string $method Uppercase request method (GET, POST etc)
     *
     * @return bool
     */
    public function isMethod($method)
    {
        return $this->request()->isMethod($method);
    }

    /**
     * Sets a header by name.
     *
     * @param array|string $key     The key
     * @param array|string $values  The value or an array of values
     * @param bool         $replace Whether to replace the actual value or not (true by default)
     */
    public function setHeader($key, $values = null, $replace = true): self
    {
        if (\is_array($key)) {
            foreach ($key as $baseKey => $baseValue) {
                $this->request()->setHeader($baseKey, $baseValue, $replace);
            }
        } else {
            $this->request()->setHeader($key, $values, $replace);
        }

        return $this;
    }

    /**
     * Creates a streaming response.
     *
     * @param mixed $callback A valid PHP callback
     * @param int   $status   The response status code
     * @param array $headers  An array of response headers
     *
     * @return StreamedResponse
     */
    public function stream($callback = null, array $headers = [], $status = 200)
    {
        return new StreamedResponse($callback, $status, $headers);
    }

    /**
     * Sends a file.
     *
     * @param \SplFileInfo|string $file        The file to stream
     * @param int                 $status      The response status code
     * @param array               $headers     An array of response headers
     * @param null|string         $disposition The type of Content-Disposition to set automatically with the filename
     *
     * @return BinaryFileResponse
     */
    public function sendFile($file, array $headers = [], $disposition = null, $status = 200)
    {
        return new BinaryFileResponse($file, $status, $headers, true, $disposition);
    }

    /**
     * Run the validator's rules against its data.
     *
     * @param null|array|Request $data
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function rules(array $rules = [], $data = null, array $messages = [], array $attributes = []): array
    {
        if (isset($data) && \is_array($data)) {
            return Validator::make($data, $rules, $messages, $attributes)->validate();
        }

        if (isset($data) && $data instanceof Request) {
            return Validator::make($data->all(), $rules, $messages, $attributes)->validate();
        }

        return Validator::make($this->request()->all(), $rules, $messages, $attributes)->validate();
    }
}
