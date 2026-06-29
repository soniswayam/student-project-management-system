<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    /** List all notifications for the current user. */
    public function index()
    {
        $notifications = auth()->user()->appNotifications()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /** Mark one notification read and follow its link. */
    public function read(Notification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === auth()->id(), 403);

        $notification->update(['read_at' => now()]);

        return redirect($notification->link ?: route('dashboard'));
    }

    /** Mark every notification read. */
    public function readAll(): RedirectResponse
    {
        auth()->user()->appNotifications()->whereNull('read_at')->update(['read_at' => now()]);

        return back();
    }
}
