@php
    $user = auth()->user();
    $unread = $user->appNotifications()->whereNull('read_at')->count();
    $roleLabel = $user->role === 'super_admin' ? 'Super Admin' : ucfirst($user->role);
    $initials = collect(explode(' ', trim($user->name)))->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('');
@endphp
<nav class="navbar app-navbar sticky-top flex-nowrap p-0">
    <button class="navbar-toggler d-lg-none border-0 ms-1" type="button"
            data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-label="Toggle menu"
            style="min-width:44px;min-height:44px">
        <i class="bi bi-list fs-3"></i>
    </button>

    <a class="navbar-brand col-lg-2 me-0 px-3 d-flex align-items-center gap-2 text-truncate" href="{{ route('dashboard') }}">
        <i class="bi bi-mortarboard-fill fs-4"></i>
        <span class="text-truncate">Project Submission</span>
    </a>

    <div class="d-flex align-items-center ms-auto pe-2 pe-md-3 gap-1">
        <a href="{{ route('notifications.index') }}" class="nav-icon-btn position-relative" aria-label="Notifications">
            <i class="bi bi-bell fs-5"></i>
            @if($unread)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.65rem">
                    {{ $unread > 99 ? '99+' : $unread }}
                    <span class="visually-hidden">unread notifications</span>
                </span>
            @endif
        </a>

        <div class="dropdown">
            <a href="#" class="user-menu dropdown-toggle text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="app-avatar">{{ strtoupper($initials ?: 'U') }}</span>
                <span class="d-none d-sm-flex flex-column lh-1 text-start">
                    <span class="fw-semibold small">{{ $user->name }}</span>
                    <span class="text-muted" style="font-size:.72rem">{{ $roleLabel }}</span>
                </span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end mt-2">
                <li class="px-2 py-1 d-sm-none">
                    <div class="fw-semibold small">{{ $user->name }}</div>
                    <div class="text-muted" style="font-size:.72rem">{{ $roleLabel }}</div>
                </li>
                <li><span class="dropdown-item-text small text-muted text-truncate" style="max-width:220px">{{ $user->email }}</span></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                        <i class="bi bi-person-gear me-2"></i> My Profile
                    </a>
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item text-danger" type="submit">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>
