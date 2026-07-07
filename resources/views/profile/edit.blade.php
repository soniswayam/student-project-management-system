@extends('layouts.app')
@section('title', 'My Profile')

@php
    $roleLabel = $user->role === 'super_admin' ? 'Super Admin' : ucfirst($user->role);
    $initials = collect(explode(' ', trim($user->name)))->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('');
@endphp

@section('content')
<div class="d-flex align-items-center gap-3 mb-4">
    <span class="app-avatar" style="width:52px;height:52px;font-size:1.3rem">{{ strtoupper($initials ?: 'U') }}</span>
    <div>
        <h4 class="mb-0"><i class="bi bi-person-gear me-2"></i>My Profile</h4>
        <span class="text-muted small">{{ $roleLabel }} · {{ $user->email }}</span>
    </div>
</div>

<div class="row g-4">
    {{-- Account details --}}
    <div class="col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-transparent"><i class="bi bi-person-vcard me-2"></i>Account details</div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="name">Full Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                                   class="form-control @error('name') is-invalid @enderror" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                                   class="form-control @error('email') is-invalid @enderror" inputmode="email" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        @if($user->isStudent() && $user->student)
                            <div class="col-md-6">
                                <label class="form-label" for="roll_no">Roll No</label>
                                <input type="text" id="roll_no" class="form-control" value="{{ $user->student->roll_no }}" disabled>
                                <div class="form-text">Managed by admin.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="department">Department</label>
                                <input type="text" id="department" class="form-control" value="{{ $user->student->department->name ?? '—' }}" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="semester">Semester</label>
                                <select id="semester" name="semester" class="form-select @error('semester') is-invalid @enderror" required>
                                    @foreach(range(1, 8) as $s)
                                        <option value="{{ $s }}" @selected((string) old('semester', $user->student->semester) === (string) $s)>{{ $s }}</option>
                                    @endforeach
                                </select>
                                @error('semester') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="phone">Phone <span class="text-muted">(optional)</span></label>
                                <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->student->phone) }}"
                                       class="form-control @error('phone') is-invalid @enderror" inputmode="tel">
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @elseif($user->isFaculty() && $user->faculty)
                            <div class="col-md-6">
                                <label class="form-label" for="department">Department</label>
                                <input type="text" id="department" class="form-control" value="{{ $user->faculty->department->name ?? '—' }}" disabled>
                                <div class="form-text">Managed by admin.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="designation">Designation</label>
                                <input type="text" id="designation" name="designation" value="{{ old('designation', $user->faculty->designation) }}"
                                       class="form-control @error('designation') is-invalid @enderror" required>
                                @error('designation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="phone">Phone <span class="text-muted">(optional)</span></label>
                                <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->faculty->phone) }}"
                                       class="form-control @error('phone') is-invalid @enderror" inputmode="tel">
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @else
                            <div class="col-md-6">
                                <label class="form-label" for="role">Role</label>
                                <input type="text" id="role" class="form-control" value="{{ $roleLabel }}" disabled>
                            </div>
                        @endif
                    </div>
                    <button type="submit" class="btn btn-primary mt-4"><i class="bi bi-check-lg me-1"></i>Save changes</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Change password --}}
    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-transparent"><i class="bi bi-shield-lock me-2"></i>Change password</div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        @include('partials.password_field', [
                            'name' => 'current_password',
                            'label' => 'Current Password',
                            'autocomplete' => 'current-password',
                        ])
                    </div>
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
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-key me-1"></i>Update password</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
