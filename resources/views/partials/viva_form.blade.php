@php $viva = $project->viva; @endphp

<div class="card mb-3">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-mic me-1"></i> {{ $viva ? 'Reschedule Viva' : 'Schedule Viva' }}
        <span class="text-muted fw-normal small">(optional)</span>
    </div>
    <div class="card-body">
        @unless($viva)
            <p class="text-muted small">
                No viva is scheduled. Leave it that way if this project does not need one —
                nothing about vivas shows to the student until you set a slot.
            </p>
        @endunless

        <form method="POST" action="{{ route('faculty.projects.viva.store', $project) }}">
            @csrf
            <div class="row g-2">
                <div class="col-md-6 mb-2">
                    <label class="form-label" for="viva_at">Date &amp; time</label>
                    <input id="viva_at" type="datetime-local" name="scheduled_at" required
                           class="form-control @error('scheduled_at') is-invalid @enderror"
                           value="{{ old('scheduled_at', $viva?->scheduled_at?->format('Y-m-d\TH:i')) }}">
                    @error('scheduled_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 mb-2">
                    <label class="form-label" for="viva_mode">Mode</label>
                    <select id="viva_mode" name="mode" class="form-select" data-viva-mode>
                        <option value="offline" @selected(old('mode', $viva?->mode) === 'offline')>In person</option>
                        <option value="online" @selected(old('mode', $viva?->mode) === 'online')>Online</option>
                    </select>
                </div>

                <div class="col-md-6 mb-2">
                    <label class="form-label" for="viva_venue">Venue</label>
                    <input id="viva_venue" name="venue" class="form-control" placeholder="e.g. Lab 3"
                           value="{{ old('venue', $viva?->venue) }}">
                </div>

                <div class="col-md-6 mb-2">
                    <label class="form-label" for="viva_link">Meeting link</label>
                    <input id="viva_link" name="meeting_link" type="url" placeholder="https://…"
                           class="form-control @error('meeting_link') is-invalid @enderror"
                           value="{{ old('meeting_link', $viva?->meeting_link) }}">
                    @error('meeting_link') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text">Required for an online viva.</div>
                </div>

                <div class="col-12 mb-2">
                    <label class="form-label" for="viva_panel">Additional panel members</label>
                    <select id="viva_panel" name="panelists[]" class="form-select" multiple size="4">
                        @foreach($panelChoices as $choice)
                            <option value="{{ $choice->id }}"
                                @selected(in_array($choice->id, old('panelists', $viva?->panelists->pluck('id')->all() ?? [])))>
                                {{ $choice->user?->name }}{{ $choice->designation ? ' — '.$choice->designation : '' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">You are always on the panel. Hold Ctrl/Cmd to pick more than one.</div>
                </div>

                <div class="col-12 mb-2">
                    <label class="form-label" for="viva_notes">Notes</label>
                    <textarea id="viva_notes" name="notes" rows="2" class="form-control"
                              placeholder="Anything the student should bring or prepare">{{ old('notes', $viva?->notes) }}</textarea>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap mt-2">
                <button class="btn btn-primary">
                    <i class="bi bi-calendar-check me-1"></i>{{ $viva ? 'Save changes' : 'Schedule viva' }}
                </button>
            </div>
        </form>

        @if($viva)
            <hr>
            <div class="d-flex gap-2 flex-wrap">
                <form method="POST" action="{{ route('faculty.projects.viva.status', $project) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="completed">
                    <button class="btn btn-sm btn-outline-success" {{ $viva->isCompleted() ? 'disabled' : '' }}>
                        <i class="bi bi-check2-circle me-1"></i>Mark completed
                    </button>
                </form>
                <form method="POST" action="{{ route('faculty.projects.viva.status', $project) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="cancelled">
                    <button class="btn btn-sm btn-outline-warning" {{ $viva->isCancelled() ? 'disabled' : '' }}>
                        <i class="bi bi-x-circle me-1"></i>Cancel viva
                    </button>
                </form>
                <form method="POST" action="{{ route('faculty.projects.viva.destroy', $project) }}"
                      onsubmit="return confirm('Remove the viva slot entirely?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash me-1"></i>Remove slot
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
