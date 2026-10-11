@extends('layouts.app')
@section('title', 'College Settings')

@section('content')
<h3 class="mb-3"><i class="bi bi-gear me-2"></i>College Settings</h3>
<p class="text-muted">These details appear on every generated PDF, certificate and printout.</p>

<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">College / Institute Name</label>
                            <input name="name" value="{{ old('name', $setting->name) }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tagline / Department</label>
                            <input name="tagline" value="{{ old('tagline', $setting->tagline) }}" class="form-control" placeholder="Department of Computer Applications">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Affiliation / University</label>
                            <input name="affiliation" value="{{ old('affiliation', $setting->affiliation) }}" class="form-control" placeholder="Affiliated to XYZ University">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Address</label>
                            <input name="address" value="{{ old('address', $setting->address) }}" class="form-control" placeholder="College Road, City, State - 000000">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" value="{{ old('email', $setting->email) }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Phone</label>
                            <input name="phone" value="{{ old('phone', $setting->phone) }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Website</label>
                            <input name="website" value="{{ old('website', $setting->website) }}" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-white fw-semibold">College Logo</div>
                <div class="card-body text-center">
                    @if($setting->logo_path)
                        <img src="{{ Storage::url($setting->logo_path) }}" alt="Logo" class="img-fluid mb-3" style="max-height:120px">
                    @else
                        <div class="text-muted mb-3"><i class="bi bi-image fs-1"></i><br>No logo uploaded</div>
                    @endif
                    <input type="file" name="logo" class="form-control" accept="image/png,image/jpeg,image/svg+xml">
                    <div class="form-text">PNG/JPG/SVG, up to 2 MB. Appears on PDFs &amp; certificates.</div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-hourglass-split me-1"></i> Late Submission Grace
                </div>
                <div class="card-body">
                    <p class="text-muted small">
                        After a due date passes, students can still submit for this many hours.
                        Anything handed in during that time is accepted but flagged
                        <span class="badge bg-warning text-dark">Late</span>. After it, the form closes.
                    </p>

                    <div class="mb-3">
                        <label class="form-label" for="project_grace_hours">Project submissions (hours)</label>
                        <input id="project_grace_hours" type="number" name="project_grace_hours" min="0" max="168"
                               value="{{ old('project_grace_hours', $setting->project_grace_hours ?? 12) }}"
                               class="form-control @error('project_grace_hours') is-invalid @enderror" required>
                        @error('project_grace_hours') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">Default 12 hours.</div>
                    </div>

                    <div>
                        <label class="form-label" for="assignment_grace_hours">Assignment submissions (hours)</label>
                        <input id="assignment_grace_hours" type="number" name="assignment_grace_hours" min="0" max="168"
                               value="{{ old('assignment_grace_hours', $setting->assignment_grace_hours ?? 6) }}"
                               class="form-control @error('assignment_grace_hours') is-invalid @enderror" required>
                        @error('assignment_grace_hours') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">Default 6 hours. Set 0 to close exactly on the due date.</div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-fingerprint me-1"></i> Duplicate detection
                </div>
                <div class="card-body">
                    <p class="text-muted small">
                        Synopses and reports are compared against other submissions held in this system,
                        including archived years. Scores are evidence for a human, not a verdict.
                    </p>

                    <div class="mb-3">
                        <label class="form-label" for="duplicate_warn_percent">Warn at (%)</label>
                        <input id="duplicate_warn_percent" type="number" name="duplicate_warn_percent" min="1" max="100"
                               value="{{ old('duplicate_warn_percent', $setting->duplicate_warn_percent ?? 60) }}"
                               class="form-control @error('duplicate_warn_percent') is-invalid @enderror" required>
                        @error('duplicate_warn_percent') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">Submission goes through, but the student and guide are told. Default 60%.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="duplicate_block_percent">Block at (%)</label>
                        <input id="duplicate_block_percent" type="number" name="duplicate_block_percent" min="1" max="100"
                               value="{{ old('duplicate_block_percent', $setting->duplicate_block_percent ?? 85) }}"
                               class="form-control @error('duplicate_block_percent') is-invalid @enderror" required>
                        @error('duplicate_block_percent') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">Synopsis is refused outright. Default 85%. Set 100 to never block.</div>
                    </div>

                    <div>
                        <label class="form-label" for="reminder_lead_days">Reminder lead time (days)</label>
                        <input id="reminder_lead_days" type="number" name="reminder_lead_days" min="0" max="60"
                               value="{{ old('reminder_lead_days', $setting->reminder_lead_days ?? 3) }}"
                               class="form-control @error('reminder_lead_days') is-invalid @enderror" required>
                        @error('reminder_lead_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">How far ahead of a due date students are reminded. Default 3 days.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Save Settings</button>
    </div>
</form>

<hr class="my-4">

<form method="POST" action="{{ route('admin.settings.levels') }}">
    @csrf @method('PUT')

    <h5 class="mb-1"><i class="bi bi-mortarboard me-2"></i>Graduation &amp; Post-Graduation</h5>
    <p class="text-muted small">
        Project rules that differ by degree level. A department's own deadline, and a per-project
        extension, both still win over the level's date.
    </p>

    <div class="row g-3">
        @foreach($levels as $key => $level)
            @php $isPg = $key === 'post_graduation'; @endphp
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header bg-white fw-semibold">
                        <span class="badge bg-{{ $isPg ? 'primary' : 'info' }} me-1">{{ $isPg ? 'PG' : 'UG' }}</span>
                        {{ $isPg ? 'Post-Graduation' : 'Graduation' }}
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label" for="{{ $key }}_group">Maximum team size</label>
                            <input id="{{ $key }}_group" type="number" min="1" max="10" required
                                   name="levels[{{ $key }}][max_group_size]"
                                   value="{{ old("levels.{$key}.max_group_size", $level->max_group_size) }}"
                                   class="form-control">
                            <div class="form-text">Set 1 to make projects at this level individual only.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="{{ $key }}_marks">
                                Maximum marks <span class="text-muted fw-normal">(optional)</span>
                            </label>
                            <input id="{{ $key }}_marks" type="number" min="1" max="1000"
                                   name="levels[{{ $key }}][max_marks]"
                                   value="{{ old("levels.{$key}.max_marks", $level->max_marks) }}"
                                   class="form-control" placeholder="Leave blank — not marked">
                            <div class="form-text">
                                Blank means this level is <strong>not marked</strong> — no marks field for
                                faculty, and nothing about marks on the project page, report or certificate.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="{{ $key }}_deadline">
                                Default project deadline <span class="text-muted fw-normal">(optional)</span>
                            </label>
                            <input id="{{ $key }}_deadline" type="datetime-local"
                                   name="levels[{{ $key }}][submission_deadline]"
                                   value="{{ old("levels.{$key}.submission_deadline", $level->submission_deadline?->format('Y-m-d\TH:i')) }}"
                                   class="form-control">
                            <div class="form-text">Used only when the department has no date of its own.</div>
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="levels[{{ $key }}][viva_required]" value="0">
                            <input class="form-check-input" type="checkbox" value="1"
                                   id="{{ $key }}_viva" name="levels[{{ $key }}][viva_required]"
                                   @checked(old("levels.{$key}.viva_required", $level->viva_required))>
                            <label class="form-check-label" for="{{ $key }}_viva">Viva expected at this level</label>
                            <div class="form-text">
                                Projects with no viva scheduled are flagged. It never blocks anything.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-3">
        <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Save Level Rules</button>
    </div>
</form>
@endsection
