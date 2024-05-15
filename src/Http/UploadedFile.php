<?php

declare(strict_types=1);

namespace Diviky\Bright\Http;

use Illuminate\Http\UploadedFile as HttpUploadedFile;

class UploadedFile extends HttpUploadedFile
{
    public function isValid(): bool
    {
        return true;
    }

    /**
     * Check that the given file is a valid file instance.
     *
     * @param  mixed  $file
     * @return bool
     */
    protected function isValidFile($file)
    {
        return $file instanceof \SplFileInfo && $file->getPath() !== '';
    }
}
