@extends('layouts.app')
@section('title', 'Faculty')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Faculty</h3>
    <a href="{{ route('admin.faculties.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Faculty</a>
</div>

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
                    <td>{{ $faculty->department?->name ?? '—' }}</td>
                    <td><span class="badge bg-info text-dark">{{ $faculty->assignments_count }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('admin.faculties.edit', $faculty) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form action="{{ route('admin.faculties.destroy', $faculty) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this faculty and their account?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
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
