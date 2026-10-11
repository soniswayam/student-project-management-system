@extends('layouts.app')
@section('title', 'Similarity Comparison')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h3 class="mb-0"><i class="bi bi-fingerprint me-2"></i>Similarity Comparison</h3>
        <p class="text-muted small mb-0">{{ ucfirst($similarity->stage) }} stage</p>
    </div>
    <a href="{{ route('admin.similarity.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="card mb-3">
    <div class="card-body d-flex align-items-center gap-3 flex-wrap">
        <span class="badge bg-{{ $similarity->severityColor() }} fs-5 px-3 py-2">
            {{ number_format($similarity->score, 0) }}%
        </span>
        <div>
            <div class="fw-semibold">{{ $similarity->severityLabel() }} similarity when recorded</div>
            <div class="text-muted small">
                Found {{ $similarity->created_at?->format('d M Y, H:i') }}
                @if(abs($live['score'] - $similarity->score) >= 1)
                    · <span class="text-warning fw-semibold">now {{ number_format($live['score'], 0) }}%</span>
                    (the text has changed since)
                @endif
            </div>
        </div>
    </div>
</div>

@if(!empty($live['phrases']))
    <div class="card mb-3">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-quote me-1"></i> Overlapping wording
            <span class="badge bg-light text-dark border ms-1">{{ $live['shared'] }} shared phrases</span>
        </div>
        <div class="card-body">
            <p class="text-muted small">
                Fragments appearing in both texts. Common technical phrasing will show up here
                legitimately — read them before drawing a conclusion.
            </p>
            <div class="d-flex flex-wrap gap-2">
                @foreach($live['phrases'] as $phrase)
                    <span class="badge bg-warning-subtle text-dark border">{{ $phrase }}</span>
                @endforeach
            </div>
        </div>
    </div>
@endif

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white">
                <div class="fw-semibold">{{ $similarity->project?->name ?? '—' }}</div>
                <div class="text-muted small">
                    {{ $similarity->project?->memberNames() ?: '—' }}
                    · {{ $similarity->project?->department?->name ?? '—' }}
                    @if($similarity->project?->academicYear)
                        · {{ $similarity->project->academicYear->label }}
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if($left)
                    <p class="small mb-0" style="white-space: pre-wrap;">{{ \Illuminate\Support\Str::limit($left, 4000) }}</p>
                @else
                    <p class="text-muted mb-0">No text available for this stage.</p>
                @endif
            </div>
            <div class="card-footer bg-white">
                @if($similarity->project)
                    <a href="{{ route('admin.projects.show', $similarity->project) }}" class="btn btn-sm btn-outline-primary">
                        Open project
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white">
                <div class="fw-semibold">{{ $similarity->matchedProject?->name ?? '—' }}</div>
                <div class="text-muted small">
                    {{ $similarity->matchedProject?->memberNames() ?: '—' }}
                    · {{ $similarity->matchedProject?->department?->name ?? '—' }}
                    @if($similarity->matchedProject?->academicYear)
                        · {{ $similarity->matchedProject->academicYear->label }}
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if($right)
                    <p class="small mb-0" style="white-space: pre-wrap;">{{ \Illuminate\Support\Str::limit($right, 4000) }}</p>
                @else
                    <p class="text-muted mb-0">No text available for this stage.</p>
                @endif
            </div>
            <div class="card-footer bg-white">
                @if($similarity->matchedProject)
                    @if($similarity->matchedProject->isArchived())
                        <a href="{{ route('admin.archive.show', $similarity->matchedProject) }}" class="btn btn-sm btn-outline-secondary">
                            Open in Archive
                        </a>
                    @else
                        <a href="{{ route('admin.projects.show', $similarity->matchedProject) }}" class="btn btn-sm btn-outline-primary">
                            Open project
                        </a>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
