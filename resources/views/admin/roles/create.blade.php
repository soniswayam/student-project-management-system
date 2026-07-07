@extends('layouts.app')
@section('title', 'Add Role')

@section('content')
<h3 class="mb-3"><i class="bi bi-diagram-3 me-2"></i>Add Role</h3>
<form method="POST" action="{{ route('admin.roles.store') }}">
    @csrf
    <div class="card mb-3">
        <div class="card-body">
            <label class="form-label">Role Name</label>
            <input name="label" value="{{ old('label') }}" class="form-control" placeholder="e.g. Coordinator" required>
            <div class="form-text">A slug (e.g. <code>coordinator</code>) is generated automatically.</div>
        </div>
    </div>

    <h6 class="text-muted mb-2">Access list — tick what this role can do</h6>
    @include('admin.roles._permissions', ['selected' => old('permissions', [])])

    <div class="mt-4">
        <button class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Create Role</button>
        <a href="{{ route('admin.access.index', ['tab' => 'roles']) }}" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Cancel</a>
    </div>
</form>
@endsection
