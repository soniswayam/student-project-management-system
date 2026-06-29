<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="bg-dark text-white">
    <div class="container d-flex flex-column justify-content-center align-items-center text-center" style="min-height:100vh">
        <i class="bi bi-mortarboard-fill display-1 text-primary mb-3"></i>
        <h1 class="fw-bold">Student Project Submission System</h1>
        <p class="lead text-secondary mb-4" style="max-width:600px">
            Submit your synopsis, get it reviewed by faculty, and upload your final project — all in one place.
        </p>
        <div class="d-flex gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">Go to Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary btn-lg">Login</a>
                <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg">Register as Student</a>
            @endauth
        </div>
    </div>
</body>
</html>
