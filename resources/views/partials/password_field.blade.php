{{--
    Reusable password input with a show/hide eye toggle.
    Params: name (required), id, label, autocomplete, required (bool), optional (bool)
    The toggle is wired by the global [data-password-toggle] script in layouts.app.
--}}
@php
    $id = $id ?? $name;
    $label = $label ?? 'Password';
    $autocomplete = $autocomplete ?? 'current-password';
    $required = $required ?? true;
    $optional = $optional ?? false;
@endphp
<label class="form-label" for="{{ $id }}">
    {{ $label }}
    @if($optional) <span class="text-muted">(optional)</span> @endif
</label>
<div class="input-group">
    <input type="password" id="{{ $id }}" name="{{ $name }}"
           class="form-control @error($name) is-invalid @enderror"
           autocomplete="{{ $autocomplete }}" @if($required) required @endif>
    <button class="btn btn-outline-secondary" type="button"
            data-password-toggle="{{ $id }}" aria-label="Show password" tabindex="-1">
        <i class="bi bi-eye"></i>
    </button>
    @error($name) <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
