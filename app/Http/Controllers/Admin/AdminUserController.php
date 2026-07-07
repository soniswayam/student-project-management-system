<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminRequest;
use App\Http\Requests\Admin\UpdateAdminRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class AdminUserController extends Controller
{
    /** Slugs of every staff role (super_admin, admin, and custom roles). */
    private function staffRoleNames(): array
    {
        return Role::where('is_staff', true)->pluck('name')->all();
    }

    /** List all staff accounts. */
    public function index()
    {
        $admins = User::whereIn('role', $this->staffRoleNames())
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(15);

        $roles = Role::where('is_staff', true)->orderBy('label')->get()->keyBy('name');

        return view('admin.admins.index', compact('admins', 'roles'));
    }

    public function create()
    {
        return view('admin.admins.create', ['roles' => $this->staffRoles()]);
    }

    public function store(StoreAdminRequest $request): RedirectResponse
    {
        User::create($request->validated());

        return redirect()->route('admin.access.index', ['tab' => 'staff'])->with('success', 'Staff account created.');
    }

    public function edit(User $admin)
    {
        abort_unless(in_array($admin->role, $this->staffRoleNames(), true), 404);

        return view('admin.admins.edit', ['admin' => $admin, 'roles' => $this->staffRoles()]);
    }

    public function update(UpdateAdminRequest $request, User $admin): RedirectResponse
    {
        abort_unless(in_array($admin->role, $this->staffRoleNames(), true), 404);

        $data = $request->validated();

        // Only overwrite the password when a new one is supplied.
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $admin->update($data);

        return redirect()->route('admin.access.index', ['tab' => 'staff'])->with('success', 'Staff account updated.');
    }

    public function destroy(User $admin): RedirectResponse
    {
        abort_unless(in_array($admin->role, $this->staffRoleNames(), true), 404);

        // Guard: never delete yourself.
        if ($admin->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Guard: keep at least one super admin in the system.
        if ($admin->role === 'super_admin' && User::where('role', 'super_admin')->count() <= 1) {
            return back()->with('error', 'At least one Super Admin must remain.');
        }

        $admin->delete();

        return back()->with('success', 'Staff account deleted.');
    }

    /** Staff roles for the role <select> dropdown. */
    private function staffRoles()
    {
        return Role::where('is_staff', true)->orderBy('label')->get();
    }
}
