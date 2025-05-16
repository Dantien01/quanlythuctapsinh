<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessageNotification; // Sẽ tạo ở bước sau

class MessageController extends Controller
{
    // Hiển thị danh sách/cuộc trò chuyện
    public function index()
    {
        $user = Auth::user();
        // Lấy tin nhắn mà user là người gửi hoặc người nhận, sắp xếp mới nhất trước
        // Nên eager load sender và receiver để tránh N+1 query
        $messages = Message::where('sender_id', $user->id)
                           ->orWhere('receiver_id', $user->id)
                           ->with(['sender', 'receiver']) // Quan trọng
                           ->latest() // Sắp xếp theo created_at giảm dần
                           ->paginate(15); // Phân trang

         // Tìm Admin (giả sử chỉ có 1 Admin hoặc lấy Admin đầu tiên)
         // Cần logic tốt hơn nếu có nhiều Admin
         $admin = User::whereHas('role', fn($q) => $q->where('name', 'Admin'))->first();


        return view('student.messages.index', compact('messages', 'user', 'admin'));
    }

    // Hiển thị form tạo tin nhắn mới
    public function create()
    {
         // Tìm Admin để gửi tin nhắn tới
         // Cần logic tốt hơn nếu có nhiều Admin hoặc muốn chọn Admin cụ thể
         $admin = User::whereHas('role', fn($q) => $q->where('name', 'Admin'))->first();

         if (!$admin) {
             return redirect()->route('student.messages.index')->with('error', 'Không tìm thấy quản trị viên để gửi tin nhắn.');
         }

        return view('student.messages.create', compact('admin'));
    }

    // Lưu tin nhắn mới
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'nullable|string|max:255', // Giữ lại nếu đã thêm cột subject
            'content' => 'required|string|max:5000', // <<< Đổi thành 'content'
            'receiver_id' => 'required|exists:users,id'
        ]);

        $user = Auth::user();
        $receiver = User::find($request->receiver_id); // Tìm người nhận

        // Kiểm tra người nhận có phải Admin không (tùy chọn)
        if (!$receiver || !$receiver->hasRole('Admin')) {
             return back()->with('error', 'Người nhận không hợp lệ.')->withInput();
        }

        // Tạo tin nhắn
        $message = Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $receiver->id,
            'subject' => $request->subject, // Giữ lại nếu đã thêm cột subject
            'content' => $request->input('content'), // <<< Đổi thành 'content'
        ]);

         // *** Gửi thông báo cho Admin ***
         try {
             $receiver->notify(new NewMessageNotification($message));
         } catch (\Exception $e) {
             // Ghi log lỗi nếu gửi mail/notification thất bại nhưng vẫn lưu message
             \Log::error("Lỗi gửi thông báo tin nhắn mới cho user {$receiver->id}: " . $e->getMessage());
         }


        return redirect()->route('student.messages.index')->with('success', 'Tin nhắn đã được gửi thành công!');
    }
}