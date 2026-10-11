@extends('layouts.app')
@section('title', 'Archive')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="mb-0"><i class="bi bi-archive me-2"></i>Archive</h3>
        <p class="text-muted small mb-0">Projects from closed academic years — read-only.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.archive.export', request()->query()) }}" class="btn btn-success">
            <i class="bi bi-file-earmark-excel"></i> Export
        </a>
    </div>
</div>

@include('partials.alerts')

@if($years->isEmpty())
    <div class="card card-body text-center text-muted py-5">
        <i class="bi bi-archive fs-1 mb-2 d-block"></i>
        <p class="mb-1 fw-semibold">Nothing archived yet</p>
        <p class="mb-3 small">
            Close an academic year to move its projects here and clear them out of the active lists.
        </p>
        @can('academic_years.manage')
            <div><a href="{{ route('admin.academic-years.index') }}" class="btn btn-primary btn-sm">Manage academic years</a></div>
        @endcan
    </div>
@else

<form class="card card-body mb-3" method="GET" data-live-search>
    <div class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label small mb-1">Search</label>
            <div class="position-relative">
                <i class="bi bi-search position-absolute top-50 translate-middle-y ms-2 text-muted"></i>
                <input name="search" value="{{ request('search') }}" class="form-control ps-4"
                       placeholder="Project, student or roll no…" autocomplete="off">
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Academic year</label>
            <select name="academic_year_id" class="form-select">
                <option value="">All archived years</option>
                @foreach($years as $year)
                    <option value="{{ $year->id }}" @selected(request('academic_year_id') == $year->id)>{{ $year->label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Department</label>
            <select name="department_id" class="form-select">
                <option value="">All departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2 align-items-center">
            <span class="text-muted small" data-live-status></span>
            @if(request()->hasAny(['search', 'academic_year_id', 'department_id', 'status']))
                <a href="{{ route('admin.archive.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            @endif
        </div>
    </div>
    <noscript><button class="btn btn-primary btn-sm mt-2">Filter</button></noscript>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Year</th><th>Project</th><th>Group</th><th>Department</th>
                    <th>Guide</th><th>Status</th><th class="text-center">Marks</th><th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($projects as $project)
                <tr>
                    <td><span class="badge bg-secondary">{{ $project->academicYear?->label ?? '—' }}</span></td>
                    <td><strong>{{ $project->name }}</strong></td>
                    <td>{{ $project->memberNames() ?: '—' }}</td>
                    <td>{{ $project->department?->name ?? '—' }}</td>
                    <td>{{ $project->assignment?->faculty?->user?->name ?? '—' }}</td>
                    <td><span class="badge bg-{{ $project->statusColor() }}">{{ $project->status }}</span></td>

                    <td class="text-center">{{ $project->marks ?? '—' }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.archive.show', $project) }}" class="act-btn act-view" title="View">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No archived projects match those filters.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $projects->links() }}</div>
@endif
@endsection
