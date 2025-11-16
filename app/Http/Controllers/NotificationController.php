<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // GET /api/notifications?only_unread=1
    public function index(Request $request)
    {
        $user = $request->user();

        $query = UserNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($request->boolean('only_unread')) {
            $query->where('is_read', false);
        }

        return $query->get();
    }

    // POST /api/notifications/{notification}/read
    public function markAsRead(Request $request, UserNotification $notification)
    {
        $user = $request->user();

        if ($notification->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $notification->update(['is_read' => true]);

        return response()->json(['message' => 'Marked as read']);
    }

    // POST /api/notifications/read-all
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        UserNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'All notifications marked as read']);
    }
}
