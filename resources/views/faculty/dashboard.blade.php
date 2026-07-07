@extends('layouts.app')
@section('title', 'Faculty Dashboard')

@section('content')
<h3 class="mb-4"><i class="bi bi-speedometer2 me-2"></i>Faculty Dashboard</h3>

<div class="row g-3 mb-4">
    @foreach([
        ['Assigned', $stats['assigned'], 'folder-check', 'primary'],
        ['Synopsis to Review', $stats['pending_synopsis'], 'hourglass-split', 'warning'],
        ['Final to Review', $stats['pending_final'], 'inbox', 'info'],
        ['Completed', $stats['completed'], 'check2-circle', 'success'],
    ] as [$label, $value, $icon, $tone])
        <div class="col-6 col-md-3">
            <div class="card stat-tile h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-ico tone-{{ $tone }}"><i class="bi bi-{{ $icon }}"></i></span>
                    <div>
                        <div class="stat-num">{{ $value }}</div>
                        <div class="stat-label">{{ $label }}</div>
                    </div>
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
                    <td class="text-end"><a href="{{ route('faculty.projects.show', $project) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye me-1"></i>Review</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No projects assigned to you yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
