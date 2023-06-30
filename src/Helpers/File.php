<?php

declare(strict_types=1);

namespace Diviky\Bright\Helpers;

use Diviky\Bright\Helpers\Iterator\ChunkedIterator;
use Diviky\Bright\Helpers\Iterator\MapIterator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
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
class File
{
    /**
     * @var array|Arrayable|Collection|JsonResource|\JsonSerializable|LazyCollection|mixed|\Port\Csv\CsvReader|\Port\Reader\ArrayReader|\Port\Spreadsheet\SpreadsheetReader|\RewindableGenerator|\Traversable
     */
    protected $reader;

    /**
     *@var callable
     */
    protected $callable;

    protected string $path;

    /**
     * @param array|\Iterator|string|\Traversable $reader
     */
    final public function __construct($reader = null, array $options = [])
    {
        if (isset($reader)) {
            $this->reader = $this->getReader($reader, $options);
        }
    }

    public function callback(callable $callable): self
    {
        $this->reader = new MapIterator($this->reader, $callable);

        return $this;
    }

    /**
     * Modify the iterator using callback closure.
     *
     * @param array $options
     */
    public function modify($options = []): self
    {
        if (isset($options['limit']) && \is_numeric($options['limit'])) {
            $offset = 0;
            if (isset($options['offset']) && $options['offset']) {
                $offset = $options['offset'];
            } elseif (isset($options['page']) && $options['page']) {
                $offset = $options['limit'] * ($options['page'] - 1);
            }

            if ($offset && empty($options['total'])) {
                $options['total'] = $this->count();
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

            if (null !== $count && $offset >= $count) {
                return (new static())->setReader(new \EmptyIterator());
            }

            return (new static())->setReader(new \LimitIterator($this->reader, intval($options['offset']), intval($options['limit'])));
        }

        return $this;
    }

    /**
     * Fetch the first row from file.
     *
     * @param array $options
     *
     * @return array
     */
    public function header($options = [])
    {
        $options['limit'] = 1;

        $columns = $this->all($options);

        return $columns[0] ?? [];
    }

    /**
     * Fetch rows from file.
     *
     * @param array $options
     *
     * @return array
     */
    public function all($options = [])
    {
        return $this->modify($options)->toArray();
    }

    /**
     * Count the iterator values.
     */
    public function count(): int
    {
        if ($this->reader instanceof Collection) {
            return $this->reader->count();
        }

        if ($this->reader instanceof LazyCollection) {
            return $this->reader->count();
        }

        if ($this->reader instanceof Arrayable) {
            return count($this->reader->toArray());
        }

        if ($this->reader instanceof \JsonSerializable) {
            return count($this->reader->jsonSerialize());
        }

        return \iterator_count($this->reader);
    }

    /**
     * check the next row from iterator.
     *
     * @return bool
     */
    public function hasNext()
    {
        return $this->count() ? true : false;
    }

    /**
     * @return array|Collection|LazyCollection|\Traversable
     */
    public function iterator()
    {
        return $this->reader;
    }

    public function getFilePath(): string
    {
        return $this->path;
    }

    /**
     * Get first row from iterator.
     *
     * @return array
     */
    public function first()
    {
        $fields = [];
        foreach ($this->iterator() as $row) {
            $fields = $row;

            break;
        }

        return $fields;
    }

    public function chunk(int $size = 100): ChunkedIterator
    {
        return new ChunkedIterator($this->iterator(), $size);
    }

    /**
     * Unzip the zip file.
     *
     * @param null|string $zip
     * @param array       $options
     *
     * @return null|string
     */
    public function unzip($zip, $options = [])
    {
        if (!isset($zip)) {
            return null;
        }

        $ext = isset($options['ext']) ? $options['ext'] : \strrchr($zip, '.');
        $ext = \strtolower($ext);

        if ($ext && \in_array($ext, ['.zip', '.tar', '.tar.gz', '.rar', '.gz'])) {
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
                    $this->path = $reader;

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

        $this->path = $zip;

        return $zip;
    }

    /**
     * Convert rows to array.
     *
     * @return array
     */
    public function toArray()
    {
        if ($this->reader instanceof Collection) {
            return $this->reader->toArray();
        }

        if ($this->reader instanceof LazyCollection) {
            return $this->reader->toArray();
        }

        if ($this->reader instanceof Arrayable) {
            return $this->reader->toArray();
        }

        if ($this->reader instanceof \JsonSerializable) {
            return $this->reader->jsonSerialize();
        }

        return \iterator_to_array($this->reader);
    }

    /**
     * @param Illuminate\Container\RewindableGenerator|\Iterator|Port\Csv\CsvReader|Port\Reader\ArrayReader|Port\Spreadsheet\SpreadsheetReader|Traversable $reader
     */
    protected function setReader($reader): self
    {
        $this->reader = $reader;

        return $this;
    }

    /**
     * Fetch all the values from file.
     *
     * @param array|\Iterator|string|\Traversable $reader
     * @param array                               $options
     *
     * @return null|iterable<array-key|mixed, mixed>|mixed|\Port\Csv\CsvReader|\Port\Reader\ArrayReader|\Port\Spreadsheet\SpreadsheetReader|string
     */
    protected function getReader($reader, $options = [])
    {
        \set_time_limit(0);

        if (!\is_string($reader)) {
            $ext = isset($options['ext']) ? $options['ext'] : '.array';
        } else {
            $reader = $this->unzip($reader, $options);
            $ext = isset($options['ext']) ? $options['ext'] : \strrchr($reader, '.');
            $ext = '.' == $ext ? '.xls' : $ext;
        }

        $ext = \strtolower($ext);

        $special = \in_array($ext, ['.array', '.iterator', '.generator']) ? true : false;

        if (!$special) {
            if (!\is_file($reader) || !\file_exists($reader)) {
                return new \EmptyIterator();
            }

            $lines = ('.txt' == $ext) ? 1 : 5;
            $file = '';

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

                    if ("\t" === $options['delimiter'] && '.xls' == $ext && 'application/vnd.ms-excel' != $mime) {
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

        return $reader;
    }

    /**
     * Indentify the delimiter from row string.
     *
     * @param string $file
     */
    protected function detectDelimiter($file, int $sample = 5): ?string
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
                        if (false !== \strpos($char, $delim)) {
                            // if the char is the delim ...
                            ++$delimCount[$delim]; // ... increment
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
     * @param string $file
     * @param int    $total
     */
    protected function getLines($file, $total = 5): array
    {
        $handle = \fopen($file, 'r');

        $line = 0;
        $lines = [];
        while (!\feof($handle)) {
            $lines[] = \fgets($handle, 1024);
            ++$line;
            if ($line >= $total) {
                break;
            }
        }

        \fclose($handle);

        return $lines;
    }
}
