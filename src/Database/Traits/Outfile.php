<?php

namespace Karla\Database\Traits;

trait Outfile
{
    protected $outpath;

    public function out($path, $local = true, $count = 10000)
    {
        $this->outpath = $path;

        if ($local) {
            return $this->outFile($path);
        }

        return $this->outLoop($path, $count);
    }

    protected function outLoop($file, $count)
    {
        $fp = fopen($file, 'w+');

        $rows = $this->flatChunk($count);

        foreach ($rows as $row) {
            fwrite($fp, '"' . implode('","', (array) $row) . '"' . "\r\n");
        }

        fclose($fp);

        return $file;
    }

    protected function outFile($path)
    {
        $sql = $this->toQuery();

        $out = 'SELECT * FROM(' . $sql . ') AS export';
        $out .= 'INTO OUTFILE ' . $this->getOutPath();
        $out .= "FIELDS TERMINATED BY ',' ";
        $out .= "OPTIONALLY ENCLOSED BY '\"' ";
        $out .= "LINES TERMINATED BY '\n'";

        $this->statement($out);

        return $path;
    }

    protected function getOutPath()
    {
        return $this->outpath;
    }
}
