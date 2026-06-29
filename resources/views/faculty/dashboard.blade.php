@extends('layouts.app')
@section('title', 'Faculty Dashboard')

@section('content')
<h3 class="mb-4">Faculty Dashboard</h3>

<div class="row g-3 mb-4">
    @foreach([
        ['Assigned', $stats['assigned'], 'folder-check', 'primary'],
        ['Synopsis to Review', $stats['pending_synopsis'], 'hourglass-split', 'warning'],
        ['Final to Review', $stats['pending_final'], 'inbox', 'info'],
        ['Completed', $stats['completed'], 'check2-circle', 'success'],
    ] as [$label, $value, $icon, $color])
        <div class="col-md-3">
            <div class="card stat-card bg-{{ $color }} text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><div class="display-6">{{ $value }}</div><div>{{ $label }}</div></div>
                    <i class="bi bi-{{ $icon }} fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="card-header bg-white fw-semibold">My Assigned Projects</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light"><tr><th>Project</th><th>Leader</th><th>Type</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($projects as $project)
                <tr>
                    <td><strong>{{ $project->name }}</strong></td>
                    <td>{{ $project->leader?->user?->name ?? '—' }}</td>
                    <td>{{ ucfirst($project->project_type) }}</td>
                    <td><span class="badge bg-{{ $project->statusColor() }}">{{ $project->status }}</span></td>
                    <td class="text-end"><a href="{{ route('faculty.projects.show', $project) }}" class="btn btn-sm btn-outline-primary">Review</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No projects assigned to you yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
