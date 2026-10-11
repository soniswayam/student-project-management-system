@extends('layouts.app')
@section('title', 'Academic Years')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Academic Years</h3>
        <p class="text-muted small mb-0">New projects and assignments are filed under the current session.</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addYear">
        <i class="bi bi-plus-lg"></i> Add Year
    </button>
</div>

@include('partials.alerts')

@if($years->total() && ! $years->contains(fn($y) => $y->is_current))
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-1"></i>
        No session is marked <strong>current</strong>. New projects and assignments will not be filed
        under any year until one is set.
    </div>
@endif

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Session</th>
                    <th>Runs</th>
                    <th>State</th>
                    <th class="text-center">Projects</th>
                    <th class="text-center">Assignments</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($years as $year)
                <tr class="{{ $year->isArchived() ? 'opacity-75' : '' }}">
                    <td class="fw-semibold">{{ $year->label }}</td>
                    <td class="text-muted small">
                        @if($year->starts_on || $year->ends_on)
                            {{ $year->starts_on?->format('d M Y') ?? '—' }} → {{ $year->ends_on?->format('d M Y') ?? '—' }}
                        @else
                            <span class="text-muted">Not set</span>
                        @endif
                    </td>
                    <td>
                        @if($year->is_current)
                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Current</span>
                        @elseif($year->isArchived())
                            <span class="badge bg-secondary"><i class="bi bi-archive me-1"></i>Archived</span>
                            <div class="text-muted small mt-1">{{ $year->archived_at->format('d M Y') }}</div>
                        @else
                            <span class="badge bg-info">Open</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $year->projects_count }}</td>
                    <td class="text-center">{{ $year->assignments_count }}</td>
                    <td class="text-end">
                        <div class="row-actions justify-content-end">
                            <button class="act-btn act-edit" data-bs-toggle="modal" data-bs-target="#editYear{{ $year->id }}" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>

                            <div class="dropdown">
                                <button class="act-btn act-menu" data-bs-toggle="dropdown" aria-expanded="false" title="More">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    @unless($year->is_current)
                                        <li>
                                            <form method="POST" action="{{ route('admin.academic-years.current', $year) }}">
                                                @csrf @method('PATCH')
                                                <button class="dropdown-item"><i class="bi bi-star me-2"></i>Make current</button>
                                            </form>
                                        </li>
                                    @endunless

                                    @if($year->isArchived())
                                        <li>
                                            <form method="POST" action="{{ route('admin.academic-years.unarchive', $year) }}">
                                                @csrf @method('PATCH')
                                                <button class="dropdown-item"><i class="bi bi-box-arrow-up me-2"></i>Reopen year</button>
                                            </form>
                                        </li>
                                    @else
                                        <li>
                                            <form method="POST" action="{{ route('admin.academic-years.archive', $year) }}"
                                                  onsubmit="return confirm('Archive {{ $year->label }}? Its projects move to the Archive and leave the active lists. This can be undone.')">
                                                @csrf @method('PATCH')
                                                <button class="dropdown-item" {{ $year->is_current ? 'disabled' : '' }}>
                                                    <i class="bi bi-archive me-2"></i>Archive year
                                                </button>
                                            </form>
                                        </li>
                                    @endif

                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('admin.academic-years.destroy', $year) }}"
                                              onsubmit="return confirm('Delete {{ $year->label }}?')">
                                            @csrf @method('DELETE')
                                            <button class="dropdown-item text-danger"
                                                {{ ($year->projects_count || $year->assignments_count || $year->is_current) ? 'disabled' : '' }}>
                                                <i class="bi bi-trash me-2"></i>Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        No academic years yet. Add one so new work can be filed under it.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $years->links() }}</div>

<div class="modal fade" id="addYear" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="{{ route('admin.academic-years.store') }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-1"></i> Add Academic Year</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Session label</label>
                    <input name="label" class="form-control" placeholder="e.g. 2026-27" required>
                    <div class="form-text">Shown on projects, reports and the archive.</div>
                </div>
                <div class="row g-2">
                    <div class="col-6 mb-3">
                        <label class="form-label">Starts on <span class="text-muted fw-normal">(optional)</span></label>
                        <input name="starts_on" type="date" class="form-control">
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Ends on <span class="text-muted fw-normal">(optional)</span></label>
                        <input name="ends_on" type="date" class="form-control">
                    </div>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="make_current" value="1" id="makeCurrentNew">
                    <label class="form-check-label" for="makeCurrentNew">
                        Make this the current session
                    </label>
                    <div class="form-text">New projects and assignments will be filed under it.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Year</button>
            </div>
        </form>
    </div>
</div>

@foreach($years as $year)
    <div class="modal fade" id="editYear{{ $year->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="POST" action="{{ route('admin.academic-years.update', $year) }}">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-1"></i> Edit {{ $year->label }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Session label</label>
                        <input name="label" value="{{ $year->label }}" class="form-control" required>
                    </div>
                    <div class="row g-2">
                        <div class="col-6 mb-1">
                            <label class="form-label">Starts on</label>
                            <input name="starts_on" type="date" class="form-control" value="{{ $year->starts_on?->format('Y-m-d') }}">
                        </div>
                        <div class="col-6 mb-1">
                            <label class="form-label">Ends on</label>
                            <input name="ends_on" type="date" class="form-control" value="{{ $year->ends_on?->format('Y-m-d') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endforeach
@endsection
