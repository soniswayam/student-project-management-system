<?php

namespace App\Console\Commands;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\CollegeSetting;
use App\Models\Notification;
use App\Models\Project;
use App\Models\ReminderLog;
use App\Models\Viva;
use Illuminate\Console\Command;

class SendDueDateReminders extends Command
{
    protected $signature = 'reminders:send
                            {--dry-run : List what would be sent without sending or logging anything}';

    protected $description = 'Remind students about upcoming deadlines and faculty about pending reviews';

    private bool $dryRun = false;

    private int $sent = 0;

    public function handle(): int
    {
        $this->dryRun = (bool) $this->option('dry-run');

        if ($this->dryRun) {
            $this->warn('Dry run — nothing will be sent or logged.');
        }

        $leadDays = CollegeSetting::reminderLeadDays();

        $this->remindProjectDeadlines($leadDays);
        $this->remindAssignmentDeadlines($leadDays);
        $this->remindPendingReviews();
        $this->remindUpcomingVivas();

        $this->info($this->dryRun
            ? "Would send {$this->sent} reminder(s)."
            : "Sent {$this->sent} reminder(s).");

        return self::SUCCESS;
    }

    private function remindProjectDeadlines(int $leadDays): void
    {
        $projects = Project::query()
            ->inActiveYear()
            ->with('department', 'members.student.user')
            ->where('status', Project::STATUS_SYNOPSIS_APPROVED)
            ->get();

        foreach ($projects as $project) {
            $deadline = $project->deadline();

            if (! $deadline || $deadline->isPast()) {
                continue;
            }

            $daysLeft = (int) now()->startOfDay()->diffInDays($deadline->copy()->startOfDay(), false);

            $kind = match (true) {
                $daysLeft === 0 => ReminderLog::KIND_DUE_TODAY,
                $daysLeft > 0 && $daysLeft <= $leadDays => ReminderLog::KIND_DUE_SOON,
                default => null,
            };

            if (! $kind) {
                continue;
            }

            $when = $kind === ReminderLog::KIND_DUE_TODAY
                ? 'today at '.$deadline->format('H:i')
                : "in {$daysLeft} day(s), on ".$deadline->format('d M Y');

            foreach ($project->members as $member) {
                $user = $member->student?->user;

                if ($user && ! $user->isGraduated()) {
                    $this->send(
                        $user->id,
                        $project,
                        $kind,
                        'Project deadline approaching',
                        "Your final submission for \"{$project->name}\" is due {$when}.",
                        route('student.submission.create')
                    );
                }
            }
        }
    }

    private function remindAssignmentDeadlines(int $leadDays): void
    {
        $assignments = Assignment::query()
            ->inActiveYear()
            ->whereNotNull('due_date')
            ->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays($leadDays)->endOfDay())
            ->with('department.students.user')
            ->get();

        foreach ($assignments as $assignment) {
            $daysLeft = (int) now()->startOfDay()->diffInDays($assignment->due_date->copy()->startOfDay(), false);
            $kind = $daysLeft === 0 ? ReminderLog::KIND_DUE_TODAY : ReminderLog::KIND_DUE_SOON;

            $submitted = AssignmentSubmission::where('assignment_id', $assignment->id)
                ->pluck('student_id')
                ->all();

            $when = $daysLeft === 0
                ? 'today at '.$assignment->due_date->format('H:i')
                : "in {$daysLeft} day(s)";

            foreach ($assignment->department?->students ?? [] as $student) {
                if (in_array($student->id, $submitted, true) || ! $assignment->isFor($student)) {
                    continue;
                }

                if (($userId = $student->user?->id) && ! $student->user->isGraduated()) {
                    $this->send(
                        $userId,
                        $assignment,
                        $kind,
                        'Assignment due soon',
                        "\"{$assignment->title}\" is due {$when}.",
                        route('student.assignments.index')
                    );
                }
            }
        }
    }

    private function remindPendingReviews(): void
    {
        $threshold = now()->subDays(CollegeSetting::reminderLeadDays());

        $projects = Project::query()
            ->inActiveYear()
            ->whereIn('status', [Project::STATUS_SYNOPSIS_REVIEW, Project::STATUS_FINAL_SUBMITTED])
            ->where('updated_at', '<=', $threshold)
            ->with('assignment.faculty.user')
            ->get();

        foreach ($projects as $project) {
            $faculty = $project->assignment?->faculty;

            if (! $faculty?->user_id) {
                continue;
            }

            $what = $project->status === Project::STATUS_SYNOPSIS_REVIEW ? 'synopsis' : 'final submission';
            $waiting = (int) $project->updated_at->diffInDays(now());

            $this->send(
                $faculty->user_id,
                $project,
                ReminderLog::KIND_OVERDUE_REVIEW,
                'Review still pending',
                "The {$what} for \"{$project->name}\" has been waiting {$waiting} day(s) for your review.",
                route('faculty.projects.show', $project)
            );
        }
    }

    private function remindUpcomingVivas(): void
    {
        $vivas = Viva::query()
            ->where('status', Viva::STATUS_SCHEDULED)
            ->whereBetween('scheduled_at', [now()->addDay()->startOfDay(), now()->addDay()->endOfDay()])
            ->with('project.members.student.user', 'panelists.user')
            ->get();

        foreach ($vivas as $viva) {
            $project = $viva->project;

            if (! $project) {
                continue;
            }

            $where = $viva->isOnline() ? 'Online' : ($viva->venue ?: 'venue to be confirmed');
            $body = "Viva for \"{$project->name}\" is tomorrow at {$viva->scheduled_at->format('H:i')}. {$where}.";

            foreach ($project->members as $member) {
                if ($userId = $member->student?->user_id) {
                    $this->send($userId, $viva, ReminderLog::KIND_VIVA_TOMORROW, 'Viva tomorrow', $body, route('student.project.show'));
                }
            }

            foreach ($viva->panelists as $panelist) {
                if ($panelist->user_id) {
                    $this->send(
                        $panelist->user_id,
                        $viva,
                        ReminderLog::KIND_VIVA_TOMORROW,
                        'Viva tomorrow',
                        $body,
                        route('faculty.projects.show', $project)
                    );
                }
            }
        }
    }

    private function send(int $userId, $thing, string $kind, string $title, string $message, string $link): void
    {
        if ($this->dryRun) {
            if (ReminderLog::alreadySent($userId, $thing, $kind)) {
                return;
            }

            $this->sent++;
            $this->line("  → user {$userId}: [{$kind}] {$title}");

            return;
        }

        if (! ReminderLog::record($userId, $thing, $kind)) {
            return;
        }

        $this->sent++;
        Notification::notify($userId, $title, $message, $link);
    }
}
