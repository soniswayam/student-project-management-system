{{-- Print-only college letterhead. Pass $docTitle. Hidden on screen, shown when printing. --}}
@php $college = config('college'); $logoPath = config('college.logo'); @endphp
<div class="print-only print-lh">
    @if($logoPath && file_exists(storage_path('app/public/'.$logoPath)))
        <img src="{{ asset('storage/'.$logoPath) }}" alt="logo" style="max-height:56px;max-width:70px;vertical-align:middle;">
    @else
        <span class="logo">{{ strtoupper(substr($college['name'], 0, 1)) }}</span>
    @endif
    <div class="cname">{{ strtoupper($college['name']) }}</div>
    <div class="ctag">{{ $college['tagline'] }}</div>
    <div class="caddr">{{ $college['address'] }}@if(!empty($college['affiliation'])) &middot; {{ $college['affiliation'] }}@endif</div>
</div>
<div class="print-only print-doc-title">{{ $docTitle ?? 'Report' }}</div>
<div class="print-only print-doc-meta">Printed on {{ now()->format('d M Y, H:i') }}</div>
