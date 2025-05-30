<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * Lấy danh sách thông báo của người dùng hiện tại.
     * GET /api/notifications
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $notifications = $user->notifications() // Lấy cả đọc và chưa đọc
                              ->latest()        // Sắp xếp mới nhất trước
                              ->paginate($request->input('per_page', 15)); // Phân trang

        // Tùy chọn: Đánh dấu các thông báo được lấy là đã đọc khi người dùng xem danh sách này.
        // $user->unreadNotifications()->whereIn('id', $notifications->pluck('id'))->update(['read_at' => now()]);

        return response()->json($notifications);
    }

    /**
     * Đánh dấu một thông báo cụ thể là đã đọc.
     * PUT /api/notifications/{notification}
     */
    public function markAsRead(Request $request, DatabaseNotification $notification)
    {
        // Ủy quyền: Đảm bảo thông báo này thuộc về người dùng hiện tại
        if ($notification->notifiable_id !== Auth::id() || $notification->notifiable_type !== get_class(Auth::user())) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return response()->json([
            'message' => 'Notification marked as read.',
            'notification' => $notification->fresh() // Lấy lại thông báo đã cập nhật
        ]);
    }

    /**
     * Đánh dấu tất cả thông báo chưa đọc của người dùng là đã đọc.
     * PUT /api/notifications/mark-all-as-read
     */
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();
        $user->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['message' => 'All unread notifications marked as read.']);
    }

    /**
     * Lấy số lượng thông báo chưa đọc.
     * GET /api/notifications/unread-count
     */
    public function unreadCount(Request $request)
    {
        $user = Auth::user();
        return response()->json(['unread_count' => $user->unreadNotifications()->count()]);
    }
}