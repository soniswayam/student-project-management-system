@extends('layouts.app')
@section('title', 'Submit Synopsis')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <h3 class="mb-1"><i class="bi bi-file-earmark-text me-2"></i>Submit Synopsis</h3>
        <p class="text-muted">Step 1 — your project begins here. Fields marked * are required.</p>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('student.synopsis.store') }}">
                    @csrf
                    @include('student.synopsis._form', ['project' => null])

                    <div class="mt-4">
                        <button class="btn btn-primary"><i class="bi bi-send me-1"></i> Submit Synopsis</button>
                        <a href="{{ route('student.dashboard') }}" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
