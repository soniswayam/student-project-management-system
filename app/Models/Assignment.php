<?php

namespace App\Models;

use App\Support\SubmissionWindow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    protected $fillable = [
        'faculty_id',
        'department_id',
        'semester',
        'academic_year_id',
        'subject',
        'assignment_no',
        'type',
        'max_marks',
        'title',
        'description',
        'attachment_path',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'datetime',
    ];

    public const TYPES = ['Theory', 'Lab', 'Tutorial', 'Project'];

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function scopeInActiveYear(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNull('academic_year_id')
                ->orWhereHas('academicYear', fn (Builder $y) => $y->whereNull('archived_at'));
        });
    }

    public function scopeForStudent(Builder $query, Student $student): Builder
    {
        return $query->where('department_id', $student->department_id)
            ->where(function (Builder $q) use ($student) {
                $q->whereNull('semester')->orWhere('semester', $student->semester);
            });
    }

    public function isFor(Student $student): bool
    {
        return $this->department_id === $student->department_id
            && ($this->semester === null || (string) $this->semester === (string) $student->semester);
    }

    public function semesterLabel(): string
    {
        return $this->semester !== null ? 'Sem '.$this->semester : 'All semesters';
    }

    public function groupLabel(): string
    {
        return ($this->department?->name ?? 'No department').' · '.$this->semesterLabel();
    }

    public function scopeOrderByDepartmentAndSemester(Builder $query): Builder
    {
        return $query
            ->orderBy(Department::select('name')->whereColumn('departments.id', 'assignments.department_id'))
            ->orderByRaw('LENGTH(assignments.semester)')
            ->orderBy('assignments.semester')
            ->latest();
    }

    public function targetStudents()
    {
        return static::currentStudents()
            ->where('department_id', $this->department_id)
            ->when($this->semester !== null, fn ($q) => $q->where('semester', $this->semester));
    }

    public static function targetCounts(iterable $assignments): array
    {
        $byClass = static::currentStudents()
            ->selectRaw('department_id, semester, COUNT(*) as total')
            ->groupBy('department_id', 'semester')
            ->get();

        $counts = [];
        foreach ($assignments as $assignment) {
            $counts[$assignment->id] = (int) $byClass
                ->where('department_id', $assignment->department_id)
                ->when($assignment->semester !== null, fn ($rows) => $rows->where('semester', (string) $assignment->semester))
                ->sum('total');
        }

        return $counts;
    }

    private static function currentStudents()
    {
        return Student::query()->whereHas('user', fn ($u) => $u
            ->where('status', User::STATUS_ACTIVE)
            ->orWhereNull('status'));
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class)->chaperone();
    }

    public function isMarked(): bool
    {
        return $this->max_marks !== null;
    }

    public function isPastDue(): bool
    {
        return $this->due_date !== null && $this->due_date->isPast();
    }

    public function submissionWindow(): SubmissionWindow
    {
        return SubmissionWindow::forAssignment($this->due_date);
    }
}
