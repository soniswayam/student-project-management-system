{{-- Reusable project milestone tracker. Expects: $project --}}
@php
    $steps = [
        \App\Models\Project::STATUS_SYNOPSIS_REVIEW   => 1,
        \App\Models\Project::STATUS_CORRECTION        => 1,
        \App\Models\Project::STATUS_SYNOPSIS_APPROVED  => 2,
        \App\Models\Project::STATUS_FINAL_SUBMITTED    => 3,
        \App\Models\Project::STATUS_FINAL_REVIEWED     => 4,
        \App\Models\Project::STATUS_COMPLETED          => 4,
    ];
    $current = $steps[$project->status] ?? 1;
    $labels = ['1. Synopsis', '2. Approved', '3. Final Upload', '4. Reviewed'];
    $percent = (int) round(($current / count($labels)) * 100);
@endphp

<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Project Progress</h5>
            <span class="badge bg-{{ $project->statusColor() }}">{{ $project->status }}</span>
        </div>

        <div class="progress mb-4" style="height:6px">
            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percent }}%"
                 aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
        </div>

        <div class="row text-center">
            @foreach($labels as $i => $label)
                <div class="col">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center
                                {{ $current >= $i + 1 ? 'bg-success text-white' : 'bg-light text-muted' }}"
                         style="width:48px;height:48px;font-weight:700">
                        {{ $current > $i + 1 ? '✓' : $i + 1 }}
                    </div>
                    <div class="small mt-2 {{ $current >= $i + 1 ? 'fw-semibold' : 'text-muted' }}">{{ $label }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>
