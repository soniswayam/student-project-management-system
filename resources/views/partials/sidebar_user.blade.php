@php
    $user = auth()->user();
    $roleLabel = $user->role === 'super_admin' ? 'Super Admin' : ucfirst($user->role);
    $initials = collect(explode(' ', trim($user->name)))->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('');
@endphp
<div class="sidebar-user dropup">
    <button type="button" class="sidebar-user-btn" data-bs-toggle="dropdown" aria-expanded="false">
        <span class="app-avatar">{{ strtoupper($initials ?: 'U') }}</span>
        <span class="sidebar-user-text">
            <span class="sidebar-user-name">{{ $user->name }}</span>
            <span class="sidebar-user-role">{{ $roleLabel }}</span>
        </span>
        <i class="bi bi-three-dots-vertical ms-auto text-muted" aria-hidden="true"></i>
    </button>
    <ul class="dropdown-menu w-100 mb-2">
        <li><span class="dropdown-item-text small text-muted text-truncate d-block">{{ $user->email }}</span></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person-gear me-2"></i>My Profile</a></li>
        <li>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
            </form>
        </li>
    </ul>
</div>
