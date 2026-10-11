<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
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

    public function hasPermission(string $key): bool
    {
        $permissions = $this->permissions ?? [];

        return in_array('*', $permissions, true) || in_array($key, $permissions, true);
    }

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
        static::saved(fn () => static::$byNameCache = []);
        static::deleted(fn () => static::$byNameCache = []);
    }

    public static function catalog(): array
    {
        return config('permissions', []);
    }

    public static function allKeys(): array
    {
        return collect(static::catalog())->flatMap(fn ($group) => array_keys($group))->all();
    }
}
