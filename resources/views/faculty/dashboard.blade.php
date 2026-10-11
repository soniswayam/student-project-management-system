@extends('layouts.app')
@section('title', 'Faculty Dashboard')

@section('content')
@php
    $h = (int) now()->format('H');
    $greet = $h < 12 ? 'Good morning' : ($h < 17 ? 'Good afternoon' : 'Good evening');

    $queue = $projects->whereIn('status', [
        \App\Models\Project::STATUS_SYNOPSIS_REVIEW,
        \App\Models\Project::STATUS_FINAL_SUBMITTED,
    ])->sortBy('updated_at');
    $toReview = $stats['pending_synopsis'] + $stats['pending_final'];
    $finalIn = $projects->whereIn('status', [
        \App\Models\Project::STATUS_FINAL_SUBMITTED,
        \App\Models\Project::STATUS_FINAL_REVIEWED,
        \App\Models\Project::STATUS_COMPLETED,
    ])->count();
@endphp

<div class="page-head d-flex flex-wrap justify-content-between align-items-start gap-2">
    <div>
        <h3 class="page-title">{{ $greet }}, {{ auth()->user()->name }}</h3>
        <p class="page-sub">
            {{ now()->format('l, j F Y') }} ·
            @if($toReview)
                {{ $toReview }} {{ $toReview === 1 ? 'submission is' : 'submissions are' }} waiting for your review
            @else
                Nothing is waiting for your review
            @endif
        </p>
    </div>
    <a href="{{ route('faculty.assignments.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>New assignment
    </a>
</div>

<div class="kpi-strip mb-4">
    <a class="kpi" href="{{ route('faculty.projects.index') }}">
        <div class="kpi-label">Assigned projects</div>
        <div class="kpi-num">{{ $stats['assigned'] }}</div>
    </a>
    <div class="kpi">
        <div class="kpi-label">To review</div>
        <div class="kpi-num {{ $toReview ? 'is-warn' : '' }}">{{ $toReview }}</div>
        <div class="kpi-sub">{{ $stats['pending_synopsis'] }} synopsis · {{ $stats['pending_final'] }} final</div>
    </div>
    <div class="kpi">
        <div class="kpi-label">Final uploads in</div>
        <div class="kpi-num">{{ $finalIn }}</div>
        <div class="kpi-sub">of {{ $stats['assigned'] }} {{ $stats['assigned'] === 1 ? 'project' : 'projects' }}</div>
    </div>
    <div class="kpi">
        <div class="kpi-label">Completed</div>
        <div class="kpi-num">{{ $stats['completed'] }}</div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <span>Review queue</span>
        <span class="text-muted small fw-normal">Longest waiting first</span>
    </div>
    @if($queue->isEmpty())
        <div class="card-body">
            <p class="text-muted mb-0"><i class="bi bi-check2-circle me-1"></i>All caught up. New submissions will appear here.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light"><tr><th>Project</th><th>Waiting for</th><th>Stage</th><th>Waiting</th><th></th></tr></thead>
                <tbody>
                @foreach($queue as $project)
                    @php
                        $isSynopsis = $project->status === \App\Models\Project::STATUS_SYNOPSIS_REVIEW;
                        $days = (int) $project->updated_at?->copy()->startOfDay()->diffInDays(now()->startOfDay());
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ route('faculty.projects.show', $project) }}" class="fw-semibold text-reset">{{ $project->name }}</a>
                            <div class="text-muted small">{{ $project->memberNames() ?: '—' }}</div>
                        </td>
                        <td><span class="badge bg-{{ $isSynopsis ? 'warning' : 'primary' }}">{{ $isSynopsis ? 'Synopsis review' : 'Final review' }}</span></td>
                        <td>@include('partials.mini_rail')</td>
                        <td><span class="wait-days {{ $days >= 7 ? 'is-late' : '' }}">{{ $days === 0 ? 'Today' : $days.' '.($days === 1 ? 'day' : 'days') }}</span></td>
                        <td class="text-end"><a href="{{ route('faculty.projects.show', $project) }}" class="btn btn-sm btn-primary">Review</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <span>My assigned projects</span>
        <span class="text-muted small fw-normal">{{ $projects->count() }} total</span>
    </div>
    @if($projects->isEmpty())
        <div class="card-body">
            <div class="empty-state text-center py-4">
                <i class="bi bi-folder2-open fs-1 d-block mb-2"></i>
                No projects assigned to you yet.
            </div>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light"><tr><th>Project</th><th>Group</th><th>Type</th><th>Stage</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @foreach($projects as $project)
                    <tr>
                        <td><strong>{{ $project->name }}</strong></td>
                        <td>{{ $project->memberNames() ?: '—' }}</td>
                        <td>{{ ucfirst($project->project_type) }}</td>
                        <td>@include('partials.mini_rail')</td>
                        <td><span class="badge bg-{{ $project->statusColor() }}">{{ $project->status }}</span></td>
                        <td class="text-end"><a href="{{ route('faculty.projects.show', $project) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye me-1"></i>Open</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
