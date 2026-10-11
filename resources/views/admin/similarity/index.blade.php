@extends('layouts.app')
@section('title', 'Plagiarism & Duplicates')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h3 class="mb-0"><i class="bi bi-fingerprint me-2"></i>Plagiarism &amp; Duplicates</h3>
        <p class="text-muted small mb-0">Similarity between submissions held in this system.</p>
    </div>
    <a href="{{ route('admin.similarity.export', request()->query()) }}" class="btn btn-success">
        <i class="bi bi-file-earmark-excel"></i> Export
    </a>
</div>

@include('partials.alerts')

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="card stat-tile h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-ico tone-danger"><i class="bi bi-exclamation-octagon"></i></span>
                <div>
                    <div class="fs-4 fw-semibold">{{ $highCount }}</div>
                    <div class="text-muted small">High ({{ $blockPercent }}%+)</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-tile h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-ico tone-warning"><i class="bi bi-eye"></i></span>
                <div>
                    <div class="fs-4 fw-semibold">{{ $reviewCount }}</div>
                    <div class="text-muted small">Worth reviewing</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-1"><i class="bi bi-info-circle me-1"></i>How to read this</div>
                <p class="small mb-0">
                    Scores compare a submission against <strong>other submissions in this system</strong>,
                    including archived years — not against the internet. A high score is
                    evidence to look at, not proof. Thresholds
                    (<strong>{{ $warnPercent }}%</strong> warn, <strong>{{ $blockPercent }}%</strong> block)
                    are editable in @can('settings.manage')<a href="{{ route('admin.settings.edit') }}">College Settings</a>@else College Settings @endcan.
                </p>
            </div>
        </div>
    </div>
</div>

<form class="card card-body mb-3" method="GET" data-live-search>
    <div class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label small mb-1">Search project</label>
            <div class="position-relative">
                <i class="bi bi-search position-absolute top-50 translate-middle-y ms-2 text-muted"></i>
                <input name="search" value="{{ request('search') }}" class="form-control ps-4"
                       placeholder="Project name…" autocomplete="off">
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Stage</label>
            <select name="stage" class="form-select">
                <option value="">Both stages</option>
                <option value="synopsis" @selected(request('stage') === 'synopsis')>Synopsis</option>
                <option value="report" @selected(request('stage') === 'report')>Report</option>
            </select>
        </div>
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
            <label class="form-label small mb-1">Min score</label>
            <select name="min_score" class="form-select">
                <option value="{{ $warnPercent }}" @selected(!request()->filled('min_score'))>{{ $warnPercent }}%+</option>
                <option value="0" @selected(request('min_score') === '0')>Show all</option>
                <option value="{{ $blockPercent }}" @selected(request('min_score') == $blockPercent)>{{ $blockPercent }}%+</option>
            </select>
        </div>
    </div>
    @if(request()->hasAny(['search', 'stage', 'department_id', 'min_score']))
        <div class="mt-2"><a href="{{ route('admin.similarity.index') }}" class="btn btn-outline-secondary btn-sm">Reset filters</a></div>
    @endif
    <noscript><button class="btn btn-primary btn-sm mt-2">Filter</button></noscript>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Score</th><th>Project</th><th>Group</th>
                    <th>Resembles</th><th>Stage</th><th>Found</th><th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($findings as $finding)
                <tr>
                    <td>
                        <span class="badge bg-{{ $finding->severityColor() }}">
                            {{ number_format($finding->score, 0) }}%
                        </span>
                    </td>
                    <td>
                        <strong>{{ $finding->project?->name ?? '—' }}</strong>
                        <div class="text-muted small">{{ $finding->project?->department?->name }}</div>
                    </td>
                    <td>{{ $finding->project?->memberNames() ?: '—' }}</td>
                    <td>
                        {{ $finding->matchedProject?->name ?? '—' }}
                        @if($finding->matchedProject?->academicYear)
                            <span class="badge bg-light text-dark border ms-1">{{ $finding->matchedProject->academicYear->label }}</span>
                        @endif
                    </td>
                    <td><span class="badge bg-light text-dark border">{{ ucfirst($finding->stage) }}</span></td>
                    <td class="small text-muted text-nowrap">{{ $finding->created_at?->format('d M Y') }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.similarity.show', $finding) }}" class="act-btn act-view" title="Compare side by side">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
                        <i class="bi bi-shield-check fs-1 d-block mb-2 text-success"></i>
                        <span class="fw-semibold d-block">Nothing flagged</span>
                        <span class="small">No submission scored above the threshold.</span>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $findings->links() }}</div>
@endsection
