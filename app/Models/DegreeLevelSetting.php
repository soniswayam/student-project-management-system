<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DegreeLevelSetting extends Model
{
    protected static array $cache = [];

    protected $fillable = [
        'level',
        'max_group_size',
        'submission_deadline',
        'max_marks',
        'viva_required',
    ];

    protected $casts = [
        'submission_deadline' => 'datetime',
        'viva_required' => 'boolean',
    ];

    public static function forLevel(?string $level): ?self
    {
        if (! $level) {
            return null;
        }

        if (! array_key_exists($level, static::$cache)) {
            static::$cache[$level] = static::where('level', $level)->first();
        }

        return static::$cache[$level];
    }

    public static function groupSizeFor(?string $level): int
    {
        return (int) (static::forLevel($level)?->max_group_size ?? 2);
    }

    public static function deadlineFor(?string $level)
    {
        return static::forLevel($level)?->submission_deadline;
    }

    public static function maxMarksFor(?string $level): ?int
    {
        $max = static::forLevel($level)?->max_marks;

        return $max !== null ? (int) $max : null;
    }

    public static function vivaRequiredFor(?string $level): bool
    {
        return (bool) static::forLevel($level)?->viva_required;
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::$cache = []);
        static::deleted(fn () => static::$cache = []);
    }

    public static function flushCache(): void
    {
        static::$cache = [];
    }
}
