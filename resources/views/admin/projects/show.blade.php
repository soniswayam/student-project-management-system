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
        @if($project->isSynopsisApproved())
            @include('partials.submission_files')
        @endif
        @include('partials.reviews_timeline')
    </div>

    <div class="col-lg-5">
        @can('projects.assign')
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
                        <i class="bi bi-person-check me-1"></i>{{ $project->assignment ? 'Reassign' : 'Assign' }} Faculty
                    </button>
                </form>
            </div>
        </div>
        @endcan
    </div>
</div>
@endsection
