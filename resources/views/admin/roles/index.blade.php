@extends('layouts.app')
@section('title', 'Manage Roles')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Roles &amp; Permissions</h3>
    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Role</a>
</div>

@include('admin.roles._table', ['roles' => $roles, 'userCounts' => $userCounts])
@endsection
