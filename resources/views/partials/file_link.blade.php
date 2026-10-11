@php
    $path = $path ?? null;
    $icon = $icon ?? 'file-earmark';

    $url = $path ? ($url ?? asset('storage/'.$path)) : null;
    $extension = $path ? strtolower(pathinfo($path, PATHINFO_EXTENSION)) : null;

    $previewable = in_array($extension, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'txt'], true);
    $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);

    $fullPath = $path ? collect([storage_path('app/private/'.$path), storage_path('app/public/'.$path)])->first(fn ($p) => is_file($p)) : null;
    $size = $fullPath ? filesize($fullPath) : null;
    $sizeLabel = $size ? ($size >= 1048576
        ? number_format($size / 1048576, 1).' MB'
        : number_format($size / 1024).' KB') : null;
@endphp

<li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-2 flex-wrap">
    <span>
        <i class="bi bi-{{ $icon }} me-1"></i> {{ $label }}
        @if($extension)
            <span class="badge bg-light text-dark border ms-1 text-uppercase">{{ $extension }}</span>
        @endif
        @if($sizeLabel)
            <span class="text-muted small ms-1">{{ $sizeLabel }}</span>
        @endif
    </span>

    @if($url)
        <span class="d-flex gap-2 align-items-center">
            @if($previewable)
                <a href="{{ $url }}"
                   class="btn btn-sm btn-outline-secondary"
                   data-file-preview
                   data-preview-kind="{{ $isImage ? 'image' : 'doc' }}"
                   data-preview-title="{{ $label }}">
                    <i class="bi bi-eye me-1"></i>Preview
                </a>
            @endif
            <a href="{{ $url }}" download class="btn btn-sm btn-outline-primary">
                <i class="bi bi-download me-1"></i>Download
            </a>
        </span>
    @else
        <span class="text-muted">—</span>
    @endif
</li>
