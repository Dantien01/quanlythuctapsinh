<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternshipSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'day_of_week',      // Số từ 1 (Thứ Hai) đến 7 (Chủ Nhật)
        'start_time',       // Dạng HH:MM:SS (ví dụ: '08:00:00')
        'end_time',         // Dạng HH:MM:SS (ví dụ: '17:30:00')
        'task_description',
        'location',
        'notes',
    ];

    /**
     * Các thuộc tính nên được nối thêm vào mảng hoặc JSON của model.
     *
     * @var array
     */
    protected $appends = ['day_name']; // <<<< ĐẢM BẢO DÒNG NÀY CÓ VÀ ĐÚNG

    // Quan hệ với Schedule
    public function schedule(): BelongsTo
    {
        // Đảm bảo App\Models\Schedule được import hoặc dùng namespace đầy đủ
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    /**
     * Get the name of the day for the slot.
     * Sẽ được tự động gọi khi truy cập $slot->day_name hoặc khi model được serialize (do có trong $appends)
     *
     * @return string
     */
    public function getDayNameAttribute(): string
    {
        // Gọi hàm static getDayName từ model Schedule
        // Đảm bảo model Schedule có phương thức public static getDayName($dayOfWeekNumber)
        return Schedule::getDayName($this->day_of_week);
    }

    // (Tùy chọn) Casts
    // protected $casts = [
    //     'start_time' => 'datetime:H:i:s',
    //     'end_time'   => 'datetime:H:i:s',
    // ];
}