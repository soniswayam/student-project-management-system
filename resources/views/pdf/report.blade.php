@extends('pdf.layouts.report')
@section('doc-title', 'Projects Analytics Report')
@section('ref', 'RPT')

@section('body')
    <div class="summary">
        <strong>Total projects:</strong> {{ $byStatus->sum() }} &nbsp;&middot;&nbsp;
        <strong>Departments:</strong> {{ $byDepartment->count() }} &nbsp;&middot;&nbsp;
        <strong>Graded projects:</strong> {{ $completed->count() }}
    </div>

    <h2>Projects by Status</h2>
    <table class="data">
        <thead><tr><th style="width:70%">Status</th><th class="c">Count</th></tr></thead>
        <tbody>
            @forelse($byStatus as $status => $count)
                <tr><td>{{ $status }}</td><td class="c">{{ $count }}</td></tr>
            @empty
                <tr><td colspan="2" class="c muted">No data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Projects by Type</h2>
    <table class="data">
        <thead><tr><th style="width:70%">Type</th><th class="c">Count</th></tr></thead>
        <tbody>
            @forelse($byType as $type => $count)
                <tr><td>{{ ucfirst($type) }}</td><td class="c">{{ $count }}</td></tr>
            @empty
                <tr><td colspan="2" class="c muted">No data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Department-wise Breakdown</h2>
    <table class="data">
        <thead><tr><th>Department</th><th class="c">Students</th><th class="c">Projects</th></tr></thead>
        <tbody>
            @forelse($byDepartment as $dept)
                <tr><td>{{ $dept->name }}</td><td class="c">{{ $dept->students_count }}</td><td class="c">{{ $dept->projects_count }}</td></tr>
            @empty
                <tr><td colspan="3" class="c muted">No departments.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Graded Projects (by marks)</h2>
    <table class="data">
        <thead><tr><th class="c" style="width:6%">#</th><th>Project</th><th>Leader</th><th>Faculty</th><th class="c">Marks</th></tr></thead>
        <tbody>
            @forelse($completed as $project)
                <tr>
                    <td class="c">{{ $loop->iteration }}</td>
                    <td>{{ $project->name }}</td>
                    <td>{{ $project->leader?->user?->name ?? '—' }}</td>
                    <td>{{ $project->assignment?->faculty?->user?->name ?? '—' }}</td>
                    <td class="c">{{ $project->marks }}/100</td>
                </tr>
            @empty
                <tr><td colspan="5" class="c muted">No graded projects yet.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
