<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\NotificationPreference;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Notification::class);
        
        $notifications = auth()->user()
            ->notifications()
            ->with(['user', 'organization']) // Eager load relationships
            ->active() // Only show non-expired notifications
            ->orderBy('priority', 'desc') // High priority first
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function getNotifications(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $limit = $request->query('limit', 5);
        
        $notifications = auth()->user()
            ->notifications()
            ->with(['user', 'organization'])
            ->active()
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'is_read' => $notification->is_read,
                    'is_blocking' => $notification->is_blocking,
                    'created_at' => $notification->created_at->toIso8601String(),
                    'cta_text' => $notification->cta_text,
                    'cta_url' => $notification->cta_url,
                ];
            });

        return response()->json([
            'data' => $notifications,
            'count' => $notifications->count(),
        ]);
    }

    public function unreadCount()
    {
        if (!auth()->check()) {
            return response()->json(['count' => 0], 401);
        }

        // Cache unread count for 30 seconds to reduce database load
        $count = cache()->remember(
            'user_' . auth()->id() . '_unread_notifications',
            30,
            function () {
                return auth()->user()->notifications()->unread()->count();
            }
        );

        return response()->json(['count' => $count]);
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $this->authorize('view', $notification);
        
        $notification->markAsRead();
        
        cache()->forget('user_' . auth()->id() . '_unread_notifications');

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        $this->authorize('viewAny', Notification::class);
        
        auth()->user()->notifications()->unread()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        cache()->forget('user_' . auth()->id() . '_unread_notifications');

        return redirect()->back()->with('success', 'Đã đánh dấu tất cả thông báo là đã đọc');
    }

    public function getBlocking(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        \Log::info('[v0] Fetching blocking notification for user: ' . auth()->id());
        
        $notification = cache()->remember(
            'user_' . auth()->id() . '_blocking_notification',
            60,
            function () {
                return auth()->user()
                    ->notifications()
                    ->blocking()
                    ->unread()
                    ->active() // Only show non-expired blocking notifications
                    ->latest()
                    ->first();
            }
        );

        if ($notification) {
            \Log::info('[v0] Blocking notification found:', [
                'notification_id' => $notification->id,
                'title' => $notification->title,
                'is_blocking' => $notification->is_blocking,
            ]);

            return response()->json([
                'blocked' => true,
                'notification' => $notification,
            ], 403);
        }

        \Log::info('[v0] No blocking notification found');
        
        return response()->json(['blocked' => false], 200);
    }

    public function getPreferences()
    {
        $this->authorize('viewAny', Notification::class);

        $preferences = cache()->remember(
            'user_' . auth()->id() . '_notification_preferences',
            3600,
            function () {
                $prefs = NotificationPreference::where('user_id', auth()->id())->get();
                
                // Build array with all notification types
                $result = [];
                $types = ['quota_warning', 'quota_reached', 'upgrade_success', 'feature_locked', 'error_alert', 'data_retention_completed'];
                
                foreach ($types as $type) {
                    $pref = $prefs->firstWhere('notification_type', $type);
                    $result[$type] = [
                        'enabled' => $pref ? $pref->enabled : true,
                        'frequency' => $pref ? $pref->frequency : 'real-time',
                    ];
                }
                
                return $result;
            }
        );

        return response()->json($preferences);
    }

    public function updatePreferences(Request $request)
    {
        $this->authorize('viewAny', Notification::class);

        $validated = $request->validate([
            'notification_type' => 'required|string',
            'enabled' => 'required|boolean',
            'frequency' => 'required|in:real-time,daily,weekly,never',
        ]);

        NotificationPreference::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'notification_type' => $validated['notification_type'],
            ],
            [
                'enabled' => $validated['enabled'],
                'frequency' => $validated['frequency'],
            ]
        );

        cache()->forget('user_' . auth()->id() . '_notification_preferences');

        return response()->json(['success' => true]);
    }
}
