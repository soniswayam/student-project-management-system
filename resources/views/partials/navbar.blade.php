@php
    $unread = auth()->user()->appNotifications()->whereNull('read_at')->count();
@endphp
<header class="app-topbar no-print">
    <button class="topbar-btn d-lg-none" type="button"
            data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-label="Open menu">
        <i class="bi bi-list fs-4"></i>
    </button>

    <div class="d-lg-none topbar-brand">@include('partials.college_brand')</div>
    <div class="d-none d-lg-block topbar-title">@yield('title', 'Dashboard')</div>

    <a href="{{ route('notifications.index') }}" class="topbar-btn ms-auto position-relative"
       aria-label="Notifications{{ $unread ? ', '.$unread.' unread' : '' }}">
        <i class="bi bi-bell fs-5"></i>
        @if($unread)
            <span class="topbar-dot">{{ $unread > 99 ? '99+' : $unread }}</span>
        @endif
    </a>
</header>
