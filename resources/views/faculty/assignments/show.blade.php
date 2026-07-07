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
                                            <span class="badge bg-warning text-dark ms-1">Late</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($submission->isChecked())
                                            <span class="badge bg-success">Checked</span>
                                        @else
                                            <span class="badge bg-secondary">Submitted</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ asset('storage/'.$submission->file_path) }}" target="_blank">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                    </td>
                                    <td class="text-end">
                                        @if($submission->isChecked())
                                            <span class="text-muted small">{{ $submission->feedback ?: '✓' }}</span>
                                        @else
                                            <form method="POST" action="{{ route('faculty.assignments.check', [$assignment, $submission]) }}" class="d-flex gap-1 justify-content-end">
                                                @csrf
                                                <input type="text" name="feedback" class="form-control form-control-sm" placeholder="Feedback (optional)" style="max-width:200px">
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
@endsection
