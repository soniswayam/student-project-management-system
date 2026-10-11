@extends('layouts.app')
@section('title', 'Project Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-folder2-open me-2"></i>Project Details</h3>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.projects.synopsis', $project) }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-file-earmark-text"></i> Synopsis PDF
        </a>
        <a href="{{ route('admin.projects.pdf', $project) }}" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-file-earmark-pdf"></i> Report PDF
        </a>
        @if($project->isSubmitted())
            <a href="{{ route('admin.projects.certificate', $project) }}" class="btn btn-outline-success btn-sm">
                <i class="bi bi-award"></i> Certificate
            </a>
        @endif
        <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-secondary btn-sm">← Back</a>
    </div>
</div>

@include('partials.progress_tracker')

<div class="row">
    <div class="col-lg-7">
        @include('partials.project_info')
        @include('partials.similarity_flag')
        @include('partials.viva_card')
        @include('partials.guide_history')
        @if($project->isSynopsisApproved())
            @include('partials.submission_files')
        @endif
        @include('partials.reviews_timeline')
    </div>

    <div class="col-lg-5">
        @can('projects.assign')
        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-person-check me-1"></i>
                {{ $project->assignment ? 'Replace Guide' : 'Assign Faculty' }}
            </div>
            <div class="card-body">
                @if($project->assignment)
                    <p class="mb-2">Currently guided by
                        <strong>{{ $project->assignment->faculty->user->name }}</strong>.</p>
                    <p class="text-muted small">
                        Replacing them records the change, and notifies both guides and the students.
                    </p>
                @else
                    <p class="text-muted mb-2">No faculty assigned yet.</p>
                @endif
                <form method="POST" action="{{ route('admin.projects.assign', $project) }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label" for="faculty_id">Guide</label>
                        <select id="faculty_id" name="faculty_id" class="form-select" required>
                            <option value="">— Select Faculty —</option>
                            @foreach($faculties as $faculty)
                                <option value="{{ $faculty->id }}" @selected($project->assignment?->faculty_id === $faculty->id)>
                                    {{ $faculty->user->name }} ({{ $faculty->designation ?? 'Faculty' }})
                                    — {{ $faculty->active_projects_count }} project(s){{ $faculty->max_projects ? ' of '.$faculty->max_projects : '' }}
                                    @if($faculty->isOverCapacity()) · at capacity @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="assign_reason">
                            Reason <span class="text-muted fw-normal">(optional)</span>
                        </label>
                        <input id="assign_reason" name="reason" class="form-control"
                               placeholder="e.g. Original guide on leave" value="{{ old('reason') }}">
                    </div>
                    <button class="btn btn-primary w-100">
                        <i class="bi bi-person-check me-1"></i>{{ $project->assignment ? 'Replace Guide' : 'Assign Faculty' }}
                    </button>
                </form>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-calendar-event me-1"></i> Submission Deadline</div>
            <div class="card-body">
                <p class="mb-2">
                    @if($window->hasDeadline())
                        <span class="badge bg-{{ $window->color() }}">{{ $window->dueAt->format('d M Y, H:i') }}</span>
                        <span class="text-muted small d-block mt-1">
                            @if($project->deadlineSource() === 'override')
                                Extension set for this project
                                @if($project->deadline_override_reason)
                                    — {{ $project->deadline_override_reason }}
                                @endif
                                <br>Department default:
                                {{ $project->department?->submission_deadline?->format('d M Y, H:i') ?? 'none' }}
                            @else
                                From the <strong>{{ $project->department?->name ?? 'department' }}</strong> deadline
                            @endif
                            <br>{{ $window->statusLabel() }}
                        </span>
                    @else
                        <span class="text-muted">No deadline set — this project can be submitted at any time.</span>
                    @endif
                </p>

                <form method="POST" action="{{ route('admin.projects.deadline', $project) }}">
                    @csrf @method('PATCH')
                    <div class="mb-2">
                        <label class="form-label small">Extend this project only</label>
                        <input type="datetime-local" name="deadline_override" class="form-control"
                               value="{{ $project->deadline_override?->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="mb-3">
                        <input type="text" name="deadline_override_reason" class="form-control form-control-sm"
                               placeholder="Reason (optional) — e.g. medical leave"
                               value="{{ $project->deadline_override_reason }}">
                    </div>
                    <button class="btn btn-outline-primary w-100 btn-sm">
                        <i class="bi bi-calendar-check me-1"></i>Save Deadline
                    </button>
                    <div class="form-text">Clear the date to put this project back on the department deadline.</div>
                </form>
            </div>
        </div>
        @endcan
    </div>
</div>
@endsection
