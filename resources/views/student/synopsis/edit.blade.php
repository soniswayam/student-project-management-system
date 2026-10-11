@extends('layouts.app')
@section('title', 'Edit Synopsis')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <h3 class="mb-1"><i class="bi bi-pencil-square me-2"></i>Edit &amp; Resubmit Synopsis</h3>
        <p class="text-muted">
            Revision {{ $project->revision }} — make the changes your guide asked for, then resubmit.
        </p>

        @php $lastReview = $project->reviews->firstWhere('stage', 'synopsis'); @endphp
        <div class="alert alert-{{ $project->status === \App\Models\Project::STATUS_REJECTED ? 'danger' : 'warning' }}">
            <div class="fw-semibold mb-1">
                <i class="bi bi-chat-left-text me-1"></i>
                {{ $project->status === \App\Models\Project::STATUS_REJECTED ? 'Rejected' : 'Correction required' }}
                @if($lastReview?->faculty?->user)
                    by {{ $lastReview->faculty->user->name }}
                @endif
            </div>
            @if($lastReview?->comments)
                <div>{{ $lastReview->comments }}</div>
            @else
                <div class="text-muted">No written comments were left — contact your guide if you are unsure what to change.</div>
            @endif
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('student.synopsis.update') }}">
                    @csrf
                    @method('PUT')
                    @include('student.synopsis._form')

                    <div class="mt-4">
                        <button class="btn btn-primary"><i class="bi bi-arrow-repeat me-1"></i> Resubmit for Review</button>
                        <a href="{{ route('student.project.show') }}" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Cancel</a>
                    </div>
                    <p class="text-muted small mt-3 mb-0">
                        Resubmitting sends your project back to <strong>Synopsis Under Review</strong> and notifies your guide.
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
