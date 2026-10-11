@extends('layouts.app')
@section('title', 'Account Approvals')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="mb-0"><i class="bi bi-person-check me-2"></i>Account Approvals</h3>
        <p class="text-muted small mb-0">Self-registered students cannot sign in until approved here.</p>
    </div>
    @if($pending->total())
        <span class="badge bg-warning text-dark fs-6">{{ $pending->total() }} waiting</span>
    @endif
</div>

@include('partials.alerts')

<form class="card card-body mb-3" method="GET" data-live-search>
    <div class="row g-2 align-items-end">
        <div class="col-md-6">
            <label class="form-label small mb-1">Search</label>
            <div class="position-relative">
                <i class="bi bi-search position-absolute top-50 translate-middle-y ms-2 text-muted"></i>
                <input name="search" value="{{ request('search') }}" class="form-control ps-4"
                       placeholder="Name, email or roll no…" autocomplete="off">
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label small mb-1">Department</label>
            <select name="department_id" class="form-select">
                <option value="">All departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2 align-items-center">
            <span class="text-muted small" data-live-status></span>
            @if(request()->hasAny(['search', 'department_id']))
                <a href="{{ route('admin.approvals.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            @endif
        </div>
    </div>
    <noscript><button class="btn btn-primary btn-sm mt-2">Filter</button></noscript>
</form>

<div data-bulk-scope>

    <div data-bulk-bar class="card card-body mb-2 d-none flex-row align-items-center flex-wrap gap-2 border-primary no-print">
        <span class="fw-semibold text-primary"><span data-bulk-count>0</span> selected</span>
        <div class="ms-auto d-flex gap-2">
            <button type="button" class="btn btn-success btn-sm"
                    data-bulk-submit
                    data-bulk-form="bulkApproveForm"
                    data-bulk-field="user_ids[]"
                    data-bulk-confirm="Approve {n} account(s)? They will be able to sign in immediately.">
                <i class="bi bi-check2-circle"></i> Approve selected
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" data-bulk-clear>Clear</button>
        </div>
    </div>

    <form id="bulkApproveForm" method="POST" action="{{ route('admin.approvals.bulk') }}" class="d-none">
        @csrf
        <div data-bulk-ids></div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:36px;"><input class="form-check-input" type="checkbox" data-bulk-all title="Select all on this page"></th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Roll No</th>
                        <th>Department</th>
                        <th>Requested</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($pending as $user)
                    <tr>
                        <td><input class="form-check-input" type="checkbox" data-bulk-item value="{{ $user->id }}"></td>
                        <td class="fw-semibold">{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td><span class="badge bg-secondary">{{ $user->student?->roll_no ?? '—' }}</span></td>
                        <td>{{ $user->student?->department?->name ?? '—' }}</td>
                        <td class="text-nowrap small text-muted">
                            {{ $user->created_at?->format('d M Y') }}
                            <div>{{ $user->created_at?->diffForHumans() }}</div>
                        </td>
                        <td class="text-end">
                            <div class="row-actions justify-content-end">
                                <form method="POST" action="{{ route('admin.approvals.approve', $user) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button class="act-btn act-ok" title="Approve" aria-label="Approve account">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                                <button class="act-btn act-danger" title="Reject"
                                        data-bs-toggle="modal" data-bs-target="#reject{{ $user->id }}">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-check2-circle fs-1 d-block mb-2 text-success"></i>
                            <span class="fw-semibold d-block">Nothing waiting</span>
                            <span class="small">Every registration has been dealt with.</span>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">{{ $pending->links() }}</div>

@if($recent->isNotEmpty())
    <div class="card mt-4">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-clock-history me-1"></i> Recent decisions
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>Name</th><th>Outcome</th><th>Reason</th><th>By</th><th>When</th></tr>
                </thead>
                <tbody>
                @foreach($recent as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>
                            @if($user->isBlocked())
                                <span class="badge bg-danger">Rejected</span>
                            @else
                                <span class="badge bg-success">Approved</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $user->status_reason ?? '—' }}</td>
                        <td class="small">{{ $user->approver?->name ?? '—' }}</td>
                        <td class="small text-muted text-nowrap">{{ $user->approved_at?->diffForHumans() }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@foreach($pending as $user)
    <div class="modal fade" id="reject{{ $user->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="POST" action="{{ route('admin.approvals.reject', $user) }}">
                @csrf @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-x-circle me-1"></i> Reject {{ $user->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <textarea name="reason" rows="3" class="form-control" required
                                  placeholder="e.g. Roll number does not match our records"></textarea>
                        <div class="form-text">The student is told this reason by email and in-app.</div>
                    </div>
                    <p class="small text-muted mb-0">
                        The account is blocked rather than deleted, so the roll number and email stay
                        claimed and the decision stays on record.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-x-lg me-1"></i>Reject</button>
                </div>
            </form>
        </div>
    </div>
@endforeach
@endsection
