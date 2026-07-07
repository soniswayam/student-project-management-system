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
        </div>
    </div>

    <div class="mt-4">
        <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Save Settings</button>
    </div>
</form>
@endsection
