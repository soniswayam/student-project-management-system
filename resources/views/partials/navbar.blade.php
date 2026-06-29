@php
    $unread = auth()->user()->appNotifications()->whereNull('read_at')->count();
@endphp
<nav class="navbar navbar-dark bg-dark sticky-top flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6 fw-bold" href="{{ route('dashboard') }}">
        <i class="bi bi-mortarboard-fill me-1"></i> Project Submission
    </a>
    <div class="d-flex align-items-center ms-auto pe-3">
        <a href="{{ route('notifications.index') }}" class="btn btn-sm position-relative me-3 text-white">
            <i class="bi bi-bell fs-5"></i>
            @if($unread)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    {{ $unread }}
                </span>
            @endif
        </a>
        <div class="dropdown">
            <a href="#" class="text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle me-1"></i>
                {{ auth()->user()->name }}
                <span class="badge bg-secondary text-uppercase">{{ auth()->user()->role }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><span class="dropdown-item-text small text-muted">{{ auth()->user()->email }}</span></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item text-danger" type="submit">
                            <i class="bi bi-box-arrow-right me-1"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>
