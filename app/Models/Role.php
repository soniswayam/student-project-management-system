<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /** Per-request in-memory cache of roles by name. */
    protected static array $byNameCache = [];

    protected $fillable = [
        'name',
        'label',
        'permissions',
        'is_staff',
        'is_system',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_staff' => 'boolean',
        'is_system' => 'boolean',
    ];

    /** Does this role grant the given permission key? "*" means all-access. */
    public function hasPermission(string $key): bool
    {
        $permissions = $this->permissions ?? [];

        return in_array('*', $permissions, true) || in_array($key, $permissions, true);
    }

    /** Lookup a role by its slug name (memoized for the current request). */
    public static function byName(?string $name): ?self
    {
        if (! $name) {
            return null;
        }

        if (! array_key_exists($name, static::$byNameCache)) {
            static::$byNameCache[$name] = static::where('name', $name)->first();
        }

        return static::$byNameCache[$name];
    }

    protected static function booted(): void
    {
        // Drop the in-memory cache when a role changes.
        static::saved(fn () => static::$byNameCache = []);
        static::deleted(fn () => static::$byNameCache = []);
    }

    /** The full grouped permission catalog (for the management UI). */
    public static function catalog(): array
    {
        return config('permissions', []);
    }

    /** Flat list of every valid permission key. */
    public static function allKeys(): array
    {
        return collect(static::catalog())->flatMap(fn ($group) => array_keys($group))->all();
    }
}
