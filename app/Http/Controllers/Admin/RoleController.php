<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::orderByDesc('is_system')->orderBy('label')->paginate(15);
        $userCounts = User::selectRaw('role, COUNT(*) as total')->groupBy('role')->pluck('total', 'role');

        return view('admin.roles.index', compact('roles', 'userCounts'));
    }

    public function create()
    {
        return view('admin.roles.create', ['catalog' => Role::catalog()]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Role::create([
            'name' => str($data['label'])->slug('_')->toString(),
            'label' => $data['label'],
            'permissions' => $data['permissions'] ?? [],
            'is_staff' => true,
            'is_system' => false,
        ]);

        return redirect()->route('admin.access.index', ['tab' => 'roles'])->with('success', 'Role created.');
    }

    public function edit(Role $role)
    {
        abort_if($role->name === 'super_admin', 403, 'The Super Admin role cannot be edited.');
        $this->guardOwnRole($role);

        return view('admin.roles.edit', ['role' => $role, 'catalog' => Role::catalog()]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        abort_if($role->name === 'super_admin', 403);
        $this->guardOwnRole($role);

        $data = $request->validated();

        $role->update([
            'label' => $data['label'],
            'permissions' => $data['permissions'] ?? [],
        ]);

        return redirect()->route('admin.access.index', ['tab' => 'roles'])->with('success', 'Role updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_system) {
            return back()->with('error', 'System roles cannot be deleted.');
        }

        if (User::where('role', $role->name)->exists()) {
            return back()->with('error', 'This role is still assigned to one or more users.');
        }

        $role->delete();

        return back()->with('success', 'Role deleted.');
    }

    private function guardOwnRole(Role $role): void
    {
        $user = auth()->user();

        abort_if(! $user->isSuperAdmin() && $user->role === $role->name, 403, 'You cannot change the permissions of your own role.');
    }
}
