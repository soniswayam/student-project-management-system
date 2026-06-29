@extends('layouts.app')
@section('title', 'Review Project')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Review Project</h3>
    <a href="{{ route('faculty.projects.index') }}" class="btn btn-outline-secondary btn-sm">← Back</a>
</div>

<div class="row">
    <div class="col-lg-7">
        @include('partials.project_info')
        @if($project->isSynopsisApproved())
            @include('partials.submission_files')
        @endif
        @include('partials.reviews_timeline')
    </div>

    <div class="col-lg-5">
        {{-- Synopsis review --}}
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
                        <button class="btn btn-primary w-100">Submit Synopsis Review</button>
                    </form>
                </div>
            </div>
        @endif

        {{-- Final review --}}
        @if(in_array($project->status, [\App\Models\Project::STATUS_FINAL_SUBMITTED, \App\Models\Project::STATUS_FINAL_REVIEWED]))
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-award me-1"></i> Review Final Project</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('faculty.projects.reviewFinal', $project) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Marks (out of 100)</label>
                            <input type="number" name="marks" min="0" max="100" value="{{ $project->marks }}" class="form-control" required>
                        </div>
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
                        <button class="btn btn-primary w-100">Submit Final Review</button>
                    </form>
                </div>
            </div>
        @endif

        @if($project->status === \App\Models\Project::STATUS_SYNOPSIS_APPROVED)
            <div class="alert alert-info">Synopsis approved. Waiting for the student to upload final files.</div>
        @endif
        @if($project->status === \App\Models\Project::STATUS_COMPLETED)
            <div class="alert alert-success">This project is completed. Final marks: <strong>{{ $project->marks }}/100</strong>.</div>
        @endif
    </div>
</div>
@endsection
