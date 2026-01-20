<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Eloquent;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class Collection extends EloquentCollection
{
    /**
     * Merge the relations attributes.
     *
     * @return $this
     */
    public function flats(array $except = [], array $exclude = [])
    {
        $this->transform(function ($row) use ($except, $exclude) {
            return $row->flat($except, $exclude);
        });

        return $this;
    }

    /**
     * Merge the relations attributes.
     *
     * @return $this
     */
    public function flattens(array $except = [], array $exclude = [])
    {
        $this->transform(function ($row) use ($except, $exclude) {
            return $row->flatten($except, $exclude);
        });

        return $this;
    }

    /**
     * Merge the relations attributes.
     *
     * @return $this
     */
    public function collapses(array $except = [], array $exclude = [])
    {
        $this->transform(function ($row) use ($except, $exclude) {
            return $row->collapse($except, $exclude);
        });

        return $this;
    }

    /**
     * Returns only the models from the collection with the specified keys.
     *
     * @return $this
     */
    public function few(array $keys)
    {
        $this->transform(function ($row) use ($keys) {
            return $row->some($keys);
        });

        return $this;
    }

    /**
     * Get the current page number.
     * This method handles both regular pagination and cursor pagination.
     *
     * @return int
     */
    public function currentPage()
    {
        $request = request();

        // Check if this is traditional pagination (page parameter exists)
        if ($request->has('page')) {
            return (int) $request->get('page');
        }

        // For cursor pagination, use session-based page tracking
        return $this->getSessionBasedPageNumber();
    }

    /**
     * Get the number of items to show per page.
     *
     * @return int
     */
    public function perPage()
    {
        $request = request();

        // Check common per_page parameter names
        if ($request->has('per_page')) {
            return (int) $request->get('per_page');
        }

        if ($request->has('limit')) {
            return (int) $request->get('limit');
        }

        // Default per page value
        return 15;
    }

    /**
     * Get the current page number using session-based tracking for cursor pagination.
     *
     * @return int
     */
    protected function getSessionBasedPageNumber()
    {
        $request = request();
        $pageKey = $this->getPaginationKey();

        // Check for explicit page reset
        if ($request->has('reset_page')) {
            session()->put("cursor_page_{$pageKey}", 1);

            return 1;
        }

        // Check for direct page navigation
        if ($request->has('goto_page')) {
            $page = max(1, (int) $request->get('goto_page'));
            session()->put("cursor_page_{$pageKey}", $page);

            return $page;
        }

        $currentPage = session()->get("cursor_page_{$pageKey}", 1);

        // If cursor parameter exists, update page counter based on navigation
        if ($request->has('cursor')) {
            $direction = $request->get('direction', 'next');

            if ($direction === 'next') {
                $currentPage++;
            } elseif ($direction === 'previous' && $currentPage > 1) {
                $currentPage--;
            }

            session()->put("cursor_page_{$pageKey}", $currentPage);
        }

        return $currentPage;
    }

    /**
     * Generate a unique key for this pagination instance.
     *
     * @return string
     */
    protected function getPaginationKey()
    {
        $request = request();
        $path = $request->path();

        $filter = $request->get('filter', '');
        $filter = is_array($filter) ? json_encode($filter) : $filter;

        // Create a unique key based on the current route
        return md5($path . ($filter ?: ''));
    }
}
