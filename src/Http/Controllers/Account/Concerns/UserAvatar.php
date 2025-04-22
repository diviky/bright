<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Controllers\Account\Concerns;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

trait UserAvatar
{
    /**
     * Set the avatar image for user.
     *
     * @param  mixed  $file
     * @param  int  $size
     * @param  string  $disk
     */
    public function setAvatar($file, $size = 400, $disk = null): void
    {
        if ($file) {
            $img = Image::make($file);
            $img->fit($size, $size);
            $filename = $this->id . '.' . $file->getClientOriginalExtension();
            $avatar = 'avatar/' . $filename;
            $resource = $img->stream()->detach();

            $disk = $disk ?: config('filesystems.default');
            $disk = ($disk == 'local') ? 'public' : $disk;

            Storage::disk($disk)->put($avatar, $resource);

            $img->fit(100, 100);
            $resource = $img->stream()->detach();

            Storage::disk($disk)->put('crop/' . $avatar, $resource);

            $this->avatar = $avatar;
        }
    }
}
