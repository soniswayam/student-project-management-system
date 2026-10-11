@php
    $isEdit = isset($project) && $project;
    $currentPartnerId = $isEdit
        ? optional($project->members->first(fn ($m) => $m->student_id !== $student->id))->student_id
        : null;

    $maxTeam = \App\Models\DegreeLevelSetting::groupSizeFor($student->department?->level);
    $allowsGroup = $maxTeam >= 2;
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="project_type">Project Type *</label>
        <select name="project_type" id="project_type" class="form-select @error('project_type') is-invalid @enderror" required>
            @php $typeValue = old('project_type', $isEdit ? $project->project_type : 'single'); @endphp
            <option value="single" @selected($typeValue === 'single' || ! $allowsGroup)>Single</option>
            @if($allowsGroup)
                <option value="group" @selected($typeValue === 'group')>Group (max {{ $maxTeam }} members)</option>
            @endif
        </select>
        @error('project_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
        @unless($allowsGroup)
            <div class="form-text">
                {{ $student->department?->levelLabel() ?? 'Your course' }} projects are done individually.
            </div>
        @endunless
    </div>
    <div class="col-md-6">
        <label class="form-label" for="name">Project Name *</label>
        <input id="name" name="name" value="{{ old('name', $isEdit ? $project->name : '') }}"
               class="form-control @error('name') is-invalid @enderror" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">You</label>
        <input class="form-control" value="{{ $student->user->name }} ({{ $student->roll_no }})" readonly>
        <small class="text-muted">You are submitting this project.</small>
    </div>

    <div class="col-md-6" id="partner_wrap">
        <label class="form-label" for="partner_search">Group Member *</label>

        <div class="input-group mb-2">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" id="partner_search" class="form-control"
                   placeholder="Search by name or roll number..." autocomplete="off">
        </div>

        <select name="partner_student_id" id="partner_student_id"
                class="form-select @error('partner_student_id') is-invalid @enderror" size="6">
            <option value="">— Select group member —</option>
            @foreach($availablePartners as $partner)
                <option value="{{ $partner->id }}"
                        data-search="{{ Str::lower($partner->user->name.' '.$partner->roll_no) }}"
                        @selected((int) old('partner_student_id', $currentPartnerId) === $partner->id)>
                    {{ $partner->roll_no }} — {{ $partner->user->name }}@if($partner->semester) · Sem {{ $partner->semester }}@endif
                </option>
            @endforeach
        </select>
        @error('partner_student_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

        <div id="partner_selected_label" class="mt-2"></div>

        <small class="text-muted d-block mt-1">
            Showing {{ $availablePartners->count() }} available
            {{ Str::plural('student', $availablePartners->count()) }} from
            <strong>{{ $student->department->name ?? 'your department' }}</strong> who are not already in a project.
        </small>
        @if($availablePartners->isEmpty())
            <div class="alert alert-warning py-2 px-3 mt-2 mb-0 small">
                No one in your department is free to partner right now — you can still submit a single project.
            </div>
        @endif
    </div>

    <div class="col-md-6">
        <label class="form-label" for="frontend_tech">Frontend Technology *</label>
        <input id="frontend_tech" name="frontend_tech" value="{{ old('frontend_tech', $isEdit ? $project->frontend_tech : '') }}"
               class="form-control @error('frontend_tech') is-invalid @enderror" placeholder="React, Bootstrap..." required>
        @error('frontend_tech') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="backend_tech">Backend Technology *</label>
        <input id="backend_tech" name="backend_tech" value="{{ old('backend_tech', $isEdit ? $project->backend_tech : '') }}"
               class="form-control @error('backend_tech') is-invalid @enderror" placeholder="Laravel, MySQL..." required>
        @error('backend_tech') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label" for="abstract">Abstract / Synopsis *</label>
        <textarea id="abstract" name="abstract" rows="6"
                  class="form-control @error('abstract') is-invalid @enderror"
                  placeholder="Describe your project idea, scope and objectives (min 30 characters)..."
                  required>{{ old('abstract', $isEdit ? $project->abstract : '') }}</textarea>
        @error('abstract') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const typeSelect = document.getElementById('project_type');
        const partnerWrap = document.getElementById('partner_wrap');
        const partnerSelect = document.getElementById('partner_student_id');
        const partnerSearch = document.getElementById('partner_search');
        const partnerLabel = document.getElementById('partner_selected_label');

        function togglePartner() {
            const isGroup = typeSelect.value === 'group';
            partnerWrap.style.display = isGroup ? '' : 'none';
            partnerSelect.required = isGroup;
            if (!isGroup) {
                partnerSelect.value = '';
                updatePartnerLabel();
            }
        }
        typeSelect.addEventListener('change', togglePartner);
        togglePartner();

        function updatePartnerLabel() {
            const chosen = partnerSelect.options[partnerSelect.selectedIndex];
            partnerLabel.innerHTML = (chosen && chosen.value)
                ? '<span class="badge bg-success"><i class="bi bi-check-lg me-1"></i>Selected: ' + chosen.textContent.trim() + '</span>'
                : '';
        }
        partnerSelect.addEventListener('change', updatePartnerLabel);
        updatePartnerLabel();

        partnerSearch.addEventListener('input', function () {
            const term = this.value.trim().toLowerCase();
            partnerSelect.querySelectorAll('option[data-search]').forEach(function (opt) {
                opt.hidden = term !== '' && !opt.dataset.search.includes(term);
            });
        });
    })();
</script>
@endpush
