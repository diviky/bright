<?php

declare(strict_types=1);

namespace Diviky\Bright\Helpers;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

/**
 * @author sankar <sankar.suda@gmail.com>
 */
class Stream
{
    /**
     * @var array
     */
    protected $mime_types = [
        '.txt' => 'text/plain',
        '.json' => 'application/json',
        '.xml' => 'application/xml',
        '.doc' => 'application/msword',
        '.rtf' => 'application/rtf',
        '.xls' => 'application/vnd.ms-excel',
        '.xlsx' => 'application/vnd.ms-excel',
        '.csv' => 'application/vnd.ms-excel',
        '.ppt' => 'application/vnd.ms-powerpoint',
        '.pdf' => 'application/pdf',
    ];

    /**
     * CSV file separator.
     *
     * @var string
     */
    protected $separator = ',';

    /**
     * Line ending.
     *
     * @var string
     */
    protected $lineEnd = "\r\n";

    /**
     * File streamr.
     *
     * @var mixed
     */
    protected $stream;

    /**
     * File streamr.
     *
     * @var string
     */
    protected $filename;

    /**
     * Start the stream.
     *
     * @param  string  $filename
     * @param  bool  $write
     * @return $this
     */
    public function start($filename, $write = false): self
    {
        $this->filename = $filename;

        $ext = \strtolower(\strrchr($filename, '.'));

        if ($ext == '.csv') {
            $this->separator = ',';
        }

        if ($write) {
            return $this->write($filename);
        }

        \set_time_limit(0);

        return $this;
    }

    /**
     * Set the headers file.
     *
     * @param  array|Collection|\Iterator  $fields
     */
    protected function setHeader($fields): self
    {
        $fields = $this->toArray($fields);
        $fields = \array_keys($fields);

        $out = [];
        foreach ($fields as $field) {
            $field = \strtoupper((string) $field);
            if (\strpos($field, ' AS ') !== false) {
                $field = \explode(' AS ', $field);
                $field = \trim($field[1]);
            } elseif (\strpos($field, '.') !== false) {
                $field = \explode('.', $field);
                $field = \trim($field[1]);
            }

            $out[] = \ucwords($field);
        }

        $this->implode($out);

        return $this;
    }

    /**
     * Set the seperator.
     *
     * @param  string  $string
     */
    public function setSeparator($string): self
    {
        $this->separator = $string;

        return $this;
    }

    /**
     * Export as excel.
     *
     * @param  array|Collection|\Iterator  $rows
     * @param  array|Collection|\Iterator  $fields
     * @param  mixed  $disposition
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function excel($rows, $fields, array $headers = [], $disposition = 'attachment')
    {
        $headers = array_merge(['X-Vapor-Base64-Encode' => 'True'], $headers);

        return response()->streamDownload(function () use ($rows, $fields): void {
            $this->setHeader($fields);
            $this->flushRows($rows);
        }, $this->filename, $headers, $disposition);
    }

    /**
     * Out put the file.
     *
     * @param  array|Collection|\Iterator  $rows
     * @param  array  $fields
     * @param  string  $disposition
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function stream($rows, $fields = [], array $headers = [], $disposition = 'attachment')
    {
        $headers = array_merge(['X-Vapor-Base64-Encode' => 'True'], $headers);

        return response()->streamDownload(function () use ($rows, $fields): void {
            $this->output($rows, $fields);
        }, $this->filename, $headers, $disposition);
    }

    /**
     * Out put the file.
     *
     * @param  array|Collection|\Iterator  $rows
     * @param  array  $fields
     */
    protected function output($rows, $fields = []): self
    {
        $rows = $this->toArray($rows);

        if (empty($fields) && !empty($rows)) {
            $fields = (array) $rows[0];
        }

        $this->setHeader($fields);

        foreach ($rows as $row) {
            $this->flush($row, $fields);
        }

        return $this->stopFile();
    }

    /**
     * Function to read local and remote file.
     *
     * @param  string  $filename
     */
    public function readFile($filename): bool
    {
        $chunksize = 2 * (1024 * 1024); // how many bytes per chunk
        $stream = \fopen($filename, 'rb');

        if ($stream === false) {
            return false;
        }

        while (!\feof($stream)) {
            echo \fread($stream, $chunksize);
        }

        return \fclose($stream);
    }

    /**
     * Close the file writing stream.
     *
     * @param  string  $filepath
     */
    protected function write($filepath): self
    {
        $ext = \strtolower(\strrchr($filepath, '.'));

        if ($ext == '.csv') {
            $this->separator = ',';
        }

        $this->stream = \fopen($filepath, 'w');

        \set_time_limit(0);

        return $this;
    }

    /**
     * Write content to file.
     */
    protected function writeFile(string $content): self
    {
        if ($this->stream) {
            \fwrite($this->stream, $content);
        }

        return $this;
    }

    /**
     * Close the file writing stream.
     */
    protected function stopFile(): self
    {
        if (is_resource($this->stream)) {
            \fclose($this->stream);
        }

        return $this;
    }

    /**
     * Clean the values.
     *
     * @param  mixed  $input
     */
    protected function clean($input): string
    {
        if (is_array($input)) {
            $input = implode(',', $input);
        }

        $input = '"' . \str_replace('"', '""', (string) $input) . '"';

        return \str_replace(["\n", "\t", "\r"], '', $input);
    }

    /**
     * Write the details.
     *
     * @param  array|object  $row
     *
     * @SuppressWarnings(PHPMD)
     */
    protected function flush($row, array $fields): self
    {
        if (!is_array($row)) {
            $row = (array) $row;
        }

        $out = [];
        foreach ($fields as $k => $v) {
            $out[] = $this->clean($row[$k] ?? '');
        }

        return $this->implode($out);
    }

    /**
     * Write multiple rows to file.
     *
     * @param  array|Collection|\Iterator  $rows
     */
    protected function flushRows($rows): self
    {
        $rows = $this->toArray($rows);

        foreach ($rows as $row) {
            $this->implode($row, true);
        }

        return $this->stopFile();
    }

    /**
     * @param  array|object  $row
     */
    protected function implode($row = [], bool $clean = false): self
    {
        if (\is_object($row)) {
            $row = (array) $row;
        }

        if ($clean) {
            $row = \array_map([$this, 'clean'], $row);
        }

        if ($this->stream) {
            return $this->writeFile(\implode($this->separator, $row) . $this->lineEnd);
        }

        echo \implode($this->separator, $row) . $this->lineEnd;

        return $this;
    }

    /**
     * Convert rows to array.
     *
     * @param  array|Arrayable|Collection|\Iterator|JsonResource|\JsonSerializable|LazyCollection|mixed  $rows
     * @return array
     */
    protected function toArray($rows)
    {
        if ($rows instanceof Collection) {
            $rows = $rows->toArray();
            $rows = \json_decode((string) \json_encode($rows), true);
        }

        if ($rows instanceof LazyCollection) {
            $rows = $rows->toArray();
            $rows = \json_decode((string) \json_encode($rows), true);
        }

        if ($rows instanceof Arrayable) {
            $rows = $rows->toArray();
            $rows = \json_decode((string) \json_encode($rows), true);
        }

        if ($rows instanceof \JsonSerializable) {
            $rows = $rows->jsonSerialize();
            $rows = \json_decode((string) \json_encode($rows), true);
        }

        if ($rows instanceof \Iterator) {
            $rows->rewind();
            $rows = \iterator_to_array($rows);
        }

        return $rows;
    }
}
