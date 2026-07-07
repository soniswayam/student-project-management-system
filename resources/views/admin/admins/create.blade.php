@extends('layouts.app')
@section('title', 'Add Admin')

@section('content')
    <h3 class="mb-3"><i class="bi bi-shield-plus me-2"></i>Add Admin</h3>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.admins.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Full Name</label><input name="name"
                            value="{{ old('name') }}" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email"
                            value="{{ old('email') }}" class="form-control" required></div>
                    <div class="col-md-6">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" @selected(old('role') === $role->name)>{{ $role->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3"><label class="form-label">Password</label><input type="password" name="password"
                            class="form-control" required></div>
                    <div class="col-md-3"><label class="form-label">Confirm</label><input type="password"
                            name="password_confirmation" class="form-control" required></div>
                </div>
                <div class="mt-4">
                    <button class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>Create Admin</button>
                    <a href="{{ route('admin.access.index', ['tab' => 'staff']) }}" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection