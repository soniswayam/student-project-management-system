<?php

namespace App\Models;

use App\Support\SubmissionWindow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectSubmission extends Model
{
    protected $fillable = [
        'project_id',
        'report_path',
        'report_text',
        'source_zip_path',
        'ppt_path',
        'screenshots',
        'submitted_at',
        'is_late',
        'late_by_minutes',
    ];

    protected $casts = [
        'screenshots' => 'array',
        'submitted_at' => 'datetime',
        'is_late' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function isLate(): bool
    {
        return (bool) $this->is_late;
    }

    public function lateLabel(): ?string
    {
        return SubmissionWindow::lateForHumans($this->late_by_minutes);
    }
}
