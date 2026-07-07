@extends('layouts.app')
@section('title', 'Add Faculty')

@section('content')
<h3 class="mb-3"><i class="bi bi-person-plus me-2"></i>Add Faculty</h3>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.faculties.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Full Name</label><input name="name" value="{{ old('name') }}" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" value="{{ old('email') }}" class="form-control" required></div>
                <div class="col-12">
                    <label class="form-label">Departments / Courses <span class="text-muted small">(select all this faculty teaches)</span></label>
                    <div class="dept-chips @error('department_ids') dept-chips--error @enderror">
                        @foreach($departments as $dept)
                            <input type="checkbox" class="dept-chip-input" name="department_ids[]"
                                   value="{{ $dept->id }}" id="dept{{ $dept->id }}"
                                   @checked(in_array($dept->id, old('department_ids', [])))>
                            <label class="dept-chip" for="dept{{ $dept->id }}">
                                <svg class="dept-chip__tick" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M13 4 6 12 3 9" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <span>{{ $dept->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('department_ids') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6"><label class="form-label">Designation</label><input name="designation" value="{{ old('designation') }}" class="form-control" placeholder="Assistant Professor"></div>
                <div class="col-md-6"><label class="form-label">Phone</label><input name="phone" value="{{ old('phone') }}" class="form-control"></div>
                <div class="col-md-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
                <div class="col-md-3"><label class="form-label">Confirm</label><input type="password" name="password_confirmation" class="form-control" required></div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>Create Faculty</button>
                <a href="{{ route('admin.faculties.index') }}" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
