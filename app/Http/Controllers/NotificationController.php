<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function unreadCount()
    {
        $count = auth()->user()->notifications()->unread()->count();
        return response()->json(['count' => $count]);
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        auth()->user()->notifications()->unread()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Đã đánh dấu tất cả thông báo là đã đọc');
    }

    public function getBlocking()
    {
        \Log::info('[v0] Fetching blocking notification for user: ' . auth()->id());
        
        $notification = auth()->user()
            ->notifications()
            ->blocking()
            ->unread()
            ->latest()
            ->first();

        \Log::info('[v0] Blocking notification found:', [
            'notification_id' => $notification?->id,
            'title' => $notification?->title,
            'message' => $notification?->message,
            'is_blocking' => $notification?->is_blocking,
        ]);

        // Return null if no notification found (not an empty object)
        return response()->json($notification);
    }
}
