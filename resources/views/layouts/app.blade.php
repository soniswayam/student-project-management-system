<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}?v={{ filemtime(public_path('assets/css/theme.css')) }}">
    <meta name="theme-color" content="#2563eb">
    @stack('styles')
</head>
<body>
@auth
    <div class="app-shell">

        <aside class="offcanvas-lg offcanvas-start sidebar app-sidebar no-print" tabindex="-1" id="sidebarMenu" aria-label="Main menu">
            <div class="sidebar-brand">
                @include('partials.college_brand')
                <button type="button" class="btn-close d-lg-none ms-2" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close menu"></button>
            </div>
            <nav class="sidebar-nav">
                @include('partials.sidebar')
            </nav>
            @include('partials.sidebar_user')
        </aside>

        <div class="app-main">
            @include('partials.navbar')
            <main class="app-content">
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
