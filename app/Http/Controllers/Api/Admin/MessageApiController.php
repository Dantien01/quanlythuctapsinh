<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageReadStatus;
use App\Http\Resources\MessageResource;
use App\Http\Resources\Admin\AdminConversationCollection;
// use App\Notifications\NewMessageNotification; // Bỏ comment nếu bạn sử dụng
use Illuminate\Support\Facades\Log;

class MessageApiController extends Controller
{
    /**
     * Lấy danh sách tất cả các cuộc trò chuyện của Admin.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Resources\Admin\AdminConversationCollection|\Illuminate\Http\JsonResponse
     */
    public function getAllConversations(Request $request)
    {
        $admin = Auth::user();

        if (!$admin instanceof User) {
            Log::error('MessageApiController_getAllConversations: Auth::user() did not return a User object.', [
                'user_type' => gettype($admin),
                'user_value' => $admin
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Authentication error: Admin user not found or invalid.'
            ], 401);
        }
        Log::info('MessageApiController_getAllConversations: Admin user retrieved successfully.', ['admin_id' => $admin->id]);

        $query = Conversation::whereHas('participants', function ($q) use ($admin) {
            $q->where('users.id', $admin->id);
        })
        ->whereHas('participants', function ($q) {
            $q->whereHas('role', fn($r) => $r->where('name', 'SinhVien'));
        })
        ->with([
            'participants' => function ($q) use ($admin) {
                $q->where('users.id', '!=', $admin->id)
                  ->select('users.id', 'users.name', 'users.mssv', 'users.profile_photo_path'); // Chọn các cột cần thiết cho participant (student)
            },
            // CẬP NHẬT EAGER LOADING Ở ĐÂY
            'lastMessage.sender:id,name,profile_photo_path' // Eager load lastMessage VÀ sender của nó, chỉ lấy cột id, name, và profile_photo_path
        ])
        ->withCount(['messages as unread_messages_count' => function ($q) use ($admin) {
            $q->where('sender_id', '!=', $admin->id)
              ->whereDoesntHave('readStatuses', function ($sq) use ($admin) {
                  $sq->where('user_id', $admin->id)->whereNotNull('read_at');
              });
        }]);

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->whereHas('participants', function ($q) use ($admin, $searchTerm) {
                $q->where('users.id', '!=', $admin->id)
                  ->where(function ($sq) use ($searchTerm) {
                      $sq->where('users.name', 'like', "%{$searchTerm}%")
                         ->orWhere('users.mssv', 'like', "%{$searchTerm}%");
                  });
            });
        }

        $conversationsPaginated = $query->orderByDesc(
            Message::select('created_at')
                ->whereColumn('conversation_id', 'conversations.id')
                ->latest()
                ->limit(1)
        )->paginate($request->input('per_page', 15));

        if ($admin instanceof User) {
            Log::info('MessageApiController_getAllConversations: Passing adminUser (User object) to AdminConversationCollection.', ['admin_id' => $admin->id]);
        } else {
            Log::critical('MessageApiController_getAllConversations: CRITICAL - adminUser is NOT a User object just before creating AdminConversationCollection!', [
                'admin_type' => gettype($admin)
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Critical server error: Admin context lost.'
            ], 500);
        }

        return new AdminConversationCollection($conversationsPaginated, $admin);
    }

    /**
     * Lấy chi tiết cuộc trò chuyện với một Sinh viên.
     * (Giữ nguyên, không có thay đổi lớn cần thiết dựa trên yêu cầu gần nhất)
     * @param  \App\Models\User  $student
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConversationWithStudent(User $student, Request $request)
    {
        $admin = Auth::user();

        if (!$admin instanceof User) {
            Log::error('MessageApiController_getConversationWithStudent: Auth::user() did not return a User object.', ['user_type' => gettype($admin)]);
            return response()->json(['success' => false, 'message' => 'Authentication error: Admin user not found or invalid.'], 401);
        }

        if (!$student->hasRole('SinhVien')) {
            return response()->json(['success' => false, 'message' => 'User is not a student.'], 404);
        }

        $conversation = Conversation::findOrCreateBetween($admin, $student);

        $messages = $conversation->messages()
            ->with('sender:id,name,profile_photo_path') // Đảm bảo sender được eager load đầy đủ cột nếu MessageResource cần
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        $studentData = [
            'id' => $student->id,
            'name' => $student->name,
            'mssv' => $student->mssv,
            'avatar_url' => $student->profile_photo_url
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'conversation_id' => $conversation->id,
                'student' => $studentData,
                'messages' => MessageResource::collection($messages)->response()->getData(true),
            ],
            'message' => 'Conversation details retrieved successfully.'
        ]);
    }

    /**
     * Admin gửi tin nhắn cho một Sinh viên.
     * (Giữ nguyên)
     * @param  \App\Models\User  $student
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMessageToStudent(User $student, Request $request)
    {
        $request->validate(['content' => 'required|string|max:5000']);
        $admin = Auth::user();

        if (!$admin instanceof User) {
            Log::error('MessageApiController_sendMessageToStudent: Auth::user() did not return a User object.', ['user_type' => gettype($admin)]);
            return response()->json(['success' => false, 'message' => 'Authentication error: Admin user not found or invalid.'], 401);
        }

        if (!$student->hasRole('SinhVien')) {
            return response()->json(['success' => false, 'message' => 'Cannot send message to this user type.'], 422);
        }

        $conversation = Conversation::findOrCreateBetween($admin, $student);

        try {
            $newMessage = $conversation->messages()->create([
                'sender_id' => $admin->id,
                'content' => $request->input('content'),
            ]);

             MessageReadStatus::updateOrCreate(
                ['message_id' => $newMessage->id, 'user_id' => $admin->id,],
                ['read_at' => now()]
            );

            // Thông báo cho student (tùy chọn)
            // $student->notify(new NewMessageNotification($newMessage->load('sender')));

            return response()->json([
                'success' => true,
                'data' => new MessageResource($newMessage->load('sender')), // load('sender') để MessageResource có thông tin sender
                'message' => 'Message sent to student successfully.'
            ], 201);

        } catch (\Exception $e) {
            Log::error("Admin sendMessageToStudent Error: Admin {$admin->id} to Student {$student->id} in Conv {$conversation->id}. Msg: " . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Failed to send message.'], 500);
        }
    }

    /**
     * Đánh dấu các tin nhắn từ Sinh viên trong cuộc trò chuyện là đã đọc bởi Admin.
     * (Giữ nguyên)
     * @param  \App\Models\User  $student
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markConversationAsRead(User $student, Request $request)
    {
        $admin = Auth::user();
        if (!$admin instanceof User) {
            Log::error('MessageApiController_markConversationAsRead: Auth::user() did not return a User object.', ['user_type' => gettype($admin)]);
            return response()->json(['success' => false, 'message' => 'Authentication error.'], 401);
        }

        if (!$student->hasRole('SinhVien')) {
            return response()->json(['success' => false, 'message' => 'Target user is not a student.'], 404);
        }

        $conversation = Conversation::whereHas('participants', function ($q) use ($admin) {
            $q->where('users.id', $admin->id);
        })->whereHas('participants', function ($q) use ($student) {
            $q->where('users.id', $student->id);
        })->first();

        if (!$conversation) {
            return response()->json(['success' => true, 'message' => 'No conversation found.']);
        }

        $messageIdsToMarkAsRead = Message::where('conversation_id', $conversation->id)
            ->where('sender_id', $student->id)
            ->whereDoesntHave('readStatuses', function ($query) use ($admin) {
                $query->where('user_id', $admin->id)->whereNotNull('read_at');
            })
            ->pluck('id');

        if ($messageIdsToMarkAsRead->isNotEmpty()) {
            $now = now();
            $statusesToInsert = $messageIdsToMarkAsRead->map(function ($messageId) use ($admin, $now) {
                return ['message_id' => $messageId, 'user_id' => $admin->id, 'read_at' => $now, 'created_at' => $now, 'updated_at' => $now,];
            })->all();
            MessageReadStatus::insert($statusesToInsert);
            Log::info("Admin {$admin->id} marked " . count($statusesToInsert) . " messages from student {$student->id} in conversation {$conversation->id} as read.");
        } else {
            Log::info("Admin {$admin->id} - No new messages from student {$student->id} in conversation {$conversation->id} to mark as read.");
        }

        return response()->json(['success' => true, 'message' => 'Messages from student marked as read.']);
    }
}