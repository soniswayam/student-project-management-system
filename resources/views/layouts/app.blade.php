<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>

    {{-- Bootstrap 5 + Icons (CDN) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    {{-- App design system (loaded after Bootstrap so tokens & polish win) --}}
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}?v={{ filemtime(public_path('assets/css/theme.css')) }}">
    <meta name="theme-color" content="#2563eb">
    @stack('styles')
</head>
<body>
@auth
    @include('partials.navbar')
    <div class="container-fluid">
        <div class="row">
            {{-- Sidebar: static column on lg+, slide-in offcanvas below lg --}}
            <div class="offcanvas-lg offcanvas-start sidebar col-lg-2 col-md-3 p-0" tabindex="-1" id="sidebarMenu">
                <div class="offcanvas-header d-lg-none border-bottom">
                    <span class="fw-bold text-dark"><i class="bi bi-mortarboard-fill me-1 text-primary"></i> Menu</span>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body d-block p-2">
                    @include('partials.sidebar')
                </div>
            </div>
            <main class="col px-3 px-md-4 py-4">
                @include('partials.alerts')
                @yield('content')
            </main>
        </div>
    </div>
@else
    <main>
        @yield('content')
    </main>
@endauth

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('assets/js/app.js') }}?v={{ filemtime(public_path('assets/js/app.js')) }}"></script>
@stack('scripts')
</body>
</html>
