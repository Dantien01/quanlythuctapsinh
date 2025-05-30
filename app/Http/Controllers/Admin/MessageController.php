<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
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
     * ĐỒNG THỜI ĐÁNH DẤU TẤT CẢ TIN NHẮN CHƯA ĐỌC CỦA ADMIN LÀ ĐÃ ĐỌC.
     */
    public function index()
    {
        $admin = Auth::user();

        // =====================================================================
        // ===== BẮT ĐẦU PHẦN CẬP NHẬT: ĐÁNH DẤU TẤT CẢ TIN NHẮN CHƯA ĐỌC =====
        // =====================================================================
        // Lấy ID của tất cả các tin nhắn mà admin này là người tham gia
        // và chưa có bản ghi MessageReadStatus cho admin này với read_at khác NULL.

        // Lấy IDs của tất cả các messages mà admin này là một participant TRONG MỘT Conversation
        // và message đó không phải do admin gửi, VÀ admin này chưa có bản ghi read_at cho message đó.
        $messageIdsToMarkAsRead = Message::whereIn('conversation_id', function ($query) use ($admin) {
                // Lấy ID các conversation mà admin là participant
                $query->select('conversation_id')
                      ->from('conversation_participants') // Giả sử bảng trung gian là conversation_user
                      ->where('user_id', $admin->id);
            })
            ->where('sender_id', '!=', $admin->id) // Tin nhắn không phải do admin gửi
            ->whereDoesntHave('readStatuses', function ($subQuery) use ($admin) {
                // Kiểm tra xem admin đã có bản ghi read_at cho message này chưa
                $subQuery->where('user_id', $admin->id)->whereNotNull('read_at');
            })
            ->pluck('id'); // Lấy danh sách ID của các message đó

        // Nếu có tin nhắn cần đánh dấu đọc, tiến hành cập nhật hoặc tạo mới MessageReadStatus
        if ($messageIdsToMarkAsRead->isNotEmpty()) {
            foreach ($messageIdsToMarkAsRead as $messageId) {
                MessageReadStatus::updateOrCreate(
                    [
                        'message_id' => $messageId,
                        'user_id' => $admin->id,
                    ],
                    [
                        'read_at' => now(),
                    ]
                );
            }
            Log::info("Admin {$admin->id} đã xem trang messages, {$messageIdsToMarkAsRead->count()} tin nhắn được đánh dấu đã đọc.");
        }
        // =====================================================================
        // ===== KẾT THÚC PHẦN CẬP NHẬT                                     =====
        // =====================================================================


        // Phần code lấy danh sách conversations giữ nguyên như của bạn
        $conversations = Conversation::whereHas('participants', function ($query) use ($admin) {
            $query->where('users.id', $admin->id);
        })
        ->whereHas('participants', function ($query) {
            $query->whereHas('role', fn($q) => $q->where('name', 'SinhVien'));
        })
        ->with([
            'participants' => function ($query) use ($admin) {
                $query->where('users.id', '!=', $admin->id)->with('role');
            },
            'lastMessage' => function ($query) {
                $query->with('sender');
            }
        ])
        ->withCount(['messages as unread_messages_count' => function ($query) use ($admin) {
            $query->where('sender_id', '!=', $admin->id)
                  ->whereDoesntHave('readStatuses', function ($subQuery) use ($admin) {
                      $subQuery->where('user_id', $admin->id)->whereNotNull('read_at');
                  });
        }])
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
    public function show(User $student)
    {
        $admin = Auth::user();

        if (!$student->hasRole('SinhVien')) {
            abort(404, 'Không tìm thấy sinh viên.');
        }

        $conversation = Conversation::findOrCreateBetween($admin, $student);

        // Đánh dấu tin nhắn từ sinh viên này TRONG CUỘC TRÒ CHUYỆN NÀY gửi cho admin là đã đọc
        MessageReadStatus::whereHas('message', function($query) use ($conversation, $student){
            $query->where('conversation_id', $conversation->id)
                  ->where('sender_id', $student->id);
        })
        ->where('user_id', $admin->id)
        ->whereNull('read_at')
        ->update(['read_at' => now()]);

        $messages = $conversation->messages()
                           ->with(['sender'])
                           ->orderBy('created_at', 'asc')
                           ->paginate(30);

        return view('admin.messages.show', compact('messages', 'student', 'admin', 'conversation'));
    }

    /**
     * Lưu tin nhắn trả lời từ Admin cho Sinh viên.
     */
    public function reply(Request $request, User $student)
    {
        $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $admin = Auth::user();

        if (!$student->hasRole('SinhVien')) {
            return back()->with('error', 'Không thể gửi tin nhắn cho người dùng này.')->withInput();
        }

        $conversation = Conversation::findOrCreateBetween($admin, $student);

        $newMessage = $conversation->messages()->create([
            'sender_id' => $admin->id,
            'content' => $request->input('content'),
        ]);

        if (!$newMessage) {
            Log::error("Không thể tạo tin nhắn mới từ Admin {$admin->id} cho SinhVien {$student->id} trong conversation {$conversation->id}");
            return back()->with('error', 'Đã xảy ra lỗi khi gửi tin nhắn. Vui lòng thử lại.')->withInput();
        }

        try {
            $student->notify(new NewMessageNotification($newMessage->load('sender')));
        } catch (\Exception $e) {
            Log::error("Lỗi gửi thông báo tin nhắn mới từ Admin {$admin->id} cho SinhVien {$student->id}: " . $e->getMessage());
        }

        return redirect()->route('admin.messages.show', $student->id)->with('success', 'Đã gửi trả lời thành công!');
    }
}