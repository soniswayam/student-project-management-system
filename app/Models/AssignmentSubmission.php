<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmission extends Model
{
    protected $fillable = [
        'assignment_id',
        'student_id',
        'file_path',
        'remarks',
        'submitted_at',
        'status',
        'feedback',
        'checked_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'checked_at' => 'datetime',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** Was this submission handed in after the assignment's due date? */
    public function isLate(): bool
    {
        $due = $this->assignment?->due_date;

        return $due !== null && $this->submitted_at !== null && $this->submitted_at->gt($due);
    }

    public function isChecked(): bool
    {
        return $this->status === 'checked';
    }
}
