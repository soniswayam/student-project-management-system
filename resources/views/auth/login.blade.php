@extends('layouts.app')
@section('title', 'Login')

@section('content')
    <div class="auth-wrap d-flex justify-content-center align-items-center px-3 py-4">
        <div class="w-100" style="max-width:420px">
            <div class="text-center mb-3">
                <a href="{{ url('/') }}" class="text-decoration-none">
                    <span class="app-avatar mx-auto mb-2" style="width:52px;height:52px;font-size:1.4rem"><i
                            class="bi bi-mortarboard-fill"></i></span>
                </a>
                <h4 class="mb-1">Welcome back</h4>
                <p class="text-muted small mb-0">Sign in to the Student Project System</p>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    @include('partials.alerts')

                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                class="form-control @error('email') is-invalid @enderror" autocomplete="email"
                                inputmode="email" required autofocus>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label mb-0" for="password">Password</label>

                            </div>
                            <div class="input-group mt-1">
                                <input type="password" id="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    autocomplete="current-password" required>
                                <button class="btn btn-outline-secondary" type="button" data-password-toggle="password"
                                    aria-label="Show password" tabindex="-1">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <a href="{{ route('password.request') }}" class="small">Forgot password?</a>
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" name="remember" class="form-check-input" id="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-lg"><i class="bi bi-box-arrow-in-right me-1"></i>Sign in</button>
                    </form>
                </div>
            </div>

            <p class="text-center small mt-3 mb-0 text-muted">
                New student? <a href="{{ route('register') }}" class="fw-semibold">Create an account</a>
            </p>
        </div>
    </div>
@endsection