@extends('layouts.app')
@section('title', 'Semester Review')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h3 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Semester Review</h3>
        <p class="text-muted small mb-0">Students sitting beyond the last semester of their course.</p>
    </div>
    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Students
    </a>
</div>

@include('partials.alerts')

@if($students->isEmpty())
    <div class="card card-body text-center text-muted py-5">
        <i class="bi bi-check2-circle fs-1 mb-2 d-block text-success"></i>
        <p class="mb-1 fw-semibold">Nothing to fix</p>
        <p class="mb-0 small">
            Every student is within their course's semester range.
        </p>
    </div>
@else
    <div class="alert alert-warning">
        <div class="fw-semibold mb-1">
            <i class="bi bi-info-circle me-1"></i>
            {{ $students->count() }} student(s) are past their course's final semester
        </div>
        <p class="mb-0 small">
            This happens to records created before each course had a length set — the promote
            action had no ceiling to stop at. Fixing moves a student <strong>back</strong> to their
            final semester; it never moves anyone forward, and nothing changes until you click.
        </p>
    </div>

    <div data-bulk-scope>
        <div data-bulk-bar class="card card-body mb-2 d-none flex-row align-items-center flex-wrap gap-2 border-primary no-print">
            <span class="fw-semibold text-primary"><span data-bulk-count>0</span> selected</span>
            <div class="ms-auto d-flex gap-2">
                <button type="button" class="btn btn-primary btn-sm"
                        data-bulk-submit
                        data-bulk-form="fixSemesterForm"
                        data-bulk-field="student_ids[]"
                        data-bulk-confirm="Move {n} student(s) back to their course's final semester?">
                    <i class="bi bi-arrow-down-circle"></i> Fix selected
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bulk-clear>Clear</button>
            </div>
        </div>

        <form id="fixSemesterForm" method="POST" action="{{ route('admin.students.fix-semesters') }}" class="d-none">
            @csrf @method('PATCH')
            <div data-bulk-ids></div>
        </form>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:36px;"><input class="form-check-input" type="checkbox" data-bulk-all title="Select all"></th>
                            <th>Roll No</th>
                            <th>Name</th>
                            <th>Course</th>
                            <th class="text-center">Now</th>
                            <th class="text-center">Should be</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($students as $student)
                        <tr>
                            <td><input class="form-check-input" type="checkbox" data-bulk-item value="{{ $student->id }}"></td>
                            <td><span class="badge bg-secondary">{{ $student->roll_no }}</span></td>
                            <td>{{ $student->user?->name ?? '—' }}</td>
                            <td>
                                {{ $student->department?->name ?? '—' }}
                                @if($student->department)
                                    <span class="badge bg-{{ $student->department->levelColor() }} ms-1">
                                        {{ $student->department->levelShort() }}
                                    </span>
                                    <div class="text-muted small">
                                        {{ $student->department->duration_years }} yr ·
                                        {{ $student->department->finalSemester() }} semesters
                                    </div>
                                @endif
                            </td>
                            <td class="text-center"><span class="badge bg-danger">Sem {{ $student->semester }}</span></td>
                            <td class="text-center"><span class="badge bg-success">Sem {{ $student->department?->finalSemester() }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <p class="text-muted small mt-2">
        <i class="bi bi-lightbulb me-1"></i>
        If a course length is wrong rather than a student's semester, correct it on the
        @can('departments.view')<a href="{{ route('admin.departments.index') }}">Departments</a>@else Departments @endcan
        screen instead — this list updates automatically.
    </p>
@endif
@endsection
