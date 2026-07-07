@extends('layouts.app')
@section('title', 'Assignments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-journal-text me-2"></i>My Assignments</h3>
    <a href="{{ route('faculty.assignments.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Assignment
    </a>
</div>

@if($assignments->isEmpty())
    <div class="alert alert-info">You have not created any assignments yet.</div>
@else
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Department</th>
                        <th>Due date</th>
                        <th class="text-center">Submissions</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assignments as $assignment)
                        <tr>
                            <td>
                                <div>{{ $assignment->title }}</div>
                                <small class="text-muted">
                                    {{ $assignment->subject }}
                                    @if($assignment->assignment_no) · Assignment {{ $assignment->assignment_no }} @endif
                                    <span class="badge bg-light text-dark border">{{ $assignment->type }}</span>
                                </small>
                            </td>
                            <td>{{ $assignment->department->name }}</td>
                            <td>
                                @if($assignment->due_date)
                                    {{ $assignment->due_date->format('d M Y, H:i') }}
                                    @if($assignment->isPastDue())
                                        <span class="badge bg-secondary ms-1">Past due</span>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary">{{ $assignment->submissions_count }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('faculty.assignments.show', $assignment) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                <form method="POST" action="{{ route('faculty.assignments.destroy', $assignment) }}" class="d-inline"
                                      onsubmit="return confirm('Delete this assignment and all its submissions?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
