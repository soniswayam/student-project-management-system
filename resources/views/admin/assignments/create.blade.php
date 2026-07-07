@extends('layouts.app')
@section('title', 'New Assignment')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <h3 class="mb-1"><i class="bi bi-journal-plus me-2"></i>New Assignment</h3>
        <p class="text-muted">Post an assignment on behalf of a faculty. The chosen department's students are notified.</p>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.assignments.store') }}" enctype="multipart/form-data">
                    @include('admin.assignments._form', ['assignment' => null])
                    <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Create Assignment</button>
                    <a href="{{ route('admin.assignments.index') }}" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
