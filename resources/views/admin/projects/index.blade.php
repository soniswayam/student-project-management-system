@extends('layouts.app')
@section('title', 'All Projects')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-folder me-2"></i>All Projects</h3>
    <div class="d-flex gap-2 flex-wrap">

        <a href="{{ route('admin.projects.export', request()->query()) }}" class="btn btn-success"><i class="bi bi-file-earmark-excel"></i> Excel (Groups)</a>
        <button onclick="window.print()" class="btn btn-outline-secondary no-print"><i class="bi bi-printer"></i> Print</button>
    </div>
</div>
<div class="print-title mb-2"><h4>{{ config('college.name') }} — Projects &amp; Groups</h4><small>Printed on {{ now()->format('d M Y') }}</small></div>

@php
    $filterKeys = ['search', 'status', 'department_id', 'semester', 'level', 'academic_year_id', 'faculty_id', 'flag', 'from', 'to'];
    $activeFilters = collect($filterKeys)->filter(fn ($k) => request()->filled($k))->count();
@endphp

<form class="card card-body mb-3" method="GET" data-live-search>
    <div class="row g-2 align-items-end">
        <div class="col-md-5">
            <label class="form-label small mb-1">Search</label>
            <div class="position-relative">
                <i class="bi bi-search position-absolute top-50 translate-middle-y ms-2 text-muted"></i>
                <input name="search" value="{{ request('search') }}" class="form-control ps-4"
                       placeholder="Project, student name or roll no…" autocomplete="off" autofocus>
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label small mb-1">Status</label>
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2 align-items-center flex-wrap">

            <button class="btn btn-outline-secondary btn-sm" type="button"
                    data-bs-toggle="collapse" data-bs-target="#moreFilters"
                    aria-expanded="{{ $activeFilters > 2 ? 'true' : 'false' }}" aria-controls="moreFilters">
                <i class="bi bi-sliders me-1"></i>More filters
                @if($activeFilters > 0)<span class="badge bg-primary ms-1">{{ $activeFilters }}</span>@endif
            </button>
            <span class="text-muted small" data-live-status></span>
            @if($activeFilters > 0)
                <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            @endif
        </div>
    </div>

    <div class="collapse {{ $activeFilters > 2 ? 'show' : '' }}" id="moreFilters">
        <hr class="my-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Department</label>
                <select name="department_id" class="form-select">
                    <option value="">All departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Semester</label>
                <select name="semester" class="form-select">
                    <option value="">All semesters</option>
                    @foreach($semesters as $sem)
                        <option value="{{ $sem }}" @selected(request('semester') == $sem)>Sem {{ $sem }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Level</label>
                <select name="level" class="form-select">
                    <option value="">All levels</option>
                    <option value="graduation" @selected(request('level') === 'graduation')>Graduation</option>
                    <option value="post_graduation" @selected(request('level') === 'post_graduation')>Post-Graduation</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Academic year</label>
                <select name="academic_year_id" class="form-select">
                    <option value="">All years</option>
                    @foreach($years as $year)
                        <option value="{{ $year->id }}" @selected(request('academic_year_id') == $year->id)>{{ $year->label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Guide</label>
                <select name="faculty_id" class="form-select">
                    <option value="">Any guide</option>
                    <option value="unassigned" @selected(request('faculty_id') === 'unassigned')>— Unassigned —</option>
                    @foreach($faculties as $faculty)
                        <option value="{{ $faculty->id }}" @selected(request('faculty_id') == $faculty->id)>{{ $faculty->user?->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-1">Flagged as</label>
                <select name="flag" class="form-select">
                    <option value="">No flag filter</option>
                    <option value="similar" @selected(request('flag') === 'similar')>Similarity flagged</option>
                    <option value="late" @selected(request('flag') === 'late')>Submitted late</option>
                    <option value="reupload" @selected(request('flag') === 'reupload')>Re-upload pending</option>
                    <option value="viva" @selected(request('flag') === 'viva')>Viva scheduled</option>
                    <option value="no_viva" @selected(request('flag') === 'no_viva')>No viva scheduled</option>
                    <option value="viva_missing" @selected(request('flag') === 'viva_missing')>Viva expected but missing</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1" for="fromDate">Created from</label>
                <input id="fromDate" type="date" name="from" value="{{ request('from') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1" for="toDate">Created to</label>
                <input id="toDate" type="date" name="to" value="{{ request('to') }}" class="form-control">
            </div>
        </div>
    </div>

    <noscript><button class="btn btn-primary btn-sm mt-2">Filter</button></noscript>
</form>

<div data-bulk-scope>
    @can('projects.assign')

        <div data-bulk-bar class="card card-body mb-2 d-none flex-row align-items-center flex-wrap gap-2 border-primary no-print">
            <span class="fw-semibold text-primary"><span data-bulk-count>0</span> selected</span>
            <div class="ms-auto d-flex gap-2 flex-wrap align-items-center">
                <select form="bulkAssignForm" name="faculty_id" class="form-select form-select-sm" style="min-width:16rem;" required>
                    <option value="">— Assign guide —</option>
                    @foreach($faculties as $faculty)
                        <option value="{{ $faculty->id }}">
                            {{ $faculty->user?->name }}
                            — {{ $faculty->active_projects_count }}{{ $faculty->max_projects ? '/'.$faculty->max_projects : '' }}
                            @if($faculty->isOverCapacity()) · at capacity @endif
                        </option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-primary btn-sm"
                        data-bulk-submit
                        data-bulk-form="bulkAssignForm"
                        data-bulk-field="project_ids[]"
                        data-bulk-confirm="Assign the selected guide to {n} project(s)?">
                    <i class="bi bi-person-check"></i> Assign
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bulk-clear>Clear</button>
            </div>
        </div>

        <form id="bulkAssignForm" method="POST" action="{{ route('admin.projects.bulkAssign') }}" class="d-none">
            @csrf
            <div data-bulk-ids></div>
        </form>
    @endcan

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        @can('projects.assign')
                            <th style="width:36px;" class="no-print">
                                <input class="form-check-input" type="checkbox" data-bulk-all title="Select all on this page">
                            </th>
                        @endcan
                        <th>Project</th><th>Type</th><th>Group</th><th>Faculty</th><th>Status</th><th></th>
                    </tr>
                </thead>
                <tbody>
                @php $colspan = auth()->user()->can('projects.assign') ? 7 : 6; @endphp
                @forelse($projects->getCollection()->groupBy(fn ($p) => $p->groupLabel()) as $groupLabel => $group)
                    @include('partials.group_row', ['label' => $groupLabel, 'count' => $group->count(), 'colspan' => $colspan])
                    @foreach($group as $project)
                    <tr>
                        @can('projects.assign')
                            <td class="no-print">
                                <input class="form-check-input" type="checkbox" data-bulk-item value="{{ $project->id }}">
                            </td>
                        @endcan
                        <td>
                            <strong>{{ $project->name }}</strong>
                            @if($project->department)
                                <span class="badge bg-{{ $project->department->levelColor() }} ms-1"
                                      title="{{ $project->department->levelLabel() }}">
                                    {{ $project->department->levelShort() }}
                                </span>
                            @endif
                            @if($project->academicYear)
                                <span class="badge bg-light text-dark border ms-1">{{ $project->academicYear->label }}</span>
                            @endif
                            @if($project->isVivaMissing())
                                <span class="badge bg-warning text-dark ms-1" title="This level expects a viva">
                                    <i class="bi bi-mic"></i> no viva
                                </span>
                            @endif
                        </td>
                        <td><span class="badge bg-{{ $project->project_type === 'group' ? 'primary' : 'secondary' }}">{{ ucfirst($project->project_type) }}</span></td>
                        <td>{{ $project->memberNames() ?: '—' }}</td>
                        <td>
                            @if($project->assignment)
                                {{ $project->assignment->faculty->user->name }}
                            @else
                                <span class="badge bg-danger">Unassigned</span>
                            @endif
                        </td>
                        <td><span class="badge bg-{{ $project->statusColor() }}">{{ $project->status }}</span></td>
                        <td class="text-end"><a href="{{ route('admin.projects.show', $project) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye me-1"></i>View</a></td>
                    </tr>
                    @endforeach
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No projects found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">{{ $projects->links() }}</div>
@endsection
