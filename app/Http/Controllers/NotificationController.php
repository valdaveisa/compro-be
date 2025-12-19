<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{

    public function index(Request $request)
    {
        $user = $request->user();

        $query = UserNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($request->boolean('only_unread')) {
            $query->where('is_read', false);
        }

        $notifications = $query->get();
        $settings = $user->settings ?? [];

        return view('notifications.index', compact('notifications', 'settings'));
    }

    public function updateSettings(Request $request)
    {
        $user = $request->user();
        
        // Checkboxes only send 'on' if checked, or nothing if unchecked. 
        // We will merge with existing settings or overwrite? 
        // Best approach for checkboxes: Manually build array based on what's possible, or just take $request->all().
        // Since we only have checkboxes for now, we can just save $request->except(['_token']).
        
        $settings = $request->except(['_token']);
        
        // Since unchecked checkboxes are not sent, we should probably default them to false or handle them in the view logic (isset).
        // Saving exactly what is sent is fine if we use `?? false` in blade.
        
        $user->update(['settings' => $settings]);

        return redirect()->route('dashboard')->with('success', 'Pengaturan berhasil disimpan.');
    }

    public function markAsRead(Request $request, UserNotification $notification)
    {
        $user = $request->user();

        if ($notification->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $notification->update(['is_read' => true]);

        return response()->json(['message' => 'Marked as read']);
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        UserNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return redirect()->back()->with('success', 'Semua notifikasi telah ditandai sebagai dibaca.');
    }
}
