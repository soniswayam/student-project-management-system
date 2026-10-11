<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Viva extends Model
{
    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [self::STATUS_SCHEDULED, self::STATUS_COMPLETED, self::STATUS_CANCELLED];

    public const MODE_OFFLINE = 'offline';

    public const MODE_ONLINE = 'online';

    public const MODES = [self::MODE_OFFLINE, self::MODE_ONLINE];

    protected $fillable = [
        'project_id',
        'scheduled_at',
        'venue',
        'mode',
        'meeting_link',
        'notes',
        'status',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function panelists(): BelongsToMany
    {
        return $this->belongsToMany(Faculty::class, 'viva_panelists');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isUpcoming(): bool
    {
        return $this->status === self::STATUS_SCHEDULED && $this->scheduled_at->isFuture();
    }

    public function isOnline(): bool
    {
        return $this->mode === self::MODE_ONLINE;
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => 'success',
            self::STATUS_CANCELLED => 'secondary',
            default => $this->scheduled_at->isPast() ? 'warning' : 'info',
        };
    }

    public function statusLabel(): string
    {
        return match (true) {
            $this->isCancelled() => 'Cancelled',
            $this->isCompleted() => 'Completed',
            $this->scheduled_at->isPast() => 'Awaiting outcome',
            default => 'Scheduled',
        };
    }
}
