@extends('layouts.app')
@section('title', 'Reports')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Reports &amp; Analytics</h3>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.reports.export') }}" class="btn btn-danger"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
        <a href="{{ route('admin.projects.export') }}" class="btn btn-success"><i class="bi bi-file-earmark-excel"></i> Excel</a>
        <button onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer"></i> Print</button>
    </div>
</div>
<div class="print-title mb-2"><h4>{{ config('college.name') }} — Projects Analytics Report</h4><small>Printed on {{ now()->format('d M Y') }}</small></div>

<div class="row g-3 mb-3">
    <div class="col-md-7">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold">Projects by Status</div>
            <div class="card-body">
                @if($byStatus->isNotEmpty())
                    <canvas id="statusBar" height="140"></canvas>
                @else
                    <p class="text-muted mb-0">No data.</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold">Projects by Type</div>
            <div class="card-body d-flex align-items-center justify-content-center">
                @if($byType->isNotEmpty())
                    <canvas id="typePie" height="180"></canvas>
                @else
                    <p class="text-muted mb-0">No data.</p>
                @endif
            </div>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const statusBar = document.getElementById('statusBar');
    if (statusBar) {
        new Chart(statusBar, {
            type: 'bar',
            data: {
                labels: @json($byStatus->keys()),
                datasets: [{
                    label: 'Projects',
                    data: @json($byStatus->values()),
                    backgroundColor: '#2563eb',
                    borderRadius: 6,
                    maxBarThickness: 48,
                }],
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            },
        });
    }

    const typePie = document.getElementById('typePie');
    if (typePie) {
        new Chart(typePie, {
            type: 'pie',
            data: {
                labels: @json($byType->keys()->map(fn ($t) => ucfirst($t))),
                datasets: [{
                    data: @json($byType->values()),
                    backgroundColor: ['#2563eb', '#059669', '#d97706', '#0ea5e9'],
                    borderWidth: 0,
                }],
            },
            options: { plugins: { legend: { position: 'bottom' } } },
        });
    }
</script>
@endpush
