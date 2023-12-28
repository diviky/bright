<?php

declare(strict_types=1);

namespace Diviky\Bright\Concerns;

use Diviky\Bright\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait WithUploads
{
    public function convertToFile(string $name): UploadedFile
    {
        $disk = config('filesystems.default');
        $from = Storage::disk($disk);
        $local = Storage::disk('local');
        $file = 'tmp/' . $name;

        if ($disk != 'local' && !$local->exists($file)) {
            $stream = $from->readStream($file);
            if ($stream) {
                $local->writeStream($file, $stream);
            }
            $from->delete($file);
        }

        $path = $local->path($file);

        return new UploadedFile(
            $path,
            $name,
            $local->mimeType($file),
            $local->size($file)
        );
    }
}
