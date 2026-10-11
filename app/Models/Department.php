<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    public const LEVEL_GRADUATION = 'graduation';

    public const LEVEL_POST_GRADUATION = 'post_graduation';

    public const LEVELS = [self::LEVEL_GRADUATION, self::LEVEL_POST_GRADUATION];

    public const DEFAULT_YEARS = [
        self::LEVEL_GRADUATION => 3,
        self::LEVEL_POST_GRADUATION => 2,
    ];

    protected $fillable = ['name', 'code', 'level', 'duration_years', 'total_semesters', 'submission_deadline'];

    protected $casts = [
        'submission_deadline' => 'datetime',
    ];

    public function isPostGraduation(): bool
    {
        return $this->level === self::LEVEL_POST_GRADUATION;
    }

    public function levelLabel(): string
    {
        return $this->isPostGraduation() ? 'Post-Graduation' : 'Graduation';
    }

    public function levelShort(): string
    {
        return $this->isPostGraduation() ? 'PG' : 'UG';
    }

    public function levelColor(): string
    {
        return $this->isPostGraduation() ? 'primary' : 'info';
    }

    public function finalSemester(): ?int
    {
        if ($this->total_semesters !== null) {
            return (int) $this->total_semesters;
        }

        return $this->duration_years ? (int) $this->duration_years * 2 : null;
    }

    public static function semesterOptions(): array
    {
        return range(1, max(8, (int) static::max('total_semesters')));
    }

    public function levelSettings(): ?DegreeLevelSetting
    {
        return DegreeLevelSetting::forLevel($this->level);
    }

    protected static function booted(): void
    {
        static::saving(function (self $department) {
            if ($department->total_semesters === null && $department->duration_years) {
                $department->total_semesters = (int) $department->duration_years * 2;
            }
        });
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function faculties(): HasMany
    {
        return $this->hasMany(Faculty::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }
}
