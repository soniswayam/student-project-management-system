@php
    $history = $project->relationLoaded('guideHistory')
        ? $project->guideHistory
        : $project->guideHistory()->with('fromFaculty.user', 'toFaculty.user', 'changedBy')->get();

    $worthShowing = $history->count() > 1 || $history->contains(fn ($h) => $h->isReplacement());
@endphp

@if($worthShowing)
    <div class="card mb-3">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-arrow-left-right me-1"></i> Guide History
        </div>
        <div class="card-body">
            @foreach($history as $entry)
                <div class="d-flex mb-3">
                    <div class="me-3">
                        <span class="stat-ico tone-{{ $entry->isReplacement() ? 'warning' : 'primary' }}">
                            <i class="bi bi-{{ $entry->isReplacement() ? 'arrow-left-right' : 'person-plus' }}"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold">
                            @if($entry->isReplacement())
                                {{ $entry->fromFaculty?->user?->name ?? 'Previous guide' }}
                                <i class="bi bi-arrow-right mx-1 text-muted"></i>
                                {{ $entry->toFaculty?->user?->name ?? '—' }}
                            @else
                                Assigned to {{ $entry->toFaculty?->user?->name ?? '—' }}
                            @endif
                        </div>
                        @if($entry->reason)
                            <div class="small">{{ $entry->reason }}</div>
                        @endif
                        <div class="text-muted small">
                            {{ $entry->changed_at?->format('d M Y, H:i') }}
                            @if($entry->changedBy) · by {{ $entry->changedBy->name }} @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
