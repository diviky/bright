<?php

namespace Diviky\Bright\Http\Controllers\Account\Traits;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

trait UserAvatarTrait
{
    public function setAvatar($file, $size = 400, $disk = 's3')
    {
        if ($file) {
            $img = Image::make($file);
            $img->fit($size, $size);
            $filename = $this->id . '.' . $file->getClientOriginalExtension();
            $avatar   = 'avatar/' . $filename;
            $resource = $img->stream()->detach();

            $disk = $disk ?: config('filesystems.default');
            $disk = ('local' == $disk) ? 'public' : $disk;

            Storage::disk($disk)->put($avatar, $resource);

            $img->fit(100, 100);
            $resource = $img->stream()->detach();

            Storage::disk($disk)->put('crop/' . $avatar, $resource);

            $this->avatar = $avatar;
        }
    }
}
