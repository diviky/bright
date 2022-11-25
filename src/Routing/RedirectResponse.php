<?php

declare(strict_types=1);

namespace Diviky\Bright\Routing;

use Illuminate\Http\RedirectResponse as BaseRedirectResponse;

class RedirectResponse extends BaseRedirectResponse
{
    /**
     * @var array
     */
    protected $with = [];

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function setTargetUrl(string $url)
    {
        if ('' === $url) {
            throw new \InvalidArgumentException('Cannot redirect to an empty URL.');
        }

        $this->headers->set('Content-Type', 'application/json');

        $this->targetUrl = $url;
        $data = $this->with;
        $data['redirect'] = $url;

        $this->setContent(\json_encode($data));

        return $this;
    }

    /**
     * Is the response a redirect of some form?
     *
     * @param string $location
     *
     * @SuppressWarnings(PHPMD)
     */
    public function isRedirect(?string $location = null): bool
    {
        return true;
    }
}
