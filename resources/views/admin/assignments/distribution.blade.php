@extends('layouts.app')
@section('title', 'Assignment Distribution')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Assignment Distribution</h3>
    <div class="d-flex gap-2 no-print">
        <a href="{{ route('admin.assignments.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Assignments</a>
        <a href="{{ route('admin.assignments.distribution.pdf', request()->query()) }}" class="btn btn-danger"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
        <a href="{{ route('admin.assignments.distribution.excel', request()->query()) }}" class="btn btn-success"><i class="bi bi-file-earmark-excel"></i> Excel</a>
        <button onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer"></i> Print</button>
    </div>
</div>

{{-- Summary strip --}}
<div class="row g-2 mb-3 no-print">
    @foreach([
        ['Faculty', $totals['faculty'], 'bi-person-badge'],
        ['Subjects', $totals['subjects'], 'bi-book'],
        ['Assignments', $totals['assignments'], 'bi-journal-text'],
        ['Submissions', $totals['submissions'], 'bi-upload'],
        ['Checked', $totals['checked'], 'bi-check2-circle'],
    ] as [$label, $val, $icon])
        <div class="col-6 col-md">
            <div class="card text-center"><div class="card-body py-2">
                <div class="text-muted small"><i class="bi {{ $icon }} me-1"></i>{{ $label }}</div>
                <div class="fs-4 fw-bold">{{ $val }}</div>
            </div></div>
        </div>
    @endforeach
</div>

<form method="GET" class="row g-2 mb-3 no-print">
    <div class="col-auto">
        <select name="department_id" class="form-select" onchange="this.form.submit()">
            <option value="">All departments</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
            @endforeach
        </select>
    </div>
</form>

{{-- ============ SCREEN: grouped by Faculty › Subject ============ --}}
<div class="no-print">
    @forelse($byFaculty as $facultyName => $assignments)
        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-person-badge me-1"></i>{{ $facultyName }}</div>
            <div class="card-body">
                @foreach($assignments->groupBy('subject') as $subject => $subjectAssignments)
                    <div class="fw-semibold text-primary mb-2"><i class="bi bi-book me-1"></i>{{ $subject ?: '—' }}</div>

                    @foreach($subjectAssignments as $a)
                        @php $total = $studentCounts[$a->department_id] ?? 0; @endphp
                        <div class="border rounded mb-3">
                            <div class="d-flex flex-wrap justify-content-between align-items-center bg-light px-3 py-2">
                                <div>
                                    <span class="fw-semibold">{{ $a->assignment_no ? 'Assignment '.$a->assignment_no.' — ' : '' }}{{ $a->title }}</span>
                                    <span class="badge bg-light text-dark border ms-1">{{ $a->type }}</span>
                                    <span class="text-muted small ms-2">Due {{ $a->due_date?->format('d M Y') ?? '—' }}</span>
                                </div>
                                <div>
                                    <span class="badge bg-primary">Submitted {{ $a->submissions_count }} / {{ $total }}</span>
                                    <span class="badge bg-success">Checked {{ $a->checked_count }}</span>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr><th style="width:5%">#</th><th>Student Name</th><th>Roll No</th><th>Submitted On</th><th class="text-center">Status</th></tr>
                                    </thead>
                                    <tbody>
                                        @forelse($a->submissions as $s)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $s->student->user->name ?? '—' }}</td>
                                                <td>{{ $s->student->roll_no ?? '—' }}</td>
                                                <td>
                                                    {{ $s->submitted_at?->format('d M Y, H:i') ?? '—' }}
                                                    @if($s->isLate())<span class="badge bg-warning text-dark ms-1">Late</span>@endif
                                                </td>
                                                <td class="text-center">
                                                    @if($s->isChecked())
                                                        <span class="badge bg-success">Checked</span>
                                                    @else
                                                        <span class="badge bg-secondary">Submitted</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center text-muted py-2">No students have submitted yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>
    @empty
        <div class="alert alert-info">No assignments found.</div>
    @endforelse
</div>

{{-- ============ PRINT ONLY: simple flat table, no signatures ============ --}}
<div class="print-only">
    <div class="print-title mb-2 text-center">
        <h4 class="mb-0">{{ config('college.name') }}</h4>
        <div class="small text-muted">Assignment Distribution · Printed on {{ now()->format('d M Y') }}</div>
    </div>
    <table class="table table-sm table-bordered">
        <thead>
            <tr><th>Faculty</th><th>Subject</th><th>Asg #</th><th>Title</th><th>Student Name</th><th>Roll No</th><th>Submitted On</th><th>Status</th></tr>
        </thead>
        <tbody>
            @foreach($byFaculty as $facultyName => $assignments)
                @foreach($assignments as $a)
                    @forelse($a->submissions as $s)
                        <tr>
                            <td>{{ $facultyName }}</td>
                            <td>{{ $a->subject ?: '—' }}</td>
                            <td>{{ $a->assignment_no ?? '—' }}</td>
                            <td>{{ $a->title }}</td>
                            <td>{{ $s->student->user->name ?? '—' }}</td>
                            <td>{{ $s->student->roll_no ?? '—' }}</td>
                            <td>{{ $s->submitted_at?->format('d M Y, H:i') ?? '—' }}{{ $s->isLate() ? ' (Late)' : '' }}</td>
                            <td>{{ ucfirst($s->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td>{{ $facultyName }}</td>
                            <td>{{ $a->subject ?: '—' }}</td>
                            <td>{{ $a->assignment_no ?? '—' }}</td>
                            <td>{{ $a->title }}</td>
                            <td colspan="4" class="text-muted">No submissions yet</td>
                        </tr>
                    @endforelse
                @endforeach
            @endforeach
        </tbody>
    </table>
</div>
@endsection
