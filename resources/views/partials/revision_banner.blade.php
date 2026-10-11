@if(!empty($revisionRequest) && $revisionRequest->isOpen())
    <div class="alert alert-warning">
        <div class="fw-semibold mb-1">
            <i class="bi bi-arrow-repeat me-1"></i>Your guide has asked you to upload this again
        </div>
        <p class="mb-2">{{ $revisionRequest->reason }}</p>
        <p class="small mb-0">
            @if($revisionRequest->due_at)
                New deadline: <strong>{{ $revisionRequest->due_at->format('d M Y, H:i') }}</strong>
                @if($revisionRequest->isOverdue())
                    <span class="text-danger fw-semibold">· overdue</span>
                @else
                    <span class="text-muted">· {{ $revisionRequest->due_at->diffForHumans() }}</span>
                @endif
                <br>
            @endif
            The upload form is open for you even though the normal deadline has passed, and a
            re-upload you were asked for is <strong>not</strong> counted as late.
        </p>
    </div>
@endif
