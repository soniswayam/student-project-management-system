@extends('layouts.app')
@section('title', 'Reports')

@section('content')
<h3 class="mb-3">Reports &amp; Analytics</h3>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold">Projects by Status</div>
            <ul class="list-group list-group-flush">
                @forelse($byStatus as $status => $count)
                    <li class="list-group-item d-flex justify-content-between">{{ $status }}<span class="badge bg-secondary rounded-pill">{{ $count }}</span></li>
                @empty
                    <li class="list-group-item text-muted">No data.</li>
                @endforelse
            </ul>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold">Projects by Type</div>
            <ul class="list-group list-group-flush">
                @forelse($byType as $type => $count)
                    <li class="list-group-item d-flex justify-content-between">{{ ucfirst($type) }}<span class="badge bg-primary rounded-pill">{{ $count }}</span></li>
                @empty
                    <li class="list-group-item text-muted">No data.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header bg-white fw-semibold">Department-wise Breakdown</div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead class="table-light"><tr><th>Department</th><th>Students</th><th>Projects</th></tr></thead>
            <tbody>
                @forelse($byDepartment as $dept)
                    <tr><td>{{ $dept->name }}</td><td>{{ $dept->students_count }}</td><td>{{ $dept->projects_count }}</td></tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-3">No departments.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white fw-semibold">Graded Projects (by marks)</div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light"><tr><th>Project</th><th>Leader</th><th>Faculty</th><th>Marks</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($completed as $project)
                    <tr>
                        <td>{{ $project->name }}</td>
                        <td>{{ $project->leader?->user?->name ?? '—' }}</td>
                        <td>{{ $project->assignment?->faculty?->user?->name ?? '—' }}</td>
                        <td><span class="badge bg-success">{{ $project->marks }}/100</span></td>
                        <td><span class="badge bg-{{ $project->statusColor() }}">{{ $project->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">No graded projects yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
