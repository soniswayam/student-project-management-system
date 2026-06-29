{{-- Expects: $project (with submission) --}}
@php $sub = $project->submission; @endphp
<div class="card mb-3">
    <div class="card-header bg-white fw-semibold"><i class="bi bi-paperclip me-1"></i> Final Submission Files</div>
    <div class="card-body">
        @if($sub)
            <ul class="list-group list-group-flush">
                <li class="list-group-item px-0 d-flex justify-content-between">
                    <span><i class="bi bi-file-earmark-text me-1"></i> Report</span>
                    @if($sub->report_path)<a href="{{ asset('storage/'.$sub->report_path) }}" target="_blank">Download</a>@else<span class="text-muted">—</span>@endif
                </li>
                <li class="list-group-item px-0 d-flex justify-content-between">
                    <span><i class="bi bi-file-earmark-zip me-1"></i> Source Code (ZIP)</span>
                    @if($sub->source_zip_path)<a href="{{ asset('storage/'.$sub->source_zip_path) }}" target="_blank">Download</a>@else<span class="text-muted">—</span>@endif
                </li>
                <li class="list-group-item px-0 d-flex justify-content-between">
                    <span><i class="bi bi-file-earmark-slides me-1"></i> Presentation (PPT)</span>
                    @if($sub->ppt_path)<a href="{{ asset('storage/'.$sub->ppt_path) }}" target="_blank">Download</a>@else<span class="text-muted">—</span>@endif
                </li>
            </ul>

            @if(!empty($sub->screenshots))
                <hr>
                <span class="text-muted small">Screenshots</span>
                <div class="row g-2 mt-1">
                    @foreach($sub->screenshots as $shot)
                        <div class="col-4 col-md-3">
                            <a href="{{ asset('storage/'.$shot) }}" target="_blank">
                                <img src="{{ asset('storage/'.$shot) }}" class="img-fluid rounded border" alt="screenshot">
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif

            <p class="text-muted small mt-3 mb-0">Submitted {{ $sub->submitted_at?->diffForHumans() }}</p>
        @else
            <p class="text-muted mb-0">No final files submitted yet.</p>
        @endif
    </div>
</div>
