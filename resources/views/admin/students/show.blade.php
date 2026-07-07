@extends('layouts.app')
@section('title', 'Student Details')

@php
    $statusMeta = [
        'active'  => ['label' => 'Active',  'class' => 'bg-success'],
        'pending' => ['label' => 'Pending', 'class' => 'bg-warning text-dark'],
        'blocked' => ['label' => 'Blocked', 'class' => 'bg-danger'],
    ];
    $status = $student->user->status ?? 'active';
    $meta = $statusMeta[$status] ?? $statusMeta['active'];
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-person-vcard me-2"></i>Student Details</h3>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
        @can('students.edit')
            <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-primary"><i class="bi bi-pencil"></i> Edit</a>
        @endcan
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6"><small class="text-muted d-block">Name</small><strong>{{ $student->user->name }}</strong></div>
            <div class="col-md-6"><small class="text-muted d-block">Email</small><strong>{{ $student->user->email }}</strong></div>
            <div class="col-md-4"><small class="text-muted d-block">Roll No</small><span class="badge bg-secondary">{{ $student->roll_no }}</span></div>
            <div class="col-md-4"><small class="text-muted d-block">Department</small><strong>{{ $student->department?->name ?? '—' }}</strong></div>
            <div class="col-md-4"><small class="text-muted d-block">Semester</small><strong>{{ $student->semester ?? '—' }}</strong></div>
            <div class="col-md-4"><small class="text-muted d-block">Phone</small><strong>{{ $student->phone ?? '—' }}</strong></div>
            <div class="col-md-4"><small class="text-muted d-block">Status</small><span class="badge {{ $meta['class'] }}">{{ $meta['label'] }}</span></div>
            <div class="col-md-4"><small class="text-muted d-block">Registered</small><strong>{{ $student->created_at?->format('d M Y, H:i') }}</strong></div>
            <div class="col-12">
                <small class="text-muted d-block">Project</small>
                @if($student->membership?->project)
                    <a href="{{ route('admin.projects.show', $student->membership->project) }}">{{ $student->membership->project->name }}</a>
                @else
                    <span class="text-muted">Not linked to any project</span>
                @endif
            </div>
        </div>

        @can('students.edit')
            <hr>
            <div class="d-flex gap-2 flex-wrap">
                @if($status !== 'active')
                    <form action="{{ route('admin.students.status', $student) }}" method="POST">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="active">
                        <button class="btn btn-success btn-sm"><i class="bi bi-check-lg"></i> {{ $status === 'pending' ? 'Approve' : 'Activate' }}</button>
                    </form>
                @endif
                @if($status !== 'blocked')
                    <form action="{{ route('admin.students.status', $student) }}" method="POST" onsubmit="return confirm('Block this student?')">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="blocked">
                        <button class="btn btn-outline-warning btn-sm"><i class="bi bi-slash-circle"></i> Block</button>
                    </form>
                @endif
            </div>
        @endcan
    </div>
</div>
@endsection
