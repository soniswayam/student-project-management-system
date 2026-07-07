@php $role = auth()->user()->role; @endphp
<ul class="nav flex-column">
    @if(auth()->user()->isStaff())
        <li class="nav-section">Main</li>
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>

        <li class="nav-section">Academics</li>
        @can('students.view')
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}" href="{{ route('admin.students.index') }}"><i class="bi bi-people me-2"></i>Students</a></li>
        @endcan
        @can('faculty.view')
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.faculties.*') ? 'active' : '' }}" href="{{ route('admin.faculties.index') }}"><i class="bi bi-person-badge me-2"></i>Faculty</a></li>
        @endcan
        @can('departments.view')
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}" href="{{ route('admin.departments.index') }}"><i class="bi bi-building me-2"></i>Departments</a></li>
        @endcan
        @can('projects.view')
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.projects.*') ? 'active' : '' }}" href="{{ route('admin.projects.index') }}"><i class="bi bi-folder me-2"></i>Projects</a></li>
        @endcan
        @can('assignments.view')
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.assignments.*') ? 'active' : '' }}" href="{{ route('admin.assignments.index') }}"><i class="bi bi-journal-text me-2"></i>Assignments</a></li>
        @endcan
        @can('reports.view')
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}"><i class="bi bi-bar-chart me-2"></i>Reports</a></li>
        @endcan

        @canany(['admins.manage', 'roles.manage', 'settings.manage'])
            <li class="nav-section">Administration</li>
        @endcanany
        @canany(['admins.manage', 'roles.manage'])
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.access.*', 'admin.admins.*', 'admin.roles.*') ? 'active' : '' }}" href="{{ route('admin.access.index') }}"><i class="bi bi-shield-lock me-2"></i>Staff &amp; Roles</a></li>
        @endcanany
        @can('settings.manage')
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.edit') }}"><i class="bi bi-gear me-2"></i>College Settings</a></li>
        @endcan
    @elseif($role === 'faculty')
        <li class="nav-section">Main</li>
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('faculty.dashboard') ? 'active' : '' }}" href="{{ route('faculty.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('faculty.projects.*') ? 'active' : '' }}" href="{{ route('faculty.projects.index') }}"><i class="bi bi-folder-check me-2"></i>Assigned Projects</a></li>
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('faculty.assignments.*') ? 'active' : '' }}" href="{{ route('faculty.assignments.index') }}"><i class="bi bi-journal-text me-2"></i>Assignments</a></li>
    @else
        <li class="nav-section">Main</li>
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" href="{{ route('student.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('student.project.*') ? 'active' : '' }}" href="{{ route('student.project.show') }}"><i class="bi bi-folder me-2"></i>My Project</a></li>
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('student.assignments.*') ? 'active' : '' }}" href="{{ route('student.assignments.index') }}"><i class="bi bi-journal-text me-2"></i>Assignments</a></li>
    @endif

    <li class="nav-section">Account</li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}"><i class="bi bi-person-gear me-2"></i>My Profile</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}" href="{{ route('notifications.index') }}"><i class="bi bi-bell me-2"></i>Notifications</a></li>
</ul>
