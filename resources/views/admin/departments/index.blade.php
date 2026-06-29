@extends('layouts.app')
@section('title', 'Departments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Departments</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDept">
        <i class="bi bi-plus-lg"></i> Add Department
    </button>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>#</th><th>Name</th><th>Code</th><th>Students</th><th>Faculty</th><th>Projects</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
            @forelse($departments as $dept)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $dept->name }}</td>
                    <td><span class="badge bg-secondary">{{ $dept->code }}</span></td>
                    <td>{{ $dept->students_count }}</td>
                    <td>{{ $dept->faculties_count }}</td>
                    <td>{{ $dept->projects_count }}</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editDept{{ $dept->id }}"><i class="bi bi-pencil"></i></button>
                        <form action="{{ route('admin.departments.destroy', $dept) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this department?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>

                {{-- Edit modal --}}
                <div class="modal fade" id="editDept{{ $dept->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <form class="modal-content" method="POST" action="{{ route('admin.departments.update', $dept) }}">
                            @csrf @method('PUT')
                            <div class="modal-header"><h5 class="modal-title">Edit Department</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body">
                                <div class="mb-3"><label class="form-label">Name</label><input name="name" value="{{ $dept->name }}" class="form-control" required></div>
                                <div class="mb-3"><label class="form-label">Code</label><input name="code" value="{{ $dept->code }}" class="form-control" required></div>
                            </div>
                            <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save</button></div>
                        </form>
                    </div>
                </div>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No departments yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $departments->links() }}</div>

{{-- Add modal --}}
<div class="modal fade" id="addDept" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="{{ route('admin.departments.store') }}">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Add Department</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" placeholder="Computer Science" required></div>
                <div class="mb-3"><label class="form-label">Code</label><input name="code" class="form-control" placeholder="CSE" required></div>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Add</button></div>
        </form>
    </div>
</div>
@endsection
