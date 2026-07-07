@extends('layouts.app')
@section('title', $assignment->title)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="{{ route('admin.assignments.index') }}" class="btn btn-link px-0 no-print"><i class="bi bi-arrow-left"></i> Back</a>
    <button onclick="window.print()" class="btn btn-outline-secondary no-print"><i class="bi bi-printer"></i> Print</button>
</div>

@include('partials.print_letterhead', ['docTitle' => 'Assignment Submissions'])

<div class="card mb-3">
    <div class="card-body">
        <h4 class="mb-1"><i class="bi bi-journal-text me-2"></i>{{ $assignment->title }}</h4>
        <p class="text-muted mb-2">
            {{ $assignment->department->name }} ·
            By {{ $assignment->faculty->user->name ?? 'Faculty' }}
            @if($assignment->due_date) · Due {{ $assignment->due_date->format('d M Y, H:i') }} @endif
        </p>
        @if($assignment->description)<p class="mb-2">{{ $assignment->description }}</p>@endif
        @if($assignment->attachment_path)
            <a href="{{ asset('storage/'.$assignment->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary no-print"><i class="bi bi-paperclip"></i> Reference file</a>
        @endif
        <div class="mt-2"><span class="badge bg-primary">{{ $assignment->submissions->count() }} of {{ $totalStudents }} students submitted</span></div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Student</th>
                    <th>Submitted</th>
                    <th>Status</th>
                    <th>Feedback</th>
                    <th class="no-print">File</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignment->submissions as $submission)
                    <tr>
                        <td>{{ $submission->student->user->name ?? 'Unknown' }}</td>
                        <td>
                            {{ $submission->submitted_at->format('d M Y, H:i') }}
                            @if($submission->isLate())<span class="badge bg-warning text-dark ms-1">Late</span>@endif
                        </td>
                        <td>
                            @if($submission->isChecked())
                                <span class="badge bg-success">Checked</span>
                            @else
                                <span class="badge bg-secondary">Submitted</span>
                            @endif
                        </td>
                        <td>{{ $submission->feedback ?: '—' }}</td>
                        <td class="no-print"><a href="{{ asset('storage/'.$submission->file_path) }}" target="_blank"><i class="bi bi-download"></i> Download</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">No submissions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('partials.print_signatures')
@endsection
