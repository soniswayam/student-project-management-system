<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    protected $fillable = [
        'project_type',
        'name',
        'leader_student_id',
        'department_id',
        'frontend_tech',
        'backend_tech',
        'abstract',
        'status',
        'marks',
        'final_remarks',
    ];

    // ----- Status constants -----
    public const STATUS_SYNOPSIS_PENDING = 'Synopsis Pending';
    public const STATUS_SYNOPSIS_REVIEW = 'Synopsis Under Review';
    public const STATUS_SYNOPSIS_APPROVED = 'Synopsis Approved';
    public const STATUS_CORRECTION = 'Correction Required';
    public const STATUS_FINAL_SUBMITTED = 'Final Submitted';
    public const STATUS_FINAL_REVIEWED = 'Final Reviewed';
    public const STATUS_COMPLETED = 'Completed';

    // ----- Relationships -----

    public function leader(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'leader_student_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function assignment(): HasOne
    {
        return $this->hasOne(FacultyAssignment::class);
    }

    public function submission(): HasOne
    {
        return $this->hasOne(ProjectSubmission::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProjectReview::class)->latest();
    }

    // ----- Convenience helpers -----

    public function assignedFaculty(): ?Faculty
    {
        return $this->assignment?->faculty;
    }

    public function isSynopsisApproved(): bool
    {
        return in_array($this->status, [
            self::STATUS_SYNOPSIS_APPROVED,
            self::STATUS_FINAL_SUBMITTED,
            self::STATUS_FINAL_REVIEWED,
            self::STATUS_COMPLETED,
        ], true);
    }

    /** Bootstrap badge colour for the current status. */
    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_SYNOPSIS_PENDING => 'secondary',
            self::STATUS_SYNOPSIS_REVIEW => 'info',
            self::STATUS_SYNOPSIS_APPROVED => 'primary',
            self::STATUS_CORRECTION => 'warning',
            self::STATUS_FINAL_SUBMITTED => 'info',
            self::STATUS_FINAL_REVIEWED => 'primary',
            self::STATUS_COMPLETED => 'success',
            default => 'secondary',
        };
    }
}
