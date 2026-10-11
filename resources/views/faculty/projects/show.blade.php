@extends('layouts.app')
@section('title', 'Review Project')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Review Project</h3>
    <a href="{{ route('faculty.projects.index') }}" class="btn btn-outline-secondary btn-sm">← Back</a>
</div>

@include('partials.progress_tracker')

<div class="row">
    <div class="col-lg-7">
        @include('partials.project_info')
        @include('partials.similarity_flag')
        @if($project->isSynopsisApproved())
            @include('partials.submission_files')
        @endif
        @include('partials.viva_card')
        @include('partials.reviews_timeline')
    </div>

    <div class="col-lg-5">

        @if(in_array($project->status, [\App\Models\Project::STATUS_SYNOPSIS_REVIEW, \App\Models\Project::STATUS_CORRECTION]))
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-clipboard-check me-1"></i> Review Synopsis</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('faculty.projects.reviewSynopsis', $project) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Decision</label>
                            <select name="action" class="form-select" required>
                                <option value="approved">Approve</option>
                                <option value="correction">Request Correction</option>
                                <option value="rejected">Reject</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Comments</label>
                            <textarea name="comments" rows="4" class="form-control" placeholder="Feedback for the student..."></textarea>
                        </div>
                        <button class="btn btn-primary w-100"><i class="bi bi-send me-1"></i>Submit Synopsis Review</button>
                    </form>
                </div>
            </div>
        @endif

        @if(in_array($project->status, [\App\Models\Project::STATUS_FINAL_SUBMITTED, \App\Models\Project::STATUS_FINAL_REVIEWED]))
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-award me-1"></i> Review Final Project</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('faculty.projects.reviewFinal', $project) }}">
                        @csrf

                        @if($project->isLevelMarked())
                            <div class="mb-3">
                                <label class="form-label" for="marks">
                                    Marks (out of {{ $project->maxMarks() }})
                                    <span class="text-muted">(optional)</span>
                                </label>
                                <input id="marks" type="number" name="marks" min="0" max="{{ $project->maxMarks() }}"
                                       value="{{ $project->marks }}"
                                       class="form-control @error('marks') is-invalid @enderror">
                                @error('marks') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @else
                            <p class="text-muted small">
                                <i class="bi bi-info-circle me-1"></i>
                                {{ $project->department?->levelLabel() ?? 'This level' }} projects are not marked —
                                leave your assessment in the remarks below.
                            </p>
                        @endif
                        <div class="mb-3">
                            <label class="form-label">Comments</label>
                            <textarea name="comments" rows="3" class="form-control"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Final Remarks</label>
                            <textarea name="final_remarks" rows="2" class="form-control">{{ $project->final_remarks }}</textarea>
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" name="complete" value="1" class="form-check-input" id="complete">
                            <label class="form-check-label" for="complete">Mark project as <strong>Completed</strong></label>
                        </div>
                        <button class="btn btn-primary w-100"><i class="bi bi-send me-1"></i>Submit Final Review</button>
                    </form>
                </div>
            </div>
        @endif

        @if($project->isSubmitted())
            @php $openRequest = $project->openFinalRevisionRequest(); @endphp
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-arrow-repeat me-1"></i> Request Re-upload
                </div>
                <div class="card-body">
                    @if($openRequest)
                        <div class="alert alert-warning py-2 small mb-2">
                            <div class="fw-semibold">Waiting on the student</div>
                            <div>{{ $openRequest->reason }}</div>
                            @if($openRequest->due_at)
                                <div class="mt-1">Due {{ $openRequest->due_at->format('d M Y, H:i') }}</div>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('faculty.revisionRequests.cancel', $openRequest) }}">
                            @csrf @method('PATCH')
                            <button class="btn btn-sm btn-outline-secondary w-100">Withdraw request</button>
                        </form>
                    @else
                        <p class="text-muted small">
                            Sends the work back to the student. Their upload form reopens even if the
                            deadline has passed, and the re-upload is not marked late.
                        </p>
                        <form method="POST" action="{{ route('faculty.projects.requestReupload', $project) }}">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label" for="reup_reason">What needs fixing?</label>
                                <textarea id="reup_reason" name="reason" rows="3" required
                                          class="form-control @error('reason') is-invalid @enderror"
                                          placeholder="e.g. The report is missing the testing chapter">{{ old('reason') }}</textarea>
                                @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-2">
                                <label class="form-label" for="reup_due">New deadline <span class="text-muted fw-normal">(optional)</span></label>
                                <input id="reup_due" type="datetime-local" name="due_at"
                                       class="form-control @error('due_at') is-invalid @enderror" value="{{ old('due_at') }}">
                                @error('due_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <button class="btn btn-warning w-100"><i class="bi bi-arrow-repeat me-1"></i>Request re-upload</button>
                        </form>
                    @endif
                </div>
            </div>
        @endif

        @include('partials.viva_form')

        @if($project->status === \App\Models\Project::STATUS_SYNOPSIS_APPROVED)
            <div class="alert alert-info">Synopsis approved. Waiting for the student to upload final files.</div>
        @endif
        @if($project->status === \App\Models\Project::STATUS_COMPLETED)
            <div class="alert alert-success">
                This project is completed.
                @if($project->marksLabel())
                    Final marks: <strong>{{ $project->marksLabel() }}</strong>.
                @elseif($project->isLevelMarked())
                    No marks were awarded.
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
