@extends('layouts.app')
@section('title', 'Departments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-building me-2"></i>Departments / Courses</h3>
    @can('departments.manage')
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDept">
            <i class="bi bi-plus-lg"></i> Add Department
        </button>
    @endcan
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>#</th><th>Name</th><th>Code</th><th>Level</th><th>Length</th><th>Project Deadline</th><th>Students</th><th>Faculty</th><th>Projects</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
            @forelse($departments as $dept)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="fw-semibold">{{ $dept->name }}</td>
                    <td><span class="badge bg-secondary">{{ $dept->code }}</span></td>
                    <td><span class="badge bg-{{ $dept->levelColor() }}">{{ $dept->levelLabel() }}</span></td>
                    <td>
                        @if($dept->duration_years)
                            {{ $dept->duration_years }} yr
                            <div class="text-muted small">{{ $dept->finalSemester() }} semesters</div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($dept->submission_deadline)
                            <span class="{{ $dept->submission_deadline->isPast() ? 'text-danger' : '' }}">
                                {{ $dept->submission_deadline->format('d M Y, H:i') }}
                            </span>
                        @else
                            <span class="text-muted">Not set</span>
                        @endif
                    </td>
                    <td>{{ $dept->students_count }}</td>
                    <td>{{ $dept->faculties_count }}</td>
                    <td>{{ $dept->projects_count }}</td>
                    <td class="text-end">
                        @can('departments.manage')
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editDept{{ $dept->id }}"><i class="bi bi-pencil"></i></button>
                            <form action="{{ route('admin.departments.destroy', $dept) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this department?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        @else
                            <span class="text-muted small">View only</span>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="text-center text-muted py-4">No departments yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $departments->links() }}</div>

@can('departments.manage')

<div class="modal fade" id="addDept" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="{{ route('admin.departments.store') }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-1"></i> Add Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input name="name" class="form-control" placeholder="e.g. MSC (IT &amp; CA)" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Code</label>
                    <input name="code" class="form-control" placeholder="e.g. MSCITCA" required>
                </div>
                <div class="row g-2">
                    <div class="col-7 mb-3">
                        <label class="form-label">Degree Level</label>
                        <select name="level" class="form-select" required>
                            <option value="graduation">Graduation (UG)</option>
                            <option value="post_graduation">Post-Graduation (PG)</option>
                        </select>
                    </div>
                    <div class="col-5 mb-3">
                        <label class="form-label">Years</label>
                        <input name="duration_years" type="number" min="1" max="7" value="3" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Total Semesters <span class="text-muted fw-normal">(optional)</span></label>
                    <input name="total_semesters" type="number" min="1" max="20" class="form-control" placeholder="Leave blank — years × 2">
                    <div class="form-text">
                        Left blank it is worked out as years × 2. This is the final semester of the
                        course; students are not promoted beyond it.
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Final Project Deadline <span class="text-muted fw-normal">(optional)</span></label>
                    <input name="submission_deadline" type="datetime-local" class="form-control">
                    <div class="form-text">
                        Applies to every project in this course. Leave empty for no deadline.
                        Late uploads are accepted for a grace period after this, then blocked.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Department</button>
            </div>
        </form>
    </div>
</div>

@foreach($departments as $dept)
    <div class="modal fade" id="editDept{{ $dept->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="POST" action="{{ route('admin.departments.update', $dept) }}">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-1"></i> Edit Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input name="name" value="{{ $dept->name }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Code</label>
                        <input name="code" value="{{ $dept->code }}" class="form-control" required>
                    </div>
                    <div class="row g-2">
                        <div class="col-7 mb-3">
                            <label class="form-label">Degree Level</label>
                            <select name="level" class="form-select" required>
                                <option value="graduation" @selected($dept->level === 'graduation')>Graduation (UG)</option>
                                <option value="post_graduation" @selected($dept->level === 'post_graduation')>Post-Graduation (PG)</option>
                            </select>
                        </div>
                        <div class="col-5 mb-3">
                            <label class="form-label">Years</label>
                            <input name="duration_years" type="number" min="1" max="7"
                                   value="{{ $dept->duration_years ?? 3 }}" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Total Semesters <span class="text-muted fw-normal">(optional)</span></label>
                        <input name="total_semesters" type="number" min="1" max="20" value="{{ $dept->total_semesters }}" class="form-control" placeholder="Leave blank — years × 2">
                        <div class="form-text">
                            Left blank it is worked out as years × 2. Students are not promoted beyond it.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Final Project Deadline <span class="text-muted fw-normal">(optional)</span></label>
                        <input name="submission_deadline" type="datetime-local" class="form-control"
                               value="{{ $dept->submission_deadline?->format('Y-m-d\TH:i') }}">
                        <div class="form-text">
                            Applies to every project in this course unless a project has its own extension.
                            Leave empty for no deadline.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endforeach
@endcan
@endsection
