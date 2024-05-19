<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Concerns;

use Diviky\Bright\Database\Filters\FiltersScope;
use Diviky\Bright\Database\Filters\Ql\Parser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait Filter
{
    /**
     * Filter types.
     *
     * @var array
     */
    protected $types = [];

    /**
     * Alias names for filters.
     *
     * @var array
     */
    protected $aliases = [];

    public function filters(array $types = [], array $aliases = []): self
    {
        $this->types = $types;
        $this->aliases = $aliases;

        return $this;
    }

    /**
     * Add Filters to database query builder.
     *
     * @param  array  $data
     * @return $this
     */
    public function filter($data = []): self
    {
        return $this->filterExact($this->cleanUpFilters($data['filter'] ?? []))
            ->filterParse($this->cleanUpFilters($data['parse'] ?? []))
            ->filterMatch($this->cleanUpFilters($data['dfilter'] ?? []), $data)
            ->filterLike($this->cleanUpFilters($data['lfilter'] ?? []))
            ->filterLeft($this->cleanUpFilters($data['efilter'] ?? []))
            ->filterDateRanges($this->cleanUpFilters($data['date'] ?? []))
            ->filterDatetimes($this->cleanUpFilters($data['datetime'] ?? []))
            ->filterDatetimes($this->cleanUpFilters($data['timestamp'] ?? []))
            ->filterUnixTimes($this->cleanUpFilters($data['unix'] ?? []))
            ->filterUnixTimes($this->cleanUpFilters($data['unixtime'] ?? []))
            ->filterRange($this->cleanUpFilters($data['range'] ?? []))
            ->filterBetween($this->cleanUpFilters($data['between'] ?? []))
            ->filterScopes($this->cleanUpFilters($data['scope'] ?? []));
    }

    /**
     * Set alias names for filters.
     *
     * @return self
     */
    public function addColumnAlias(string $column, string $alias)
    {
        $this->aliases[$column] = $alias;

        return $this;
    }

    /**
     * Set filter type.
     *
     * @return self
     */
    public function addFilterType(string $filter, string $type)
    {
        $this->types[$filter] = $type;

        return $this;
    }

    /**
     * Add where condition for filters.
     *
     * @param  string  $column
     * @param  array|string  $value
     * @param  string  $condition
     */
    public function addWhere($column, $value, $condition = '='): self
    {
        if ($this->hasModel()) {
            if (Str::startsWith($column, ':')) {
                return $this->filterScopes([substr($column, 1) => $value]);
            }

            if (Str::contains($column, '.')) {
                return $this->filterRelations(explode('|', $column), $value, $condition);
            }
        }

        if (Str::contains($column, '|')) {
            $columns = \explode('|', $column);

            $this->whereFilter(array_map(function ($column) {
                return $this->cleanField($column);
            }, $columns), $value, $condition);
        } elseif (is_array($value)) {
            $this->whereIn($this->cleanField($column), $value);
        } else {
            $this->where($this->cleanField($column), $condition, $value);
        }

        return $this;
    }

    /**
     * Clean the filters.
     *
     * @param  mixed  $filters
     * @return array
     */
    protected function cleanUpFilters($filters)
    {
        if (!isset($filters)) {
            return [];
        }

        if (!is_array($filters) || empty($filters)) {
            return [];
        }

        return array_filter($filters, function ($value) {
            return $value !== null && $value !== false && $value !== '';
        });
    }

    protected function filterExact(array $filters = []): self
    {
        foreach ($filters as $column => $value) {
            if (isset($value) && $value[0] != '') {
                $type = $this->types[$column] ?? null;

                if (is_null($type)) {
                    $this->addWhere($column, $value);

                    continue;
                }

                if ($type == 'scope') {
                    $this->filterScopes([$column => $value]);
                } elseif ($type == 'like') {
                    $this->filterLike([$column => $value]);
                } elseif ($type == 'left') {
                    $this->filterLeft([$column => $value]);
                } elseif ($type == 'right') {
                    $this->filterRight([$column => $value]);
                } elseif ($type == 'between') {
                    $this->filterBetween([$column => $value]);
                } elseif ($type == 'range') {
                    $this->filterRange([$column => $value]);
                } elseif ($type == 'unixtime' || $type == 'unix') {
                    $this->filterUnixTimes([$column => $value]);
                } elseif ($type == 'datetime' || $type == 'timestamp') {
                    $this->filterDatetimes([$column => $value]);
                } elseif ($type == 'date') {
                    $this->filterDateRanges([$column => $value]);
                } elseif ($type == 'parser') {
                    $this->filterParse([$column => $value]);
                } else {
                    $this->addWhere($column, $value);
                }
            }
        }

        return $this;
    }

    protected function filterLike(array $filters = []): self
    {
        foreach ($filters as $column => $value) {
            if (isset($value) && $value != '' && !empty($column)) {
                $value = '%' . $value . '%';

                $this->addWhere($column, $value, 'like');
            }
        }

        return $this;
    }

    protected function filterLeft(array $filters = []): self
    {
        foreach ($filters as $column => $value) {
            if (isset($value) && $value != '' && !empty($column)) {
                $value = '%' . $value;
                $this->addWhere($column, $value, 'like');
            }
        }

        return $this;
    }

    protected function filterRight(array $filters = []): self
    {
        foreach ($filters as $column => $value) {
            if (isset($value) && $value != '' && !empty($column)) {
                $value .= '%';
                $this->addWhere($column, $value, 'like');
            }
        }

        return $this;
    }

    protected function filterMatch(array $filters = [], array $data = []): self
    {
        foreach ($filters as $value => $column) {
            $value = $data[$value];
            if (isset($value) && $value != '' && !empty($column)) {
                if (Str::startsWith('%', $column)) {
                    $value = '%' . $value;

                    $this->addWhere(ltrim($column, '%'), $value, 'like');
                } elseif (Str::endsWith('%', $column)) {
                    $value .= '%';

                    $this->addWhere(rtrim($column, '%'), $value, 'like');
                } else {
                    $value = '%' . $value . '%';

                    $this->addWhere($column, $value, 'like');
                }
            }
        }

        return $this;
    }

    protected function filterScopes(array $scopes): self
    {
        foreach ($scopes as $scope => $values) {
            if (empty($scope)) {
                continue;
            }

            $scope = $this->aliases[$scope] ?? $scope;

            (new FiltersScope())($this->builder, $values, $scope);
        }

        return $this;
    }

    protected function filterRelations($attributes, $searchTerm, string $condition = '='): self
    {
        $this->builder->where(function (Builder $query) use ($attributes, $searchTerm, $condition) {
            foreach (Arr::wrap($attributes) as $attribute) {
                $query->when(
                    str_contains($attribute, '.'),
                    function (Builder $query) use ($attribute, $searchTerm, $condition) {
                        [$relationName, $relationAttribute] = explode('.', $attribute);

                        $query->orWhereHas($relationName, function (Builder $query) use ($relationAttribute, $searchTerm, $condition) {
                            $query->where($relationAttribute, $condition, $searchTerm);
                        });
                    },
                    function (Builder $query) use ($attribute, $searchTerm, $condition) {
                        $query->orWhere($attribute, $condition, $searchTerm);
                    }
                );
            }
        });

        return $this;
    }

    protected function filterDateRanges(array $date_range): self
    {
        foreach ($date_range as $column => $date) {
            if (empty($date)) {
                continue;
            }

            if (!\is_array($date) && is_string($date)) {
                $date = \explode(' - ', $date);
                $date = [
                    'from' => isset($date[0]) ? \trim($date[0]) : null,
                    'to' => isset($date[1]) ? \trim($date[1]) : null,
                ];
            }

            $from = $this->toTime($date['from'], 'Y-m-d');
            $to = $this->toTime($date['to'], 'Y-m-d');
            $column = $this->cleanField($column);

            if ($from && $to) {
                $this->whereDateBetween($column, [$from, $to]);
            } elseif ($from) {
                $this->whereDate($column, '=', $from);
            }
        }

        return $this;
    }

    protected function filterDatetimes(array $datetime): self
    {
        foreach ($datetime as $column => $date) {
            if (empty($date)) {
                continue;
            }

            if (!\is_array($date)) {
                $date = \explode(' - ', $date);
                $date = [
                    'from' => isset($date[0]) ? \trim($date[0]) : null,
                    'to' => isset($date[1]) ? \trim($date[1]) : null,
                ];
            }

            $from = $date['from'];
            $to = $date['to'];
            $to = $to ?: $from;

            $from = $this->toTime($from, 'Y-m-d H:i:s', '00:00:00');
            $to = $this->toTime($to, 'Y-m-d H:i:s', '23:59:59');

            $this->whereBetween($this->cleanField($column), [$from, $to]);
        }

        return $this;
    }

    protected function filterUnixTimes(array $unixtime): self
    {
        foreach ($unixtime as $column => $date) {
            if (empty($date)) {
                continue;
            }

            if (!\is_array($date)) {
                $date = \explode(' - ', $date);
                $date = [
                    'from' => isset($date[0]) ? \trim($date[0]) : null,
                    'to' => isset($date[1]) ? \trim($date[1]) : null,
                ];
            }

            $from = isset($date['from']) ? \trim($date['from']) : null;
            $to = isset($date['to']) ? \trim($date['to']) : null;
            $to = $to ?? $from;

            if (!\is_numeric($from)) {
                $from = $this->toTime($from, null, '00:00:00');
                $from = $from && !is_string($from) ? $from->timestamp : null;
            }

            if (!\is_numeric($to)) {
                $to = $this->toTime($to, null, '23:59:59');
                $to = $to && !is_string($to) ? $to->timestamp : null;
            }

            $this->whereBetween($this->cleanField($column), [$from, $to]);
        }

        return $this;
    }

    protected function filterRange(array $ranges): self
    {
        foreach ($ranges as $column => $date) {
            if (empty($date)) {
                continue;
            }

            if (!\is_array($date)) {
                $date = \explode(' - ', $date);
                $date = [
                    'from' => isset($date[0]) ? \trim($date[0]) : null,
                    'to' => isset($date[1]) ? \trim($date[1]) : null,
                ];
            }

            $from = $this->toTime($date['from']);
            $to = $this->toTime($date['to']);
            $column = $this->cleanField($column);

            if ($from && $to) {
                $this->whereBetween($column, [$from, $to]);
            } elseif ($from) {
                $this->addWhere($column, $from);
            }
        }

        return $this;
    }

    protected function filterBetween(array $between): self
    {
        foreach ($between as $column => $date) {
            if (empty($date)) {
                continue;
            }

            if (!\is_array($date)) {
                $date = \explode(' - ', $date);
                $date = [
                    'from' => isset($date[0]) ? \trim($date[0]) : null,
                    'to' => isset($date[1]) ? \trim($date[1]) : null,
                ];
            }

            $from = $date['from'];
            $to = $date['to'];
            $column = $this->cleanField($column);

            if ($from && $to) {
                $this->whereBetween($column, [$from, $to]);
            } elseif ($from) {
                $this->addWhere($column, $from);
            }
        }

        return $this;
    }

    protected function filterParse(array $filters): self
    {
        foreach ($filters as $input) {
            if (empty($input)) {
                continue;
            }

            $parseTree = (new Parser())->parse($input);

            if (isset($parseTree)) {
                $this->addParserPredicates($parseTree->getPredicates());
            }
        }

        return $this;
    }

    protected function addParserPredicates(array $predicates): self
    {
        $this->where(function ($query) use ($predicates): void {
            foreach ($predicates as $result) {
                if (is_array($result)) {
                    $this->addParserPredicates($result);
                } else {
                    $combinedBy = $result->getCombinedBy();
                    $predicate = $result->getPredicate();

                    $op = ($predicate->op == '=~') ? 'like' : $predicate->op;

                    if ($op == ':') {
                        $this->filterScopes([(string) $predicate->left => $predicate->right]);
                    } elseif (substr($op, 0, 1) == '.') {
                        $this->filterRelations([$predicate->left], $predicate->right, substr($op, 1));
                    } else {
                        if ($combinedBy === 'OR') {
                            $query->orWhere($this->cleanField((string) $predicate->left), $op, $predicate->right);
                        } else {
                            $query->where($this->cleanField((string) $predicate->left), $op, $predicate->right);
                        }
                    }
                }
            }
        });

        return $this;
    }

    /**
     * Cleanup the give column.
     *
     * @param  string  $string  Database column
     * @return string Cleaned String
     */
    protected function cleanField($string)
    {
        if (Str::contains($string, '.')) {
            [$alias, $column] = explode('.', $string, 2);
            $column = $this->aliases[$column] ?? $column;

            return $this->getExpressionValue($this->raw($alias . '.' . $this->wrap($column)));
        }

        return $this->getExpressionValue($this->raw($this->wrap($string)));
    }

    /**
     * Convert time to proper format.
     *
     * @param  string  $time
     * @param  string  $format
     * @param  null|mixed  $prefix
     * @return null|\Illuminate\Support\Carbon|string
     */
    protected function toTime($time, $format = null, $prefix = null)
    {
        if (empty($time)) {
            return null;
        }

        if (Str::contains($format, ':')) {
            $time = Str::contains($time, ':') ? $time : $time . ' ' . $prefix;
        }

        if (\strtotime($time) === false) {
            $time = now()->toDateTimeString();
        }

        return carbon(\trim($time), $format);
    }
}
