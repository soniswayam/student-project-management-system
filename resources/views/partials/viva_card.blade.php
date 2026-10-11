@php $viva = $project->viva; @endphp

@if($viva)
    <div class="card mb-3">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold"><i class="bi bi-mic me-1"></i> Viva</span>
            <span class="badge bg-{{ $viva->statusColor() }}">{{ $viva->statusLabel() }}</span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-2">
                    <span class="text-muted small">Date &amp; time</span><br>
                    <span class="fw-semibold">{{ $viva->scheduled_at->format('d M Y, H:i') }}</span>
                    @if($viva->isUpcoming())
                        <span class="text-muted small">· {{ $viva->scheduled_at->diffForHumans() }}</span>
                    @endif
                </div>
                <div class="col-md-6 mb-2">
                    <span class="text-muted small">Mode</span><br>
                    @if($viva->isOnline())
                        <span class="badge bg-info">Online</span>
                        @if($viva->meeting_link && ! $viva->isCancelled())
                            <a href="{{ $viva->meeting_link }}" target="_blank" rel="noopener" class="ms-1">Join link</a>
                        @endif
                    @else
                        <span class="badge bg-secondary">In person</span>
                        @if($viva->venue) <span class="ms-1">{{ $viva->venue }}</span> @endif
                    @endif
                </div>

                @if($viva->panelists->isNotEmpty())
                    <div class="col-12 mb-2">
                        <span class="text-muted small">Panel</span><br>
                        @foreach($viva->panelists as $panelist)
                            <span class="badge bg-light text-dark border">{{ $panelist->user?->name ?? '—' }}</span>
                        @endforeach
                    </div>
                @endif

                @if($viva->notes)
                    <div class="col-12">
                        <span class="text-muted small">Notes</span><br>
                        {{ $viva->notes }}
                    </div>
                @endif
            </div>

            @if($viva->isCancelled())
                <div class="alert alert-secondary mt-3 mb-0 py-2 small">
                    <i class="bi bi-x-circle me-1"></i>This viva was cancelled. Your guide will schedule a new one.
                </div>
            @endif
        </div>
    </div>
@endif
