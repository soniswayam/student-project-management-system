@extends('layouts.app')
@section('title', $assignment->title)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <a href="{{ route('faculty.assignments.index') }}" class="btn btn-link px-0 mb-2"><i class="bi bi-arrow-left"></i> Back</a>

        <div class="card mb-3">
            <div class="card-body">
                <h4 class="mb-1"><i class="bi bi-journal-text me-2"></i>{{ $assignment->title }}</h4>
                <p class="text-muted mb-2">{{ $assignment->department->name }}
                    @if($assignment->due_date)
                        · Due {{ $assignment->due_date->format('d M Y, H:i') }}
                    @endif
                </p>
                @if($assignment->description)
                    <p class="mb-2">{{ $assignment->description }}</p>
                @endif
                @if($assignment->attachment_path)
                    <a href="{{ asset('storage/'.$assignment->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-paperclip"></i> Reference file
                    </a>
                @endif
                <div class="mt-3">
                    <span class="badge bg-primary">{{ $assignment->submissions->count() }} of {{ $totalStudents }} students submitted</span>
                </div>
            </div>
        </div>

        <h5 class="mb-2">Submissions</h5>
        @if($assignment->submissions->isEmpty())
            <div class="alert alert-info">No submissions yet.</div>
        @else
            <div class="card">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Submitted</th>
                                <th>Status</th>

                                @if($assignment->isMarked())
                                    <th class="text-center">Marks</th>
                                @endif
                                <th>File</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignment->submissions as $submission)
                                <tr>
                                    <td>{{ $submission->student->user->name ?? 'Unknown' }}</td>
                                    <td>
                                        {{ $submission->submitted_at->format('d M Y, H:i') }}
                                        @if($submission->isLate())
                                            <span class="badge bg-warning text-dark ms-1">Late{{ $submission->lateLabel() ? ' · '.$submission->lateLabel() : '' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($submission->isChecked())
                                            <span class="badge bg-success">Checked</span>
                                        @else
                                            <span class="badge bg-secondary">Submitted</span>
                                        @endif
                                    </td>
                                    @if($assignment->isMarked())
                                        <td class="text-center">

                                            {{ $submission->marksLabel() ?? '—' }}
                                        </td>
                                    @endif
                                    <td>
                                        @php
                                            $ext = strtolower(pathinfo($submission->file_path, PATHINFO_EXTENSION));
                                            $canPreview = in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp'], true);
                                        @endphp
                                        <span class="d-flex gap-1 flex-wrap">
                                            @if($canPreview)
                                                <a href="{{ route('files.assignment-submission', $submission) }}"
                                                   class="btn btn-sm btn-outline-secondary"
                                                   data-file-preview
                                                   data-preview-kind="{{ $ext === 'pdf' ? 'doc' : 'image' }}"
                                                   data-preview-title="{{ $submission->student?->user?->name }} — {{ $assignment->title }}">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            @endif
                                            <a href="{{ route('files.assignment-submission', $submission) }}" download
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        @if($submission->isChecked())
                                            <div class="d-flex gap-2 justify-content-end align-items-center">
                                                <span class="text-muted small">{{ $submission->feedback ?: '✓' }}</span>
                                                <button class="btn btn-sm btn-outline-warning" title="Ask for a re-upload"
                                                        data-bs-toggle="modal" data-bs-target="#reup{{ $submission->id }}">
                                                    <i class="bi bi-arrow-repeat"></i>
                                                </button>
                                            </div>
                                        @elseif($submission->openRevisionRequest())
                                            <span class="badge bg-warning text-dark">Re-upload requested</span>
                                        @else
                                            <form method="POST" action="{{ route('faculty.assignments.check', [$assignment, $submission]) }}" class="d-flex gap-1 justify-content-end flex-wrap">
                                                @csrf
                                                @if($assignment->isMarked())
                                                    <input type="number" name="marks" min="0" max="{{ $assignment->max_marks }}"
                                                           class="form-control form-control-sm" style="max-width:80px"
                                                           placeholder="/{{ $assignment->max_marks }}"
                                                           aria-label="Marks out of {{ $assignment->max_marks }} (optional)">
                                                @endif
                                                <input type="text" name="feedback" class="form-control form-control-sm" placeholder="Feedback (optional)" style="max-width:180px">
                                                <button class="btn btn-sm btn-success"><i class="bi bi-check2"></i> Mark checked</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>

@foreach($assignment->submissions as $submission)
    @continue(! $submission->isChecked())
    <div class="modal fade" id="reup{{ $submission->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="POST"
                  action="{{ route('faculty.assignments.requestReupload', [$assignment, $submission]) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-arrow-repeat me-1"></i>
                        Request re-upload — {{ $submission->student?->user?->name ?? 'student' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">
                        This reopens the submission for this student even if the deadline has passed,
                        and the re-upload is not counted as late.
                    </p>
                    <div class="mb-3">
                        <label class="form-label">What needs fixing?</label>
                        <textarea name="reason" rows="3" class="form-control" required
                                  placeholder="e.g. Wrong file uploaded — please send the report, not the slides"></textarea>
                    </div>
                    <div>
                        <label class="form-label">New deadline <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="datetime-local" name="due_at" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-arrow-repeat me-1"></i>Request re-upload</button>
                </div>
            </form>
        </div>
    </div>
@endforeach
@endsection
