<?php

declare(strict_types=1);

namespace Diviky\Bright\Http;

use Illuminate\Http\UploadedFile as HttpUploadedFile;

class UploadedFile extends HttpUploadedFile
{
    /**
     * Returns whether the file has been uploaded with HTTP and no error occurred.
     *
     * @return bool
     */
    public function isValid()
    {
        return true;
    }
}
