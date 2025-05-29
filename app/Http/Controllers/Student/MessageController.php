<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Conversation; // << THÊM MỚI
use App\Models\MessageReadStatus; // << THÊM MỚI (nếu dùng)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Illuminate\Support\Facades\Log; // << THÊM MỚI

class MessageController extends Controller
{
    /**
     * Hiển thị cuộc trò chuyện của sinh viên với Admin.
     * Sinh viên thường chỉ có 1 cuộc trò chuyện chính với hệ thống/Admin.
     */
    public function index()
    {
        $student = Auth::user();
        // Tìm Admin (giả sử chỉ có 1 Admin hoặc lấy Admin đầu tiên có vai trò 'Admin')
        $admin = User::whereHas('role', fn($q) => $q->where('name', 'Admin'))->first();

        if (!$admin) {
            // Xử lý trường hợp không tìm thấy admin (ví dụ: hiển thị thông báo)
            return view('student.messages.index_no_admin'); // Tạo view này
        }

        // Tìm hoặc tạo Conversation giữa sinh viên và admin
        $conversation = Conversation::findOrCreateBetween($student, $admin);

        // Đánh dấu tin nhắn từ admin trong cuộc trò chuyện này gửi cho sinh viên là đã đọc
        // Nếu dùng bảng message_read_statuses:
        MessageReadStatus::whereHas('message', function($query) use ($conversation, $admin){
            $query->where('conversation_id', $conversation->id)
                  ->where('sender_id', $admin->id); // Tin nhắn do admin gửi
        })
        ->where('user_id', $student->id) // Cho sinh viên này
        ->whereNull('read_at')
        ->update(['read_at' => now()]);


        // Lấy toàn bộ tin nhắn trong cuộc trò chuyện này
        $messages = $conversation->messages()
                           ->with(['sender'])
                           ->orderBy('created_at', 'asc')
                           ->paginate(30); // Hoặc get() nếu không muốn phân trang ở đây

        return view('student.messages.index', compact('messages', 'student', 'admin', 'conversation'));
    }


    /**
     * Sinh viên gửi tin nhắn cho Admin.
     * Form gửi tin nhắn sẽ submit vào đây.
     */
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:5000',
            // 'receiver_id' không còn cần thiết nếu sinh viên chỉ chat với Admin mặc định
        ]);

        $student = Auth::user();
        // Tìm Admin để gửi tin nhắn tới
        $admin = User::whereHas('role', fn($q) => $q->where('name', 'Admin'))->first();

        if (!$admin) {
            return back()->with('error', 'Không tìm thấy quản trị viên để gửi tin nhắn.')->withInput();
        }

        // 1. Tìm hoặc tạo Conversation
        $conversation = Conversation::findOrCreateBetween($student, $admin);

        // 2. Tạo tin nhắn mới
        $newMessage = $conversation->messages()->create([
            'sender_id' => $student->id,
            'content' => $request->input('content'),
            // 'subject' không còn nữa trừ khi bạn giữ lại
        ]);

        if (!$newMessage) {
            Log::error("Không thể tạo tin nhắn mới từ SinhVien {$student->id} cho Admin {$admin->id} trong conversation {$conversation->id}");
            return back()->with('error', 'Đã xảy ra lỗi khi gửi tin nhắn. Vui lòng thử lại.')->withInput();
        }

        // 3. (Tùy chọn) Tạo MessageReadStatus cho người nhận (admin)
        // MessageReadStatus::create([
        //     'message_id' => $newMessage->id,
        //     'user_id' => $admin->id,
        //     'read_at' => null,
        // ]);

        // Gửi thông báo cho Admin
        try {
            $admin->notify(new NewMessageNotification($newMessage->load('sender')));
        } catch (\Exception $e) {
            Log::error("Lỗi gửi thông báo tin nhắn mới từ SinhVien {$student->id} cho Admin {$admin->id}: " . $e->getMessage());
        }

        // Redirect lại trang chat (index) sau khi gửi
        return redirect()->route('student.messages.index')->with('success', 'Tin nhắn đã được gửi thành công!');
    }
    public function create()
    {
        // Tìm Admin để gửi tin nhắn tới
        $admin = User::whereHas('role', fn($q) => $q->where('name', 'Admin'))->first();

        if (!$admin) {
            return redirect()->route('student.messages.index')->with('error', 'Không tìm thấy quản trị viên để gửi tin nhắn.');
        }
        // Giả sử bạn có view resources/views/student/messages/create.blade.php
        return view('student.messages.create', compact('admin'));
    }
}