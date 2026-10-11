@extends('layouts.app')
@section('title', 'Assignments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-journal-text me-2"></i>My Assignments</h3>
    <a href="{{ route('faculty.assignments.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Assignment
    </a>
</div>

@if(isset($picker))
    @include('partials.class_picker', [
        'action' => route('faculty.assignments.index'),
        'allUrl' => route('faculty.assignments.index', ['all' => 1]),
        'noun' => 'assignments',
    ])
@else
<form method="GET" class="row g-2 mb-3 align-items-center">
    <div class="col-auto">
        <select name="department_id" class="form-select" onchange="this.form.submit()">
            <option value="">All departments</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>{{ $dept->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <select name="semester" class="form-select" onchange="this.form.submit()">
            <option value="">All semesters</option>
            <option value="common" @selected(request('semester') === 'common')>Common to all semesters</option>
            @foreach($semesters as $sem)
                <option value="{{ $sem }}" @selected(request('semester') == $sem)>Sem {{ $sem }}</option>
            @endforeach
        </select>
    </div>
    <noscript><div class="col-auto"><button class="btn btn-primary">Filter</button></div></noscript>
    <div class="col-auto">
        <a href="{{ route('faculty.assignments.index') }}" class="small"><i class="bi bi-arrow-left-right me-1"></i>Change class</a>
    </div>
</form>

@if($assignments->isEmpty())
    <div class="alert alert-info">{{ (request()->filled('department_id') || request()->filled('semester')) ? 'No assignments match these filters.' : 'You have not created any assignments yet.' }}</div>
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
                    @foreach($assignments->groupBy(fn ($a) => $a->groupLabel()) as $groupLabel => $group)
                        @include('partials.group_row', ['label' => $groupLabel, 'count' => $group->count(), 'colspan' => 5])
                        @foreach($group as $assignment)
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
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endif
@endsection
