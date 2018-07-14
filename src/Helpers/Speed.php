<?php

namespace Karla\Helpers;

use Karla\Routing\Capsule;

/**
 * @author Sankar <sankar.suda@gmail.com>
 */
class Speed extends Capsule
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
        $max = $this->get('db')->table($tbl)
            ->where($where)
            ->max('ordering');

        return $max + 1;
    }

    public function reOrder($table, $where = [], $field = 'id')
    {
        $rows = $this->get('db')->table($table)
            ->where($where)
            ->orderBy('ordering', 'asc')
            ->get([$field, 'ordering']);

        // compact the ordering numbers
        $i = 0;
        foreach ($rows as $row) {
            ++$i;
            if ($row->ordering != $i) {
                $this->get('db')->table($table)
                    ->where($field, $row->$field)
                    ->update(['ordering' => $i]);
            }
        }

        return $this;
    }

    public function sorting($table, $values = [], $field = 'id')
    {
        if (empty($values)) {
            return $this;
        }

        $i = 0;
        foreach ($values as $id => $value) {
            if (is_array($value)) {
                $this->sorting($table, $value, $field);
            } else {
                ++$i;
                if ($value != $i) {
                    $update = ['ordering' => $i];
                    $this->get('db')->table($table)
                        ->where($field, $id)
                        ->update($update);
                }
            }
        }

        return $this;
    }
}
