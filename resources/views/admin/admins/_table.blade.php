{{-- Staff accounts table. Params: $admins (paginator), $roleLabels (Role map keyed by name). --}}
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Name</th><th>Email</th><th>Role</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
            @forelse($admins as $admin)
                <tr>
                    <td class="fw-semibold">{{ $admin->name }}</td>
                    <td>{{ $admin->email }}</td>
                    <td>
                        <span class="badge bg-{{ $admin->role === 'super_admin' ? 'dark' : 'secondary' }}">
                            {{ $roleLabels[$admin->role]->label ?? ucfirst($admin->role) }}
                        </span>
                        @if($admin->id === auth()->id())
                            <span class="badge bg-info text-dark">You</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('admin.admins.edit', $admin) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        @if($admin->id !== auth()->id())
                            <form action="{{ route('admin.admins.destroy', $admin) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this admin account?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">No admin accounts.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $admins->links() }}</div>
