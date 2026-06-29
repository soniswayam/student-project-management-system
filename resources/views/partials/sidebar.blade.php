@php $role = auth()->user()->role; @endphp
<ul class="nav flex-column">
    @if($role === 'admin')
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}" href="{{ route('admin.students.index') }}"><i class="bi bi-people me-2"></i>Students</a></li>
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.faculties.*') ? 'active' : '' }}" href="{{ route('admin.faculties.index') }}"><i class="bi bi-person-badge me-2"></i>Faculty</a></li>
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}" href="{{ route('admin.departments.index') }}"><i class="bi bi-building me-2"></i>Departments</a></li>
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.projects.*') ? 'active' : '' }}" href="{{ route('admin.projects.index') }}"><i class="bi bi-folder me-2"></i>Projects</a></li>
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}"><i class="bi bi-bar-chart me-2"></i>Reports</a></li>
    @elseif($role === 'faculty')
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('faculty.dashboard') ? 'active' : '' }}" href="{{ route('faculty.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('faculty.projects.*') ? 'active' : '' }}" href="{{ route('faculty.projects.index') }}"><i class="bi bi-folder-check me-2"></i>Assigned Projects</a></li>
    @else
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" href="{{ route('student.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('student.project.*') ? 'active' : '' }}" href="{{ route('student.project.show') }}"><i class="bi bi-folder me-2"></i>My Project</a></li>
    @endif
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}" href="{{ route('notifications.index') }}"><i class="bi bi-bell me-2"></i>Notifications</a></li>
</ul>
