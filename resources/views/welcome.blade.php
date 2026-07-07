<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <meta name="theme-color" content="#2563eb">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}?v={{ filemtime(public_path('assets/css/theme.css')) }}">
    <style>
        .landing-hero {
            background:
                radial-gradient(1200px 500px at 50% -10%, #e8f0ff 0%, rgba(232, 240, 255, 0) 60%),
                var(--color-canvas);
        }
        .hero-badge {
            display: inline-flex; align-items: center; gap: .4rem;
            background: #fff; border: 1px solid var(--color-border);
            color: var(--color-primary-700); font-weight: 600; font-size: .82rem;
            padding: .4rem .85rem; border-radius: 999px; box-shadow: var(--shadow-xs);
        }
        .hero-title {
            font-weight: 700; letter-spacing: -.03em; line-height: 1.08;
            font-size: clamp(2.1rem, 6vw, 3.6rem);
        }
        .feature-icon {
            width: 52px; height: 52px; border-radius: 14px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
        }
        .step-chip {
            width: 30px; height: 30px; border-radius: 50%; flex: 0 0 auto;
            display: inline-flex; align-items: center; justify-content: center;
            background: var(--color-primary); color: #fff; font-weight: 700; font-size: .85rem;
        }
        .how-step-ico {
            position: relative;
            width: 56px; height: 56px; border-radius: 16px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.6rem;
        }
        .how-step-ico.tone-primary { background: var(--color-primary-soft); color: var(--color-primary); }
        .how-step-ico.tone-info    { background: var(--color-info-soft);    color: var(--color-info); }
        .how-step-ico.tone-warning { background: var(--color-warning-soft); color: var(--color-warning); }
        .how-step-ico.tone-success { background: var(--color-success-soft); color: var(--color-success); }
        .how-step-num {
            position: absolute; top: -8px; right: -8px;
            width: 22px; height: 22px; border-radius: 50%;
            background: var(--color-primary); color: #fff;
            font-size: .72rem; font-weight: 700;
            display: inline-flex; align-items: center; justify-content: center;
            border: 2px solid #fff; box-shadow: var(--shadow-xs);
        }
    </style>
</head>

<body>
    {{-- Top bar --}}
    <nav class="navbar app-navbar sticky-top px-3 px-md-4">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
            <i class="bi bi-mortarboard-fill fs-4"></i>
            <span class="d-none d-sm-inline">Student Project System</span>
            <span class="d-sm-none">SPS</span>
        </a>
        <div class="d-flex align-items-center gap-2">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm">Login</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Register</a>
            @endauth
        </div>
    </nav>

    {{-- Hero --}}
    <header class="landing-hero">
        <div class="container text-center py-5 my-md-4" style="max-width:820px">
            <span class="hero-badge mb-4"><i class="bi bi-stars"></i> Synopsis → Review → Final Submission</span>
            <h1 class="hero-title mb-3">
                Your college project journey,<br class="d-none d-md-block"> all in one simple place.
            </h1>
            <p class="lead text-muted mb-4 mx-auto" style="max-width:560px">
                Submit your synopsis, get faculty feedback, upload your final project and download your
                certificate — without the paperwork chaos.
            </p>
            <div class="d-flex flex-column flex-sm-row justify-content-center gap-2 gap-sm-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg px-4"><i class="bi bi-speedometer2 me-1"></i> Go to Dashboard</a>
                @else
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg px-4">Get started as a student</a>
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-lg px-4">I already have an account</a>
                @endauth
            </div>
        </div>
    </header>

    {{-- Features --}}
    <main class="container py-5">
        <div class="row g-3 g-md-4">
            <div class="col-12 col-md-4">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <span class="feature-icon mb-3" style="background:var(--color-primary-soft);color:var(--color-primary)"><i class="bi bi-file-earmark-text"></i></span>
                        <h5 class="mb-2">Submit your synopsis</h5>
                        <p class="text-muted mb-0">Register, pick your department and submit your synopsis online in minutes.</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <span class="feature-icon mb-3" style="background:var(--color-success-soft);color:var(--color-success)"><i class="bi bi-clipboard-check"></i></span>
                        <h5 class="mb-2">Get faculty review</h5>
                        <p class="text-muted mb-0">Assigned faculty review your work, request corrections and approve — with a clear history.</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <span class="feature-icon mb-3" style="background:var(--color-warning-soft);color:var(--color-warning)"><i class="bi bi-award"></i></span>
                        <h5 class="mb-2">Track &amp; get certified</h5>
                        <p class="text-muted mb-0">Follow every step of your project status and download your certificate when completed.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- How it works --}}
        <div class="card mt-4 mt-md-5">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4 mb-md-5">
                    <h4 class="mb-1">How it works</h4>
                    <p class="text-muted mb-0">From sign-up to certificate in five simple steps.</p>
                </div>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-4 g-lg-3 text-center">
                    @foreach([
                        ['bi-person-plus', 'primary', 'Create your account', 'Register with your roll number, department and semester to get started.'],
                        ['bi-file-earmark-text', 'info', 'Submit your synopsis', 'Upload your project synopsis online for your assigned faculty to review.'],
                        ['bi-chat-square-text', 'warning', 'Get faculty review', 'Faculty approve your synopsis or request corrections, with a clear feedback history.'],
                        ['bi-cloud-arrow-up', 'primary', 'Upload the final project', 'Once approved, submit your final project files for evaluation.'],
                        ['bi-award', 'success', 'Track & get certified', 'Follow every status update and download your certificate once completed.'],
                    ] as $i => [$icon, $tone, $title, $desc])
                        <div class="col">
                            <span class="how-step-ico tone-{{ $tone }} mb-3">
                                <i class="bi {{ $icon }}"></i>
                                <span class="how-step-num">{{ $i + 1 }}</span>
                            </span>
                            <div class="fw-semibold mb-1">{{ $title }}</div>
                            <p class="text-muted small mb-0">{{ $desc }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="border-top py-4 mt-2">
        <div class="container d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 text-muted small">
            <span><i class="bi bi-mortarboard-fill text-primary me-1"></i> {{ config('app.name') }}</span>
            <span>&copy; {{ date('Y') }} · All rights reserved</span>
        </div>
    </footer>
</body>

</html>
