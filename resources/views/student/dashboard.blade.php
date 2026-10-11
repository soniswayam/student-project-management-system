@extends('layouts.app')
@section('title', 'Student Dashboard')

@section('content')
@php
    $h = (int) now()->format('H');
    $greet = $h < 12 ? 'Good morning' : ($h < 17 ? 'Good afternoon' : 'Good evening');
    $firstName = \Illuminate\Support\Str::of(auth()->user()->name)->explode(' ')->first();
    $P = \App\Models\Project::class;
@endphp

<div class="page-head d-flex flex-wrap justify-content-between align-items-end gap-2">
    <div>
        <h3 class="page-title">{{ $greet }}, {{ $firstName }}</h3>
        <p class="page-sub">{{ now()->format('l, j F Y') }}</p>
    </div>
    @if($project)
        <a href="{{ route('student.synopsis.download') }}" class="btn btn-outline-secondary">
            <i class="bi bi-file-earmark-text me-1"></i> View synopsis
        </a>
    @endif
</div>

@if(!$project)

    <div class="card">
        <div class="card-body text-center py-5">
            <div class="empty-state">
                <i class="bi bi-file-earmark-plus display-3 d-block mb-3"></i>
            </div>
            <h4>Submit your synopsis to get started</h4>
            <p class="text-muted mb-4 mx-auto" style="max-width:520px">
                Before you can access the full project submission system, you must submit your synopsis
                and get it approved by the assigned faculty.
            </p>
            <a href="{{ route('student.synopsis.create') }}" class="btn btn-primary btn-lg">
                <i class="bi bi-pencil-square me-1"></i> Submit Synopsis
            </a>
        </div>
    </div>
@else

    <div class="card project-hero mb-4">
        <div class="project-hero-main">
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <h4 class="project-hero-title mb-0">{{ $project->name }}</h4>
                    <span class="badge bg-{{ $project->statusColor() }}">{{ $project->status }}</span>
                </div>
                <div class="project-hero-meta">
                    <span>Guide <b>{{ $project->assignment?->faculty?->user?->name ?? 'Not assigned yet' }}</b></span>
                    <span>Group <b>{{ $project->memberNames() ?: '—' }}</b></span>
                    @if($project->department)
                        <span>Department <b>{{ $project->department->name }}</b></span>
                    @endif
                </div>
            </div>
            @include('partials.stage_rail')
        </div>

        <div class="project-hero-next">
            <div class="next-kicker">Your next step</div>
            @if($project->status === $P::STATUS_SYNOPSIS_REVIEW)
                <div class="next-title">Wait for your guide's review</div>
                <p class="mb-0">Your synopsis is under review. You will get a notification when your guide replies.</p>
            @elseif($project->status === $P::STATUS_CORRECTION)
                <div class="next-title">Correct your synopsis</div>
                <p>Your guide asked for changes. Read the feedback, then send the synopsis again.</p>
                @if($project->isSynopsisEditable() && ! $student->isAlumni())
                    <a href="{{ route('student.synopsis.edit') }}" class="btn btn-primary mt-auto align-self-start"><i class="bi bi-pencil-square me-1"></i> Edit &amp; resubmit synopsis</a>
                @endif
            @elseif($project->status === $P::STATUS_SYNOPSIS_APPROVED)
                <div class="next-title">Upload your final project</div>
                <p>Your synopsis is approved. Add the report and source code.</p>

                @if(! $student->isAlumni() || $project->openFinalRevisionRequest())
                    <a href="{{ route('student.submission.create') }}" class="btn btn-primary mt-auto align-self-start"><i class="bi bi-upload me-1"></i> Upload final project</a>
                @endif
            @elseif($project->status === $P::STATUS_FINAL_SUBMITTED)
                <div class="next-title">Wait for the final review</div>
                <p>Your final project is submitted. Your guide will review it.</p>
                <div class="mt-auto d-flex flex-wrap gap-2">
                    <a href="{{ route('student.project.certificate') }}" class="btn btn-success"><i class="bi bi-award me-1"></i> Download certificate</a>
                    @if(! $student->isAlumni() || $project->openFinalRevisionRequest())
                        <a href="{{ route('student.submission.create') }}" class="btn btn-outline-secondary bg-white">Re-upload files</a>
                    @endif
                </div>
            @elseif($project->status === $P::STATUS_FINAL_REVIEWED)
                <div class="next-title">Result coming soon</div>
                <p class="mb-0">Your project is reviewed. The final result will be declared soon.</p>
                @if($project->openFinalRevisionRequest())
                    <a href="{{ route('student.submission.create') }}" class="btn btn-outline-secondary bg-white mt-3 align-self-start">Re-upload files</a>
                @endif
            @elseif($project->status === $P::STATUS_COMPLETED)
                <div class="next-title">Result declared</div>
                <p>@if($project->marksLabel())Your final result: <strong>{{ $project->marksLabel() }}</strong>@else Your project is complete.@endif</p>
                <a href="{{ route('student.project.certificate') }}" class="btn btn-success mt-auto align-self-start"><i class="bi bi-award me-1"></i> Download certificate</a>
            @else
                <div class="next-title">{{ $project->status }}</div>
                <p class="mb-0">Open My Project for details.</p>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            @include('partials.project_info')
            @if($project->isSynopsisApproved())
                @include('partials.submission_files')
            @endif
        </div>

        <div class="col-12 col-lg-5 order-first order-lg-last">
            @include('partials.reviews_timeline')
        </div>
    </div>
@endif
@endsection
