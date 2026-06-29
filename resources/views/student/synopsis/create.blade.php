@extends('layouts.app')
@section('title', 'Submit Synopsis')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <h3 class="mb-1">Submit Synopsis</h3>
        <p class="text-muted">Step 1 — your project begins here. Fields marked * are required.</p>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('student.synopsis.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Project Type *</label>
                            <select name="project_type" id="project_type" class="form-select" required>
                                <option value="single" @selected(old('project_type')==='single')>Single</option>
                                <option value="group" @selected(old('project_type')==='group')>Group (max 2 members)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Project Name *</label>
                            <input name="name" value="{{ old('name') }}" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Leader Student</label>
                            <input class="form-control" value="{{ $student->user->name }} ({{ $student->roll_no }})" readonly>
                            <small class="text-muted">You are the project leader.</small>
                        </div>
                        <div class="col-md-6" id="partner_wrap">
                            <label class="form-label">Partner Student *</label>
                            <select name="partner_student_id" class="form-select">
                                <option value="">— Select Partner —</option>
                                @foreach($availablePartners as $partner)
                                    <option value="{{ $partner->id }}" @selected(old('partner_student_id')==$partner->id)>
                                        {{ $partner->user->name }} ({{ $partner->roll_no }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Only students without a project are listed.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Frontend Technology *</label>
                            <input name="frontend_tech" value="{{ old('frontend_tech') }}" class="form-control" placeholder="React, Bootstrap..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Backend Technology *</label>
                            <input name="backend_tech" value="{{ old('backend_tech') }}" class="form-control" placeholder="Laravel, MySQL..." required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Abstract / Synopsis *</label>
                            <textarea name="abstract" rows="6" class="form-control" placeholder="Describe your project idea, scope and objectives (min 30 characters)..." required>{{ old('abstract') }}</textarea>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button class="btn btn-primary"><i class="bi bi-send me-1"></i> Submit Synopsis</button>
                        <a href="{{ route('student.dashboard') }}" class="btn btn-link">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const typeSelect = document.getElementById('project_type');
    const partnerWrap = document.getElementById('partner_wrap');
    const partnerSelect = partnerWrap.querySelector('select');

    function togglePartner() {
        const isGroup = typeSelect.value === 'group';
        partnerWrap.style.display = isGroup ? '' : 'none';
        partnerSelect.required = isGroup;
        if (!isGroup) partnerSelect.value = '';
    }
    typeSelect.addEventListener('change', togglePartner);
    togglePartner();
</script>
@endpush
