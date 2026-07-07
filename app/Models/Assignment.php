<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    protected $fillable = [
        'faculty_id',
        'department_id',
        'subject',
        'assignment_no',
        'type',
        'title',
        'description',
        'attachment_path',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'datetime',
    ];

    /** The nature options an assignment can have. */
    public const TYPES = ['Theory', 'Lab', 'Tutorial', 'Project'];

    /** Short label like "DBMS · Assignment 1" for headings. */
    public function subjectLabel(): string
    {
        $parts = array_filter([
            $this->subject,
            $this->assignment_no ? "Assignment {$this->assignment_no}" : null,
        ]);

        return implode(' · ', $parts);
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    /** True once the due date has passed. */
    public function isPastDue(): bool
    {
        return $this->due_date !== null && $this->due_date->isPast();
    }
}
