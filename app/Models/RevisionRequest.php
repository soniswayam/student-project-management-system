<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RevisionRequest extends Model
{
    public const STAGE_SYNOPSIS = 'synopsis';

    public const STAGE_FINAL = 'final';

    public const STAGE_ASSIGNMENT = 'assignment';

    protected $fillable = [
        'project_id',
        'assignment_submission_id',
        'stage',
        'faculty_id',
        'reason',
        'due_at',
        'resolved_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('resolved_at');
    }

    public function scopeStage(Builder $query, string $stage): Builder
    {
        return $query->where('stage', $stage);
    }

    public function isOpen(): bool
    {
        return $this->resolved_at === null;
    }

    public function resolve(): void
    {
        $this->forceFill(['resolved_at' => now()])->save();
    }

    public function lateByMinutes(\DateTimeInterface $at): int
    {
        if ($this->due_at === null || $at <= $this->due_at) {
            return 0;
        }

        return (int) $this->due_at->diffInMinutes($at);
    }

    public function isOverdue(): bool
    {
        return $this->isOpen() && $this->due_at !== null && $this->due_at->isPast();
    }
}
