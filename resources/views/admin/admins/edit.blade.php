@extends('layouts.app')
@section('title', 'Edit Admin')

@section('content')
<h3 class="mb-3"><i class="bi bi-shield-check me-2"></i>Edit Admin</h3>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.admins.update', $admin) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Full Name</label><input name="name" value="{{ old('name', $admin->name) }}" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" value="{{ old('email', $admin->email) }}" class="form-control" required></div>
                <div class="col-md-6">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select" required @disabled($admin->id === auth()->id())>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" @selected(old('role', $admin->role) === $role->name)>{{ $role->label }}</option>
                        @endforeach
                    </select>
                    @if($admin->id === auth()->id())
                        <input type="hidden" name="role" value="{{ $admin->role }}">
                        <small class="text-muted">You cannot change your own role.</small>
                    @endif
                </div>
                <div class="col-md-3"><label class="form-label">New Password</label><input type="password" name="password" class="form-control" placeholder="Leave blank to keep"></div>
                <div class="col-md-3"><label class="form-label">Confirm</label><input type="password" name="password_confirmation" class="form-control"></div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
                <a href="{{ route('admin.access.index', ['tab' => 'staff']) }}" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
