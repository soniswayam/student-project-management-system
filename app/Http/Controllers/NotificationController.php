<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->appNotifications()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function read(Notification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === auth()->id(), 403);

        $notification->update(['read_at' => now()]);

        return redirect($notification->link ?: route('dashboard'));
    }

    public function readAll(): RedirectResponse
    {
        auth()->user()->appNotifications()->whereNull('read_at')->update(['read_at' => now()]);

        return back();
    }
}
