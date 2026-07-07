@extends('layouts.app')
@section('title', 'Upload Final Project')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <h3 class="mb-1"><i class="bi bi-cloud-arrow-up me-2"></i>Upload Final Project</h3>
        <p class="text-muted">Synopsis approved — upload your deliverables below.</p>

        @php $sub = $project->submission; @endphp
        @if($sub)
            <div class="alert alert-info">You have already submitted. Uploading a file again will replace the previous one. Leave a field empty to keep the existing file.</div>
        @endif

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

                    <button class="btn btn-primary"><i class="bi bi-upload me-1"></i> Submit Final Project</button>
                    <a href="{{ route('student.project.show') }}" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
