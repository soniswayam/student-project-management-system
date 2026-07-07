{{-- Expects: $project (with reviews.faculty.user) --}}
@php $hideResult = auth()->user()->isStudent() && ! $project->isCompleted(); @endphp
<div class="card mb-3">
    <div class="card-header bg-white fw-semibold"><i class="bi bi-chat-left-text me-1"></i> Review History &amp; Comments</div>
    <div class="card-body">
        @forelse($project->reviews as $review)
            <div class="d-flex mb-3">
                <div class="me-3">
                    <span class="badge bg-{{ $review->actionColor() }} rounded-pill text-uppercase">{{ $review->action }}</span>
                </div>
                <div>
                    <div class="small text-muted">
                        {{ ucfirst($review->stage) }} review by
                        <strong>{{ $review->faculty?->user?->name ?? 'Faculty' }}</strong>
                        · {{ $review->created_at->diffForHumans() }}
                        @if($review->marks !== null && ! $hideResult) · <strong>{{ $review->marks }}/100</strong> @endif
                    </div>
                    <div>{{ $review->comments ?: 'No comments.' }}</div>
                </div>
            </div>
        @empty
            <p class="text-muted mb-0">No reviews yet.</p>
        @endforelse
    </div>
</div>
