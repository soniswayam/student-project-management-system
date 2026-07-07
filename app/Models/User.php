<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /** Account status values. */
    public const STATUS_ACTIVE = 'active';

    public const STATUS_PENDING = 'pending';

    public const STATUS_BLOCKED = 'blocked';

    public const STATUSES = [self::STATUS_ACTIVE, self::STATUS_PENDING, self::STATUS_BLOCKED];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    /** New accounts are active unless the flow (e.g. self-registration) says otherwise. */
    protected $attributes = [
        'status' => self::STATUS_ACTIVE,
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ----- Relationships -----

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function faculty(): HasOne
    {
        return $this->hasOne(Faculty::class);
    }

    public function appNotifications(): HasMany
    {
        return $this->hasMany(Notification::class)->latest();
    }

    // ----- Role helpers -----

    /** The Role model backing this user's role slug (cached). */
    public function roleModel(): ?Role
    {
        return Role::byName($this->role);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /** True if this user's role may access the admin area (staff role). */
    public function isStaff(): bool
    {
        return (bool) ($this->roleModel()?->is_staff);
    }

    /**
     * Backwards-compatible alias: "admin" now means "any staff role".
     */
    public function isAdmin(): bool
    {
        return $this->isStaff();
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    // ----- Account status helpers -----

    /** True when the account may sign in and use its dashboard. Null (legacy) is treated as active. */
    public function isActive(): bool
    {
        return ! in_array($this->status, [self::STATUS_PENDING, self::STATUS_BLOCKED], true);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isBlocked(): bool
    {
        return $this->status === self::STATUS_BLOCKED;
    }

    public function isFaculty(): bool
    {
        return $this->role === 'faculty';
    }

    /** Does this user's role grant the given permission? Super admin bypasses. */
    public function hasPermission(string $key): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return (bool) $this->roleModel()?->hasPermission($key);
    }
}
