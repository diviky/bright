<?php

declare(strict_types=1);

namespace Diviky\Bright\Services;

class GeoIpUpdater
{
    const GEOLITE2_URL_BASE = 'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key=%s&suffix=tar.gz';

    /**
     * @var bool|string
     */
    protected $databaseFileGzipped;

    /**
     * @var bool|string
     */
    protected $databaseFile;

    /**
     * @var bool|string
     */
    protected $md5File;

    /**
     * @var array
     */
    protected $messages = [];

    /**
     * @var string
     */
    protected $geoDbUrl;

    /**
     * @var string
     */
    protected $geoDbMd5Url;

    /**
     * Get messages.
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Download and update GeoIp database.
     *
     * @param string      $destinationPath
     * @param null|string $geoDbUrl
     * @param null|string $geoDbMd5Url
     *
     * @return bool
     */
    public function updateGeoIpFiles($destinationPath, $geoDbUrl = null, $geoDbMd5Url = null)
    {
        $this->geoDbUrl = $this->getDbFileUrl($geoDbUrl);
        $this->geoDbMd5Url = $this->getMd5FileName($geoDbMd5Url);

        if ($this->databaseIsUpdated($this->geoDbMd5Url, $destinationPath)) {
            return true;
        }

        if ($this->downloadGzipped($destinationPath, $geoDbUrl)) {
            return true;
        }

        $this->addMessage("Unknown error downloading {$geoDbUrl}.");

        return false;
    }

    /**
     * Check db is up to date or not.
     *
     * @param string $geoDbMd5Url
     * @param string $destinationPath
     */
    protected function databaseIsUpdated($geoDbMd5Url, $destinationPath): bool
    {
        $destinationGeoDbFile = $this->getDbFileName($destinationPath . DIRECTORY_SEPARATOR);

        $this->md5File = $this->getHTTPFile($geoDbMd5Url, $destinationPath . DIRECTORY_SEPARATOR, $this->getDbFileName() . '.sha256');

        if (!file_exists($destinationGeoDbFile)) {
            return false;
        }

        $updated = false;

        if (is_string($this->md5File)) {
            $updated = file_get_contents($this->md5File) == hash('sha256', $destinationGeoDbFile, false);
        }

        if ($updated) {
            $this->addMessage('Database is already updated.');
        }

        return $updated;
    }

    protected function getDbFileName(string $path = ''): string
    {
        return $path . 'GeoLite2-City.mmdb';
    }

    /**
     * Download gzipped database, unzip and check md5.
     *
     * @param string $destinationPath
     * @param string $geoDbUrl
     *
     * @return bool
     */
    protected function downloadGzipped($destinationPath, $geoDbUrl)
    {
        $this->databaseFileGzipped = $this->getHTTPFile($geoDbUrl, ($destination = $destinationPath . DIRECTORY_SEPARATOR), $this->getDbFileName() . '.tar.gz');

        if (!$this->databaseFileGzipped) {
            $this->addMessage("Unable to download file {$geoDbUrl} to {$destination}.");
        }

        $this->databaseFile = $this->dezipGzFile($destinationPath . DIRECTORY_SEPARATOR);

        return true;
    }

    /**
     * Make directory.
     *
     * @param string $destinationPath
     *
     * @return bool
     */
    protected function makeDir($destinationPath)
    {
        return file_exists($destinationPath) || mkdir($destinationPath, 0770, true);
    }

    /**
     * Remove .gzip extension from file.
     *
     * @param string $filePath
     *
     * @return mixed
     */
    protected function removeGzipExtension($filePath)
    {
        return str_replace('.gz', '', $filePath);
    }

    /**
     * Read url to file.
     *
     * @param string      $uri
     * @param null|string $fileName
     *
     * @return bool|string
     */
    protected function getHTTPFile($uri, string $destinationPath, $fileName = null)
    {
        set_time_limit(360);

        if (!$this->makeDir($destinationPath)) {
            return false;
        }

        $fileWriteName = $destinationPath . ($fileName ?? basename($uri));
        $fileRead = fopen($uri, 'rb');
        $fileWrite = fopen($fileWriteName, 'wb');

        if (false === $fileRead || (false === $fileWrite)) {
            $this->addMessage("Unable to open {$uri} (read) or {$fileWriteName} (write).");

            return false;
        }

        while (!feof($fileRead)) {
            $content = fread($fileRead, 1024 * 16);
            $success = fwrite($fileWrite, $content);

            if (false === $success) {
                $this->addMessage("Error downloading file {$uri} to {$fileWriteName}.");

                return false;
            }
        }

        fclose($fileWrite);

        fclose($fileRead);

        return $fileWriteName;
    }

    /**
     * Extract gzip file.
     *
     * @return bool|string
     */
    protected function dezipGzFile(string $filePath)
    {
        $buffer_size = 8192; // read 8kb at a time

        $out_file_name = $this->getDbFileName($filePath);

        $fileRead = gzopen($this->getDbFileName($filePath) . '.tar.gz', 'rb');

        $fileWrite = fopen($out_file_name, 'wb');

        if (false === $fileRead || false === $fileWrite) {
            $this->addMessage("Unable to extract gzip file {$filePath} to {$out_file_name}.");

            return false;
        }

        while (!gzeof($fileRead)) {
            $success = fwrite($fileWrite, gzread($fileRead, $buffer_size));

            if (false === $success) {
                $this->addMessage("Error degzipping file {$filePath} to {$out_file_name}.");

                return false;
            }
        }

        // Files are done, close files
        fclose($fileWrite);

        gzclose($fileRead);

        return $out_file_name;
    }

    /**
     * Add a message.
     *
     * @param $string
     */
    protected function addMessage(string $string): void
    {
        $this->messages[] = $string;
    }

    /**
     * get the db file url.
     *
     * @param null|string $geoDbUrl
     */
    protected function getDbFileUrl($geoDbUrl): string
    {
        return $geoDbUrl ?: self::GEOLITE2_URL_BASE;
    }

    /**
     * Get the db file name.
     *
     * @param null|string $geoDbMd5Url
     */
    protected function getMd5FileName($geoDbMd5Url): string
    {
        return $geoDbMd5Url ?: $this->geoDbUrl . '.sha256';
    }
}
