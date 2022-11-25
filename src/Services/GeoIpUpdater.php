<?php

declare(strict_types=1);

namespace Diviky\Bright\Services;

use Matomo\Decompress\Tar;

class GeoIpUpdater
{
    public const GEOLITE2_URL_BASE = 'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key=%s&suffix=tar.gz';

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
        $zipped = $this->getHTTPFile($geoDbUrl, $destination = $destinationPath . DIRECTORY_SEPARATOR, $this->getDbFileName() . '.tar.gz');

        if (!$zipped) {
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
        $zip = $this->getDbFileName($filePath) . '.tar.gz';
        $out = $this->getDbFileName($filePath);
        $sha = $this->getDbFileName($filePath) . '.sha256';

        $folder = explode('  ', file_get_contents($sha))[1];
        $folder = explode('.', $folder)[0];

        // Extracting Gzip file
        $archive = new Tar($zip, 'gz');

        $files = $archive->extract($filePath);

        if (file_exists($zip)) {
            unlink($zip);
        }

        if (true !== $files) {
            $this->addMessage($archive->errorInfo());
            $this->addMessage("Unable to extract gzip file {$filePath} to {$out}.");

            return false;
        }

        $dir = $filePath . '/' . $folder . '/';
        copy($this->getDbFileName($dir), $out);

        $this->cleanup($dir);

        return $out;
    }

    protected function cleanup(string $dir): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }

        rmdir($dir);
    }

    /**
     * Add a message.
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
