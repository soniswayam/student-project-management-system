<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Faculty extends Model
{
    protected $table = 'faculties';

    protected $fillable = [
        'user_id',
        'department_id',
        'designation',
        'phone',
        'max_projects',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'faculty_department');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(FacultyAssignment::class);
    }

    public function givenAssignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function givenSubmissions(): HasManyThrough
    {
        return $this->hasManyThrough(AssignmentSubmission::class, Assignment::class);
    }

    public function activeProjectCount(): int
    {
        return $this->assignments()
            ->whereHas('project', fn ($p) => $p->inActiveYear())
            ->count();
    }

    public function isOverCapacity(?int $additional = 0): bool
    {
        if ($this->max_projects === null) {
            return false;
        }

        $current = $this->active_projects_count ?? $this->activeProjectCount();

        return ($current + $additional) > $this->max_projects;
    }

    public function loadPercent(): int
    {
        $current = $this->active_projects_count ?? $this->activeProjectCount();
        $capacity = $this->max_projects ?? 10;

        return (int) min(100, round(($current / max($capacity, 1)) * 100));
    }

    public function loadScore(): int
    {
        return ($this->active_projects_count ?? 0) * 3
            + ($this->pending_synopsis_count ?? 0) * 2
            + ($this->pending_final_count ?? 0) * 2
            + ($this->unchecked_submissions_count ?? 0);
    }

    public function projects()
    {
        return $this->hasManyThrough(
            Project::class,
            FacultyAssignment::class,
            'faculty_id',
            'id',
            'id',
            'project_id'
        );
    }
}
