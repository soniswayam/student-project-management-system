@extends('layouts.app')
@section('title', 'Assigned Projects')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0"><i class="bi bi-folder-check me-2"></i>Assigned Projects</h3>
    @unless(isset($picker))
    <div class="btn-group btn-group-sm" role="group">
        <a href="{{ route('faculty.projects.index', ['all' => 1]) }}" class="btn btn-outline-secondary {{ request('flag') ? '' : 'active' }}">All</a>
        <a href="{{ route('faculty.projects.index', ['flag' => 'similar']) }}" class="btn btn-outline-warning {{ request('flag') === 'similar' ? 'active' : '' }}">
            <i class="bi bi-fingerprint me-1"></i>Possible duplicates
        </a>
    </div>
    @endunless
</div>

@if(isset($picker))
    @include('partials.class_picker', [
        'action' => route('faculty.projects.index'),
        'allUrl' => route('faculty.projects.index', ['all' => 1]),
        'noun' => 'projects',
    ])
@else
@php $warnPercent = \App\Models\CollegeSetting::warnPercent(); @endphp

<form method="GET" class="row g-2 mb-3 align-items-center">
    @if(request('flag'))<input type="hidden" name="flag" value="{{ request('flag') }}">@endif
    <div class="col-auto">
        <select name="department_id" class="form-select" onchange="this.form.submit()">
            <option value="">All departments</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>{{ $dept->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <select name="semester" class="form-select" onchange="this.form.submit()">
            <option value="">All semesters</option>
            @foreach($semesters as $sem)
                <option value="{{ $sem }}" @selected(request('semester') == $sem)>Sem {{ $sem }}</option>
            @endforeach
        </select>
    </div>
    <noscript><div class="col-auto"><button class="btn btn-primary">Filter</button></div></noscript>
    <div class="col-auto">
        <a href="{{ route('faculty.projects.index') }}" class="small"><i class="bi bi-arrow-left-right me-1"></i>Change class</a>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light"><tr><th>Project</th><th>Group</th><th>Type</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($projects->getCollection()->groupBy(fn ($p) => $p->groupLabel()) as $groupLabel => $group)
                @include('partials.group_row', ['label' => $groupLabel, 'count' => $group->count(), 'colspan' => 5])
                @foreach($group as $project)
                @php $topFlag = $project->similarities->where('score', '>=', $warnPercent)->sortByDesc('score')->first(); @endphp
                <tr>
                    <td>
                        <strong>{{ $project->name }}</strong>
                        @if($topFlag)
                            <span class="badge bg-{{ $topFlag->severityColor() }} ms-1"
                                  title="Resembles &quot;{{ $topFlag->matchedProject?->name }}&quot; by {{ $topFlag->matchedProject?->memberNames() ?: 'unknown' }} — {{ number_format($topFlag->score, 0) }}% match">
                                <i class="bi bi-fingerprint"></i> possible duplicate
                            </span>
                        @endif
                    </td>
                    <td>{{ $project->memberNames() ?: '—' }}</td>
                    <td>{{ ucfirst($project->project_type) }}</td>
                    <td><span class="badge bg-{{ $project->statusColor() }}">{{ $project->status }}</span></td>
                    <td class="text-end"><a href="{{ route('faculty.projects.show', $project) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye me-1"></i>Review</a></td>
                </tr>
                @endforeach
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No projects assigned to you yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $projects->links() }}</div>
@endif
@endsection
