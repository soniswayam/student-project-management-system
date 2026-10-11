@extends('layouts.app')
@section('title', 'Assignments')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="bi bi-journal-text me-2"></i>Assignments</h3>
        @unless($assignments->isEmpty())
            <div class="d-flex gap-2">
                <a href="{{ route('student.assignments.exportPdf') }}" class="btn btn-danger"><i
                        class="bi bi-file-earmark-pdf"></i> PDF</a>

            </div>
        @endunless
    </div>

    @if($assignments->isEmpty())
        <div class="alert alert-info">No assignments have been posted for your department yet.</div>
    @else
        <div class="row g-3">
            @foreach($assignments as $assignment)
                @php $mine = $mySubmissions->get($assignment->id); @endphp
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="text-uppercase small fw-semibold text-primary" style="letter-spacing:.5px">
                                        <span class="badge bg-primary rounded-pill me-1">No. {{ $loop->iteration }}</span>
                                        {{ $assignment->subject }}
                                        @if($assignment->assignment_no) · Assignment {{ $assignment->assignment_no }} @endif
                                        <span class="badge bg-light text-dark border ms-1">{{ $assignment->type }}</span>
                                    </div>
                                    <h5 class="mb-1">{{ $assignment->title }}</h5>
                                </div>
                                @if($mine)
                                    @if($mine->isChecked())
                                        <span class="badge bg-success">Checked</span>
                                    @else
                                        <span class="badge bg-primary">Submitted</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">Pending</span>
                                @endif
                            </div>
                            @php $window = $assignment->submissionWindow(); @endphp
                            <p class="text-muted small mb-2">
                                By {{ $assignment->faculty->user->name ?? 'Faculty' }}
                                @if($assignment->due_date)
                                    · Due {{ $assignment->due_date->format('d M Y, H:i') }}
                                    @if($window->isClosed())
                                        <span class="badge bg-danger ms-1">Closed</span>
                                    @elseif($window->isInGrace())
                                        <span class="badge bg-warning text-dark ms-1">Overdue — {{ $window->timeLeftForHumans() }} left</span>
                                    @endif
                                @endif
                            </p>
                            @if($assignment->description)
                                <p class="mb-2">{{ $assignment->description }}</p>
                            @endif
                            @if($assignment->attachment_path)
                                <a href="{{ asset('storage/' . $assignment->attachment_path) }}" target="_blank"
                                    class="btn btn-sm btn-outline-secondary mb-2">
                                    <i class="bi bi-paperclip"></i> Reference file
                                </a>
                            @endif

                            <hr>
                            @if($mine)
                                <p class="small text-success mb-2">
                                    <i class="bi bi-check-circle"></i>
                                    Submitted on {{ $mine->submitted_at->format('d M Y, H:i') }}
                                    @if($mine->isLate())
                                        <span class="badge bg-warning text-dark">Late{{ $mine->lateLabel() ? ' · '.$mine->lateLabel() : '' }}</span>
                                    @endif
                                </p>

                                @if($mine->hasMarks())
                                    <p class="small mb-2">
                                        <span class="badge bg-success">Marks: {{ $mine->marksLabel() }}</span>
                                    </p>
                                @endif

                                @if($mine->isChecked() && $mine->feedback)
                                    <p class="small mb-2"><span class="text-muted">Feedback:</span> {{ $mine->feedback }}</p>
                                @endif
                            @endif

                            @php $reup = $mine?->openRevisionRequest(); @endphp
                            @include('partials.revision_banner', ['revisionRequest' => $reup])

                            @if($window->isClosed() && ! $reup)
                                <p class="small text-muted mb-0">
                                    <i class="bi bi-lock me-1"></i>
                                    Submissions closed on {{ $window->closesAt()->format('d M Y, H:i') }}.
                                    @unless($mine) You did not submit this assignment. @endunless
                                </p>
                            @elseif(!$mine || $reup)
                                @if($window->isInGrace() && ! $reup)
                                    <div class="alert alert-warning py-2 px-2 small mb-2">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        Past due — you can still submit for {{ $window->timeLeftForHumans() }},
                                        but it will be marked <strong>late</strong>.
                                    </div>
                                @endif
                                <form method="POST" action="{{ route('student.assignments.submit', $assignment) }}"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-2">
                                        <input type="file" name="file"
                                            class="form-control form-control-sm @error('file') is-invalid @enderror" required
                                            accept=".pdf,.doc,.docx,.zip,.rar,.jpg,.jpeg,.png">
                                        @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="mb-2">
                                        <input type="text" name="remarks" class="form-control form-control-sm"
                                            placeholder="Remarks (optional)">
                                    </div>
                                    <button class="btn btn-sm btn-primary">
                                        <i class="bi bi-upload me-1"></i> {{ $mine ? 'Re-submit' : 'Submit' }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
