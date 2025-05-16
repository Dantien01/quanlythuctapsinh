<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo
use App\Models\User; // Import User để sử dụng trong relationship

class Message extends Model
{
    use HasFactory; // Bỏ Notifiable nếu không cần thiết cho model này

    /**
     * Các thuộc tính có thể được gán hàng loạt.
     * Đổi 'content' thành 'body' và thêm 'subject' để khớp với logic controller/migration.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'subject', // Thêm subject (nếu bạn dùng trong controller/migration)
        'content',    // Đổi từ 'content' thành 'body'
        'read_at'
    ];

    /**
     * Các thuộc tính nên được chuyển đổi kiểu dữ liệu.
     * Thêm casting cho read_at.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'read_at' => 'datetime', // Chuyển đổi read_at thành đối tượng Carbon
    ];

    /**
     * Lấy người gửi tin nhắn.
     * Relationship đã đúng.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Lấy người nhận tin nhắn.
     * Relationship đã đúng.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}