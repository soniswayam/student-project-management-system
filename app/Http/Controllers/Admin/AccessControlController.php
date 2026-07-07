<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;

class AccessControlController extends Controller
{
    /**
     * Combined "Access Control" page: Staff accounts and Roles as two tabs.
     * Each tab's data (and the tab itself) is only loaded when the current
     * user holds the matching permission.
     */
    public function index()
    {
        $user = auth()->user();
        $canStaff = $user->hasPermission('admins.manage');
        $canRoles = $user->hasPermission('roles.manage');

        abort_unless($canStaff || $canRoles, 403);

        $admins = $roleLabels = null;
        if ($canStaff) {
            $staffRoleNames = Role::where('is_staff', true)->pluck('name')->all();
            $admins = User::whereIn('role', $staffRoleNames)
                ->orderBy('role')
                ->orderBy('name')
                ->paginate(15, ['*'], 'staff_page')
                ->appends(['tab' => 'staff']);
            $roleLabels = Role::where('is_staff', true)->orderBy('label')->get()->keyBy('name');
        }

        $roles = $userCounts = null;
        if ($canRoles) {
            $roles = Role::orderByDesc('is_system')
                ->orderBy('label')
                ->paginate(15, ['*'], 'roles_page')
                ->appends(['tab' => 'roles']);
            $userCounts = User::selectRaw('role, COUNT(*) as total')->groupBy('role')->pluck('total', 'role');
        }

        // Default to whichever tab the user is allowed to see.
        $activeTab = request('tab', $canStaff ? 'staff' : 'roles');
        if (($activeTab === 'staff' && ! $canStaff) || ($activeTab === 'roles' && ! $canRoles)) {
            $activeTab = $canStaff ? 'staff' : 'roles';
        }

        return view('admin.access.index', compact(
            'admins', 'roleLabels', 'roles', 'userCounts', 'canStaff', 'canRoles', 'activeTab'
        ));
    }
}
