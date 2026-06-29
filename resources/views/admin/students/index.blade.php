@extends('layouts.app')
@section('title', 'Students')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Students</h3>
    <a href="{{ route('admin.students.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Student</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Roll No</th><th>Name</th><th>Email</th><th>Department</th><th>Project</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
            @forelse($students as $student)
                <tr>
                    <td><span class="badge bg-secondary">{{ $student->roll_no }}</span></td>
                    <td>{{ $student->user->name }}</td>
                    <td>{{ $student->user->email }}</td>
                    <td>{{ $student->department?->name ?? '—' }}</td>
                    <td>
                        @if($student->membership)
                            <span class="badge bg-info text-dark">{{ $student->membership->project->name }}</span>
                        @else
                            <span class="text-muted small">No project</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form action="{{ route('admin.students.destroy', $student) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this student and their account?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No students yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $students->links() }}</div>
@endsection
