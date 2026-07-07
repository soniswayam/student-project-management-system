<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    protected $fillable = [
        'user_id',
        'department_id',
        'roll_no',
        'semester',
        'phone',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /** The membership row linking this student to a project (if any). */
    public function membership(): HasOne
    {
        return $this->hasOne(ProjectMember::class);
    }

    /** The project this student leads (if any). */
    public function ledProject(): HasOne
    {
        return $this->hasOne(Project::class, 'leader_student_id');
    }

    /** Resolve the project this student belongs to, whether leader or partner. */
    public function project(): ?Project
    {
        return $this->membership?->project;
    }

    public function hasProject(): bool
    {
        return $this->membership()->exists();
    }

    /** This student's assignment submissions. */
    public function assignmentSubmissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }
}
