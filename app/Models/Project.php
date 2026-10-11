<?php

namespace App\Models;

use App\Support\SubmissionWindow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Project extends Model
{
    protected $fillable = [
        'project_type',
        'name',
        'leader_student_id',
        'department_id',
        'academic_year_id',
        'frontend_tech',
        'backend_tech',
        'abstract',
        'status',
        'revision',
        'deadline_override',
        'deadline_override_reason',
        'marks',
        'final_remarks',
    ];

    protected $casts = [
        'deadline_override' => 'datetime',
    ];

    public const STATUS_SYNOPSIS_PENDING = 'Synopsis Pending';

    public const STATUS_SYNOPSIS_REVIEW = 'Synopsis Under Review';

    public const STATUS_SYNOPSIS_APPROVED = 'Synopsis Approved';

    public const STATUS_CORRECTION = 'Correction Required';

    public const STATUS_REJECTED = 'Rejected';

    public const STATUS_FINAL_SUBMITTED = 'Final Submitted';

    public const STATUS_FINAL_REVIEWED = 'Final Reviewed';

    public const STATUS_COMPLETED = 'Completed';

    public function leader(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'leader_student_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
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

    public function similarities(): HasMany
    {
        return $this->hasMany(ProjectSimilarity::class)->orderByDesc('score');
    }

    public function viva(): HasOne
    {
        return $this->hasOne(Viva::class);
    }

    public function revisionRequests(): HasMany
    {
        return $this->hasMany(RevisionRequest::class)->latest();
    }

    public function guideHistory(): HasMany
    {
        return $this->hasMany(FacultyAssignmentHistory::class)->latest('changed_at');
    }

    public function openFinalRevisionRequest(): ?RevisionRequest
    {
        return $this->revisionRequests()
            ->open()
            ->stage(RevisionRequest::STAGE_FINAL)
            ->first();
    }

    public function scopeInActiveYear(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNull('academic_year_id')
                ->orWhereHas('academicYear', fn (Builder $y) => $y->whereNull('archived_at'));
        });
    }

    public function scopeOrderByDepartmentAndSemester(Builder $query): Builder
    {
        $leaderSemester = fn () => Student::query()->whereColumn('students.id', 'projects.leader_student_id');

        return $query
            ->orderBy(Department::select('name')->whereColumn('departments.id', 'projects.department_id'))
            ->orderBy($leaderSemester()->selectRaw('LENGTH(semester)'))
            ->orderBy($leaderSemester()->select('semester'))
            ->latest();
    }

    public function scopeInSemester(Builder $query, ?string $semester): Builder
    {
        return $query->when(
            $semester !== null && $semester !== '',
            fn (Builder $q) => $q->whereHas('leader', fn (Builder $s) => $semester === 'none'
                ? $s->whereNull('semester')
                : $s->where('semester', $semester))
        );
    }

    public function scopeInArchivedYear(Builder $query): Builder
    {
        return $query->whereHas('academicYear', fn (Builder $y) => $y->whereNotNull('archived_at'));
    }

    public function assignedFaculty(): ?Faculty
    {
        return $this->assignment?->faculty;
    }

    public function memberNames(): string
    {
        return $this->members
            ->map(fn (ProjectMember $m) => $m->student?->user?->name)
            ->filter()
            ->implode(', ');
    }

    public function groupLabel(): string
    {
        $semester = $this->leader?->semester;

        return ($this->department?->name ?? 'No department').' · '.($semester ? 'Sem '.$semester : 'Semester not set');
    }

    public function isArchived(): bool
    {
        return (bool) $this->academicYear?->isArchived();
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

    public function isSubmitted(): bool
    {
        return in_array($this->status, [
            self::STATUS_FINAL_SUBMITTED,
            self::STATUS_FINAL_REVIEWED,
            self::STATUS_COMPLETED,
        ], true);
    }

    public function deadline(): ?Carbon
    {
        return $this->deadline_override
            ?? $this->department?->submission_deadline
            ?? DegreeLevelSetting::deadlineFor($this->department?->level);
    }

    public function deadlineSource(): ?string
    {
        return match (true) {
            $this->deadline_override !== null => 'override',
            $this->department?->submission_deadline !== null => 'department',
            DegreeLevelSetting::deadlineFor($this->department?->level) !== null => 'level',
            default => null,
        };
    }

    public function maxMarks(): ?int
    {
        return DegreeLevelSetting::maxMarksFor($this->department?->level);
    }

    public function marksLabel(): ?string
    {
        if ($this->marks === null || ! $this->isLevelMarked()) {
            return null;
        }

        $max = $this->maxMarks();

        return $max ? "{$this->marks} / {$max}" : (string) $this->marks;
    }

    public function isLevelMarked(): bool
    {
        return $this->maxMarks() !== null;
    }

    public function isVivaMissing(): bool
    {
        return DegreeLevelSetting::vivaRequiredFor($this->department?->level)
            && $this->viva === null;
    }

    public function submissionWindow(): SubmissionWindow
    {
        return SubmissionWindow::forProject($this->deadline());
    }

    public function isSynopsisEditable(): bool
    {
        return in_array($this->status, [
            self::STATUS_CORRECTION,
            self::STATUS_REJECTED,
        ], true);
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_SYNOPSIS_PENDING => 'secondary',
            self::STATUS_SYNOPSIS_REVIEW => 'info',
            self::STATUS_SYNOPSIS_APPROVED => 'primary',
            self::STATUS_CORRECTION => 'warning',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_FINAL_SUBMITTED => 'info',
            self::STATUS_FINAL_REVIEWED => 'primary',
            self::STATUS_COMPLETED => 'success',
            default => 'secondary',
        };
    }
}
