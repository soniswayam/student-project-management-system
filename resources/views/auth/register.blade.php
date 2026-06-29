@extends('layouts.app')
@section('title', 'Register')

@section('content')
<div class="d-flex justify-content-center align-items-center py-5" style="min-height:100vh">
    <div class="card shadow p-2" style="width:100%;max-width:520px">
        <div class="card-body">
            <div class="text-center mb-3">
                <i class="bi bi-person-plus-fill display-5 text-primary"></i>
                <h4 class="mt-2">Student Registration</h4>
            </div>

            @include('partials.alerts')

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Roll No</label>
                        <input type="text" name="roll_no" value="{{ old('roll_no') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Department</label>
                        <select name="department_id" class="form-select" required>
                            <option value="">— Select —</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone <span class="text-muted">(optional)</span></label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 mt-4">Register</button>
            </form>

            <p class="text-center small mt-3 mb-0">
                Already registered? <a href="{{ route('login') }}">Sign in</a>
            </p>
        </div>
    </div>
</div>
@endsection
