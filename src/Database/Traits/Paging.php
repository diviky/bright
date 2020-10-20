<?php

namespace Diviky\Bright\Database\Traits;

trait Paging
{
    public function paging($perPage = 25, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $perPage = \is_null($perPage) ? 25 : $perPage;

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
