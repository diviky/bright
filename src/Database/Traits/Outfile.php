<?php

namespace Karla\Database\Traits;

trait Outfile
{
    protected $outpath;

    public function out($path = null, $local = true, $count = 10000)
    {
        $path          = $path ?: '/tmp/' . uniqid() . '.csv';
        $this->outpath = $path;

        if ($local) {
            return $this->outFile($path);
        }

        return $this->outLoop($path, $count);
    }

    protected function outLoop($file = null, $count = 10000)
    {
        $file          = $file ?: '/tmp/' . uniqid() . '.csv';
        $this->outpath = $file;

        $fp = fopen($file, 'w+');

        $rows = $this->flatChunk($count);

        foreach ($rows as $row) {
            fwrite($fp, '"' . implode('","', (array) $row) . '"' . "\r\n");
        }

        fclose($fp);

        return $file;
    }

    protected function outFile($file = null)
    {
        $file          = $file ?: '/tmp/' . uniqid() . '.csv';
        $this->outpath = $file;

        $sql = $this->toQuery();

        $out = 'SELECT * FROM(' . $sql . ') AS export';
        $out .= " INTO OUTFILE '" . $file . "'";
        $out .= " FIELDS TERMINATED BY ','";
        $out .= " OPTIONALLY ENCLOSED BY '\"'";
        $out .= " LINES TERMINATED BY '\n'";

        $this->statement($out);

        return $file;
    }

    protected function getOutPath()
    {
        return $this->outpath;
    }

    public function into($table, $file = true, $path = null)
    {
        $table = $this->grammar->wrapTable($table);
        if ($file) {
            $path = $this->outFile();

            $sql = "LOAD DATA LOCAL INFILE '" . $path . "'";
            $sql .= ' IGNORE INTO TABLE ' . $table;
            $sql .= " FIELDS TERMINATED BY ','";
            $sql .= " OPTIONALLY ENCLOSED BY '\"'";
            $sql .= " LINES TERMINATED BY '\n'";
        } else {
            $sql = 'INSERT INTO ' . $table . ' ' . $this->toQuery();
        }

        return $this->statement($out);
    }
}
