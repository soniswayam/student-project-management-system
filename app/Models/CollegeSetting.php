<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollegeSetting extends Model
{
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
        'project_grace_hours',
        'assignment_grace_hours',
        'duplicate_warn_percent',
        'duplicate_block_percent',
        'reminder_lead_days',
    ];

    public const DEFAULT_WARN_PERCENT = 60;

    public const DEFAULT_BLOCK_PERCENT = 85;

    public const DEFAULT_REMINDER_LEAD_DAYS = 3;

    public static function current(): ?self
    {
        if (! static::$loaded) {
            static::$cached = static::first();
            static::$loaded = true;
        }

        return static::$cached;
    }

    public static function warnPercent(): int
    {
        return (int) (static::current()?->duplicate_warn_percent ?? self::DEFAULT_WARN_PERCENT);
    }

    public static function blockPercent(): int
    {
        return (int) (static::current()?->duplicate_block_percent ?? self::DEFAULT_BLOCK_PERCENT);
    }

    public static function reminderLeadDays(): int
    {
        return (int) (static::current()?->reminder_lead_days ?? self::DEFAULT_REMINDER_LEAD_DAYS);
    }

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
