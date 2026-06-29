@extends('layouts.app')
@section('title', 'All Projects')

@section('content')
<h3 class="mb-3">All Projects</h3>

<form class="card card-body mb-3" method="GET">
    <div class="row g-2 align-items-end">
        <div class="col-md-5">
            <label class="form-label small mb-1">Search by name</label>
            <input name="search" value="{{ request('search') }}" class="form-control" placeholder="Project name...">
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
        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-primary flex-fill">Filter</button>
            <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </div>
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
                    <td class="text-end"><a href="{{ route('admin.projects.show', $project) }}" class="btn btn-sm btn-outline-primary">View</a></td>
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
