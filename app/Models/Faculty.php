<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faculty extends Model
{
    protected $table = 'faculties';

    protected $fillable = [
        'user_id',
        'department_id',
        'designation',
        'phone',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** The faculty's primary/home department. */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /** All departments/courses this faculty teaches in (many-to-many). */
    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'faculty_department');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(FacultyAssignment::class);
    }

    /** Homework/assignments this faculty has created for students. */
    public function givenAssignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /** Projects assigned to this faculty member. */
    public function projects()
    {
        return $this->hasManyThrough(
            Project::class,
            FacultyAssignment::class,
            'faculty_id',   // FK on faculty_assignments
            'id',           // local key on projects
            'id',           // local key on faculties
            'project_id'    // FK on faculty_assignments pointing to projects
        );
    }
}
