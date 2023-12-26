<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Middleware;

use Diviky\Bright\Models\Models;
use Illuminate\Http\Middleware\TrustHosts as Middleware;

class TrustWhiteListedHosts extends Middleware
{
    /**
     * Get the host patterns that should be trusted.
     *
     * @return array
     */
    public function hosts()
    {
        return array_merge([
            $this->allSubdomainsOfApplicationUrl(),
        ], $this->getWhiteListedHosts());
    }

    /**
     * Get the all domains added in whitelisted.
     *
     * @return array
     */
    protected function getWhiteListedHosts()
    {
        $hosts = Models::branding()::remember(30 * 60, 'domains')->pluck('domain');

        $hosts = $hosts->map(function ($row) {
            return '^(.+\.)?' . preg_quote($row) . '$';
        });

        return $hosts->toArray();
    }
}
