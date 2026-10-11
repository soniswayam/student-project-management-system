@extends('layouts.app')
@section('title', 'Upload Final Project')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <h3 class="mb-1"><i class="bi bi-cloud-arrow-up me-2"></i>Upload Final Project</h3>
        <p class="text-muted">Synopsis approved — upload your deliverables below.</p>

        @include('partials.revision_banner')

        @include('partials.deadline_banner', [
            'sourceNote' => $project->deadlineSource() === 'override'
                ? 'This deadline was set specifically for your project.'
                : null,
        ])

        @php $sub = $project->submission; @endphp
        @if($sub)
            <div class="alert alert-info">You have already submitted. Uploading a file again will replace the previous one. Leave a field empty to keep the existing file.</div>
        @endif

        @if($window->isClosed() && ! ($revisionRequest ?? null))
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-lock display-5 text-muted"></i>
                    <p class="mt-3 mb-1 fw-semibold">Submissions are closed</p>
                    <p class="text-muted mb-3">You can no longer upload or replace files for this project.</p>
                    <a href="{{ route('student.project.show') }}" class="btn btn-outline-secondary">Back to my project</a>
                </div>
            </div>
        @else
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('student.submission.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Project Report (PDF/DOC) {{ $sub?->report_path ? '' : '*' }}</label>
                        <input type="file" name="report" class="form-control" accept=".pdf,.doc,.docx">
                        @if($sub?->report_path)<small class="text-success">Current file uploaded ✓</small>@endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Source Code (ZIP/RAR) {{ $sub?->source_zip_path ? '' : '*' }}</label>
                        <input type="file" name="source_zip" class="form-control" accept=".zip,.rar">
                        @if($sub?->source_zip_path)<small class="text-success">Current file uploaded ✓</small>@endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Presentation (PPT/PDF)</label>
                        <input type="file" name="ppt" class="form-control" accept=".ppt,.pptx,.pdf">
                        @if($sub?->ppt_path)<small class="text-success">Current file uploaded ✓</small>@endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Screenshots (up to 6 images)</label>
                        <input type="file" name="screenshots[]" class="form-control" accept="image/*" multiple>
                    </div>

                    @if($window->isInGrace())
                        <div class="alert alert-warning py-2 px-3 small">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            The due date has passed — this will be recorded as a <strong>late submission</strong>.
                        </div>
                    @endif

                    <button class="btn btn-primary"><i class="bi bi-upload me-1"></i> Submit Final Project</button>
                    <a href="{{ route('student.project.show') }}" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Cancel</a>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
