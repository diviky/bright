<?php

namespace Karla\Traits;

use Illuminate\Http\JsonResponse;
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
    public function request()
    {
        return $this->get('request');
    }

    /**
     * Retrieve an input item from the request.
     *
     * @param string            $key
     * @param string|array|null $default
     *
     * @return string|array
     */
    public function input($key = null, $default = null)
    {
        return $this->get('request')->input($key, $default);
    }

    /**
     * Retrieve an input item from the request.
     *
     * @return string|array
     */
    public function all()
    {
        return $this->get('request')->all();
    }

    /**
     * Retrieve a query string item from the request.
     *
     * @param string            $key
     * @param string|array|null $default
     *
     * @return string|array
     */
    public function query($key = null, $default = null)
    {
        return $this->get('request')->query($key, $default);
    }

    /**
     * Retrieve a cookie from the request.
     *
     * @param string            $key
     * @param string|array|null $default
     *
     * @return string|array
     */
    public function cookie($key = null, $default = null)
    {
        return $this->get('request')->cookie($key, $default);
    }

    /**
     * Retrieve a files from the request.
     *
     * @param string            $key
     * @param string|array|null $default
     *
     * @return string|array
     */
    public function files($key = null, $default = null)
    {
        return $this->get('request')->files($key, $default, true);
    }

    /**
     * Retrieve a post items from the request.
     *
     * @param string            $key
     * @param string|array|null $default
     *
     * @return string|array
     */
    public function post($key = null, $default = null)
    {
        return $this->get('request')->post($key, $default);
    }

    /**
     * Retrieve a header from the request.
     *
     * @param string            $key
     * @param string|array|null $default
     *
     * @return string|array
     */
    public function header($key = null, $default = null)
    {
        return $this->get('request')->headers($key, $default);
    }

    /**
     * Retrieve a server variable from the request.
     *
     * @param string            $key
     * @param string|array|null $default
     *
     * @return string|array
     */
    public function server($key = null, $default = null)
    {
        return $this->get('request')->server($key, $default);
    }

    /**
     * Get the input method.
     *
     * @param string $type
     *
     * @return string|bool
     */
    public function method($type = null)
    {
        $method = $this->get('request')->getMethod();

        if ($type) {
            return strtoupper($type) == $method ? true : false;
        }

        return $method;
    }

    /**
     * Get the input method.
     *
     * @param string $type
     *
     * @return string|bool
     */
    public function isMethod($name = null)
    {
        return $this->method($name);
    }

    /**
     * Sets a header by name.
     *
     * @param string|array $key     The key
     * @param string|array $values  The value or an array of values
     * @param bool         $replace Whether to replace the actual value or not (true by default)
     */
    public function setHeader($key, $values = null, $replace = true)
    {
        if (is_array($key)) {
            foreach ($key as $baseKey => $baseValue) {
                $this->get('request')->setHeader($baseKey, $baseValue, $replace);
            }
        } else {
            $this->get('request')->setHeader($key, $values, $replace);
        }

        return $this;
    }

    /**
     * Create a new cookie instance.
     *
     * @param  string  $name
     * @param  string  $value
     * @param  int  $minutes
     * @param  string  $path
     * @param  string  $domain
     * @param  bool  $secure
     * @param  bool  $httpOnly
     * @param  bool  $raw
     * @param  string|null  $sameSite
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
    public function setSession($name, $value = null)
    {
        return $this->get('session')->set($name, $value);
    }

    /**
     * Get Session.
     *
     * @param string $name    Name of the Session
     * @param mixed  $default The default value if not found
     */
    public function getSession($name, $default = null)
    {
        return $this->get('session')->get($name, $default);
    }

    /**
     * Delete Session.
     *
     * @param string $name Name of the Session
     */
    public function removeSession($name)
    {
        return $this->get('session')->remove($name);
    }

    /**
     * Generate the URL to a named route.
     *
     * @param  array|string  $name
     * @param  array  $parameters
     * @param  bool  $absolute
     * @return string
     */

    public function route($name, $parameters = [], $absolute = true)
    {
        return $this->get('url')->route($name, $parameters, $absolute);
    }

    /**
     * Get an instance of the redirector.
     *
     * @param  string|null  $to
     * @param  int     $status
     * @param  array   $headers
     * @param  bool    $secure
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function redirect($to = null, $status = 302, $headers = [], $secure = null)
    {
        if (is_null($to)) {
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
    public function stream($callback = null, $status = 200, array $headers = [])
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
        return htmlspecialchars($text, $flags, $charset ?: $this['charset'], $doubleEncode);
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
    public function json($data = [], $status = 200, array $headers = [])
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
    public function sendFile($file, $status = 200, array $headers = [], $disposition = null)
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
    public function abort($statusCode, $message = '', array $headers = [])
    {
        if ($statusCode == 404) {
            throw new NotFoundHttpException($message);
        }

        throw new HttpException($statusCode, $message, null, $headers);
    }

}
