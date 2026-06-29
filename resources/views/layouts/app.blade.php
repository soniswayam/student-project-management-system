<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>

    {{-- Bootstrap 5 + Icons (CDN) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { background:#f4f6f9; }
        .sidebar { min-height: calc(100vh - 56px); background:#1e293b; }
        .sidebar .nav-link { color:#cbd5e1; border-radius:.4rem; margin:.15rem .5rem; }
        .sidebar .nav-link:hover { background:#334155; color:#fff; }
        .sidebar .nav-link.active { background:#2563eb; color:#fff; }
        .stat-card { border:none; border-radius:.75rem; }
        .stat-card .display-6 { font-weight:700; }
        .card { border:none; box-shadow:0 1px 3px rgba(0,0,0,.08); border-radius:.6rem; }
    </style>
    @stack('styles')
</head>
<body>
@auth
    @include('partials.navbar')
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block sidebar p-2">
                @include('partials.sidebar')
            </nav>
            <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
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
@stack('scripts')
</body>
</html>
