<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageReadStatus;
use App\Http\Resources\MessageResource;
// use App\Notifications\NewMessageNotification; // Bỏ comment nếu bạn sử dụng
use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Str; // Không thấy sử dụng Str trực tiếp

class MessageApiController extends Controller
{
    /**
     * Helper để lấy admin user (có thể cache).
     * @return User|null
     */
    private function getDefaultAdmin(): ?User
    {
        // Bạn có thể cache admin này nếu nó ít thay đổi
        // return cache()->remember('default_messaging_admin', now()->addHours(1), function () {
        //     return User::whereHas('role', fn($q) => $q->where('name', 'Admin'))->first();
        // });
        return User::whereHas('role', fn($q) => $q->where('name', 'Admin'))->first();
    }

    /**
     * Lấy cuộc trò chuyện với Admin và danh sách tin nhắn.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConversationWithAdmin(Request $request)
    {
        $student = Auth::user();
        $admin = $this->getDefaultAdmin();

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy quản trị viên để bắt đầu cuộc trò chuyện.'
            ], 404);
        }

        $conversation = Conversation::findOrCreateBetween($student, $admin);

        $messages = $conversation->messages()
                           // CẬP NHẬT EAGER LOADING Ở ĐÂY
                           ->with('sender:id,name,profile_photo_path') // Eager load sender và chọn các cột cần thiết
                           ->orderBy('created_at', 'desc')
                           ->paginate($request->input('per_page', 15));

        $adminData = [
            'id' => $admin->id,
            'name' => $admin->name,
            'avatar_url' => $admin->profile_photo_url, // Giả sử admin có profile_photo_url
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'conversation_id' => $conversation->id,
                'admin' => $adminData,
                'messages' => MessageResource::collection($messages)->response()->getData(true),
            ],
            'message' => 'Conversation retrieved successfully.'
        ]);
    }

    /**
     * Sinh viên gửi một tin nhắn mới cho Admin.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMessageToAdmin(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $student = Auth::user();
        $admin = $this->getDefaultAdmin();

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy quản trị viên để gửi tin nhắn.'
            ], 404);
        }

        $conversation = Conversation::findOrCreateBetween($student, $admin);

        try {
            $newMessage = $conversation->messages()->create([
                'sender_id' => $student->id,
                'content' => $request->input('content'),
            ]);

            MessageReadStatus::create([
                'message_id' => $newMessage->id,
                'user_id' => $admin->id,
                'read_at' => null,
            ]);

            // Tạm thời comment out notification để tập trung vào core logic
            // if ($admin->shouldReceiveNewMessageNotifications()) {
            //     $admin->notify(new NewMessageNotification($newMessage->load('sender')));
            // }

            return response()->json([
                'success' => true,
                'data' => new MessageResource($newMessage->load('sender')), // load('sender') để MessageResource có thông tin sender
                'message' => 'Message sent successfully.'
            ], 201);

        } catch (\Exception $e) {
            Log::error("Student sendMessageToAdmin Error: User {$student->id} to Admin {$admin->id} in Conv {$conversation->id}. Msg: " . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message. Please try again.'
            ], 500);
        }
    }

    /**
     * Đánh dấu các tin nhắn từ Admin trong cuộc trò chuyện là đã đọc bởi Sinh viên.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markConversationAsRead(Request $request)
    {
        $student = Auth::user();
        $admin = $this->getDefaultAdmin();

        if (!$admin) {
            return response()->json(['success' => false, 'message' => 'Admin not found.'], 404);
        }

        $conversation = Conversation::whereHas('participants', function ($q) use ($student) {
            $q->where('users.id', $student->id);
        })->whereHas('participants', function ($q) use ($admin) {
            $q->where('users.id', $admin->id);
        })->first();

        if (!$conversation) {
            return response()->json(['success' => true, 'message' => 'No conversation found to mark as read.']);
        }

        $this->doMarkMessagesAsRead($conversation, $student, $admin);

        return response()->json(['success' => true, 'message' => 'Messages marked as read.']);
    }

    /**
     * Helper method để đánh dấu tin nhắn đã đọc (đổi tên để rõ ràng hơn)
     *
     * @param Conversation $conversation
     * @param User $reader Người đọc tin nhắn
     * @param User $senderOfMessagesToMark Người gửi của những tin nhắn cần đánh dấu là đã đọc
     */
    private function doMarkMessagesAsRead(Conversation $conversation, User $reader, User $senderOfMessagesToMark): void
    {
        $messageIdsToMarkAsRead = Message::where('conversation_id', $conversation->id)
            ->where('sender_id', $senderOfMessagesToMark->id)
            ->whereDoesntHave('readStatuses', function ($query) use ($reader) {
                $query->where('user_id', $reader->id)->whereNotNull('read_at');
            })
            ->pluck('id');

        if ($messageIdsToMarkAsRead->isNotEmpty()) {
            $now = now();
            $statusesToInsert = $messageIdsToMarkAsRead->map(function ($messageId) use ($reader, $now) {
                return [
                    'message_id' => $messageId,
                    'user_id' => $reader->id,
                    'read_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })->all();

            MessageReadStatus::insert($statusesToInsert);
            Log::info("User {$reader->id} marked " . count($statusesToInsert) . " messages from user {$senderOfMessagesToMark->id} in conversation {$conversation->id} as read.");
        } else {
             Log::info("User {$reader->id} - No new messages from user {$senderOfMessagesToMark->id} in conversation {$conversation->id} to mark as read.");
        }
    }
}