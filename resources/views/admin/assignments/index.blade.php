@extends('layouts.app')
@section('title', 'Assignments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-journal-text me-2"></i>Assignments</h3>
    <div class="d-flex gap-2 no-print">
        @can('assignments.manage')
            <a href="{{ route('admin.assignments.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Assignment</a>
        @endcan
        <a href="{{ route('admin.assignments.distribution') }}" class="btn btn-outline-primary"><i class="bi bi-diagram-3"></i> Distribution</a>
        <a href="{{ route('admin.assignments.exportPdf', request()->query()) }}" class="btn btn-danger"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
        <a href="{{ route('admin.assignments.export', request()->query()) }}" class="btn btn-success"><i class="bi bi-file-earmark-excel"></i> Excel</a>
        <button onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer"></i> Print</button>
    </div>
</div>

@include('partials.print_letterhead', ['docTitle' => 'Assignments Report'])

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

@if($assignments->isEmpty())
    <div class="alert alert-info">No assignments found.</div>
@else
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Department</th>
                        <th>Faculty</th>
                        <th>Due date</th>
                        <th class="text-center">Submissions</th>
                        <th class="text-center">Checked</th>
                        <th class="text-end no-print">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assignments as $assignment)
                        <tr>
                            <td>
                                <div>{{ $assignment->title }}</div>
                                <small class="text-muted">{{ $assignment->subject }}@if($assignment->assignment_no) · Asg {{ $assignment->assignment_no }} @endif · {{ $assignment->type }}</small>
                            </td>
                            <td>{{ $assignment->department->name }}</td>
                            <td>{{ $assignment->faculty->user->name ?? '—' }}</td>
                            <td>{{ $assignment->due_date?->format('d M Y, H:i') ?? '—' }}</td>
                            <td class="text-center"><span class="badge bg-primary">{{ $assignment->submissions_count }}</span></td>
                            <td class="text-center"><span class="badge bg-success">{{ $assignment->checked_count }}</span></td>
                            <td class="text-end no-print">
                                <a href="{{ route('admin.assignments.show', $assignment) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> View</a>
                                @can('assignments.manage')
                                    <a href="{{ route('admin.assignments.edit', $assignment) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                    <form method="POST" action="{{ route('admin.assignments.destroy', $assignment) }}" class="d-inline"
                                          onsubmit="return confirm('Delete this assignment and all its submissions?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3 no-print">{{ $assignments->links() }}</div>
@endif

@include('partials.print_signatures')
@endsection
