<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class AcademicYear extends Model
{
    protected static ?self $cachedCurrent = null;

    protected static bool $currentLoaded = false;

    protected $fillable = [
        'label',
        'starts_on',
        'ends_on',
        'is_current',
        'archived_at',
    ];

    protected $casts = [
        'starts_on' => 'date',
        'ends_on' => 'date',
        'is_current' => 'boolean',
        'archived_at' => 'datetime',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }

    public static function current(): ?self
    {
        if (! static::$currentLoaded) {
            static::$cachedCurrent = static::where('is_current', true)->first();
            static::$currentLoaded = true;
        }

        return static::$cachedCurrent;
    }

    public static function currentId(): ?int
    {
        return static::current()?->id;
    }

    public function markCurrent(): void
    {
        DB::transaction(function () {
            static::where('id', '!=', $this->id)->update(['is_current' => false]);
            $this->forceFill(['is_current' => true, 'archived_at' => null])->save();
        });
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function archive(): void
    {
        $this->forceFill(['archived_at' => now(), 'is_current' => false])->save();
    }

    public function unarchive(): void
    {
        $this->forceFill(['archived_at' => null])->save();
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::flushCurrent());
        static::deleted(fn () => static::flushCurrent());
    }

    public static function flushCurrent(): void
    {
        static::$cachedCurrent = null;
        static::$currentLoaded = false;
    }
}
