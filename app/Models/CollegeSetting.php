<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollegeSetting extends Model
{
    /** Per-request in-memory cache of the settings row. */
    protected static ?self $cached = null;

    protected static bool $loaded = false;

    protected $fillable = [
        'name',
        'tagline',
        'address',
        'affiliation',
        'email',
        'phone',
        'website',
        'logo_path',
    ];

    /** The single settings row (memoized for the request), or null if not seeded. */
    public static function current(): ?self
    {
        if (! static::$loaded) {
            static::$cached = static::first();
            static::$loaded = true;
        }

        return static::$cached;
    }

    /**
     * The college logo as a base64 data-URI for reliable embedding in
     * DomPDF-generated documents, or null when no logo is configured.
     */
    public static function logoDataUri(): ?string
    {
        $path = config('college.logo');

        if (! $path) {
            return null;
        }

        $full = storage_path('app/public/'.$path);

        if (! is_file($full)) {
            return null;
        }

        $mime = mime_content_type($full) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($full));
    }

    /** Shape matching config/college.php so it can override config('college'). */
    public function toConfigArray(): array
    {
        return [
            'name' => $this->name,
            'tagline' => $this->tagline,
            'address' => $this->address,
            'affiliation' => $this->affiliation,
            'email' => $this->email,
            'phone' => $this->phone,
            'website' => $this->website,
            'logo' => $this->logo_path,
        ];
    }

    protected static function booted(): void
    {
        static::saved(function () {
            static::$cached = null;
            static::$loaded = false;
        });
    }
}
