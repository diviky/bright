<?php

declare(strict_types=1);

namespace Diviky\Bright\Helpers;

use Diviky\Bright\Helpers\Iterator\ChunkedIterator;
use Diviky\Bright\Helpers\Iterator\MapIterator;
use Illuminate\Container\RewindableGenerator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;
use Iterator;
use Port\Csv\CsvReader;
use Port\Excel\ExcelReader;
use Port\Reader\ArrayReader;
use Port\Spreadsheet\SpreadsheetReader;
use wapmorgan\UnifiedArchive\UnifiedArchive;

/**
 * Reader class to get details from iterator.
 *
 * @author sankar <sankar.suda@gmail.com>
 *
 * @SuppressWarnings(PHPMD)
 */
class Reader
{
    /**
     * Fetch all the values form file.
     *
     * @param  array|\Iterator|string|\Traversable  $reader
     * @param  array  $options
     * @return \Iterator|\Port\Csv\CsvReader|\Port\Reader\ArrayReader|\Port\Spreadsheet\SpreadsheetReader|RewindableGenerator|\Traversable
     */
    public function fetchAll($reader, ?callable $callable = null, $options = [])
    {
        \set_time_limit(0);

        if (!\is_string($reader)) {
            $ext = isset($options['ext']) ? $options['ext'] : '.array';
        } else {
            $ext = isset($options['ext']) ? $options['ext'] : \strrchr($reader, '.');
            $ext = $ext == '.' ? '.xls' : $ext;

            if ($ext !== '.array') {
                $reader = $this->unzip($reader, $options);
            }
        }

        $ext = \strtolower($ext);
        $special = \in_array($ext, ['.array', '.iterator', '.generator']) ? true : false;

        if (!$special) {
            if (!\is_file($reader) || !\file_exists($reader)) {
                return new \EmptyIterator;
            }

            $lines = ($ext == '.txt') ? 1 : 5;

            \ini_set('auto_detect_line_endings', 'on');
            $file = new \SplFileObject($reader);
            // Auto detect delimiter
            if (empty($options['delimiter'])) {
                $options['delimiter'] = $this->detectDelimiter($reader, $lines);
            }

            switch ($ext) {
                case '.txt':
                case '.csv':
                    if ($options['delimiter']) {
                        $reader = new CsvReader($file, $options['delimiter']);
                    } else {
                        $reader = new CsvReader($file);
                    }
                    $reader->setStrict(false);

                    break;
                case '.xls':
                case '.xlsx':
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($file->getRealPath());

                    if ($options['delimiter'] === "\t" && $ext == '.xls' && $mime != 'application/vnd.ms-excel') {
                        $reader = new CsvReader($file, $options['delimiter']);
                    } else {
                        if (class_exists('Port\Excel\ExcelReader')) {
                            $reader = new ExcelReader($file, null, 0);
                        } else {
                            $reader = new SpreadsheetReader($file, null, 0);
                        }
                    }

                    break;
            }
        } else {
            switch ($ext) {
                case '.array':
                    if (!\is_array($reader) && isset($reader)) {
                        $reader = \explode("\n", $reader);
                    }

                    if (!is_array($reader)) {
                        $reader = [];
                    }

                    $reader = new ArrayReader($reader);

                    break;
                case '.generator':
                case '.iterator':
                default:
                    break;
            }
        }

        return $this->modify($reader, $callable, $options);
    }

    /**
     * Modify the iterator using callback closure.
     *
     * @param  \Iterator|\Port\Csv\CsvReader|\Port\Reader\ArrayReader|\Port\Spreadsheet\SpreadsheetReader|RewindableGenerator|\Traversable  $reader  Travarsable object
     * @param  array  $options
     * @return \Iterator|\Port\Csv\CsvReader|\Port\Reader\ArrayReader|\Port\Spreadsheet\SpreadsheetReader|RewindableGenerator|\Traversable
     */
    public function modify($reader, ?callable $callable = null, $options = [])
    {
        if (isset($options['limit']) && \is_numeric($options['limit'])) {
            $offset = 0;
            if (isset($options['offset']) && $options['offset']) {
                $offset = $options['offset'];
            } elseif (isset($options['page']) && $options['page']) {
                $offset = $options['limit'] * ($options['page'] - 1);
            }

            if ($offset && empty($options['total'])) {
                $options['total'] = $this->count($reader);
            }

            // number of records to fetch total
            if (isset($options['total']) && $options['total']) {
                $total = $offset + $options['limit'];
                if ($total > $options['total']) {
                    $options['limit'] = $options['total'] - $offset;
                }
            }

            $options['offset'] = $offset;

            $count = isset($options['total']) ? $options['total'] : null;

            if ($count !== null && $offset >= $count) {
                $reader = new \EmptyIterator;
            } else {
                $reader = new \LimitIterator($reader, intval($options['offset']), intval($options['limit']));
            }
        }

        if (!\is_null($callable)) {
            $reader = new MapIterator($reader, $callable);
        }

        return $reader;
    }

    /**
     * Ftech the first row from file.
     *
     * @param  string  $file
     * @param  array  $options
     * @return array
     */
    public function fetchHeader($file, ?callable $callable = null, $options = [])
    {
        $options['limit'] = 1;

        $columns = $this->fetchArray($file, $callable, $options);

        return $this->cleanHeader($columns[0] ?? []);
    }

    public function cleanHeader($columns)
    {
        $columns = array_map(function ($column) {
            // Remove BOM character (UTF-8 BOM: EF BB BF, UTF-16 BOM: FF FE or FE FF)
            $column = str_replace(["\xEF\xBB\xBF", "\xFF\xFE", "\xFE\xFF"], '', $column);
            // Remove other common invisible characters
            $column = preg_replace('/[\x00-\x1F\x7F]/', '', $column);
            // Keep only alphanumeric, hyphens, and underscores
            $column = preg_replace('/[^a-zA-Z0-9_-]/', '', $column);

            $column = trim($column);

            return $column ?? 'empty';
        }, $columns);

        return $columns;
    }

    /**
     * Get the file rows as array.
     *
     * @param  string  $file
     * @param  array  $options
     * @return array
     */
    public function fetchArray($file, ?callable $callable = null, $options = [])
    {
        $rows = $this->fetchAll($file, $callable, $options);

        return $this->toArray($rows);
    }

    /**
     * Get the number of rows in file.
     *
     * @param  string  $file
     * @param  array  $options
     * @return int
     */
    public function fetchCount($file, ?callable $callable = null, $options = [])
    {
        $rows = $this->fetchAll($file, $callable, $options);

        return $this->count($rows);
    }

    /**
     * Count the interator values.
     *
     * @param  Arrayable|Collection|\Iterator|\JsonSerializable|LazyCollection|\Port\Csv\CsvReader|\Port\Reader\ArrayReader|\Port\Spreadsheet\SpreadsheetReader|RewindableGenerator|\Traversable  $iterator  Travarsable object
     */
    public function count($iterator): int
    {
        if ($iterator instanceof Collection) {
            return $iterator->count();
        }

        if ($iterator instanceof LazyCollection) {
            return $iterator->count();
        }

        if ($iterator instanceof Arrayable) {
            return count($iterator->toArray());
        }

        if ($iterator instanceof \JsonSerializable) {
            return count($iterator->jsonSerialize());
        }

        return \iterator_count($iterator);
    }

    /**
     * check the next row from iterator.
     *
     * @param  Arrayable|Collection|\Iterator|\JsonSerializable|LazyCollection|\Port\Csv\CsvReader|\Port\Reader\ArrayReader|\Port\Spreadsheet\SpreadsheetReader|RewindableGenerator|\Traversable  $iterator  Travarsable object
     * @return bool
     */
    public function hasNext($iterator)
    {
        return $this->count($iterator) ? true : false;
    }

    /**
     * Get first row from integrator.
     *
     * @return array
     */
    public function first(\Traversable $iterator)
    {
        $fields = [];
        foreach ($iterator as $row) {
            $fields = $row;

            break;
        }

        return $fields;
    }

    public function chunk(\Traversable $iterator, int $size = 100): ChunkedIterator
    {
        return new ChunkedIterator($iterator, $size);
    }

    /**
     * Indentify the delimiter from row string.
     *
     * @param  string  $file
     */
    public function detectDelimiter($file, int $sample = 5): ?string
    {
        $delimsRegex = ",|;:\t"; // whichever is first in the list will be the default
        $delims = \str_split($delimsRegex);
        $delimCount = [];
        $delimiters = [];
        foreach ($delims as $delim) {
            $delimCount[$delim] = 0;
            $delimiters[] = $delim;
        }

        $lines = $this->getLines($file, $sample);

        foreach ($lines as $row) {
            if (!is_string($row)) {
                continue;
            }

            $row = \preg_replace('/\r\n/', '', \trim($row)); // clean up .. strip new line and line return chars
            $row = \preg_replace("/[^{$delimsRegex}]/", '', $row); // clean up .. strip evthg which is not a dilim'r
            $rowChars = \str_split($row); // break it apart char by char

            if (is_array($rowChars)) {
                foreach ($rowChars as $char) {
                    foreach ($delimiters as $delim) {
                        if (\strpos($char, $delim) !== false) {
                            // if the char is the delim ...
                            $delimCount[$delim]++; // ... increment
                        }
                    }
                }
            }
        }

        $max = \max($delimCount);

        if ($max <= 0) {
            return null;
        }

        $detected = \array_keys($delimCount, $max);

        return $detected[0];
    }

    /**
     * @param  string  $file
     * @param  int  $total
     */
    public function getLines($file, $total = 5): array
    {
        $handle = \fopen($file, 'r');

        $line = 0;
        $lines = [];
        while (!\feof($handle)) {
            $lines[] = \fgets($handle, 1024);
            $line++;
            if ($line >= $total) {
                break;
            }
        }

        \fclose($handle);

        return $lines;
    }

    /**
     * Unzip the zip file.
     *
     * @param  null|string  $zip
     * @param  array  $options
     * @return null|string
     */
    public function unzip($zip, $options = [])
    {
        if (!isset($zip)) {
            return null;
        }

        $ext = isset($options['ext']) ? $options['ext'] : \strrchr($zip, '.');
        $ext = \strtolower($ext);

        if ($ext && !\in_array($ext, ['.zip', '.tar', '.tar.gz', '.rar', '.gz'])) {
            // check the file exists or download to local
            return $zip;
        }

        $zip = $this->download($zip);
        $extensions = ['.csv', '.xls', '.xlsx', '.txt'];
        $directory = \dirname($zip);
        $extract = '/tmp/' . \uniqid() . '/';

        try {
            $archive = UnifiedArchive::open($zip);
            $files = $archive->getFileNames();
            $tmpfile = null;
            $extension = null;

            foreach ($files as $file) {
                $extension = \strtolower(\strrchr($file, '.'));
                if (\in_array($extension, $extensions)) {
                    $tmpfile = $file;

                    break;
                }
            }

            if ($extension && $tmpfile) {
                $archive->extractFiles($extract);
                $reader = $directory . '/' . \md5(\uniqid()) . $extension;

                \rename($extract . '/' . $tmpfile, $reader);
                \chmod($reader, 0777);
                \unlink($zip);

                return $reader;
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function download($path)
    {
        $localDisk = Storage::disk('local');

        if ($localDisk->exists($path)) {
            return $localDisk->path($path);
        }

        // Get the file from remote and store it locally
        $contents = Storage::get($path);

        // Check if contents exist before storing
        if (empty($contents)) {
            return $path;
        }

        // Store the file in local disk
        $localDisk->put($path, $contents);
        $localPath = $localDisk->path($path);

        return $localPath;
    }

    public function cleanup($path)
    {
        $localDisk = Storage::disk('local');

        if ($localDisk->exists($path)) {
            $localDisk->delete($path);
        }
    }

    /**
     * Convert rows to array.
     *
     * @param  array|Arrayable|Collection|JsonResource|\JsonSerializable|LazyCollection|mixed|\Traversable  $rows
     * @return array
     */
    public function toArray($rows)
    {
        if ($rows instanceof Collection) {
            return $rows->toArray();
        }

        if ($rows instanceof LazyCollection) {
            return $rows->toArray();
        }

        if ($rows instanceof Arrayable) {
            return $rows->toArray();
        }

        if ($rows instanceof \JsonSerializable) {
            return $rows->jsonSerialize();
        }

        return \iterator_to_array($rows);
    }
}
