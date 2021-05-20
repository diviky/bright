<?php

namespace Diviky\Bright\Database\Traits;

trait Paging
{
    /**
     * Paginate the given query into a simple paginator.
     *
     * @param int      $perPage
     * @param array    $columns
     * @param string   $pageName
     * @param null|int $page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paging($perPage = 25, $columns = ['*'], $pageName = 'page', $page = null)
    {
        if (\count($this->tables) > 0) {
            $rows = $this->paginateComplex($perPage, $columns, $pageName, $page);
        } else {
            $rows = $this->paginate($perPage, $columns, $pageName, $page);
        }

        $rows->from   = $rows->firstItem();
        $rows->to     = $rows->lastItem();
        $rows->serial = $rows->firstItem();

        $i = $rows->perPage() * ($rows->currentPage() - 1);
        $rows->transform(function ($row) use (&$i) {
            $row->serial = ++$i;

            return $row;
        });

        return $rows;
    }
}
