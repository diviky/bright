<?php

namespace Diviky\Bright\Helpers;

use Illuminate\Support\Collection;
use Iterator;

/**
 * @author sankar <sankar.suda@gmail.com>
 */
class Stream
{
    protected $mime_types = [
        '.txt'  => 'text/plain',
        '.json' => 'application/json',
        '.xml'  => 'application/xml',
        '.doc'  => 'application/msword',
        '.rtf'  => 'application/rtf',
        '.xls'  => 'application/vnd.ms-excel',
        '.xlsx' => 'application/vnd.ms-excel',
        '.csv'  => 'application/vnd.ms-excel',
        '.ppt'  => 'application/vnd.ms-powerpoint',
        '.pdf'  => 'application/pdf',
    ];

    protected $separator = ',';
    protected $lineEnd   = "\r\n";
    protected $handle;

    public function start($filename, $write = false)
    {
        if ($write) {
            return $this->write($filename);
        }

        $ext  = \strtolower(\strrchr($filename, '.'));
        $type = $this->mime_types[$ext];

        if ('.csv' == $ext) {
            $this->separator = ',';
        }

        \set_time_limit(0);
        \header('Content-Type: application/octet-stream');
        \header('Content-Description: File Transfer');
        \header('Content-Type: ' . $type);
        \header('Content-Disposition: attachment;filename="' . $filename . '"');

        $seconds = 30;
        \header('Expires: ' . \gmdate('D, d M Y H:i:s', \time() + $seconds) . ' GMT');
        \header('Cache-Control: max-age=' . $seconds . ', s-maxage=' . $seconds . ', must-revalidate, proxy-revalidate');

        \session_cache_limiter(false); // Disable session_start() caching headers
        if (\session_id()) {
            // Remove Pragma: no-cache generated by session_start()
            if (\function_exists('header_remove')) {
                \header_remove('Pragma');
            } else {
                \header('Pragma:');
            }
        }

        echo "\xEF\xBB\xBF";

        return $this;
    }

    public function setHeader($fields = [])
    {
        if ($fields instanceof Collection) {
            $fields = (array) $fields->first();
            $fields = \array_keys($fields);
        }

        if ($fields instanceof Iterator) {
            $fields->rewind();
            $fields = (array) $fields->current();
            $fields = \array_keys($fields);
        }

        if (\is_array($fields)) {
            $out = [];
            foreach ($fields as $field) {
                $field = \strtoupper($field);
                if (false !== \strpos($field, ' AS ')) {
                    $field = \explode(' AS ', $field);
                    $field = \trim($field[1]);
                }
                $out[] = \ucwords($field);
            }

            $this->implode($out);
        }

        return $this;
    }

    public function setSeparator($string)
    {
        $this->separator = $string;

        return $this;
    }

    public function excel($rows = [], $headers = [])
    {
        $this->setHeader($headers);
        $this->flushRows($rows);
    }

    public function output($rows = [], $fields = [])
    {
        if ($rows instanceof Collection) {
            $rows = $rows->toArray();
        }

        if ($rows instanceof Iterator) {
            $rows->rewind();
            $rows = (array) \iterator_to_array($rows);
        }

        if (empty($fields)) {
            $fields = \array_keys($rows[0]);
        }

        $this->setHeader($fields);

        foreach ($rows as $row) {
            $this->flush($row, $fields);
        }

        if ($this->handle) {
            return $this->stopFile();
        }

        return $this;
    }

    public function flush($row = [], $fields = null): self
    {
        $out = [];
        foreach ($fields as $k => $v) {
            $out[] = $this->clean($row[$k]);
        }

        $this->implode($row);

        return $this;
    }

    public function flushRows($rows = []): self
    {
        if ($rows instanceof Collection) {
            $rows = $rows->toArray();
            $rows = \json_decode(\json_encode($rows), true);
        }

        foreach ($rows as $row) {
            $this->implode($row, true);
        }

        if ($this->handle) {
            return $this->stopFile();
        }

        return $this;
    }

    public function clean($string)
    {
        $string = '"' . \str_replace('"', '""', $string) . '"';

        return \str_replace(["\n", "\t", "\r"], '', $string);
    }

    /**
     * Function to read local and remote file.
     *
     * @param [type] $filename [description]
     *
     * @return [type] [description]
     */
    public function readFile($filename)
    {
        $chunksize = 2 * (1024 * 1024); // how many bytes per chunk
        $buffer    = '';

        $handle = \fopen($filename, 'rb');

        if (false === $handle) {
            return false;
        }

        while (!\feof($handle)) {
            $buffer = \fread($handle, $chunksize);
            echo $buffer;
            \ob_flush();
            \flush();
        }

        return \fclose($handle);
    }

    public function write($filepath)
    {
        $ext = \strtolower(\strrchr($filepath, '.'));

        if ('.csv' == $ext) {
            $this->separator = ',';
        }

        $this->handle = \fopen($filepath, 'w');

        \set_time_limit(0);

        return $this;
    }

    public function writeFile($content)
    {
        \fwrite($this->handle, $content);

        return $this;
    }

    public function stopFile()
    {
        \fclose($this->handle);

        return $this;
    }

    protected function implode($row = [], $clean = false)
    {
        if (\is_object($row)) {
            $row = (array) $row;
        }

        if ($clean) {
            $row = \array_map([$this, 'clean'], $row);
        }

        if ($this->handle) {
            return $this->writeFile(\implode($this->separator, $row) . $this->lineEnd);
        }

        echo \implode($this->separator, $row) . $this->lineEnd;

        \flush();
        \ob_flush();

        return $this;
    }
}
