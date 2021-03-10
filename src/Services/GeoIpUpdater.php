<?php
namespace Diviky\Bright\Services;

use PharData;

class GeoIpUpdater
{
    const GEOLITE2_URL_BASE = 'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key=%s&suffix=tar.gz';

    protected $databaseFileGzipped;

    protected $databaseFile;

    protected $md5File;

    protected $messages = [];

    protected $geoDbUrl;

    protected $geoDbMd5Url;

    /**
     * Add a message.
     *
     * @param $string
     */
    private function addMessage($string)
    {
        $this->messages[] = $string;
    }

    protected function databaseIsUpdated($geoDbFileUrl, $geoDbMd5Url, $destinationPath)
    {
        $destinationGeoDbFile = $this->getDbFileName($destinationPath . DIRECTORY_SEPARATOR);

        $this->md5File = $this->getHTTPFile($geoDbMd5Url, $destinationPath . DIRECTORY_SEPARATOR, $this->getDbFileName().'.sha256');

        if (! file_exists($destinationGeoDbFile)) {
            return false;
        }

        if ($updated = file_get_contents($this->md5File) == hash("sha256", $destinationGeoDbFile, false)) {
            $this->addMessage('Database is already updated.');
        }

        return $updated;
    }

    protected function getDbFileName($path = '')
    {
        return $path.'GeoLite2-City.mmdb';
    }

    /**
     * Download gzipped database, unzip and check md5.
     *
     * @param $destinationPath
     * @param $geoDbUrl
     * @return bool
     */
    protected function downloadGzipped($destinationPath, $geoDbUrl)
    {
        if (! $this->databaseFileGzipped = $this->getHTTPFile($geoDbUrl, ($destination = $destinationPath . DIRECTORY_SEPARATOR), $this->getDbFileName().'.tar.gz')) {
            $this->addMessage("Unable to download file {$geoDbUrl} to {$destination}.");
        }

        $this->databaseFile = $this->dezipGzFile($destinationPath . DIRECTORY_SEPARATOR);

        return true;
    }

    private function getDbFileUrl($geoDbUrl)
    {
        return $geoDbUrl ?: static::GEOLITE2_URL_BASE;
    }

    private function getMd5FileName($geoDbMd5Url)
    {
        return $geoDbMd5Url ?: $this->geoDbUrl . '.sha256';
    }

    /**
     * Get messages.
     *
     * @return mixed
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Make directory.
     *
     * @param $destinationPath
     * @return bool
     */
    protected function makeDir($destinationPath)
    {
        return file_exists($destinationPath) || mkdir($destinationPath, 0770, true);
    }

    /**
     * Compare MD5s.
     *
     * @return bool
     */
    private function md5Match()
    {
        if (hash("sha256", $this->databaseFile, false) !== file_get_contents($this->md5File)) {
            $this->addMessage("SHA256 is not matching for {$this->databaseFile} and {$this->md5File}.");

            return false;
        }

        $this->addMessage("Database successfully downloaded to {$this->databaseFile}.");

        return true;
    }

    /**
     * Remove .gzip extension from file.
     *
     * @param $filePath
     * @return mixed
     */
    protected function removeGzipExtension($filePath)
    {
        return str_replace('.gz', '', $filePath);
    }

    /**
     * Download and update GeoIp database.
     *
     * @param $destinationPath
     * @param null $geoDbUrl
     * @param null $geoDbMd5Url
     * @return bool
     */
    public function updateGeoIpFiles($destinationPath, $geoDbUrl = null, $geoDbMd5Url = null)
    {
        $this->geoDbUrl = $this->getDbFileUrl($geoDbUrl);
        $this->geoDbMd5Url = $this->getMd5FileName($geoDbMd5Url);

        if ($this->databaseIsUpdated($this->geoDbUrl, $this->geoDbMd5Url, $destinationPath)) {
            return true;
        }

        if ($this->downloadGzipped($destinationPath, $geoDbUrl)) {
            return true;
        }

        $this->addMessage("Unknown error downloading {$geoDbUrl}.");

        return false;
    }

    /**
     * Read url to file.
     *
     * @param $uri
     * @param $destinationPath
     * @return bool|string
     */
    protected function getHTTPFile($uri, $destinationPath, $fileName = null)
    {
        set_time_limit(360);

        if (! $this->makeDir($destinationPath)) {
            return false;
        }

        $fileWriteName = $destinationPath . ($fileName ?? basename($uri));

        if (($fileRead = @fopen($uri, "rb")) === false || ($fileWrite = @fopen($fileWriteName, 'wb')) === false) {
            $this->addMessage("Unable to open {$uri} (read) or {$fileWriteName} (write).");

            return false;
        }

        while (! feof($fileRead)) {
            $content = @fread($fileRead, 1024*16);

            $success = fwrite($fileWrite, $content);

            if ($success === false) {
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
     * @param $filePath
     * @return bool|mixed
     */
    protected function dezipGzFile($filePath)
    {
        //$archive = new PharData($tarFile);

        $buffer_size = 8192; // read 8kb at a time

        $out_file_name = $this->getDbFileName($filePath);

        $fileRead = gzopen($this->getDbFileName($filePath).'.tar.gz', 'rb');

        $fileWrite = fopen($out_file_name, 'wb');

        if ($fileRead === false || $fileWrite === false) {
            $this->addMessage("Unable to extract gzip file {$filePath} to {$out_file_name}.");

            return false;
        }

        while (!gzeof($fileRead)) {
            $success = fwrite($fileWrite, gzread($fileRead, $buffer_size));

            if ($success === false) {
                $this->addMessage("Error degzipping file {$filePath} to {$out_file_name}.");

                return false;
            }
        }

        // Files are done, close files
        fclose($fileWrite);

        gzclose($fileRead);

        return $out_file_name;
    }
}
