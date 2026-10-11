@extends('layouts.app')
@php $isSuper = auth()->user()->isSuperAdmin(); @endphp
@section('title', $isSuper ? 'Super Admin Dashboard' : 'Admin Dashboard')

@section('content')
@php
    $h = (int) now()->format('H');
    $greet = $h < 12 ? 'Good morning' : ($h < 17 ? 'Good afternoon' : 'Good evening');
    $canApprovals = auth()->user()->can('approvals.manage');
    $canProjects = auth()->user()->can('projects.view');
    $levels = collect($levelStats)->filter(fn ($l) => $l['students'] > 0 || $l['projects'] > 0);
@endphp

<div class="page-head d-flex flex-wrap justify-content-between align-items-end gap-2">
    <div>
        <h3 class="page-title">{{ $greet }}, {{ auth()->user()->name }}</h3>
        <p class="page-sub">{{ now()->format('l, j F Y') }} · {{ $stats['departments'] }} {{ $stats['departments'] === 1 ? 'department' : 'departments' }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @can('reports.view')
            <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-file-earmark-bar-graph me-1"></i>Reports
            </a>
        @endcan
        @can('students.create')
            <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus me-1"></i>Add student
            </a>
        @endcan
    </div>
</div>

@if($canApprovals || $canProjects)
    <div class="row g-3 mb-4">
        @if($canApprovals)
            <div class="col-12 col-md-6 col-lg-4">
                <a href="{{ route('admin.approvals.index') }}" class="attn-tile {{ $pendingCount > 0 ? 'is-hot' : '' }}">
                    <span class="attn-n">{{ $pendingCount }}</span>
                    <span><b>Account approvals</b><small>{{ $pendingCount > 0 ? 'New sign-ups waiting' : 'Nothing waiting' }}</small></span>
                </a>
            </div>
        @endif
        @if($canProjects)
            <div class="col-12 col-md-6 col-lg-4">
                <a href="{{ route('admin.projects.index', ['faculty_id' => 'unassigned']) }}" class="attn-tile {{ $unassigned > 0 ? 'is-hot' : '' }}">
                    <span class="attn-n">{{ $unassigned }}</span>
                    <span><b>Projects without a guide</b><small>{{ $unassigned > 0 ? 'Assign a faculty member' : 'Every project has a guide' }}</small></span>
                </a>
            </div>
        @endif
    </div>
@endif

@php
    $P = \App\Models\Project::class;
    $pipeline = collect([
        ['In synopsis',    [$P::STATUS_SYNOPSIS_PENDING, $P::STATUS_SYNOPSIS_REVIEW], $P::STATUS_SYNOPSIS_REVIEW, 'pipe-c1'],
        ['Needs correction', [$P::STATUS_CORRECTION],        $P::STATUS_CORRECTION,        'pipe-c6'],
        ['Approved',       [$P::STATUS_SYNOPSIS_APPROVED],   $P::STATUS_SYNOPSIS_APPROVED, 'pipe-c2'],
        ['Final uploaded', [$P::STATUS_FINAL_SUBMITTED],     $P::STATUS_FINAL_SUBMITTED,   'pipe-c3'],
        ['Reviewed',       [$P::STATUS_FINAL_REVIEWED],      $P::STATUS_FINAL_REVIEWED,    'pipe-c4'],
        ['Result declared',[$P::STATUS_COMPLETED],           $P::STATUS_COMPLETED,         'pipe-c5'],
        ['Rejected',       [$P::STATUS_REJECTED],            $P::STATUS_REJECTED,          'pipe-c7'],
    ])->map(fn ($s) => [
        'label' => $s[0],
        'count' => (int) collect($s[1])->sum(fn ($st) => $statusCounts[$st] ?? 0),
        'url'   => route('admin.projects.index', ['status' => $s[2]]),
        'class' => $s[3],
    ])->filter(fn ($s) => $s['count'] > 0 || in_array($s['label'], ['In synopsis', 'Approved', 'Final uploaded', 'Reviewed', 'Result declared'], true));
    $pipeTotal = $pipeline->sum('count');
@endphp

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span>Where every project stands</span>
        @can('projects.view')
            <a href="{{ route('admin.projects.index') }}" class="small fw-semibold">Open projects</a>
        @endcan
    </div>
    <div class="card-body">
        @if($pipeTotal === 0)
            <p class="text-muted mb-0">No projects yet. They will appear here once students send a synopsis.</p>
        @else
            <div class="pipe-bar" role="img"
                 aria-label="Projects by stage: {{ $pipeline->map(fn ($s) => $s['label'].' '.$s['count'])->implode(', ') }}">
                @foreach($pipeline as $s)
                    @if($s['count'] > 0)
                        <a href="{{ $s['url'] }}" class="{{ $s['class'] }}" style="flex: {{ $s['count'] }}"
                           title="{{ $s['label'] }}: {{ $s['count'] }}" tabindex="-1" aria-hidden="true"></a>
                    @endif
                @endforeach
            </div>
            <div class="pipe-legend">
                @foreach($pipeline as $s)
                    <a href="{{ $s['url'] }}">
                        <div class="pipe-num">{{ $s['count'] }}</div>
                        <div class="pipe-label"><span class="pipe-key {{ $s['class'] }}"></span>{{ $s['label'] }}</div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>

<div class="kpi-strip mb-4">
    <div class="kpi">
        <a href="{{ route('admin.students.index') }}" class="kpi-label d-block">Students</a>
        <div class="kpi-num">{{ $stats['students'] }}</div>
        @if($levels->isNotEmpty())
            <div class="kpi-sub">
                @foreach($levels as $level)
                    <a href="{{ route('admin.students.index', ['level' => $level['level']]) }}">{{ $level['short'] }} {{ $level['students'] }}</a>@if(! $loop->last) · @endif
                @endforeach
            </div>
        @endif
    </div>
    <a class="kpi" href="{{ route('admin.faculties.index') }}">
        <div class="kpi-label">Faculty</div>
        <div class="kpi-num">{{ $stats['faculties'] }}</div>
    </a>
    <a class="kpi" href="{{ route('admin.departments.index') }}">
        <div class="kpi-label">Departments</div>
        <div class="kpi-num">{{ $stats['departments'] }}</div>
    </a>
    <div class="kpi">
        <a href="{{ route('admin.projects.index') }}" class="kpi-label d-block">Projects</a>
        <div class="kpi-num">{{ $stats['projects'] }}</div>
        @if($levels->isNotEmpty())
            <div class="kpi-sub">
                @foreach($levels as $level)
                    <a href="{{ route('admin.projects.index', ['level' => $level['level']]) }}">{{ $level['short'] }} {{ $level['projects'] }}</a>@if(! $loop->last) · @endif
                @endforeach
            </div>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span>Recent projects</span>
        <a href="{{ route('admin.projects.index') }}" class="small fw-semibold">View all</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Project</th><th>Group</th><th>Guide</th><th>Stage</th><th>Status</th></tr>
            </thead>
            <tbody>
                @forelse($recentProjects as $project)
                    <tr>
                        <td><a href="{{ route('admin.projects.show', $project) }}" class="fw-semibold">{{ $project->name }}</a></td>
                        <td>{{ $project->memberNames() ?: '—' }}</td>
                        <td>
                            @if($guide = $project->assignment?->faculty?->user?->name)
                                {{ $guide }}
                            @else
                                <span class="text-danger fw-semibold">Not assigned</span>
                            @endif
                        </td>
                        <td>@include('partials.mini_rail')</td>
                        <td><span class="badge bg-{{ $project->statusColor() }}">{{ $project->status }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <div class="empty-state">
                                <i class="bi bi-folder d-block fs-3 mb-1"></i>No projects yet.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
