@extends('layouts.app')
@php $isSuper = auth()->user()->isSuperAdmin(); @endphp
@section('title', $isSuper ? 'Super Admin Dashboard' : 'Admin Dashboard')

@section('content')
<h3 class="mb-4">
    <i class="bi {{ $isSuper ? 'bi-shield-lock' : 'bi-speedometer2' }} me-2"></i>
    {{ $isSuper ? 'Super Admin' : 'Admin' }} Dashboard
</h3>

<div class="row g-3 mb-4">
    @foreach([
        ['Students', $stats['students'], 'people', 'primary', route('admin.students.index')],
        ['Faculty', $stats['faculties'], 'person-badge', 'success', route('admin.faculties.index')],
        ['Departments', $stats['departments'], 'building', 'warning', route('admin.departments.index')],
        ['Projects', $stats['projects'], 'folder', 'info', route('admin.projects.index')],
    ] as [$label, $value, $icon, $tone, $url])
        <div class="col-6 col-md-3">
            <a href="{{ $url }}" class="text-decoration-none">
                <div class="card stat-tile h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="stat-ico tone-{{ $tone }}"><i class="bi bi-{{ $icon }}"></i></span>
                        <div>
                            <div class="stat-num">{{ $value }}</div>
                            <div class="stat-label">{{ $label }}</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-md-5">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold">Projects by Status</div>
            @if($statusCounts->isNotEmpty())
                <div class="card-body pb-0">
                    <canvas id="statusChart" height="200"></canvas>
                </div>
            @endif
            <ul class="list-group list-group-flush">
                @forelse($statusCounts as $status => $count)
                    <li class="list-group-item d-flex justify-content-between">
                        {{ $status }}
                        <span class="badge bg-secondary rounded-pill">{{ $count }}</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted">No projects yet.</li>
                @endforelse
                <li class="list-group-item d-flex justify-content-between text-danger">
                    <strong>Unassigned to faculty</strong>
                    <span class="badge bg-danger rounded-pill">{{ $unassigned }}</span>
                </li>
            </ul>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                Recent Projects
                <a href="{{ route('admin.projects.index') }}" class="small">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr><th>Project</th><th>Leader</th><th>Faculty</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($recentProjects as $project)
                            <tr>
                                <td><a href="{{ route('admin.projects.show', $project) }}">{{ $project->name }}</a></td>
                                <td>{{ $project->leader?->user?->name ?? '—' }}</td>
                                <td>{{ $project->assignment?->faculty?->user?->name ?? '—' }}</td>
                                <td><span class="badge bg-{{ $project->statusColor() }}">{{ $project->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No projects yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const statusEl = document.getElementById('statusChart');
    if (statusEl) {
        new Chart(statusEl, {
            type: 'doughnut',
            data: {
                labels: @json($statusCounts->keys()),
                datasets: [{
                    data: @json($statusCounts->values()),
                    backgroundColor: ['#64748b', '#0ea5e9', '#2563eb', '#d97706', '#059669', '#7c3aed', '#0d9488'],
                    borderWidth: 0,
                }],
            },
            options: {
                plugins: { legend: { position: 'bottom' } },
                cutout: '60%',
            },
        });
    }
</script>
@endpush
