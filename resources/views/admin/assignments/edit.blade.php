@extends('layouts.app')
@section('title', 'Edit Assignment')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <h3 class="mb-1"><i class="bi bi-pencil-square me-2"></i>Edit Assignment</h3>
        <p class="text-muted">{{ $assignment->subject }} @if($assignment->assignment_no) · Assignment {{ $assignment->assignment_no }} @endif</p>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.assignments.update', $assignment) }}" enctype="multipart/form-data">
                    @method('PUT')
                    @include('admin.assignments._form', ['assignment' => $assignment])
                    <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Update Assignment</button>
                    <a href="{{ route('admin.assignments.index') }}" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
