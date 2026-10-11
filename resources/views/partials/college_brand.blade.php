@php
    $collegeName = config('college.name') ?: config('app.name');
    $logoPath = config('college.logo');
    $logoUrl = $logoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($logoPath)
        ? asset('storage/' . $logoPath) . '?v=' . \Illuminate\Support\Facades\Storage::disk('public')->lastModified($logoPath)
        : null;
@endphp
<a class="college-brand {{ $logoUrl ? 'has-logo' : '' }}" href="{{ route('dashboard') }}" title="{{ $collegeName }}">
    <span class="college-logo">
        @if($logoUrl)
            <img src="{{ $logoUrl }}" alt="{{ $collegeName }} logo">
        @else
            <i class="bi bi-mortarboard-fill" aria-hidden="true"></i>
        @endif
    </span>

</a>
