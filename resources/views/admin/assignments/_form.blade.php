@csrf
@php $a = $assignment ?? null; @endphp

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Faculty *</label>
        <select name="faculty_id" class="form-select @error('faculty_id') is-invalid @enderror" required>
            <option value="">— Select faculty —</option>
            @foreach($faculties as $f)
                <option value="{{ $f->id }}" {{ old('faculty_id', $a?->faculty_id) == $f->id ? 'selected' : '' }}>
                    {{ $f->user?->name ?? 'Unknown' }}@if($f->department) ({{ $f->department->name }})@endif
                </option>
            @endforeach
        </select>
        @error('faculty_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Department (students who submit) *</label>
        <select name="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
            <option value="">— Select department —</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}" {{ old('department_id', $a?->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
            @endforeach
        </select>
        @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Subject *</label>
        <input type="text" name="subject" value="{{ old('subject', $a?->subject) }}" list="subjectList"
               class="form-control @error('subject') is-invalid @enderror" placeholder="e.g. Database Management (DBMS)" required>
        <datalist id="subjectList">
            @foreach($subjects as $s)<option value="{{ $s }}">@endforeach
        </datalist>
        @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Assignment No.</label>
        <input type="number" name="assignment_no" value="{{ old('assignment_no', $a?->assignment_no) }}" min="1" max="99"
               class="form-control @error('assignment_no') is-invalid @enderror" placeholder="e.g. 1">
        @error('assignment_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Type *</label>
        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
            @foreach(\App\Models\Assignment::TYPES as $t)
                <option value="{{ $t }}" {{ old('type', $a?->type ?? 'Theory') === $t ? 'selected' : '' }}>{{ $t }}</option>
            @endforeach
        </select>
        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Title *</label>
    <input type="text" name="title" value="{{ old('title', $a?->title) }}" class="form-control @error('title') is-invalid @enderror" required>
    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label">Description / Instructions</label>
    <textarea name="description" rows="4" class="form-control">{{ old('description', $a?->description) }}</textarea>
</div>

<div class="row">
    <div class="col-md-7 mb-3">
        <label class="form-label">Reference file (PDF/DOC/ZIP)</label>
        <input type="file" name="attachment" class="form-control" accept=".pdf,.doc,.docx,.zip">
        @if($a?->attachment_path)
            <small class="text-success">Current file uploaded ✓ — upload a new one to replace it.</small>
        @endif
    </div>
    <div class="col-md-5 mb-3">
        <label class="form-label">Due date</label>
        <input type="datetime-local" name="due_date" value="{{ old('due_date', $a?->due_date?->format('Y-m-d\TH:i')) }}" class="form-control">
    </div>
</div>
