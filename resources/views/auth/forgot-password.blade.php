@extends('layouts.app')
@section('title', 'Forgot Password')

@section('content')
<div class="auth-wrap d-flex justify-content-center align-items-center px-3 py-4">
    <div class="w-100" style="max-width:420px">
        <div class="text-center mb-3">
            <a href="{{ url('/') }}" class="text-decoration-none">
                <span class="app-avatar mx-auto mb-2" style="width:52px;height:52px;font-size:1.4rem"><i class="bi bi-shield-lock-fill"></i></span>
            </a>
            <h4 class="mb-1">Forgot your password?</h4>
            <p class="text-muted small mb-0">We'll email you a link to set a new one</p>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                @include('partials.alerts')

                @if(session('reset_url_preview'))

                    <div class="alert alert-warning small">
                        <div class="fw-semibold mb-1">
                            <i class="bi bi-tools me-1"></i>Local mode — no mail server configured
                        </div>
                        <p class="mb-2">Email delivery is set to <code>log</code>, so use this link directly:</p>
                        <a href="{{ session('reset_url_preview') }}" class="btn btn-sm btn-warning w-100">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Open reset link
                        </a>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               class="form-control @error('email') is-invalid @enderror"
                               autocomplete="email" inputmode="email" required autofocus>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <button type="submit" class="btn btn-primary w-100 btn-lg"><i class="bi bi-envelope me-1"></i>Continue</button>
                </form>
            </div>
        </div>

        <p class="text-center small mt-3 mb-0 text-muted">
            Remembered it? <a href="{{ route('login') }}" class="fw-semibold">Back to sign in</a>
        </p>
    </div>
</div>
@endsection
