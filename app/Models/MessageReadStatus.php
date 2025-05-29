<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // << THÊM IMPORT NÀY

class MessageReadStatus extends Model
{
    use HasFactory;

    protected $table = 'message_read_statuses';

    protected $fillable = [
        'message_id',
        'user_id',
        'read_at',
        // 'conversation_id', // Nếu bạn đã thêm cột này vào bảng và muốn fillable
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public $timestamps = true; // Hoặc false tùy theo migration của bạn

    /**
     * Lấy tin nhắn mà trạng thái đọc này thuộc về.
     */
    public function message(): BelongsTo // << ĐỊNH NGHĨA MỐI QUAN HỆ NÀY
    {
        return $this->belongsTo(Message::class, 'message_id');
    }

    /**
     * Lấy người dùng đã đọc tin nhắn.
     */
    public function user(): BelongsTo // << ĐỊNH NGHĨA MỐI QUAN HỆ NÀY
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}