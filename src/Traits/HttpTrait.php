<?php

declare(strict_types=1);

namespace Diviky\Bright\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * Create a new cookie instance.
     *
     * @param string      $name
     * @param string      $value
     * @param int         $minutes
     * @param string      $path
     * @param string      $domain
     * @param bool        $secure
     * @param bool        $httpOnly
     * @param bool        $raw
     * @param null|string $sameSite
     *
     * @return \Illuminate\Cookie\CookieJar|\Symfony\Component\HttpFoundation\Cookie
     */
    public function setCookie($name = null, $value = null, $minutes = 0, $path = null, $domain = null, $secure = false, $httpOnly = true, $raw = false, $sameSite = null)
    {
        return cookie($name, $value, $minutes, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }

    /**
     * Set Session.
     *
     * @param string $name  Name of the Session
     * @param string $value Value of the Session
     */
    public function setSession($name, $value = null): void
    {
        $this->get('session')->put($name, $value);
    }

    /**
     * Get Session.
     *
     * @param string $name    Name of the Session
     * @param mixed  $default The default value if not found
     *
     * @return mixed
     */
    public function getSession($name, $default = null)
    {
        return $this->get('session')->get($name, $default);
    }

    /**
     * Delete Session.
     *
     * @param string $name Name of the Session
     *
     * @return mixed
     */
    public function removeSession($name)
    {
        return $this->get('session')->remove($name);
    }

    /**
     * Generate the URL to a named route.
     *
     * @param array|string $name
     * @param array        $parameters
     * @param bool         $absolute
     *
     * @return string
     */
    public function route($name, $parameters = [], $absolute = true)
    {
        return $this->get('url')->route($name, $parameters, $absolute);
    }

    /**
     * Get an instance of the redirector.
     *
     * @param null|string $to
     * @param int         $status
     * @param array       $headers
     * @param bool        $secure
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function redirect($to = null, $status = 302, $headers = [], $secure = null)
    {
        if (\is_null($to)) {
            return $this->get('redirect');
        }

        return $this->get('redirect')->to($to, $status, $headers, $secure);
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
     * Escapes a text for HTML.
     *
     * @param string $text         The input text to be escaped
     * @param int    $flags        The flags (@see htmlspecialchars)
     * @param string $charset      The charset
     * @param bool   $doubleEncode Whether to try to avoid double escaping or not
     *
     * @return string Escaped text
     */
    public function escape($text, $flags = ENT_COMPAT, $charset = null, $doubleEncode = true)
    {
        return \htmlspecialchars($text, $flags, $charset ?: $this['charset'], $doubleEncode);
    }

    /**
     * Convert some data into a JSON response.
     *
     * @param mixed $data    The response data
     * @param int   $status  The response status code
     * @param array $headers An array of response headers
     *
     * @return JsonResponse
     */
    public function toJson($data = [], array $headers = [], $status = 200)
    {
        return new JsonResponse($data, $status, $headers);
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
     * Aborts the current request by sending a proper HTTP error.
     *
     * @param int    $statusCode The HTTP status code
     * @param string $message    The status message
     * @param array  $headers    An array of HTTP headers
     */
    public function abort($statusCode, $message = '', array $headers = []): void
    {
        if (404 == $statusCode) {
            throw new NotFoundHttpException($message);
        }

        throw new HttpException($statusCode, $message, null, $headers);
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
