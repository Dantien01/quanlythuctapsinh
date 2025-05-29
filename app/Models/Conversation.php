<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB; // << Đảm bảo có dòng này
use Illuminate\Support\Facades\Log; // << Đảm bảo có dòng này
// use Illuminate\Database\Eloquent\Concerns\HasUuids; // Uncomment nếu dùng UUID
// use Illuminate\Support\Str; // Uncomment nếu dùng UUID

class Conversation extends Model
{
    use HasFactory;
    // use HasUuids; // Uncomment nếu dùng UUID

    // ... (các thuộc tính $fillable, $casts, $incrementing, $keyType nếu cần) ...

    // ... (các mối quan hệ participants, messages, lastMessage) ...
    public function participants()
    {
        return $this->belongsToMany(User::class, 'conversation_participants', 'conversation_id', 'user_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id')->orderBy('created_at', 'asc');
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class, 'conversation_id')->latestOfMany();
    }

    public function getOtherParticipant(int $currentUserId): ?User
    {
        return $this->participants()->where('users.id', '!=', $currentUserId)->first();
    }
    // ...

    public static function findOrCreateBetween(User $user1, User $user2): Conversation
    {
        // Đảm bảo $user1 và $user2 thực sự là instance của User và có id
        if (!$user1 instanceof User || !$user1->id || !$user2 instanceof User || !$user2->id) {
            Log::error("MIGRATION_DATA or CHAT_LOGIC: Invalid user objects passed to findOrCreateBetween.");
            throw new \InvalidArgumentException("Invalid user objects provided.");
        }

        $sortedUserIds = collect([$user1->id, $user2->id])->sort()->values();

        // Log để debug
        Log::debug("findOrCreateBetween: Attempting to find conversation for users: " . $sortedUserIds->implode(', '));

        $conversation = Conversation::whereHas('participants', function ($query) use ($sortedUserIds) {
            $query->whereIn('user_id', $sortedUserIds->all());
        }, '=', count($sortedUserIds->all())) // Phải có tất cả user trong $sortedUserIds
        ->whereDoesntHave('participants', function ($query) use ($sortedUserIds) {
            $query->whereNotIn('user_id', $sortedUserIds->all()); // Và không có user nào khác
        })->first();


        if (!$conversation) {
            Log::debug("findOrCreateBetween: No existing conversation found. Creating new one for users: " . $sortedUserIds->implode(', '));
            DB::beginTransaction();
            try {
                $conversation = Conversation::create([
                    // Nếu ID là UUID và bạn tự tạo, thêm 'id' => (string) Str::uuid() ở đây
                    // Nếu ID là auto-increment hoặc dùng HasUuids trait, không cần làm gì
                ]);

                // Gắn 2 người dùng vào cuộc trò chuyện mới
                $conversation->participants()->attach($sortedUserIds->all());

                DB::commit();
                Log::info("findOrCreateBetween: Successfully created new conversation (ID: {$conversation->id}) for users: " . $sortedUserIds->implode(', '));
            } catch (\Exception $e) {
                DB::rollBack();
                // Sử dụng Log::error cho lỗi nghiêm trọng
                Log::error("findOrCreateBetween: Error creating conversation between user {$user1->id} and {$user2->id}. Error: " . $e->getMessage(), ['exception' => $e]);
                throw $e; // Ném lại lỗi để xử lý ở nơi gọi
            }
        } else {
            Log::debug("findOrCreateBetween: Found existing conversation (ID: {$conversation->id}) for users: " . $sortedUserIds->implode(', '));
        }
        return $conversation;
    }
}