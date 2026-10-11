@php
    $P = \App\Models\Project::class;
    $now = match ($project->status) {
        $P::STATUS_SYNOPSIS_APPROVED => 3,
        $P::STATUS_FINAL_SUBMITTED   => 4,
        $P::STATUS_FINAL_REVIEWED    => 5,
        $P::STATUS_COMPLETED         => 6,
        default                      => 1,
    };
    $synopsisNote = match ($project->status) {
        $P::STATUS_CORRECTION       => 'Correction needed',
        $P::STATUS_REJECTED         => 'Rejected',
        $P::STATUS_SYNOPSIS_PENDING => 'Not sent yet',
        default                     => $now > 1 ? 'Sent' : 'Under review',
    };
    $hideResult = auth()->user()->isStudent() && ! $project->isCompleted();
    $stages = [
        ['Synopsis', $synopsisNote],
        ['Approved', $now > 2 ? 'By guide' : 'Waiting for guide'],
        ['Final upload', $now > 3 ? 'Uploaded' : 'Report + source code'],
        ['Reviewed', $now > 4 ? 'Done' : 'Viva by guide'],
        ['Result', $now > 5 ? ((! $hideResult && $project->marksLabel()) ?: 'Declared') : 'Certificate'],
    ];
    $needsAction = in_array($project->status, [$P::STATUS_CORRECTION, $P::STATUS_REJECTED], true);
@endphp
<ol class="stage-rail" aria-label="Project progress: {{ min($now - 1, 5) }} of 5 steps done">
    @foreach($stages as $i => [$name, $note])
        @php
            $step = $i + 1;
            $state = $step < $now ? 'is-done' : ($step === $now ? 'is-now' : '');
            if ($step === 1 && $needsAction) { $state .= ' is-warn'; }
        @endphp
        <li class="stage {{ $state }}" @if($step === $now) aria-current="step" @endif>
            <span class="stage-dot" aria-hidden="true">
                @if($step < $now)<i class="bi bi-check-lg"></i>@else{{ $step }}@endif
            </span>
            <span class="stage-name">{{ $name }}</span>
            <span class="stage-note">{{ $note }}</span>
        </li>
    @endforeach
</ol>
