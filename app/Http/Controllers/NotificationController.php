<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = UserNotification::query()
            ->with('notification')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(UserNotification $userNotification): RedirectResponse
    {
        abort_unless((int) $userNotification->user_id === (int) auth()->id(), 403);

        if (! $userNotification->is_read) {
            $userNotification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        return back();
    }

    public function markAllRead(): RedirectResponse
    {
        UserNotification::query()
            ->where('user_id', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return back()->with('success', 'All notifications marked as read.');
    }
}

