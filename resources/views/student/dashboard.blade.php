@extends('layouts.app')
@section('title', 'Student Dashboard')

@section('content')
<h3 class="mb-4">Welcome, {{ auth()->user()->name }}</h3>

@if(!$project)
    {{-- No synopsis yet --}}
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-file-earmark-plus display-3 text-primary mb-3"></i>
            <h4>Step 1: Submit Your Synopsis</h4>
            <p class="text-muted mb-4" style="max-width:520px;margin:auto">
                Before you can access the full project submission system, you must submit your synopsis
                and get it approved by the assigned faculty.
            </p>
            <a href="{{ route('student.synopsis.create') }}" class="btn btn-primary btn-lg">
                <i class="bi bi-pencil-square me-1"></i> Submit Synopsis
            </a>
        </div>
    </div>
@else
    {{-- Progress tracker --}}
    @include('partials.progress_tracker')

    <div class="row">
        <div class="col-lg-7">
            @include('partials.project_info')
            @if($project->isSynopsisApproved())
                @include('partials.submission_files')
            @endif
        </div>
        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold">Next Action</div>
                <div class="card-body">
                    @if($project->status === \App\Models\Project::STATUS_SYNOPSIS_REVIEW)
                        <p class="mb-0"><i class="bi bi-hourglass-split text-warning"></i> Your synopsis is under review. Please wait for faculty feedback.</p>
                    @elseif($project->status === \App\Models\Project::STATUS_CORRECTION)
                        <p><i class="bi bi-exclamation-triangle text-warning"></i> Faculty requested corrections. Review the comments below.</p>
                    @elseif($project->status === \App\Models\Project::STATUS_SYNOPSIS_APPROVED)
                        <p>Your synopsis is approved! You can now upload your final project.</p>
                        <a href="{{ route('student.submission.create') }}" class="btn btn-primary w-100"><i class="bi bi-upload me-1"></i> Upload Final Project</a>
                    @elseif($project->status === \App\Models\Project::STATUS_FINAL_SUBMITTED)
                        <p class="mb-2"><i class="bi bi-hourglass text-info"></i> Final project submitted. Awaiting faculty review.</p>
                        <a href="{{ route('student.project.certificate') }}" class="btn btn-success w-100 mb-2"><i class="bi bi-award me-1"></i> Download Certificate</a>
                        <a href="{{ route('student.submission.create') }}" class="btn btn-outline-secondary w-100">Re-upload Files</a>
                    @elseif($project->status === \App\Models\Project::STATUS_FINAL_REVIEWED)
                        <p class="mb-0"><i class="bi bi-hourglass text-info"></i> Your project is reviewed — the final result will be declared soon.</p>
                    @elseif($project->status === \App\Models\Project::STATUS_COMPLETED)
                        <p class="mb-2"><i class="bi bi-award text-success"></i> Result declared! Your final result: <strong>{{ $project->marks }}/100</strong></p>
                        <a href="{{ route('student.project.certificate') }}" class="btn btn-success w-100"><i class="bi bi-award me-1"></i> Download Certificate</a>
                    @endif
                </div>
            </div>
            @include('partials.reviews_timeline')
        </div>
    </div>
@endif
@endsection
