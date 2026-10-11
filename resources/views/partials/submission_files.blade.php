@php $sub = $project->submission; @endphp
<div class="card mb-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold"><i class="bi bi-paperclip me-1"></i> Final Submission Files</span>
        @if($sub && $sub->isLate())
            <span class="badge bg-warning text-dark" title="Handed in after the deadline">
                <i class="bi bi-clock-history me-1"></i>Late{{ $sub->lateLabel() ? ' · '.$sub->lateLabel() : '' }}
            </span>
        @endif
    </div>
    <div class="card-body">
        @if($sub)
            <ul class="list-group list-group-flush">
                @include('partials.file_link', [
                    'path' => $sub->report_path, 'label' => 'Report', 'icon' => 'file-earmark-text',
                    'url' => route('files.project-submission', [$sub, 'report']),
                ])
                @include('partials.file_link', [
                    'path' => $sub->source_zip_path, 'label' => 'Source Code (ZIP)', 'icon' => 'file-earmark-zip',
                    'url' => route('files.project-submission', [$sub, 'source_zip']),
                ])
                @include('partials.file_link', [
                    'path' => $sub->ppt_path, 'label' => 'Presentation (PPT)', 'icon' => 'file-earmark-slides',
                    'url' => route('files.project-submission', [$sub, 'ppt']),
                ])
            </ul>

            @if(!empty($sub->screenshots))
                <hr>
                <span class="text-muted small">
                    Screenshots <span class="badge bg-light text-dark border ms-1">{{ count($sub->screenshots) }}</span>
                </span>

                <div class="row g-2 mt-1" data-lightbox-group>
                    @foreach($sub->screenshots as $i => $shot)
                        <div class="col-4 col-md-3">
                            <a href="{{ route('files.project-screenshot', [$sub, $i]) }}"
                               data-lightbox
                               data-caption="Screenshot {{ $i + 1 }}"
                               title="Click to enlarge">
                                <img src="{{ route('files.project-screenshot', [$sub, $i]) }}" class="img-fluid rounded border"
                                     alt="Screenshot {{ $i + 1 }}" loading="lazy">
                            </a>
                        </div>
                    @endforeach
                </div>
                <div class="form-text">Click any screenshot to view it full size — use ← → to move between them.</div>
            @endif

            <p class="text-muted small mt-3 mb-0">Submitted {{ $sub->submitted_at?->diffForHumans() }}</p>
        @else
            <p class="text-muted mb-0">No final files submitted yet.</p>
        @endif
    </div>
</div>
