<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function markRead(Request $request, UserNotification $userNotification): RedirectResponse|JsonResponse
    {
        abort_unless((int) $userNotification->user_id === (int) auth()->id(), 403);

        if (! $userNotification->is_read) {
            $userNotification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Notification marked as read.']);
        }

        return back();
    }

    public function markAllRead(Request $request): RedirectResponse|JsonResponse
    {
        UserNotification::query()
            ->where('user_id', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'All notifications marked as read.']);
        }

        return back()->with('success', 'All notifications marked as read.');
    }
}
