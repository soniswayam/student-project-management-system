@extends('layouts.app')
@section('title', 'New Assignment')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <h3 class="mb-1"><i class="bi bi-journal-plus me-2"></i>New Assignment</h3>
        <p class="text-muted">Post an assignment for one of your departments/courses. All its students are notified.</p>

        @if($departments->isEmpty())
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-1"></i>
                You are not assigned to any department/course yet. Please ask an administrator to assign you before creating assignments.
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('faculty.assignments.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Department *</label>
                            <select name="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                                <option value="">— Select department —</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}"
                                        {{ old('department_id', $faculty->department_id) == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Subject *</label>
                            <input type="text" name="subject" value="{{ old('subject') }}" list="subjectList"
                                   class="form-control @error('subject') is-invalid @enderror" placeholder="e.g. Database Management (DBMS)" required>
                            <datalist id="subjectList">
                                @foreach($subjects as $s)<option value="{{ $s }}">@endforeach
                            </datalist>
                            @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Assignment No.</label>
                            <input type="number" name="assignment_no" value="{{ old('assignment_no') }}" min="1" max="99"
                                   class="form-control @error('assignment_no') is-invalid @enderror" placeholder="e.g. 1">
                            @error('assignment_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Type *</label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                @foreach(\App\Models\Assignment::TYPES as $t)
                                    <option value="{{ $t }}" {{ old('type', 'Theory') === $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Due date</label>
                            <input type="datetime-local" name="due_date" value="{{ old('due_date') }}" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description / Instructions</label>
                        <textarea name="description" rows="4" class="form-control">{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reference file (PDF/DOC/ZIP)</label>
                        <input type="file" name="attachment" class="form-control" accept=".pdf,.doc,.docx,.zip">
                    </div>

                    <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Create Assignment</button>
                    <a href="{{ route('faculty.assignments.index') }}" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
