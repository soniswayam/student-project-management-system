@extends('layouts.app')
@section('title', 'Students')

@php
    $statusMeta = [
        'active' => ['label' => 'Active', 'class' => 'bg-success'],
        'pending' => ['label' => 'Pending', 'class' => 'bg-warning text-dark'],
        'blocked' => ['label' => 'Blocked', 'class' => 'bg-danger'],
    ];
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h3 class="mb-0"><i class="bi bi-people me-2"></i>Students</h3>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.students.index', ['status' => 'pending']) }}"
                class="btn btn-outline-warning position-relative">
                <i class="bi bi-hourglass-split"></i> Pending
                @if($pendingCount > 0)
                    <span
                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $pendingCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.students.export') }}" class="btn btn-success"><i class="bi bi-file-earmark-excel"></i>
                Export</a>
            @can('students.create')
                <a href="{{ route('admin.students.import.form') }}" class="btn btn-outline-primary"><i class="bi bi-upload"></i>
                    Import</a>
                <a href="{{ route('admin.students.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add
                    Student</a>
            @endcan
        </div>
    </div>
    <div class="print-title mb-2">
        <h4>{{ config('college.name') }} — Student List</h4><small>Printed on {{ now()->format('d M Y') }}</small>
    </div>

    <form method="GET" class="card card-body mb-3" data-live-search>
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Search</label>
                <div class="position-relative">
                    <i class="bi bi-search position-absolute top-50 translate-middle-y ms-2 text-muted"></i>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control ps-4"
                        placeholder="Search Name, email or roll number…" autocomplete="off" autofocus>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Department / Course</label>
                <select name="department_id" class="form-select">
                    <option value="">All departments / courses</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>{{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Semester</label>
                <select name="semester" class="form-select">
                    <option value="">All</option>
                    @foreach($semesters as $sem)
                        <option value="{{ $sem }}" @selected(request('semester') == $sem)>{{ $sem }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    @foreach($statusMeta as $key => $meta)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $meta['label'] }}</option>
                    @endforeach
                </select>
            </div>
            @if(request()->hasAny(['search', 'department_id', 'semester', 'status']))
                <div class="col-md-1 d-grid">
                    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary"
                        title="Clear filters" aria-label="Clear filters">Clear</a>
                </div>
            @endif
        </div>
    </form>

    @can('students.edit')
        {{-- Bulk promote bar: appears when one or more students are selected. --}}
        <div id="bulkBar" class="card card-body mb-2 d-none flex-row align-items-center flex-wrap gap-2 border-primary no-print">
            <span class="fw-semibold text-primary"><span id="selCount">0</span> selected</span>
            <span id="semFlow" class="small text-muted"></span>
            <div class="ms-auto d-flex gap-2">
                <button type="button" id="bulkPromoteBtn" class="btn btn-primary btn-sm">
                    <i class="bi bi-arrow-up-circle"></i> Promote selected to next semester
                </button>
                <button type="button" id="clearSelBtn" class="btn btn-outline-secondary btn-sm">Clear</button>
            </div>
        </div>

        {{-- Hidden form that carries the chosen student IDs to the promote route. --}}
        <form id="promoteForm" method="POST" action="{{ route('admin.students.promote-semester') }}" class="d-none">
            @csrf
            @method('PATCH')
            <div id="promoteIds"></div>
        </form>
    @endcan

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        @can('students.edit')
                            <th style="width:36px;"><input class="form-check-input" type="checkbox" id="selectAll" title="Select all on this page"></th>
                        @endcan
                        <th>Roll No</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Semester</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        @php $status = $student->user->status ?? 'active';
                        $meta = $statusMeta[$status] ?? $statusMeta['active'];
                        $totalSem = $student->department?->total_semesters;
                        $isFinal = $totalSem !== null && (int) $student->semester >= $totalSem; @endphp
                        <tr>
                            @can('students.edit')
                                <td>
                                    <input class="form-check-input row-check" type="checkbox" value="{{ $student->id }}"
                                        data-sem="{{ (int) $student->semester }}" @disabled($isFinal)
                                        title="{{ $isFinal ? 'Already in final semester' : 'Select' }}">
                                </td>
                            @endcan
                            <td><span class="badge bg-secondary">{{ $student->roll_no }}</span></td>
                            <td>{{ $student->user->name }}</td>
                            <td>{{ $student->user->email }}</td>
                            <td>{{ $student->department?->name ?? '—' }}</td>
                            <td>{{ $student->semester ?? '—' }}</td>
                            <td>{{ $student->phone ?? '—' }}</td>
                            <td><span class="badge {{ $meta['class'] }}">{{ $meta['label'] }}</span></td>
                            <td class="text-nowrap small text-muted">{{ $student->created_at?->format('d M Y') }}</td>
                            <td class="text-end">
                                <div class="row-actions">
                                    <a href="{{ route('admin.students.show', $student) }}" class="act-btn act-view"
                                        title="View" aria-label="View student"><i class="bi bi-eye"></i></a>
                                    @can('students.edit')
                                        <a href="{{ route('admin.students.edit', $student) }}" class="act-btn act-edit"
                                            title="Edit" aria-label="Edit student"><i class="bi bi-pencil"></i></a>
                                    @endcan
                                    @canany(['students.edit', 'students.delete'])
                                        <div class="dropdown">
                                            <button type="button" class="act-btn act-menu" data-bs-toggle="dropdown" aria-expanded="false"
                                                title="More actions" aria-label="More actions for {{ $student->user->name }}"><i class="bi bi-three-dots-vertical"></i></button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                @can('students.edit')
                                                    <li>
                                                        <button type="button" class="dropdown-item promote-one" data-id="{{ $student->id }}" @disabled($isFinal)>
                                                            <i class="bi bi-arrow-up-circle me-2 text-secondary"></i>{{ $isFinal ? 'Final semester' : 'Promote semester' }}
                                                        </button>
                                                    </li>
                                                    @if($status === 'pending' || $status === 'blocked')
                                                        <li>
                                                            <form action="{{ route('admin.students.status', $student) }}" method="POST">
                                                                @csrf @method('PATCH')
                                                                <input type="hidden" name="status" value="active">
                                                                <button class="dropdown-item text-success"><i class="bi bi-check-lg me-2"></i>{{ $status === 'pending' ? 'Approve' : 'Activate' }}</button>
                                                            </form>
                                                        </li>
                                                    @else
                                                        <li>
                                                            <form action="{{ route('admin.students.status', $student) }}" method="POST"
                                                                onsubmit="return confirm('Block this student? They will not be able to log in.')">
                                                                @csrf @method('PATCH')
                                                                <input type="hidden" name="status" value="blocked">
                                                                <button class="dropdown-item text-warning"><i class="bi bi-slash-circle me-2"></i>Block</button>
                                                            </form>
                                                        </li>
                                                    @endif
                                                @endcan
                                                @can('students.delete')
                                                    @can('students.edit')<li><hr class="dropdown-divider"></li>@endcan
                                                    <li>
                                                        <form action="{{ route('admin.students.destroy', $student) }}" method="POST"
                                                            onsubmit="return confirm('Delete this student and their account?')">
                                                            @csrf @method('DELETE')
                                                            <button class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button>
                                                        </form>
                                                    </li>
                                                @endcan
                                            </ul>
                                        </div>
                                    @endcanany
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()?->can('students.edit') ? 10 : 9 }}" class="text-center text-muted py-4">No students found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $students->links() }}</div>
@endsection

@push('scripts')
<script>
(function () {
    const form = document.getElementById('promoteForm');
    if (!form) return; // user lacks students.edit — no promote UI

    const idsBox    = document.getElementById('promoteIds');
    const bar       = document.getElementById('bulkBar');
    const selCount  = document.getElementById('selCount');
    const semFlow   = document.getElementById('semFlow');
    const selectAll = document.getElementById('selectAll');

    // Only non-final (enabled) checkboxes are selectable.
    const boxes  = () => Array.from(document.querySelectorAll('.row-check')).filter(c => !c.disabled);
    const chosen = () => boxes().filter(c => c.checked);

    function submitIds(ids) {
        idsBox.innerHTML = '';
        ids.forEach(function (id) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'student_ids[]';
            input.value = id;
            idsBox.appendChild(input);
        });
        form.submit();
    }

    function updateBar() {
        const sel = chosen();
        selCount.textContent = sel.length;
        bar.classList.toggle('d-none', sel.length === 0);

        // Semester-wise: show the transition when the selection shares one semester.
        const sems = [...new Set(sel.map(c => c.dataset.sem))].sort((a, b) => a - b);
        if (sel.length === 0) {
            semFlow.textContent = '';
        } else if (sems.length === 1) {
            semFlow.innerHTML = '· <strong>Semester ' + sems[0] + ' → Semester ' + (Number(sems[0]) + 1) + '</strong>';
        } else {
            semFlow.innerHTML = '· ' + sems.length + ' semesters (each moves +1)';
        }

        if (selectAll) {
            const all = boxes();
            selectAll.checked = all.length > 0 && all.every(c => c.checked);
        }
    }

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('row-check')) {
            updateBar();
        } else if (e.target.id === 'selectAll') {
            boxes().forEach(c => { c.checked = e.target.checked; });
            updateBar();
        }
    });

    document.getElementById('bulkPromoteBtn').addEventListener('click', function () {
        const ids = chosen().map(c => c.value);
        if (!ids.length) return;
        if (confirm('Promote ' + ids.length + ' selected student(s) to the next semester?')) {
            submitIds(ids);
        }
    });

    document.getElementById('clearSelBtn').addEventListener('click', function () {
        boxes().forEach(c => { c.checked = false; });
        if (selectAll) selectAll.checked = false;
        updateBar();
    });

    document.querySelectorAll('.promote-one').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (btn.disabled) return;
            if (confirm('Promote this student to the next semester?')) {
                submitIds([btn.dataset.id]);
            }
        });
    });
})();
</script>
@endpush