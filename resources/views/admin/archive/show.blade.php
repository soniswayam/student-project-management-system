@extends('layouts.app')
@section('title', $project->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h3 class="mb-0"><i class="bi bi-archive me-2"></i>{{ $project->name }}</h3>
        <p class="text-muted small mb-0">
            Archived under <strong>{{ $project->academicYear?->label ?? '—' }}</strong>
        </p>
    </div>
    <a href="{{ route('admin.archive.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Archive
    </a>
</div>

<div class="alert alert-secondary d-flex align-items-center">
    <i class="bi bi-lock me-2"></i>
    <div>
        This academic year is closed, so the project is read-only. Reopen
        {{ $project->academicYear?->label ? '"'.$project->academicYear->label.'"' : 'the year' }}
        under Academic Years to edit it again.
    </div>
</div>

@include('partials.project_info')
@include('partials.submission_files')
@include('partials.reviews_timeline')
@endsection
