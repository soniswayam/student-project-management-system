<?php

namespace App\Support;

use App\Models\CollegeSetting;
use Illuminate\Support\Carbon;

class SubmissionWindow
{
    public const DEFAULT_PROJECT_GRACE = 12;

    public const DEFAULT_ASSIGNMENT_GRACE = 6;

    public function __construct(
        public readonly ?Carbon $dueAt,
        public readonly int $graceHours,
    ) {}

    public static function forProject(?Carbon $dueAt): self
    {
        return new self($dueAt, self::graceHours('project_grace_hours', self::DEFAULT_PROJECT_GRACE));
    }

    public static function forAssignment(?Carbon $dueAt): self
    {
        return new self($dueAt, self::graceHours('assignment_grace_hours', self::DEFAULT_ASSIGNMENT_GRACE));
    }

    private static function graceHours(string $column, int $default): int
    {
        $value = CollegeSetting::current()?->{$column};

        return $value !== null ? (int) $value : $default;
    }

    public function closesAt(): ?Carbon
    {
        return $this->dueAt?->copy()->addHours($this->graceHours);
    }

    public function hasDeadline(): bool
    {
        return $this->dueAt !== null;
    }

    public function isOpen(): bool
    {
        return ! $this->isClosed();
    }

    public function isInGrace(): bool
    {
        return $this->hasDeadline()
            && now()->gt($this->dueAt)
            && now()->lte($this->closesAt());
    }

    public function isClosed(): bool
    {
        return $this->hasDeadline() && now()->gt($this->closesAt());
    }

    public function lateByMinutes(?Carbon $at = null): int
    {
        $at ??= now();

        if (! $this->hasDeadline() || $at->lte($this->dueAt)) {
            return 0;
        }

        return (int) $this->dueAt->diffInMinutes($at);
    }

    public function timeLeftForHumans(): ?string
    {
        $target = $this->isInGrace() ? $this->closesAt() : $this->dueAt;

        if (! $target || now()->gt($target)) {
            return null;
        }

        return now()->diffForHumans($target, ['syntax' => Carbon::DIFF_ABSOLUTE, 'parts' => 2]);
    }

    public function color(): string
    {
        if (! $this->hasDeadline()) {
            return 'secondary';
        }

        return match (true) {
            $this->isClosed() => 'danger',
            $this->isInGrace() => 'warning',
            default => 'info',
        };
    }

    public function statusLabel(): string
    {
        if (! $this->hasDeadline()) {
            return 'No deadline set — submissions are open.';
        }

        if ($this->isClosed()) {
            return 'Closed on '.$this->closesAt()->format('d M Y, H:i').' — submissions are no longer accepted.';
        }

        if ($this->isInGrace()) {
            return 'Overdue — late submission still accepted for another '.$this->timeLeftForHumans().'.';
        }

        return 'Due '.$this->dueAt->format('d M Y, H:i').' — '.$this->timeLeftForHumans().' left.';
    }

    public static function lateForHumans(?int $minutes): ?string
    {
        if (! $minutes) {
            return null;
        }

        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        return $hours > 0
            ? trim($hours.' h '.($mins > 0 ? $mins.' m' : '')).' late'
            : $mins.' m late';
    }
}
