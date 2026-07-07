@extends('layouts.app')
@section('title', 'Manage Admins')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Manage Staff Accounts</h3>
    <a href="{{ route('admin.admins.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Staff</a>
</div>

@include('admin.admins._table', ['admins' => $admins, 'roleLabels' => $roles])
@endsection
