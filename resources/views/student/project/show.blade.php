@extends('layouts.app')
@section('title', 'My Project')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0"><i class="bi bi-folder me-2"></i>My Project</h3>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('student.synopsis.download') }}" class="btn btn-outline-primary"><i class="bi bi-file-earmark-text me-1"></i> Synopsis PDF</a>
        @if($project->isSubmitted())
            <a href="{{ route('student.project.certificate') }}" class="btn btn-success"><i class="bi bi-award me-1"></i> Download Certificate</a>
        @endif
        @if($project->status === \App\Models\Project::STATUS_SYNOPSIS_APPROVED)
            <a href="{{ route('student.submission.create') }}" class="btn btn-primary"><i class="bi bi-upload me-1"></i> Upload Final Project</a>
        @elseif($project->status === \App\Models\Project::STATUS_FINAL_SUBMITTED)
            <a href="{{ route('student.submission.create') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-repeat me-1"></i> Re-upload Files</a>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-lg-7">
        @include('partials.project_info')
        @if($project->isSynopsisApproved())
            @include('partials.submission_files')
        @endif
    </div>
    <div class="col-lg-5">
        @include('partials.reviews_timeline')
    </div>
</div>
@endsection
