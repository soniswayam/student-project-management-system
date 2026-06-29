@extends('layouts.app')
@section('title', 'Edit Faculty')

@section('content')
<h3 class="mb-3">Edit Faculty</h3>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.faculties.update', $faculty) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Full Name</label><input name="name" value="{{ old('name', $faculty->user->name) }}" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" value="{{ old('email', $faculty->user->email) }}" class="form-control" required></div>
                <div class="col-md-6">
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-select" required>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(old('department_id', $faculty->department_id) == $dept->id)>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Designation</label><input name="designation" value="{{ old('designation', $faculty->designation) }}" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Phone</label><input name="phone" value="{{ old('phone', $faculty->phone) }}" class="form-control"></div>
                <div class="col-12"><hr><p class="text-muted small mb-2">Leave password blank to keep it unchanged.</p></div>
                <div class="col-md-3"><label class="form-label">New Password</label><input type="password" name="password" class="form-control"></div>
                <div class="col-md-3"><label class="form-label">Confirm</label><input type="password" name="password_confirmation" class="form-control"></div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary">Update Faculty</button>
                <a href="{{ route('admin.faculties.index') }}" class="btn btn-link">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
