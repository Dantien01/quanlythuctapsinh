<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo đã có
use Illuminate\Database\Eloquent\Relations\HasMany;   // Import HasMany đã có

class Diary extends Model
{
    use HasFactory;

    /**
     * Các thuộc tính có thể được gán hàng loạt.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'diary_date',
        'title',
        'content',
        'status',
        // --- THÊM CÁC TRƯỜNG CHO NHẬN XÉT ADMIN ---
        'admin_comment',
        'grade',
        'reviewed_at',
        'reviewed_by',
        // --------------------------------------
    ];

    /**
     * Các thuộc tính nên được chuyển đổi kiểu dữ liệu.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'diary_date' => 'date', // <<< GIỮ NGUYÊN DÒNG NÀY
        // --- THÊM CASTING CHO NGÀY NHẬN XÉT ---
        'reviewed_at' => 'datetime',
        // -----------------------------------
    ];

    /**
     * Lấy người dùng (sinh viên) đã viết nhật ký này.
     */
    public function user(): BelongsTo // Giữ nguyên relationship này (có thể đổi tên thành student nếu muốn)
    {
        return $this->belongsTo(User::class, 'user_id'); // Đảm bảo khóa ngoại đúng là user_id
    }

    /**
     * Relationship: Lấy tất cả comments của nhật ký này
     */
    public function comments(): HasMany // <<< GIỮ NGUYÊN RELATIONSHIP NÀY
    {
        // Sắp xếp comment theo thời gian tạo tăng dần (cũ nhất trước)
        return $this->hasMany(DiaryComment::class)->orderBy('created_at', 'asc');
    }

    // <<< THÊM RELATIONSHIP ĐỂ LẤY ADMIN ĐÃ NHẬN XÉT >>>
    /**
     * Get the user (admin) who reviewed the diary.
     * Lấy admin đã nhận xét nhật ký này.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by'); // Liên kết qua cột reviewed_by
    }
    // <<< KẾT THÚC THÊM RELATIONSHIP REVIEWER >>>
}