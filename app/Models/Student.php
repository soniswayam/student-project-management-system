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
        'passed_out_year_id',
        'passed_out_at',
    ];

    protected $casts = [
        'passed_out_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function passedOutYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'passed_out_year_id');
    }

    public function isAlumni(): bool
    {
        return (bool) $this->user?->isGraduated();
    }

    public function batchLabel(): ?string
    {
        return $this->passedOutYear?->label;
    }

    public function hasCompletedProject(): bool
    {
        $project = $this->membership?->project ?? $this->ledProject;

        return $project !== null && $project->status === Project::STATUS_COMPLETED;
    }

    public function membership(): HasOne
    {
        return $this->hasOne(ProjectMember::class);
    }

    public function ledProject(): HasOne
    {
        return $this->hasOne(Project::class, 'leader_student_id');
    }

    public function project(): ?Project
    {
        return $this->membership?->project;
    }

    public function hasProject(): bool
    {
        return $this->membership()->exists();
    }

    public function assignmentSubmissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public static function availablePartnersFor(self $student, ?Project $currentProject = null)
    {
        return self::with('user')
            ->where('id', '!=', $student->id)
            ->where('department_id', $student->department_id)
            ->where('semester', $student->semester)
            ->whereHas('user', fn ($u) => $u->where('status', User::STATUS_ACTIVE))
            ->where(function ($q) use ($currentProject) {
                $q->whereDoesntHave('membership');

                if ($currentProject) {
                    $q->orWhereHas('membership', fn ($m) => $m->where('project_id', $currentProject->id));
                }
            })
            ->orderBy('roll_no')
            ->get();
    }
}
