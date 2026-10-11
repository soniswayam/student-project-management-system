<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectSimilarity extends Model
{
    public const STAGE_SYNOPSIS = 'synopsis';

    public const STAGE_REPORT = 'report';

    protected $fillable = [
        'project_id',
        'matched_project_id',
        'stage',
        'score',
        'details',
    ];

    protected $casts = [
        'score' => 'float',
        'details' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function matchedProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'matched_project_id');
    }

    public function scopeFlagged(Builder $query): Builder
    {
        return $query->where('score', '>=', CollegeSetting::warnPercent());
    }

    public function phrases(): array
    {
        return $this->details['phrases'] ?? [];
    }

    public function severityColor(): string
    {
        return match (true) {
            $this->score >= CollegeSetting::blockPercent() => 'danger',
            $this->score >= CollegeSetting::warnPercent() => 'warning',
            default => 'secondary',
        };
    }

    public function severityLabel(): string
    {
        return match (true) {
            $this->score >= CollegeSetting::blockPercent() => 'High',
            $this->score >= CollegeSetting::warnPercent() => 'Review',
            default => 'Low',
        };
    }
}
