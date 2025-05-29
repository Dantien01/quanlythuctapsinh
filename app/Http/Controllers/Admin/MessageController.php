<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation; // << THÊM MỚI
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\MessageReadStatus;

class MessageController extends Controller
{
    /**
     * Hiển thị danh sách các cuộc trò chuyện của Admin với Sinh viên.
     */
    public function index()
    {
        $admin = Auth::user();

        $conversations = Conversation::whereHas('participants', function ($query) use ($admin) {
            $query->where('users.id', $admin->id); // Admin là một người tham gia
        })
        ->whereHas('participants', function ($query) { // Và người tham gia còn lại là SinhVien
            $query->whereHas('role', fn($q) => $q->where('name', 'SinhVien'));
        })
        ->with([
            // Lấy thông tin người tham gia còn lại (SinhVien)
            'participants' => function ($query) use ($admin) {
                $query->where('users.id', '!=', $admin->id)->with('role'); // Eager load role của student
            },
            // Lấy tin nhắn cuối cùng và người gửi của nó
            'lastMessage' => function ($query) {
                $query->with('sender');
            }
        ])
        // Đếm số tin nhắn CHƯA ĐỌC mà SINH VIÊN GỬI cho ADMIN này
        ->withCount(['messages as unread_messages_count' => function ($query) use ($admin) {
            $query->where('sender_id', '!=', $admin->id) // Tin nhắn không phải do admin gửi
                  // Giả sử dùng MessageReadStatus hoặc logic khác để check read status
                  // Nếu dùng MessageReadStatus:
                  ->whereDoesntHave('readStatuses', function ($subQuery) use ($admin) {
                      $subQuery->where('user_id', $admin->id)->whereNotNull('read_at');
                  });
            // Nếu bạn dùng cột read_at trực tiếp trên messages (đã bỏ) thì sẽ là:
            // ->whereNull('read_at'); // Và admin chưa đọc (logic này cần xem lại nếu read_at là của người nhận)
        }])
        // Sắp xếp theo thời gian của tin nhắn cuối cùng
        // (Cần đảm bảo lastMessage() được định nghĩa và hoạt động đúng)
        // Cách sắp xếp này có thể cần tối ưu hóa trên CSDL lớn
        ->orderByDesc(
            Message::select('created_at')
                ->whereColumn('conversation_id', 'conversations.id')
                ->latest()
                ->limit(1)
        )
        ->paginate(20);

        return view('admin.messages.index', compact('conversations', 'admin'));
    }

    /**
     * Hiển thị chi tiết cuộc trò chuyện với một sinh viên cụ thể.
     * Đánh dấu các tin nhắn chưa đọc của sinh viên gửi cho admin là đã đọc.
     */
    public function show(User $student) // $student là sinh viên được chọn
    {
        $admin = Auth::user();

        if (!$student->hasRole('SinhVien')) {
            abort(404, 'Không tìm thấy sinh viên.');
        }

        // Tìm Conversation giữa admin và sinh viên này
        $conversation = Conversation::findOrCreateBetween($admin, $student); // Sử dụng helper

        // Đánh dấu tin nhắn từ sinh viên này trong cuộc trò chuyện này gửi cho admin là đã đọc
        // Nếu dùng bảng message_read_statuses:
        MessageReadStatus::whereHas('message', function($query) use ($conversation, $student){
            $query->where('conversation_id', $conversation->id)
                  ->where('sender_id', $student->id); // Tin nhắn do sinh viên gửi
        })
        ->where('user_id', $admin->id) // Cho admin này
        ->whereNull('read_at')
        ->update(['read_at' => now()]);

        // Lấy toàn bộ tin nhắn trong cuộc trò chuyện này, sắp xếp theo thời gian tạo
        $messages = $conversation->messages()
                           ->with(['sender']) // Eager load người gửi
                           ->orderBy('created_at', 'asc')
                           ->paginate(30);

        return view('admin.messages.show', compact('messages', 'student', 'admin', 'conversation'));
    }

    /**
     * Lưu tin nhắn trả lời từ Admin cho Sinh viên.
     */
    public function reply(Request $request, User $student) // $student ở đây là sinh viên nhận tin nhắn
    {
        $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $admin = Auth::user();

        if (!$student->hasRole('SinhVien')) {
            return back()->with('error', 'Không thể gửi tin nhắn cho người dùng này.')->withInput();
        }

        // 1. Tìm hoặc tạo Conversation
        $conversation = Conversation::findOrCreateBetween($admin, $student);

        // 2. Tạo tin nhắn mới
        $newMessage = $conversation->messages()->create([
            'sender_id' => $admin->id,
            'content' => $request->input('content'),
        ]);

        if (!$newMessage) {
            Log::error("Không thể tạo tin nhắn mới từ Admin {$admin->id} cho SinhVien {$student->id} trong conversation {$conversation->id}");
            return back()->with('error', 'Đã xảy ra lỗi khi gửi tin nhắn. Vui lòng thử lại.')->withInput();
        }

        // 3. (Tùy chọn) Tạo MessageReadStatus cho người nhận (sinh viên)
        // MessageReadStatus::create([
        //     'message_id' => $newMessage->id,
        //     'user_id' => $student->id,
        //     'read_at' => null,
        // ]);

        // Gửi thông báo cho Sinh viên
        try {
            $student->notify(new NewMessageNotification($newMessage->load('sender'))); // Load sender để notification có thông tin
        } catch (\Exception $e) {
            Log::error("Lỗi gửi thông báo tin nhắn mới từ Admin {$admin->id} cho SinhVien {$student->id}: " . $e->getMessage());
        }

        return redirect()->route('admin.messages.show', $student->id)->with('success', 'Đã gửi trả lời thành công!');
    }
}