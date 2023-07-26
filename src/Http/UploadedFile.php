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
}
