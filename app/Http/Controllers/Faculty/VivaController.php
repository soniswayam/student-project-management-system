<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Viva;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VivaController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        $faculty = $this->authorizeProject($project);

        $data = $request->validate([
            'scheduled_at' => ['required', 'date'],
            'venue' => ['nullable', 'string', 'max:255'],
            'mode' => ['required', Rule::in(Viva::MODES)],
            'meeting_link' => ['nullable', 'url', 'max:255', 'required_if:mode,'.Viva::MODE_ONLINE],
            'notes' => ['nullable', 'string', 'max:1000'],
            'panelists' => ['nullable', 'array'],
            'panelists.*' => ['integer', 'exists:faculties,id'],
        ], [
            'meeting_link.required_if' => 'An online viva needs a meeting link.',
        ]);

        $existing = $project->viva;
        $wasScheduledFor = $existing?->scheduled_at;

        $viva = DB::transaction(function () use ($project, $data, $faculty, $existing) {
            $viva = Viva::updateOrCreate(
                ['project_id' => $project->id],
                [
                    'scheduled_at' => $data['scheduled_at'],
                    'venue' => $data['venue'] ?? null,
                    'mode' => $data['mode'],
                    'meeting_link' => $data['mode'] === Viva::MODE_ONLINE ? ($data['meeting_link'] ?? null) : null,
                    'notes' => $data['notes'] ?? null,
                    'status' => Viva::STATUS_SCHEDULED,
                    'created_by' => $existing?->created_by ?? auth()->id(),
                ]
            );

            $panel = collect($data['panelists'] ?? [])->push($faculty->id)->unique()->all();
            $viva->panelists()->sync($panel);

            return $viva;
        });

        $this->notifyEveryone(
            $project,
            $viva,
            $wasScheduledFor ? 'Viva rescheduled' : 'Viva scheduled'
        );

        return back()->with('success', $wasScheduledFor ? 'Viva rescheduled and everyone notified.' : 'Viva scheduled and everyone notified.');
    }

    public function updateStatus(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeProject($project);

        $viva = $project->viva;
        abort_unless($viva, 404, 'No viva has been scheduled for this project.');

        $data = $request->validate([
            'status' => ['required', Rule::in(Viva::STATUSES)],
        ]);

        $viva->update(['status' => $data['status']]);

        if ($data['status'] === Viva::STATUS_CANCELLED) {
            $this->notifyEveryone($project, $viva, 'Viva cancelled');
        }

        return back()->with('success', 'Viva marked as '.$data['status'].'.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorizeProject($project);

        $viva = $project->viva;
        abort_unless($viva, 404);

        $this->notifyEveryone($project, $viva, 'Viva cancelled');
        $viva->delete();

        return back()->with('success', 'Viva removed.');
    }

    private function authorizeProject(Project $project): Faculty
    {
        $faculty = auth()->user()->faculty;
        $owns = $faculty && $project->assignment && $project->assignment->faculty_id === $faculty->id;

        abort_unless($owns, 403, 'This project is not assigned to you.');
        abort_if($project->isArchived(), 403, 'This project belongs to an archived academic year and is read-only.');

        return $faculty;
    }

    private function notifyEveryone(Project $project, Viva $viva, string $title): void
    {
        $when = $viva->scheduled_at->format('d M Y, H:i');
        $where = $viva->isOnline()
            ? 'Online'
            : ($viva->venue ? "Venue: {$viva->venue}" : 'Venue to be confirmed');

        $cancelled = str_contains(strtolower($title), 'cancel');
        $body = $cancelled
            ? "The viva for \"{$project->name}\" (was {$when}) has been cancelled."
            : "Viva for \"{$project->name}\" is on {$when}. {$where}.";

        $project->loadMissing('members.student.user');

        foreach ($project->members as $member) {
            if ($userId = $member->student?->user_id) {
                Notification::notify($userId, $title, $body, route('student.project.show'));
            }
        }

        foreach ($viva->panelists()->with('user')->get() as $panelist) {
            if ($panelist->user_id) {
                Notification::notify(
                    $panelist->user_id,
                    $title,
                    $body,
                    route('faculty.projects.show', $project)
                );
            }
        }
    }
}
