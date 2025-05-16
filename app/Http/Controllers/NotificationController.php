<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification; // Import class DatabaseNotification
use Illuminate\Support\Facades\Auth;               // Import Auth facade
use Illuminate\Http\JsonResponse;                  // Import JsonResponse để gợi ý kiểu trả về
use Illuminate\Http\RedirectResponse;              // Import RedirectResponse để gợi ý kiểu trả về
use Illuminate\Http\Response;                      // Import Response

class NotificationController extends Controller
{
    /**
     * Create a new controller instance.
     * Áp dụng middleware 'auth' cho tất cả các phương thức trong controller này.
     * Đảm bảo chỉ người dùng đã đăng nhập mới có thể tương tác với thông báo.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Đánh dấu một thông báo cụ thể là đã đọc.
     *
     * @param Request $request Request instance
     * @param DatabaseNotification $notification Notification instance được tự động tìm bởi Route Model Binding
     * @return JsonResponse|RedirectResponse|Response
     */
    public function markAsRead(Request $request, DatabaseNotification $notification): JsonResponse|RedirectResponse|Response
    {
        // --- Bước 1: Ủy quyền (Authorization) ---
        if (Auth::id() !== $notification->notifiable_id || get_class(Auth::user()) !== $notification->notifiable_type) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }

        // --- Bước 2: Thực hiện hành động ---
        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        // --- Bước 3: Phản hồi ---
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Thông báo đã được đánh dấu là đã đọc.',
                'unread_count' => Auth::user()->unreadNotifications()->count()
            ]);
        }
        return response()->noContent();
    }

    /**
     * Đánh dấu tất cả thông báo chưa đọc của người dùng hiện tại là đã đọc.
     *
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function markAllRead(Request $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        // Lấy tất cả thông báo chưa đọc của người dùng
        $unreadNotifications = $user->unreadNotifications; // Đây là một Collection

        // Lặp qua collection và đánh dấu từng thông báo là đã đọc
        if ($unreadNotifications->isNotEmpty()) {
            foreach ($unreadNotifications as $notification) {
                $notification->markAsRead(); // Gọi markAsRead() trên từng instance DatabaseNotification
            }
        }

        // Phản hồi
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Tất cả thông báo đã được đánh dấu là đã đọc.',
                // Sau khi đánh dấu tất cả, số lượng chưa đọc sẽ là 0
                'unread_count' => 0
            ]);
        }

        return redirect()->back()->with('success', 'Tất cả thông báo đã được đánh dấu là đã đọc.');
    }

    /**
     * (Tùy chọn) Hiển thị trang danh sách tất cả thông báo (cả đọc và chưa đọc).
     * Bạn cần tạo view cho chức năng này.
     *
     * @return \Illuminate\View\View
     */
    // public function index()
    // {
    //     $user = Auth::user();
    //     $notifications = $user->notifications()->latest()->paginate(20); // Lấy tất cả, phân trang

    //     return view('notifications.index', compact('notifications')); // Tạo view 'notifications/index.blade.php'
    // }

     /**
      * (Tùy chọn) Xóa một thông báo cụ thể.
      *
      * @param Request $request
      * @param DatabaseNotification $notification
      * @return JsonResponse|RedirectResponse
      */
    // public function destroy(Request $request, DatabaseNotification $notification): JsonResponse|RedirectResponse
    // {
    //     // Kiểm tra quyền
    //     if (Auth::id() !== $notification->notifiable_id || get_class(Auth::user()) !== $notification->notifiable_type) {
    //         if ($request->expectsJson()) { return response()->json(['status' => 'error', 'message' => 'Unauthorized action.'], 403); }
    //         abort(403, 'Bạn không có quyền thực hiện hành động này.');
    //     }

    //     $notification->delete();

    //     if ($request->expectsJson()) {
    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Thông báo đã được xóa.',
    //             'unread_count' => Auth::user()->unreadNotifications()->count() // Cập nhật lại số lượng
    //         ]);
    //     }

    //     return redirect()->back()->with('success', 'Thông báo đã được xóa.');
    // }
}