{{-- Roles table. Params: $roles (paginator), $userCounts (total per role name). --}}
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Role</th><th>Type</th><th>Access</th><th>Users</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
            @forelse($roles as $role)
                <tr>
                    <td class="fw-semibold">{{ $role->label }}</td>
                    <td>
                        @if($role->is_system)<span class="badge bg-dark">System</span>@else<span class="badge bg-secondary">Custom</span>@endif
                        @unless($role->is_staff)<span class="badge bg-light text-dark">Non-staff</span>@endunless
                    </td>
                    <td>
                        @if(in_array('*', $role->permissions ?? [], true))
                            <span class="badge bg-success">Full access</span>
                        @elseif($role->is_staff)
                            <span class="badge bg-info text-dark">{{ count($role->permissions ?? []) }} permissions</span>
                        @else
                            <span class="text-muted small">Own area</span>
                        @endif
                    </td>
                    <td>{{ $userCounts[$role->name] ?? 0 }}</td>
                    <td class="text-end">
                        @if($role->name !== 'super_admin' && $role->is_staff)
                            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        @endif
                        @unless($role->is_system)
                            <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this role?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        @endunless
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No roles.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $roles->links() }}</div>
