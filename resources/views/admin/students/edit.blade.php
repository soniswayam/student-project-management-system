@extends('layouts.app')
@section('title', 'Edit Student')

@section('content')
<h3 class="mb-3"><i class="bi bi-pencil-square me-2"></i>Edit Student</h3>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.students.update', $student) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Full Name</label><input name="name" value="{{ old('name', $student->user->name) }}" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Roll No</label><input name="roll_no" value="{{ old('roll_no', $student->roll_no) }}" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" value="{{ old('email', $student->user->email) }}" class="form-control" required></div>
                <div class="col-md-6">
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-select" required>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(old('department_id', $student->department_id) == $dept->id)>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Semester</label>
                    <select name="semester" class="form-select" required>
                        <option value="">—</option>
                        @foreach(range(1, 8) as $s)
                            <option value="{{ $s }}" @selected(old('semester', $student->semester) == $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        @foreach(['active' => 'Active', 'pending' => 'Pending', 'blocked' => 'Blocked'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('status', $student->user->status) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Phone</label><input name="phone" value="{{ old('phone', $student->phone) }}" class="form-control"></div>
                <div class="col-12"><hr><p class="text-muted small mb-2">Leave password blank to keep it unchanged.</p></div>
                <div class="col-md-3"><label class="form-label">New Password</label><input type="password" name="password" class="form-control"></div>
                <div class="col-md-3"><label class="form-label">Confirm</label><input type="password" name="password_confirmation" class="form-control"></div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Update Student</button>
                <a href="{{ route('admin.students.index') }}" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
