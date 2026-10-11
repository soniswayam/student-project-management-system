@extends('layouts.app')
@section('title', 'Faculty Workload')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h3 class="mb-0"><i class="bi bi-speedometer me-2"></i>Faculty Workload</h3>
        <p class="text-muted small mb-0">Open academic years only — closed sessions do not count against anyone.</p>
    </div>
    <a href="{{ route('admin.workload.export', request()->query()) }}" class="btn btn-success">
        <i class="bi bi-file-earmark-excel"></i> Export
    </a>
</div>

@include('partials.alerts')

<form class="card card-body mb-3" method="GET" data-live-search>
    <div class="row g-2 align-items-end">
        <div class="col-md-6">
            <label class="form-label small mb-1">Search faculty</label>
            <div class="position-relative">
                <i class="bi bi-search position-absolute top-50 translate-middle-y ms-2 text-muted"></i>
                <input name="search" value="{{ request('search') }}" class="form-control ps-4"
                       placeholder="Name or email…" autocomplete="off">
            </div>
        </div>
        <div class="col-md-4">
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
            @if(request()->hasAny(['search', 'department_id']))
                <a href="{{ route('admin.workload.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
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
                    <th>Faculty</th>
                    <th style="min-width:160px;">Load</th>
                    <th class="text-center" title="Projects currently guided">Projects</th>
                    <th class="text-center" title="Synopses waiting for review">Synopsis</th>
                    <th class="text-center" title="Final submissions waiting for review">Final</th>
                    <th class="text-center" title="Assignments this faculty has set">Set</th>
                    <th class="text-center" title="Assignment submissions not yet checked">Unchecked</th>
                    <th class="text-end">Capacity</th>
                </tr>
            </thead>
            <tbody>
            @forelse($faculties as $faculty)
                @php $over = $faculty->isOverCapacity(); @endphp
                <tr class="{{ $over ? 'table-danger' : '' }}">
                    <td>
                        <strong>{{ $faculty->user?->name ?? '—' }}</strong>
                        <div class="text-muted small">
                            {{ $faculty->designation ?? '—' }}
                            @if($faculty->department) · {{ $faculty->department->name }} @endif
                        </div>
                    </td>
                    <td>
                        <div class="progress" style="height:8px;" role="progressbar"
                             aria-valuenow="{{ $faculty->loadPercent() }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar bg-{{ $over ? 'danger' : ($faculty->loadPercent() >= 75 ? 'warning' : 'success') }}"
                                 style="width: {{ $faculty->loadPercent() }}%"></div>
                        </div>
                        <div class="text-muted small mt-1">
                            {{ $faculty->active_projects_count }}
                            @if($faculty->max_projects !== null) / {{ $faculty->max_projects }} @endif
                            @if($over) <span class="text-danger fw-semibold">· over capacity</span> @endif
                        </div>
                    </td>
                    <td class="text-center">{{ $faculty->active_projects_count }}</td>
                    <td class="text-center">
                        @if($faculty->pending_synopsis_count)
                            <span class="badge bg-info">{{ $faculty->pending_synopsis_count }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($faculty->pending_final_count)
                            <span class="badge bg-warning text-dark">{{ $faculty->pending_final_count }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $faculty->given_assignments_count }}</td>
                    <td class="text-center">
                        @if($faculty->unchecked_submissions_count)
                            <span class="badge bg-secondary">{{ $faculty->unchecked_submissions_count }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @can('faculty.manage')
                            <form method="POST" action="{{ route('admin.workload.capacity', $faculty) }}"
                                  class="d-flex justify-content-end gap-1">
                                @csrf @method('PATCH')
                                <input type="number" name="max_projects" min="0" max="999"
                                       value="{{ $faculty->max_projects }}" placeholder="∞"
                                       class="form-control form-control-sm" style="width:5rem;"
                                       aria-label="Maximum projects for {{ $faculty->user?->name }}">
                                <button class="btn btn-sm btn-outline-primary" title="Save capacity">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </form>
                        @else
                            {{ $faculty->max_projects ?? 'Uncapped' }}
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No faculty match those filters.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<p class="text-muted small mt-2">
    <i class="bi bi-info-circle me-1"></i>
    Leave capacity blank for uncapped. Capacity only warns during assignment — it never blocks one.
</p>
@endsection
