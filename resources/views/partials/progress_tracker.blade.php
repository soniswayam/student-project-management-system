<div class="card {{ $trackerClass ?? 'mb-4' }}">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <h5 class="mb-0">Project progress</h5>
            <span class="badge bg-{{ $project->statusColor() }}">{{ $project->status }}</span>
        </div>
        @include('partials.stage_rail')
    </div>
</div>
