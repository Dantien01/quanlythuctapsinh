<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;
use App\Models\User; // Cần để lấy thông tin sinh viên
use App\Notifications\NewMessageNotification; // Import notification class
use Illuminate\Support\Facades\DB; // Để sử dụng DB facade (nếu cần)
use Illuminate\Support\Facades\Log; // Để ghi log lỗi

class MessageController extends Controller
{
    /**
     * Hiển thị danh sách các cuộc trò chuyện (theo sinh viên).
     */
    public function index()
    {
        $adminId = Auth::id();

        // Lấy danh sách ID sinh viên có tin nhắn với admin
        $studentIdsWithMessages = Message::where('sender_id', $adminId)
            ->orWhere('receiver_id', $adminId)
            ->select('sender_id', 'receiver_id')
            ->get()
            ->flatMap(fn ($message) => [$message->sender_id, $message->receiver_id])
            ->filter(fn($id) => $id !== $adminId)
            ->unique()
            ->values();

        // Bắt đầu query để lấy thông tin sinh viên
        $conversationsQuery = User::whereIn('id', $studentIdsWithMessages)
            ->whereHas('role', fn($q) => $q->where('name', 'SinhVien')) // Chỉ lấy sinh viên
            // Đếm số tin nhắn chưa đọc MÀ SINH VIÊN GỬI TỚI ADMIN
            ->withCount(['receivedMessages as unread_messages_count' => function ($query) use ($adminId) {
                // Eloquent tự động thêm `where messages.sender_id = users.id`
                $query->where('receiver_id', $adminId) // Người nhận là admin hiện tại
                      ->whereNull('read_at');         // Và chưa đọc
            }])
            // Lấy tin nhắn cuối cùng trong cuộc trò chuyện
            // Sử dụng a subquery with `addSelect` để lấy tin nhắn cuối cùng và có thể sắp xếp hiệu quả
            ->addSelect(['last_message_id' => Message::select('id')
                ->where(function($query) use ($adminId) {
                    // Điều kiện liên kết subquery với bảng users bên ngoài
                    $query->whereColumn('sender_id', 'users.id')->where('receiver_id', $adminId) // Tin SV gửi
                          ->orWhere(function($q) use ($adminId) {
                              $q->whereColumn('receiver_id', 'users.id')->where('sender_id', $adminId); // Tin Admin gửi
                          });
                })
                ->orderByDesc('created_at') // Lấy tin mới nhất
                ->limit(1)
            ])
            // Eager load tin nhắn cuối cùng dựa trên ID đã lấy ở trên
            // và cả người gửi của tin nhắn đó
            ->with(['lastMessage' => fn ($query) => $query->with('sender')]); // Định nghĩa relationship 'lastMessage' trong User model

        // Sắp xếp theo thời gian của tin nhắn cuối cùng (cần last_message_id hoặc join)
        // Sắp xếp bằng subquery select time thì phức tạp hơn, tạm sắp xếp theo ID user
         $conversations = $conversationsQuery->orderByDesc('id') // Tạm sắp xếp
                                             ->paginate(20);

        // Nếu không dùng addSelect/with('lastMessage'), bạn có thể eager load tất cả tin nhắn
        // và xử lý lấy tin cuối + sắp xếp bằng Collection ở đây (ít hiệu quả hơn với dữ liệu lớn)
        // $conversations = User::whereIn(...)
        //     ->with(['sentMessages', 'receivedMessages']) // Load tất cả
        //     ->paginate(20);
        // $conversations = $conversations->each(function ($user) { ... /* logic lấy last message time */ ... })
        //                              ->sortByDesc('last_message_time');


        return view('admin.messages.index', compact('conversations'));
    }

    /**
     * Hiển thị chi tiết cuộc trò chuyện với một sinh viên cụ thể.
     * Đánh dấu các tin nhắn chưa đọc của sinh viên gửi cho admin là đã đọc.
     */
    public function show(User $user) // Sử dụng Route Model Binding với User model (sinh viên)
    {
        $admin = Auth::user();

        // Đảm bảo user được truyền vào là SinhVien
         if (!$user->hasRole('SinhVien')) {
              abort(404, 'Không tìm thấy sinh viên.');
         }

        // Đánh dấu tin nhắn từ sinh viên này gửi cho admin là đã đọc
        Message::where('sender_id', $user->id)
               ->where('receiver_id', $admin->id)
               ->whereNull('read_at')
               ->update(['read_at' => now()]);

        // Lấy toàn bộ tin nhắn giữa admin và sinh viên này, sắp xếp theo thời gian tạo
        $messages = Message::where(function ($query) use ($admin, $user) {
                                $query->where('sender_id', $admin->id)
                                      ->where('receiver_id', $user->id);
                            })
                           ->orWhere(function ($query) use ($admin, $user) {
                                $query->where('sender_id', $user->id)
                                      ->where('receiver_id', $admin->id);
                            })
                           ->with(['sender', 'receiver']) // Eager load để hiển thị tên
                           ->orderBy('created_at', 'asc') // Sắp xếp từ cũ đến mới để đọc
                           ->paginate(30); // Phân trang nếu cuộc trò chuyện dài

        return view('admin.messages.show', compact('messages', 'user', 'admin')); // Truyền cả $user (sinh viên) và $admin vào view
    }

    /**
     * Lưu tin nhắn trả lời từ Admin cho Sinh viên.
     */
    public function reply(Request $request, User $user) // $user ở đây là sinh viên nhận tin nhắn
    {
       // Chỉ validate content vì không có subject
       $request->validate([
        'content' => 'required|string|max:5000',
    ]);

    $admin = Auth::user();

    if (!$user->hasRole('SinhVien')) {
          return back()->with('error', 'Không thể gửi tin nhắn cho người dùng này.')->withInput();
     }

    // Tạo tin nhắn mới - Bỏ trường subject
    $newMessage = Message::create([
        'sender_id' => $admin->id,
        'receiver_id' => $user->id,
        // 'subject' => $request->subject, // <<< XÓA HOẶC COMMENT DÒNG NÀY
        'content' => $request->input('content'),
    ]);

    // Kiểm tra xem $newMessage có được tạo thành công không (tùy chọn)
    if (!$newMessage) {
        Log::error("Không thể tạo tin nhắn mới từ Admin {$admin->id} cho SinhVien {$user->id}");
        return back()->with('error', 'Đã xảy ra lỗi khi gửi tin nhắn. Vui lòng thử lại.')->withInput();
    }


    // Gửi thông báo cho Sinh viên
    try {
        $user->notify(new NewMessageNotification($newMessage));
    } catch (\Exception $e) {
        Log::error("Lỗi gửi thông báo tin nhắn mới từ Admin {$admin->id} cho SinhVien {$user->id}: " . $e->getMessage());
        // Vẫn nên báo thành công gửi tin nhắn dù notification lỗi
    }

    return redirect()->route('admin.messages.show', $user->id)->with('success', 'Đã gửi trả lời thành công!');
    }
}

/**
 * Thêm relationship này vào app/Models/User.php để lấy tin nhắn cuối cùng hiệu quả
 */
 // public function lastMessage(): \Illuminate\Database\Eloquent\Relations\HasOne
 // {
 //     return $this->hasOne(Message::class, 'id', 'last_message_id');
 // }