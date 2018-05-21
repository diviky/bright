<?php

namespace Karla\Helpers;

use Speedwork\Core\Helper;

/**
 * @author Sankar <sankar.suda@gmail.com>
 */
class Speed extends Helper
{
    public function formatToSave($save, $required = null)
    {
        $required = ($required) ? explode(',', $required) : null;
        $fields = array_keys($save);

        $pass = true;
        // check all required keys exits in fields
        if (!empty($required)) {
            foreach ($required as $key) {
                if (!in_array($key, $fields)) {
                    $pass = false;
                    break;
                }
            }
        }

        if ($pass !== true) {
            return [];
        }

        $total = count($save[$fields[0]]);
        $data = [];

        for ($i = 0; $i < $total; ++$i) {
            $row = [];
            $add = true;
            foreach ($fields as $field) {
                $value = $save[$field][$i];

                if (!empty($required)
                    && in_array($field, $required)
                    && empty($value)
                ) {
                    $add = false;
                    continue;
                }

                $row[$field] = $value;
            }

            if ($add) {
                $data[] = $row;
            }
        }
        unset($save, $required);

        return $data;
    }

    public function formatFiles($files = [])
    {
        $data = [];
        $names = array_keys($files);

        foreach ($names as $name) {
            $file = $files[$name];

            if (is_array($file['name'])) {
                $fields = array_keys($file);

                foreach ($file['name'] as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $k => $v) {
                            $add = true;
                            $values = [];
                            foreach ($fields as $field) {
                                $val = $file[$field][$key][$k];
                                if ($field == 'name' && empty($val)) {
                                    $add = false;
                                    continue;
                                }
                                $values[$field] = $val;
                            }
                            if ($add) {
                                if (is_numeric($key)) {
                                    $data[$name][$key] = $values;
                                } else {
                                    $data[$name][$key][] = $values;
                                }
                            }
                        }
                    } else {
                        $add = true;
                        $values = [];
                        foreach ($fields as $field) {
                            $val = $file[$field][$key];
                            if ($field == 'name' && empty($val)) {
                                $add = false;
                                continue;
                            }
                            $values[$field] = $val;
                        }
                        if ($add) {
                            if (is_numeric($key)) {
                                $data[$name][$key] = $values;
                            } else {
                                $data[$name][$key][] = $values;
                            }
                        }
                    }
                }
            } else {
                $data[$name][] = $file;
            }
        }

        return $data;
    }

    public function nextOrder($tbl, $where = [])
    {
        $row = $this->get('database')->find($tbl, 'first', [
            'fields' => ['MAX(ordering) as morder'],
            'conditions' => $where,
        ]);

        $maxord = $row['morder'];

        return $maxord + 1;
    }

    public function reOrder($table, $where = [], $field = 'id')
    {
        $rows = $this->get('database')->find($table, 'all', [
            'order' => ['ordering'],
            'conditions' => $where,
            'fields' => [$field, 'ordering'],
        ]);

        // compact the ordering numbers
        $i = 0;
        foreach ($rows as $row) {
            ++$i;
            if ($row['ordering'] != $i) {
                $this->get('database')->update(
                    $table,
                    ['ordering' => $i],
                    [$field => $row[$field]]
                );
            }
        }

        return true;
    }

    public function sorting($table, $data = [], $field = 'id')
    {
        if (empty($data)) {
            return true;
        }

        $i = 0;
        foreach ($data as $id => $order) {
            if (is_array($order)) {
                $this->sorting($table, $order, $field);
            } else {
                ++$i;
                if ($order != $i) {
                    $update = [
                        'ordering' => $i,
                    ];
                    $this->get('database')->update($table, $update, [
                        $field => $id,
                    ]);
                }
            }
        }
    }
}
