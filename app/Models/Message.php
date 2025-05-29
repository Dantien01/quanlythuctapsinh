<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; // << THÊM IMPORT NÀY
use App\Models\Conversation; // << UNCOMMENT DÒNG NÀY
use App\Models\User;       // Đảm bảo User model cũng được import (thường đã có)
use App\Models\MessageReadStatus; // << UNCOMMENT DÒNG NÀY

class Message extends Model
{
    use HasFactory;

    /**
     * Các thuộc tính có thể được gán hàng loạt.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'content',
        // 'subject', // TùY CHỌN: Giữ lại nếu bạn vẫn dùng, xóa nếu không
    ];

    /**
     * Các thuộc tính nên được chuyển đổi kiểu dữ liệu.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Lấy cuộc trò chuyện mà tin nhắn này thuộc về.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    /**
     * Lấy người gửi tin nhắn.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Lấy tất cả các trạng thái đọc cho tin nhắn này.
     * Sử dụng nếu bạn có bảng message_read_statuses.
     */
    public function readStatuses(): HasMany // << UNCOMMENT VÀ THÊM KIỂU TRẢ VỀ HasMany
    {                                        // << UNCOMMENT
        return $this->hasMany(MessageReadStatus::class, 'message_id'); // << UNCOMMENT
    }                                        // << UNCOMMENT

    /**
     * (TÙY CHỌN) Kiểm tra xem tin nhắn đã được đọc bởi một người dùng cụ thể chưa.
     * Sử dụng nếu bạn có bảng message_read_statuses.
     *
     * @param int $userId
     * @return bool
     */
    public function isReadBy(int $userId): bool // << UNCOMMENT NẾU BẠN MUỐN SỬ DỤNG TRỰC TIẾP PHƯƠNG THỨC NÀY
    {
        // Kiểm tra xem có bản ghi nào trong message_read_statuses cho message này và user này
        // mà có read_at không null không.
        // Eager load readStatuses nếu nó chưa được load để tối ưu
        if (!$this->relationLoaded('readStatuses')) {
            $this->load('readStatuses');
        }
        return $this->readStatuses->where('user_id', $userId)->whereNotNull('read_at')->isNotEmpty();
    }
}