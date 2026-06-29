@extends('layouts.app')
@section('title', 'Project Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Project Details</h3>
    <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-secondary btn-sm">← Back to Projects</a>
</div>

<div class="row">
    <div class="col-lg-7">
        @include('partials.project_info')
        @if($project->isSynopsisApproved())
            @include('partials.submission_files')
        @endif
        @include('partials.reviews_timeline')
    </div>

    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-person-check me-1"></i> Assign Faculty</div>
            <div class="card-body">
                @if($project->assignment)
                    <p class="mb-2">Currently assigned to
                        <strong>{{ $project->assignment->faculty->user->name }}</strong>.
                        You may reassign below.</p>
                @else
                    <p class="text-muted mb-2">No faculty assigned yet.</p>
                @endif
                <form method="POST" action="{{ route('admin.projects.assign', $project) }}">
                    @csrf
                    <div class="mb-3">
                        <select name="faculty_id" class="form-select" required>
                            <option value="">— Select Faculty —</option>
                            @foreach($faculties as $faculty)
                                <option value="{{ $faculty->id }}" @selected($project->assignment?->faculty_id === $faculty->id)>
                                    {{ $faculty->user->name }} ({{ $faculty->designation ?? 'Faculty' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-primary w-100">
                        {{ $project->assignment ? 'Reassign' : 'Assign' }} Faculty
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
