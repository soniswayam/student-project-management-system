@extends('layouts.app')
@section('title', 'My Project')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">My Project</h3>
    @if($project->status === \App\Models\Project::STATUS_SYNOPSIS_APPROVED)
        <a href="{{ route('student.submission.create') }}" class="btn btn-primary"><i class="bi bi-upload me-1"></i> Upload Final Project</a>
    @elseif($project->status === \App\Models\Project::STATUS_FINAL_SUBMITTED)
        <a href="{{ route('student.submission.create') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-repeat me-1"></i> Re-upload Files</a>
    @endif
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
