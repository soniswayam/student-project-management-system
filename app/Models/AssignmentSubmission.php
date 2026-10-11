<?php

namespace App\Models;

use App\Support\SubmissionWindow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssignmentSubmission extends Model
{
    protected $fillable = [
        'assignment_id',
        'student_id',
        'file_path',
        'remarks',
        'submitted_at',
        'is_late',
        'late_by_minutes',
        'status',
        'feedback',
        'marks',
        'checked_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'checked_at' => 'datetime',
        'is_late' => 'boolean',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function isLate(): bool
    {
        if ($this->is_late) {
            return true;
        }

        $due = $this->assignment?->due_date;

        return $due !== null && $this->submitted_at !== null && $this->submitted_at->gt($due);
    }

    public function lateLabel(): ?string
    {
        return SubmissionWindow::lateForHumans($this->late_by_minutes);
    }

    public function isChecked(): bool
    {
        return $this->status === 'checked';
    }

    public function hasMarks(): bool
    {
        return $this->marks !== null;
    }

    public function marksLabel(): ?string
    {
        if (! $this->hasMarks()) {
            return null;
        }

        $max = $this->assignment?->max_marks;

        return $max ? "{$this->marks} / {$max}" : (string) $this->marks;
    }

    public function revisionRequests(): HasMany
    {
        return $this->hasMany(RevisionRequest::class)->latest();
    }

    public function openRevisionRequest(): ?RevisionRequest
    {
        return $this->revisionRequests()->open()->first();
    }
}
