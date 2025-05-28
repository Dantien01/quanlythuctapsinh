<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Concerns\HasUuids; // Uncomment nếu dùng UUID
// use Illuminate\Support\Str; // Uncomment nếu dùng UUID

class Conversation extends Model
{
    use HasFactory;
    // use HasUuids; // Uncomment nếu dùng UUID

    // ... (các thuộc tính $fillable, $casts, $incrementing, $keyType nếu cần) ...

    /**
     * Get the participants of the conversation.
     * Mối quan hệ Many-to-Many với User thông qua bảng trung gian 'conversation_participants'.
     */
    public function participants()
    {
        // Tên bảng trung gian: 'conversation_participants'
        // Khóa ngoại trong bảng trung gian trỏ về Conversation: 'conversation_id'
        // Khóa ngoại trong bảng trung gian trỏ về User: 'user_id'
        return $this->belongsToMany(User::class, 'conversation_participants', 'conversation_id', 'user_id');
    }

    /**
     * Get all of the messages for the conversation.
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id')->orderBy('created_at', 'asc');
    }

    /**
     * Get the last message of the conversation.
     */
    public function lastMessage()
    {
        return $this->hasOne(Message::class, 'conversation_id')->latestOfMany();
    }

    // ... (Hàm findOrCreateBetween nếu bạn đã thêm vào) ...
}