@php
    $flags = $project->relationLoaded('similarities')
        ? $project->similarities
        : $project->similarities()->with('matchedProject.academicYear', 'matchedProject.members.student.user', 'matchedProject.submission')->get();

    $warn = \App\Models\CollegeSetting::warnPercent();
    $flags = $flags->filter(fn ($f) => $f->score >= $warn);
@endphp

@if($flags->isNotEmpty())
    <div class="card mb-3 border-warning">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold text-warning-emphasis">
                <i class="bi bi-fingerprint me-1"></i> Similarity flagged
            </span>
            <span class="badge bg-{{ $flags->first()->severityColor() }}">
                {{ number_format($flags->max('score'), 0) }}% highest
            </span>
        </div>
        <div class="card-body">
            <p class="text-muted small">
                This submission resembles other work already in the system. That is a prompt to
                look, not a conclusion — similar tooling and phrasing are common.
            </p>
            <ul class="list-group list-group-flush">
                @php
                    $submittedWhen = fn ($flag) => $flag->stage === \App\Models\ProjectSimilarity::STAGE_REPORT
                        ? $flag->matchedProject?->submission?->submitted_at
                        : $flag->matchedProject?->created_at;
                @endphp
                @foreach($flags as $flag)
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-2 flex-wrap">
                        <span>
                            <span class="badge bg-light text-dark border me-1">{{ ucfirst($flag->stage) }}</span>
                            {{ $flag->matchedProject?->name ?? 'a removed project' }}
                            @if($flag->matchedProject && $flag->matchedProject->memberNames())
                                <span class="text-muted small">— {{ $flag->matchedProject->memberNames() }}</span>
                            @endif
                            @if($when = $submittedWhen($flag))
                                <span class="text-muted small">, submitted first on {{ $when->format('d M Y') }}</span>
                            @endif
                            @if($flag->matchedProject?->academicYear)
                                <span class="text-muted small">({{ $flag->matchedProject->academicYear->label }})</span>
                            @endif
                        </span>
                        <span class="d-flex align-items-center gap-2">
                            <span class="badge bg-{{ $flag->severityColor() }}">{{ number_format($flag->score, 0) }}%</span>
                            @can('similarity.view')
                                <a href="{{ route('admin.similarity.show', $flag) }}" class="btn btn-sm btn-outline-secondary">Compare</a>
                            @endcan
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
