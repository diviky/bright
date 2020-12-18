<?php

namespace Diviky\Bright\Database\Traits;

trait Outfile
{
    protected $outpath;

    public function out($path = null, $local = true, $count = 10000, $options = [])
    {
        $path = $this->generateFilePath($path);

        $options = \array_merge([
            'separated' => ',',
            'enclosed'  => '"',
            'ends'      => '\n',
        ], $options);

        if ($local) {
            return $this->outFile($path, $options);
        }

        return $this->outLoop($path, $count, $options);
    }

    public function into($table, $path = null, $options = [], $ignore = false)
    {
        $options = \array_merge([
            'separated' => ',',
            'enclosed'  => '"',
            'ends'      => '\n',
        ], $options);

        $table = $this->grammar->wrapTable($table);
        if ($path) {
            $path = $this->outFile($path, $options);

            $sql = "LOAD DATA LOCAL INFILE '" . $path . "'";
            $sql .= (($ignore) ?' IGNORE' : '').' INTO TABLE ' . $table;
            $sql .= " FIELDS TERMINATED BY '".$options['separated']."'";
            $sql .= " OPTIONALLY ENCLOSED BY '".$options['enclosed']."'";
            $sql .= " LINES TERMINATED BY '".$options['ends']."'";
        } else {
            $sql = 'INSERT '.(($ignore) ? ' IGNORE ': '').' INTO ' . $table . ' ' . $this->toQuery();
        }

        return $this->statement($sql);
    }

    protected function outLoop($file = null, $count = 10000, $options = []): string
    {
        $options = \array_merge([
            'separated' => ',',
            'enclosed'  => '"',
            'ends'      => '\n',
        ], $options);

        $file = $this->generateFilePath($file);

        $fp = \fopen($file, 'w+');

        $rows = $this->flatChunk($count);

        foreach ($rows as $row) {
            \fwrite($fp, $options['enclosed'] . \implode('","', (array) $row) . $options['enclosed'] . $options['ends']);
        }

        \fclose($fp);

        return $file;
    }

    protected function outFile($file = null, $options = []): string
    {
        $options = \array_merge([
            'separated' => ',',
            'enclosed'  => '"',
            'ends'      => '\n',
        ], $options);

        $file = $this->generateFilePath($file);

        $sql = $this->toQuery();

        $out = 'SELECT * FROM (' . $sql . ') AS export';
        $out .= " INTO OUTFILE '" . $file . "'";
        $out .= " FIELDS TERMINATED BY '".$options['separated']."'";
        $out .= " OPTIONALLY ENCLOSED BY '".$options['enclosed']."'";
        $out .= " LINES TERMINATED BY '".$options['ends']."'";

        $this->statement($out);

        return $file;
    }

    protected function getOutPath()
    {
        return $this->outpath;
    }

    protected function generateFilePath($file)
    {
        $file          = $file && is_string($file) ? $file : \sys_get_temp_dir() . '/' . \uniqid() . '.csv';
        $this->outpath = $file;

        return $file;
    }
}
