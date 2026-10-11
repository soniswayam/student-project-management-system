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
        @if($project->isSynopsisEditable() && ! $student->isAlumni())
            <a href="{{ route('student.synopsis.edit') }}" class="btn btn-warning"><i class="bi bi-pencil-square me-1"></i> Edit &amp; Resubmit Synopsis</a>
        @endif

        @if(! $student->isAlumni() || $project->openFinalRevisionRequest())
            @if($project->status === \App\Models\Project::STATUS_SYNOPSIS_APPROVED)
                <a href="{{ route('student.submission.create') }}" class="btn btn-primary"><i class="bi bi-upload me-1"></i> Upload Final Project</a>
            @elseif($project->status === \App\Models\Project::STATUS_FINAL_SUBMITTED || $revisionRequest)

                <a href="{{ route('student.submission.create') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-repeat me-1"></i> Re-upload Files</a>
            @endif
        @endif
    </div>
</div>

@if($student->isAlumni())
    <div class="alert alert-primary d-flex align-items-start gap-2">
        <i class="bi bi-mortarboard-fill fs-5"></i>
        <div>
            <strong>You have passed out{{ $student->batchLabel() ? ' — batch '.$student->batchLabel() : '' }}.</strong>
            Your records are read-only now. You can still view your project and reviews, and
            download your completion certificate.
        </div>
    </div>
@endif

@include('partials.revision_banner')

@if($project->isSynopsisApproved() && ! $project->isSubmitted())
    @include('partials.deadline_banner', [
        'window' => $project->submissionWindow(),
        'sourceNote' => $project->deadlineSource() === 'override'
            ? 'This deadline was set specifically for your project.'
            : null,
    ])
@endif

@if($project->isSynopsisEditable())
    <div class="alert alert-{{ $project->status === \App\Models\Project::STATUS_REJECTED ? 'danger' : 'warning' }} d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <div>
            @if($project->status === \App\Models\Project::STATUS_REJECTED)
                Your synopsis was <strong>rejected</strong>. Read your guide's comments, revise it, and resubmit.
            @else
                Your guide has asked for <strong>corrections</strong>. Update your synopsis and resubmit it for review.
            @endif
        </div>
    </div>
@endif

<div class="row">
    <div class="col-lg-7">
        @include('partials.project_info')
        @if($project->isSynopsisApproved())
            @include('partials.submission_files')
        @endif
    </div>
    <div class="col-lg-5">
        @include('partials.viva_card')
        @include('partials.reviews_timeline')
    </div>
</div>
@endsection
