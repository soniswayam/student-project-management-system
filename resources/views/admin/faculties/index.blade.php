@extends('layouts.app')
@section('title', 'Faculty')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-person-badge me-2"></i>Faculty</h3>
    @can('faculty.manage')
        <a href="{{ route('admin.faculties.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Faculty</a>
    @endcan
</div>

<form method="GET" class="card card-body mb-3" data-live-search>
    <div class="row g-2 align-items-end">
        <div class="col-md-6">
            <label class="form-label small text-muted mb-1">Search</label>
            <div class="position-relative">
                <i class="bi bi-search position-absolute top-50 translate-middle-y ms-2 text-muted"></i>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control ps-4"
                       placeholder="Start typing name or email…" autocomplete="off" autofocus>
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label small text-muted mb-1">Department</label>
            <select name="department_id" class="form-select">
                <option value="">All departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-grid gap-2 d-md-flex align-items-center">
            <span class="text-muted small" data-live-status></span>
            @if(request()->hasAny(['search', 'department_id']))
                <a href="{{ route('admin.faculties.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
            @endif
        </div>
    </div>
    <noscript><button class="btn btn-primary btn-sm mt-2"><i class="bi bi-search"></i> Filter</button></noscript>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Name</th><th>Email</th><th>Designation</th><th>Department</th><th>Assigned Projects</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
            @forelse($faculties as $faculty)
                <tr>
                    <td>{{ $faculty->user->name }}</td>
                    <td>{{ $faculty->user->email }}</td>
                    <td>{{ $faculty->designation ?? '—' }}</td>
                    <td>
                        @forelse($faculty->departments as $d)
                            <span class="badge bg-light text-dark border">{{ $d->name }}</span>
                        @empty
                            <span class="text-muted">—</span>
                        @endforelse
                    </td>
                    <td><span class="badge bg-info text-dark">{{ $faculty->assignments_count }}</span></td>
                    <td class="text-end">
                        @can('faculty.manage')
                            <a href="{{ route('admin.faculties.edit', $faculty) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.faculties.destroy', $faculty) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this faculty and their account?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        @else
                            <span class="text-muted small">View only</span>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No faculty yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $faculties->links() }}</div>
@endsection
