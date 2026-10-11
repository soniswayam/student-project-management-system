<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacultyAssignmentHistory extends Model
{
    protected $table = 'faculty_assignment_histories';

    protected $fillable = [
        'project_id',
        'from_faculty_id',
        'to_faculty_id',
        'reason',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function fromFaculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class, 'from_faculty_id');
    }

    public function toFaculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class, 'to_faculty_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function isReplacement(): bool
    {
        return $this->from_faculty_id !== null;
    }
}
