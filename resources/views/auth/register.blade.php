@extends('layouts.app')
@section('title', 'Register')

@section('content')
<div class="auth-wrap d-flex justify-content-center align-items-center px-3 py-4">
    <div class="w-100" style="max-width:560px">
        <div class="text-center mb-3">
            <a href="{{ url('/') }}" class="text-decoration-none">
                <span class="app-avatar mx-auto mb-2" style="width:52px;height:52px;font-size:1.4rem"><i class="bi bi-person-plus-fill"></i></span>
            </a>
            <h4 class="mb-1">Create your student account</h4>
            <p class="text-muted small mb-0">It only takes a minute to get started</p>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                @include('partials.alerts')

                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="name">Full Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control" autocomplete="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="roll_no">Roll No</label>
                            <input type="text" id="roll_no" name="roll_no" value="{{ old('roll_no') }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" autocomplete="email" inputmode="email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="department_id">Department</label>
                            <select id="department_id" name="department_id" class="form-select" required>
                                <option value="">— Select —</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="semester">Semester</label>
                            <select id="semester" name="semester" class="form-select" required>
                                <option value="">— Select —</option>
                                @foreach(range(1, 8) as $s)
                                    <option value="{{ $s }}" @selected(old('semester') == $s)>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="phone">Phone <span class="text-muted">(optional)</span></label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" class="form-control" autocomplete="tel" inputmode="tel">
                        </div>
                        <div class="col-md-6">
                            @include('partials.password_field', [
                                'name' => 'password',
                                'label' => 'Password',
                                'autocomplete' => 'new-password',
                            ])
                        </div>
                        <div class="col-md-6">
                            @include('partials.password_field', [
                                'name' => 'password_confirmation',
                                'label' => 'Confirm Password',
                                'autocomplete' => 'new-password',
                            ])
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 btn-lg mt-4"><i class="bi bi-person-plus me-1"></i>Create account</button>
                </form>
            </div>
        </div>

        <p class="text-center small mt-3 mb-0 text-muted">
            Already registered? <a href="{{ route('login') }}" class="fw-semibold">Sign in</a>
        </p>
    </div>
</div>
@endsection
