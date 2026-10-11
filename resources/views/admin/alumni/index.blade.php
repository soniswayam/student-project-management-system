@extends('layouts.app')
@section('title', 'Alumni')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h3 class="mb-0"><i class="bi bi-mortarboard me-2"></i>Alumni</h3>
        <p class="text-muted small mb-0">Students who have finished their course.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if($totalAlumni > 0)
            <a href="{{ route('admin.alumni.export', request()->query()) }}" class="btn btn-success">
                <i class="bi bi-file-earmark-excel"></i> Export
            </a>
        @endif
        <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-people"></i> Students
        </a>
    </div>
</div>

@include('partials.alerts')

@if($totalAlumni === 0)
    <div class="card card-body text-center text-muted py-5">
        <i class="bi bi-mortarboard fs-1 mb-2 d-block"></i>
        <p class="mb-1 fw-semibold">No alumni yet</p>
        <p class="mb-3 small">
            Students appear here once you move them across. Select students in their final semester
            on the Students list and use <strong>Move to Alumni</strong>.
        </p>
        @can('students.view')
            <div><a href="{{ route('admin.students.index') }}" class="btn btn-primary btn-sm">Go to Students</a></div>
        @endcan
    </div>
@else

<form class="card card-body mb-3" method="GET" data-live-search>
    <div class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label small mb-1">Search</label>
            <div class="position-relative">
                <i class="bi bi-search position-absolute top-50 translate-middle-y ms-2 text-muted"></i>
                <input name="search" value="{{ request('search') }}" class="form-control ps-4"
                       placeholder="Name, email or roll no…" autocomplete="off">
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Batch</label>
            <select name="batch" class="form-select">
                <option value="">All batches</option>
                @foreach($batches as $batch)
                    <option value="{{ $batch->id }}" @selected(request('batch') == $batch->id)>{{ $batch->label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Course</label>
            <select name="department_id" class="form-select">
                <option value="">All courses</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">Level</label>
            <select name="level" class="form-select">
                <option value="">All</option>
                <option value="graduation" @selected(request('level') === 'graduation')>Graduation</option>
                <option value="post_graduation" @selected(request('level') === 'post_graduation')>Post-Graduation</option>
            </select>
        </div>
    </div>
    <div class="d-flex gap-2 align-items-center mt-2">
        <span class="text-muted small" data-live-status></span>
        <span class="text-muted small">Showing {{ $alumni->total() }} of {{ $totalAlumni }} alumni</span>
        @if(request()->hasAny(['search', 'batch', 'department_id', 'level']))
            <a href="{{ route('admin.alumni.index') }}" class="btn btn-outline-secondary btn-sm ms-auto">Reset</a>
        @endif
    </div>
    <noscript><button class="btn btn-primary btn-sm mt-2">Filter</button></noscript>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Roll No</th>
                    <th>Name</th>
                    <th>Course</th>
                    <th>Batch</th>
                    <th>Passed Out</th>
                    <th>Project</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($alumni as $student)
                @php $project = $student->membership?->project; @endphp
                <tr>
                    <td><span class="badge bg-secondary">{{ $student->roll_no }}</span></td>
                    <td>
                        {{ $student->user?->name ?? '—' }}
                        <div class="text-muted small">{{ $student->user?->email }}</div>
                    </td>
                    <td>
                        {{ $student->department?->name ?? '—' }}
                        @if($student->department)
                            <span class="badge bg-{{ $student->department->levelColor() }} ms-1">
                                {{ $student->department->levelShort() }}
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($student->batchLabel())
                            <span class="badge bg-primary">{{ $student->batchLabel() }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-nowrap small text-muted">
                        {{ $student->passed_out_at?->format('d M Y') ?? '—' }}
                    </td>
                    <td>
                        @if($project)
                            {{ $project->name }}
                            <div>
                                <span class="badge bg-{{ $project->statusColor() }}">{{ $project->status }}</span>

                                @if($project->marksLabel())
                                    <span class="badge bg-success ms-1">{{ $project->marksLabel() }}</span>
                                @endif
                            </div>
                        @else
                            <span class="badge bg-light text-dark border">No project</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="row-actions justify-content-end">
                            @can('students.view')
                                <a href="{{ route('admin.students.show', $student) }}" class="act-btn act-view" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                            @endcan
                            @can('alumni.manage')
                                <form method="POST" action="{{ route('admin.alumni.restore', $student) }}"
                                      onsubmit="return confirm('Put {{ $student->user?->name }} back to studying?')">
                                    @csrf @method('PATCH')
                                    <button class="act-btn act-warn" title="Restore to studying">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No alumni match those filters.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $alumni->links() }}</div>

<p class="text-muted small mt-2">
    <i class="bi bi-info-circle me-1"></i>
    Alumni keep read-only access — they can still sign in to view their project and download their
    certificate, but cannot submit anything new. Restoring puts them back to studying.
</p>
@endif
@endsection
