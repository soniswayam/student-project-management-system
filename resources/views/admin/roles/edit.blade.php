@extends('layouts.app')
@section('title', 'Edit Role')

@section('content')
<h3 class="mb-3"><i class="bi bi-diagram-3 me-2"></i>Edit Role — {{ $role->label }}</h3>
<form method="POST" action="{{ route('admin.roles.update', $role) }}">
    @csrf @method('PUT')
    <div class="card mb-3">
        <div class="card-body">
            <label class="form-label">Role Name</label>
            <input name="label" value="{{ old('label', $role->label) }}" class="form-control" required @disabled($role->is_system)>
            @if($role->is_system)
                <input type="hidden" name="label" value="{{ $role->label }}">
                <div class="form-text">System role name cannot be changed; you may still adjust its access list.</div>
            @endif
        </div>
    </div>

    <h6 class="text-muted mb-2">Access list — tick what this role can do</h6>
    @include('admin.roles._permissions', ['selected' => old('permissions', $role->permissions ?? [])])

    <div class="mt-4">
        <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
        <a href="{{ route('admin.access.index', ['tab' => 'roles']) }}" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Cancel</a>
    </div>
</form>
@endsection
