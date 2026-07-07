@extends('layouts.app')
@section('title', 'All Projects')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-folder me-2"></i>All Projects</h3>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.projects.export') }}" class="btn btn-success"><i class="bi bi-file-earmark-excel"></i> Excel (Groups)</a>
        <button onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer"></i> Print</button>
    </div>
</div>
<div class="print-title mb-2"><h4>{{ config('college.name') }} — Projects &amp; Groups</h4><small>Printed on {{ now()->format('d M Y') }}</small></div>

<form class="card card-body mb-3" method="GET" data-live-search>
    <div class="row g-2 align-items-end">
        <div class="col-md-5">
            <label class="form-label small mb-1">Search by name</label>
            <div class="position-relative">
                <i class="bi bi-search position-absolute top-50 translate-middle-y ms-2 text-muted"></i>
                <input name="search" value="{{ request('search') }}" class="form-control ps-4"
                       placeholder="Start typing project name…" autocomplete="off" autofocus>
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label small mb-1">Status</label>
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2 align-items-center">
            <span class="text-muted small" data-live-status></span>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            @endif
        </div>
    </div>
    <noscript><button class="btn btn-primary btn-sm mt-2">Filter</button></noscript>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Project</th><th>Type</th><th>Leader</th><th>Members</th><th>Faculty</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            @forelse($projects as $project)
                <tr>
                    <td><strong>{{ $project->name }}</strong></td>
                    <td><span class="badge bg-{{ $project->project_type === 'group' ? 'primary' : 'secondary' }}">{{ ucfirst($project->project_type) }}</span></td>
                    <td>{{ $project->leader?->user?->name ?? '—' }}</td>
                    <td>{{ $project->members->count() }}</td>
                    <td>
                        @if($project->assignment)
                            {{ $project->assignment->faculty->user->name }}
                        @else
                            <span class="badge bg-danger">Unassigned</span>
                        @endif
                    </td>
                    <td><span class="badge bg-{{ $project->statusColor() }}">{{ $project->status }}</span></td>
                    <td class="text-end"><a href="{{ route('admin.projects.show', $project) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye me-1"></i>View</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No projects found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $projects->links() }}</div>
@endsection
