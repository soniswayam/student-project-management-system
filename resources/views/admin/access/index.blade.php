@extends('layouts.app')
@section('title', 'Staff & Roles')

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <h3 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Staff &amp; Roles</h3>
</div>
<p class="text-muted mb-3">Manage staff accounts and the roles that control what each of them can do.</p>

<ul class="nav nav-tabs mb-3" role="tablist">
    @if($canStaff)
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'staff' ? 'active' : '' }}" id="staff-tab"
                    data-bs-toggle="tab" data-bs-target="#staff-pane" type="button" role="tab">
                <i class="bi bi-people me-1"></i> Staff
                @if($admins) <span class="badge rounded-pill bg-secondary ms-1">{{ $admins->total() }}</span> @endif
            </button>
        </li>
    @endif
    @if($canRoles)
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'roles' ? 'active' : '' }}" id="roles-tab"
                    data-bs-toggle="tab" data-bs-target="#roles-pane" type="button" role="tab">
                <i class="bi bi-diagram-3 me-1"></i> Roles
                @if($roles) <span class="badge rounded-pill bg-secondary ms-1">{{ $roles->total() }}</span> @endif
            </button>
        </li>
    @endif
</ul>

<div class="tab-content">
    {{-- Staff --}}
    @if($canStaff)
        <div class="tab-pane fade {{ $activeTab === 'staff' ? 'show active' : '' }}" id="staff-pane" role="tabpanel" aria-labelledby="staff-tab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 text-muted">Staff accounts</h5>
                <a href="{{ route('admin.admins.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Staff</a>
            </div>
            @include('admin.admins._table', ['admins' => $admins, 'roleLabels' => $roleLabels])
        </div>
    @endif

    {{-- Roles --}}
    @if($canRoles)
        <div class="tab-pane fade {{ $activeTab === 'roles' ? 'show active' : '' }}" id="roles-pane" role="tabpanel" aria-labelledby="roles-tab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 text-muted">Roles &amp; permissions</h5>
                <a href="{{ route('admin.roles.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Role</a>
            </div>
            @include('admin.roles._table', ['roles' => $roles, 'userCounts' => $userCounts])
        </div>
    @endif
</div>
@endsection
