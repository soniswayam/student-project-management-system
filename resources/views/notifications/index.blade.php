@extends('layouts.app')
@section('title', 'Notifications')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-bell me-2"></i>Notifications</h3>
    @if($notifications->total())
        <form method="POST" action="{{ route('notifications.readAll') }}">
            @csrf
            <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-check-lg me-1"></i>Mark all as read</button>
        </form>
    @endif
</div>

<div class="card">
    <div class="list-group list-group-flush">
        @forelse($notifications as $note)
            <a href="{{ route('notifications.read', $note) }}"
               class="list-group-item list-group-item-action {{ $note->read_at ? '' : 'bg-light' }}">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">
                        @unless($note->read_at)<span class="badge bg-primary me-1">New</span>@endunless
                        {{ $note->title }}
                    </h6>
                    <small class="text-muted">{{ $note->created_at->diffForHumans() }}</small>
                </div>
                <p class="mb-0 small text-muted">{{ $note->message }}</p>
            </a>
        @empty
            <div class="list-group-item text-center text-muted py-5">
                <i class="bi bi-bell-slash fs-1 d-block mb-2"></i>No notifications yet.
            </div>
        @endforelse
    </div>
</div>

<div class="mt-3">{{ $notifications->links() }}</div>
@endsection
