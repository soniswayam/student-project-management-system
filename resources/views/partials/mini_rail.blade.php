@php
    $P = \App\Models\Project::class;
    $now = match ($project->status) {
        $P::STATUS_SYNOPSIS_APPROVED => 3,
        $P::STATUS_FINAL_SUBMITTED   => 4,
        $P::STATUS_FINAL_REVIEWED    => 5,
        $P::STATUS_COMPLETED         => 6,
        default                      => 1,
    };
@endphp
<span class="mini-rail" role="img" aria-label="Step {{ min($now, 5) }} of 5">
    @for($s = 1; $s <= 5; $s++)
        <i class="{{ $s < $now ? 'is-done' : ($s === $now ? 'is-now' : '') }}"></i>
    @endfor
</span>
