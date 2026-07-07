@extends('layouts.app')
@section('title', 'Reset Password')

@section('content')
<div class="auth-wrap d-flex justify-content-center align-items-center px-3 py-4">
    <div class="w-100" style="max-width:420px">
        <div class="text-center mb-3">
            <a href="{{ url('/') }}" class="text-decoration-none">
                <span class="app-avatar mx-auto mb-2" style="width:52px;height:52px;font-size:1.4rem"><i class="bi bi-key-fill"></i></span>
            </a>
            <h4 class="mb-1">Set a new password</h4>
            <p class="text-muted small mb-0">for <span class="fw-semibold">{{ $email }}</span></p>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                @include('partials.alerts')

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="mb-3">
                        @include('partials.password_field', [
                            'name' => 'password',
                            'label' => 'New Password',
                            'autocomplete' => 'new-password',
                        ])
                    </div>
                    <div class="mb-3">
                        @include('partials.password_field', [
                            'name' => 'password_confirmation',
                            'label' => 'Confirm New Password',
                            'autocomplete' => 'new-password',
                        ])
                    </div>
                    <button type="submit" class="btn btn-primary w-100 btn-lg"><i class="bi bi-shield-lock me-1"></i>Reset password</button>
                </form>
            </div>
        </div>

        <p class="text-center small mt-3 mb-0 text-muted">
            <a href="{{ route('login') }}" class="fw-semibold">Back to sign in</a>
        </p>
    </div>
</div>
@endsection
