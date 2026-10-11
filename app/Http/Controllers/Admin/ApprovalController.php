<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('status', User::STATUS_PENDING)
            ->with('student.department');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('student', fn ($s) => $s->where('roll_no', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('department_id')) {
            $query->whereHas('student', fn ($s) => $s->where('department_id', $request->department_id));
        }

        $pending = $query->oldest()->paginate(20)->withQueryString();

        $departments = Department::orderBy('name')->get();

        $recent = User::whereNotNull('approved_at')
            ->with('student', 'approver')
            ->latest('approved_at')
            ->limit(10)
            ->get();

        return view('admin.approvals.index', compact('pending', 'departments', 'recent'));
    }

    public function approve(User $user): RedirectResponse
    {
        if (! $user->isPending()) {
            return back()->with('info', 'That account is not waiting for approval.');
        }

        $this->activate($user);

        return back()->with('success', "{$user->name} approved and notified.");
    }

    public function bulkApprove(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $users = User::whereIn('id', $data['user_ids'])
            ->where('status', User::STATUS_PENDING)
            ->get();

        foreach ($users as $user) {
            $this->activate($user);
        }

        $skipped = count($data['user_ids']) - $users->count();
        $message = "Approved {$users->count()} account(s).";
        if ($skipped > 0) {
            $message .= " {$skipped} were already decided and were skipped.";
        }

        return back()->with('success', $message);
    }

    public function reject(Request $request, User $user): RedirectResponse
    {
        if (! $user->isPending()) {
            return back()->with('info', 'That account is not waiting for approval.');
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($user, $data) {
            $user->update([
                'status' => User::STATUS_BLOCKED,
                'status_reason' => $data['reason'],
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);
        });

        Notification::notify(
            $user->id,
            'Registration not approved',
            'Your account request was not approved. Reason: '.$data['reason'],
            route('login')
        );

        return back()->with('success', "{$user->name} was rejected and notified.");
    }

    private function activate(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->update([
                'status' => User::STATUS_ACTIVE,
                'status_reason' => null,
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);
        });

        Notification::notify(
            $user->id,
            'Account approved',
            'Your account has been approved. You can now sign in and submit your synopsis.',
            route('login')
        );
    }
}
