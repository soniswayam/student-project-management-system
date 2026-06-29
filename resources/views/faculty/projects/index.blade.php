@extends('layouts.app')
@section('title', 'Assigned Projects')

@section('content')
<h3 class="mb-3">Assigned Projects</h3>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light"><tr><th>Project</th><th>Leader</th><th>Members</th><th>Type</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($projects as $project)
                <tr>
                    <td><strong>{{ $project->name }}</strong></td>
                    <td>{{ $project->leader?->user?->name ?? '—' }}</td>
                    <td>{{ $project->members->count() }}</td>
                    <td>{{ ucfirst($project->project_type) }}</td>
                    <td><span class="badge bg-{{ $project->statusColor() }}">{{ $project->status }}</span></td>
                    <td class="text-end"><a href="{{ route('faculty.projects.show', $project) }}" class="btn btn-sm btn-outline-primary">Review</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No projects assigned to you yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $projects->links() }}</div>
@endsection
