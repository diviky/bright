<?php

namespace Karla\Routing;

use Illuminate\Http\RedirectResponse as BaseRedirectResponse;

class RedirectResponse extends BaseRedirectResponse
{
    protected $with  = [];

    /**
     * Flash a piece of data to the session.
     *
     * @param array|string $key
     * @param mixed        $value
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function with($key, $value = null)
    {
        $key = \is_array($key) ? $key : [$key => $value];

        foreach ($key as $k => $v) {
            $this->with[$k] = $v;
        }

        return parent::with($key, $value);
    }

    /**
     * Sets the redirect target of this response.
     *
     * @param string $url The URL to redirect to
     *
     * @throws \InvalidArgumentException
     *
     * @return RedirectResponse the current response
     */
    public function setTargetUrl($url)
    {
        if (empty($url)) {
            throw new \InvalidArgumentException('Cannot redirect to an empty URL.');
        }

        $this->headers->set('Content-Type', 'application/json');

        $this->targetUrl  = $url;
        $data             = $this->with;
        $data['redirect'] = $url;

        $this->setContent(\json_encode($data));

        return $this;
    }

    /**
     * Is the response a redirect of some form?
     *
     * @param string $location
     *
     * @return bool
     */
    public function isRedirect(?string $location = null): bool
    {
        return true;
    }
}
