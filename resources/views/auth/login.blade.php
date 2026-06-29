@extends('layouts.app')
@section('title', 'Login')

@section('content')
<div class="d-flex justify-content-center align-items-center" style="min-height:100vh">
    <div class="card shadow p-2" style="width:100%;max-width:420px">
        <div class="card-body">
            <div class="text-center mb-3">
                <i class="bi bi-mortarboard-fill display-5 text-primary"></i>
                <h4 class="mt-2">Sign in</h4>
                <p class="text-muted small mb-0">Student Project Submission System</p>
            </div>

            @include('partials.alerts')

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>

            <p class="text-center small mt-3 mb-0">
                New student? <a href="{{ route('register') }}">Create an account</a>
            </p>
        </div>
    </div>
</div>
@endsection
