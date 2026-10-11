<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class StoredFiles
{
    public static function delete(iterable $paths): void
    {
        $paths = collect($paths)->flatten()->filter()->unique()->values()->all();

        if ($paths === []) {
            return;
        }

        foreach (['local', 'public'] as $disk) {
            Storage::disk($disk)->delete($paths);
        }
    }
}
