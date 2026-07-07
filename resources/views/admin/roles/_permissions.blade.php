{{-- Permission checkbox grid. Expects: $catalog (grouped), $selected (array of keys) --}}
<div class="row g-3">
    @foreach($catalog as $group => $permissions)
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold py-2">
                    <i class="bi bi-collection me-1"></i>{{ $group }}
                </div>
                <div class="card-body py-2">
                    @foreach($permissions as $key => $label)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]"
                                   value="{{ $key }}" id="perm_{{ $key }}"
                                   @checked(in_array($key, $selected, true))>
                            <label class="form-check-label" for="perm_{{ $key }}">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>
