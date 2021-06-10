<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Concerns;

use Illuminate\Pagination\Paginator;

trait Paging
{
    /**
     * Database table names.
     *
     * @var array
     */
    protected $tables = [];

    /**
     * Total records of multiple tables.
     *
     * @var array
     */
    protected $tableTotals = [];

    public function tables(array $tables): self
    {
        \array_shift($tables);
        $this->tables = $tables;

        return $this;
    }

    /**
     * Create a new length-aware paginator instance.
     *
     * @param int      $perPage
     * @param array    $columns
     * @param string   $pageName
     * @param null|int $page
     */
    public function complexPaginate($perPage = 15, $columns = ['*'], $pageName = 'page', $page = null): \Illuminate\Pagination\LengthAwarePaginator
    {
        $total = $this->getCountForPagination($columns);
        $page = $page ?? Paginator::resolveCurrentPage($pageName);

        if (\count($this->tables) > 0) {
            $this->tableTotals[$this->from] = $total;
            foreach ($this->tables as $table) {
                $count = $this->getCountForTablePagination($table, $columns);

                $this->tableTotals[$table] = $count;
                $total += $count;
            }
        }

        $tables = [];
        if (1 == $page) {
            $tables[] = $this->from;
            $tables = \array_merge($tables, $this->tables);
            $skip = ($page - 1) * $perPage;
        } else {
            $totals = 0;
            $offset = $perPage * ($page - 1);
            $offsets = 0;

            foreach ($this->tableTotals as $table => $count) {
                if (0 == $count) {
                    unset($this->tableTotals[$table]);

                    continue;
                }

                $totals += $count;
                if ($totals < $offset) {
                    unset($this->tableTotals[$table]);
                } else {
                    $skip = $offset - $offsets;

                    break;
                }

                $offsets += $count;
            }

            $tables = \array_keys($this->tableTotals);
        }

        $results = collect();

        if ($total && \count($tables) > 0) {
            $count = 0;
            $skip = 0;
            $limit = $perPage;
            foreach ($tables as $table) {
                $results = $results->merge(
                    $this->skip($skip)
                        ->take($limit)
                        ->from($table)
                        ->get($columns)
                );

                $count += $results->count();
                $limit = $limit - $count;
                $skip = 0;
                if ($count >= $perPage) {
                    break;
                }
            }
        }

        return $this->paginator($results, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }

    /**
     * Get the count of the total records for the paginator.
     *
     * @param array  $columns
     * @param string $table
     *
     * @return int
     */
    public function getCountForTablePagination($table, $columns = ['*'])
    {
        $results = $this->runPaginationTableCountQuery($table, $columns);

        // Once we have run the pagination count query, we will get the resulting count and
        // take into account what type of query it was. When there is a group by we will
        // just return the count of the entire results set since that will be correct.
        if (!empty($this->groups)) {
            return \count($results);
        }

        if (!isset($results[0])) {
            return 0;
        }

        if (\is_object($results[0])) {
            return (int) $results[0]->aggregate;
        }

        return (int) \array_change_key_case((array) $results[0])['aggregate'];
    }

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
            $rows = $this->complexPaginate($perPage, $columns, $pageName, $page);
        } else {
            $rows = $this->paginate($perPage, $columns, $pageName, $page);
        }

        $rows->from = $rows->firstItem();
        $rows->to = $rows->lastItem();
        $rows->serial = $rows->firstItem();

        $i = $rows->perPage() * ($rows->currentPage() - 1);
        $rows->transform(function (object $row) use (&$i) {
            $row->serial = ++$i;

            return $row;
        });

        return $rows;
    }

    /**
     * Run a pagination count query.
     *
     * @param array $columns
     * @param mixed $table
     *
     * @return array
     */
    protected function runPaginationTableCountQuery($table, $columns = ['*'])
    {
        return $this->cloneWithout(['columns', 'orders', 'limit', 'offset'])
            ->cloneWithoutBindings(['select', 'order'])
            ->setAggregate('count', $this->withoutSelectAliases($columns))
            ->from($table)
            ->get()
            ->all();
    }
}
