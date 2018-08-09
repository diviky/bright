<?php

namespace Karla\Helpers;

use Closure;
use EmptyIterator;
use finfo;
use Generator;
use Karla\Helpers\Iterator\ChunkedIterator;
use Karla\Helpers\Iterator\MapGeneratorIterator;
use Karla\Helpers\Iterator\MapIterator;
use LimitIterator;
use Port\Csv\CsvReader;
use Port\Excel\ExcelReader;
use Port\Reader\ArrayReader;
use RewindableGenerator;
use SplFileObject;
use Traversable;
use wapmorgan\UnifiedArchive\UnifiedArchive;

/**
 * Reader class to get details from iterator.
 *
 * @author sankar <sankar.suda@gmail.com>
 */
class Reader
{
    public function fetchAll($reader, Closure $callable = null, $options = [])
    {
        $reader = $this->unzip($reader, $options);

        set_time_limit(0);

        if (!is_string($reader)) {
            $ext = $options['ext'] ?: '.array';
        } else {
            $ext = $options['ext'] ?: strrchr($reader, '.');
            $ext = '.' == $ext ? '.xls' : $ext;
        }

        $ext     = strtolower($ext);
        $special = in_array($ext, ['.array', '.iterator', '.generator']) ? true : false;

        if (!$special && (!is_file($reader) || !file_exists($reader))) {
            return new EmptyIterator();
        }

        $lines = ('.txt' == $ext) ? 1 : 5;
        $file  = null;

        if (!$special) {
            $file = new SplFileObject($reader);
            //Auto detect delimiter
            if (empty($options['delimiter'])) {
                $options['delimiter'] = $this->detectDelimiter($reader, $lines);
            }
        }

        switch ($ext) {
            case '.txt':
            case '.csv':
                $duplicates = CsvReader::DUPLICATE_HEADERS_INCREMENT;
                if ($options['delimiter']) {
                    $reader = new CsvReader($file, $options['delimiter']);
                } else {
                    $reader = new CsvReader($file);
                }
                break;
            case '.xls':
            case '.xlsx':
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime  = $finfo->file($file->getRealPath());

                if ("\t" === $options['delimiter'] && '.xls' == $ext && 'application/vnd.ms-excel' != $mime) {
                    $reader = new CsvReader($file, $options['delimiter']);
                } else {
                    $reader     = new ExcelReader($file, null, 0);
                    $duplicates = null;
                }

                break;
            case '.array':
                if (!is_array($reader)) {
                    $reader = explode("\n", $reader);
                }

                $reader = new ArrayReader($reader);
                break;

            case '.generator':
                $reader = new RewindableGenerator($reader);
                break;

            case '.iterator':
            default:
                break;
        }

        if (!$special && $options['header']) {
            $reader->setHeaderRowNumber($options['header'] - 1, $duplicates);
        }

        // Closing the file object
        $file = null;

        return $this->modify($reader, $callable, $options);
    }

    /**
     * Modify the iterator using callback closure.
     *
     * @param Traversable $reader   Travarsable object
     * @param Closure     $callable [description]
     * @param array       $options  [description]
     *
     * @return Traversable [description]
     */
    public function modify($reader, Closure $callable = null, $options = [])
    {
        if (is_numeric($options['limit'])) {
            $offset = 0;
            if ($options['offset']) {
                $offset = $options['offset'];
            } elseif ($options['page']) {
                $offset = $options['limit'] * ($options['page'] - 1);
            }

            if ($offset && empty($options['total'])) {
                $options['total'] = $this->count($reader);
            }

            // number of records to fetch total
            if ($options['total']) {
                $total = $offset + $options['limit'];
                if ($total > $options['total']) {
                    $options['limit'] = $options['total'] - $offset;
                }
            }

            $options['offset'] = $offset;
            $count             = $options['total'];

            if (null !== $count && $offset >= $count) {
                $reader = new EmptyIterator();
            } else {
                $reader = new LimitIterator($reader, $options['offset'], $options['limit']);
            }
        }

        if (!is_null($callable)) {
            if ($reader instanceof Generator) {
                $reader = new MapGeneratorIterator($reader, $callable);
            } else {
                $reader = new MapIterator($reader, $callable);
            }
        }

        return $reader;
    }

    public function fetchHeader($file, Closure $callable = null, $options = [])
    {
        $options['limit'] = 1;

        $columns = $this->fetchArray($file, $callable, $options);

        return $columns[0];
    }

    public function fetchArray($file, Closure $callable = null, $options = [])
    {
        $rows = $this->fetchAll($file, $callable, $options);

        return $this->toArray($rows);
    }

    public function fetchCount($file, Closure $callable = null, $options = [])
    {
        $rows = $this->fetchAll($file, $callable, $options);

        return $this->count($rows);
    }

    public function count(Traversable $iterator)
    {
        return iterator_count($iterator);
    }

    public function hasNext(Traversable $iterator)
    {
        return $this->count($iterator);
    }

    public function first(Traversable $iterator)
    {
        $fields = [];
        foreach ($iterator as $row) {
            $fields = $row;
            break;
        }

        //$iterator->rewind();
        //$fields = (array) $iterator->current();
        //$fields = array_keys($fields);

        return $fields;
    }

    public function chunk(Traversable $iterator, $size = 100)
    {
        return new ChunkedIterator($iterator, $size);
    }

    public function toArray(Traversable $iterator)
    {
        return iterator_to_array($iterator);
    }

    public function detectDelimiter($file, $sample = 5)
    {
        $delimsRegex = "|,;:\t"; // whichever is first in the list will be the default
        $delims      = str_split($delimsRegex);
        $delimCount  = $delimiters  = [];
        foreach ($delims as $delim) {
            $delimCount[$delim] = 0;
            $delimiters[]       = $delim;
        }

        $lines = $this->getLines($file, $sample);

        foreach ($lines as $row) {
            $row      = preg_replace('/\r\n/', '', trim($row)); // clean up .. strip new line and line return chars
            $row      = preg_replace("/[^$delimsRegex]/", '', $row); // clean up .. strip evthg which is not a dilim'r
            $rowChars = str_split($row); // break it apart char by char

            foreach ($rowChars as $char) {
                foreach ($delimiters as $delim) {
                    if (false !== strpos($char, $delim)) {
                        // if the char is the delim ...
                        ++$delimCount[$delim]; // ... increment
                    }
                }
            }
        }

        $detected = array_keys($delimCount, max($delimCount));

        return $detected[0];
    }

    public function getLines($file, $total = 5)
    {
        $handle = fopen($file, 'r');

        $line  = 0;
        $lines = [];
        while (!feof($handle)) {
            $lines[] = fgets($handle, 1024);
            ++$line;
            if ($line >= $total) {
                break;
            }
        }

        fclose($handle);

        return $lines;
    }

    public function unzip($zip, $options = [])
    {
        if (is_string($zip)) {
            $ext = $options['ext'] ?: strrchr($zip, '.');
            $ext = strtolower($ext);
        }

        if ($ext && in_array($ext, ['.zip', '.tar', '.tar.gz', '.rar', '.gz'])) {
            $extensions = ['.csv', '.xls', '.xlsx', '.txt'];
            $directory  = dirname($zip);
            $extract    = '/tmp/' . uniqid() . '/';

            try {
                $archive   = UnifiedArchive::open($zip);
                $files     = $archive->getFileNames();
                $tmpfile   = null;
                $extension = null;

                foreach ($files as $file) {
                    $extension = strtolower(strrchr($file, '.'));
                    if (in_array($extension, $extensions)) {
                        $tmpfile = $file;
                        break;
                    }
                }

                if ($extension && $tmpfile) {
                    $archive->extractFiles($extract);
                    $reader = $directory . '/' . md5(uniqid()) . $extension;

                    rename($extract . '/' . $tmpfile, $reader);
                    chmod($reader, 0777);
                    unlink($zip);

                    return $reader;
                }

                return null;
            } catch (\Exception $e) {
                return null;
            }
        }

        return $zip;
    }
}
