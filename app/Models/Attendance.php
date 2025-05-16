<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon; // Đã có import Carbon

/**
 * App\Models\Attendance
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $schedule_id // <<< THÊM VÀO PHPDOC NẾU CÓ (QUAN TRỌNG CHO COMMAND)
 * @property \Illuminate\Support\Carbon|null $check_in_time
 * @property \Illuminate\Support\Carbon|null $check_out_time
 * @property string $status
 * @property string|null $image_path
 * @property string|null $notes
 * @property string|null $created_by // <<< THÊM VÀO PHPDOC NẾU CÓ
 * @property \Illuminate\Support\Carbon|null $attendance_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @property-read \App\Models\Schedule|null $schedule // <<< THÊM VÀO PHPDOC NẾU CÓ RELATIONSHIP
 * @property-read float|null $work_duration_in_hours
 * @property-read string $status_text // <<< THÊM VÀO PHPDOC CHO ACCESSOR MỚI
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance query()
 * // ... (các method khác giữ nguyên) ...
 * @mixin \Eloquent
 */
class Attendance extends Model
{
    use HasFactory;

    // =========================================================================
    // Hằng số cho trạng thái điểm danh
    // =========================================================================
    const STATUS_PRESENT                   = 'present';                     // Có mặt đúng giờ
    const STATUS_LATE                      = 'late';                        // Đi trễ
    const STATUS_ABSENT_WITH_PERMISSION    = 'absent_with_permission';    // Vắng có phép
    const STATUS_ABSENT_WITHOUT_PERMISSION = 'absent_without_permission'; // Vắng không phép (Hệ thống tự động ghi nhận)
    const STATUS_EARLY_LEAVE               = 'early_leave';               // (Tùy chọn) Về sớm
    // Bạn có thể thêm các trạng thái khác nếu cần

    /**
     * Các thuộc tính có thể được gán hàng loạt.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'schedule_id',      // <<< THÊM 'schedule_id' NẾU CHƯA CÓ (RẤT QUAN TRỌNG CHO COMMAND)
        'check_in_time',
        'check_out_time',
        'status',
        'image_path',
        'notes',
        'attendance_date',
        'created_by',       // <<< THÊM 'created_by' NẾU COMMAND CẦN GHI NHẬN NGƯỜI TẠO (có thể là null)
        // Thêm 'ip_address', 'device_info' nếu bạn có các cột này
    ];

    /**
     * Các thuộc tính nên được chuyển đổi kiểu dữ liệu.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'check_in_time'   => 'datetime',
        'check_out_time'  => 'datetime',
        'attendance_date' => 'date',
        // Nếu bạn dùng Enum cho status, thêm vào đây
        // 'status' => \App\Enums\AttendanceStatus::class,
    ];

    /**
     * Lấy người dùng (sinh viên) của lượt điểm danh này.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class); // Giả sử foreign key là user_id (mặc định)
    }

    /**
     * Lấy lịch trình (schedule) liên quan đến lượt điểm danh này.
     * QUAN TRỌNG: Cần có cột `schedule_id` trong bảng `attendances`.
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class); // Giả sử foreign key là schedule_id
    }

    /**
     * (Tùy chọn) Lấy người dùng (Admin) đã tạo/sửa bản ghi điểm danh này.
     * Cần cột `created_by` (hoặc `updated_by`) trong bảng `attendances`.
     */
    // public function editor(): BelongsTo
    // {
    //     return $this->belongsTo(User::class, 'created_by'); // Hoặc 'updated_by'
    // }


    /**
     * Accessor: Tự động tính toán thời gian làm việc (tính bằng giờ)
     */
    public function getWorkDurationInHoursAttribute(): ?float
    {
        if ($this->check_in_time && $this->check_out_time) {
            return round($this->check_out_time->diffInSeconds($this->check_in_time) / 3600, 2);
        }
        return null;
    }

    /**
     * Accessor: Lấy text mô tả cho trạng thái điểm danh.
     * Cách dùng: $attendance->status_text
     */
    public function getStatusTextAttribute(): string
    {
        $statusTexts = [
            self::STATUS_PRESENT                   => __('Có mặt'),
            self::STATUS_LATE                      => __('Đi trễ'),
            self::STATUS_ABSENT_WITH_PERMISSION    => __('Vắng có phép'),
            self::STATUS_ABSENT_WITHOUT_PERMISSION => __('Vắng không phép'),
            self::STATUS_EARLY_LEAVE               => __('Về sớm'),
            // Thêm các case khác nếu bạn định nghĩa thêm STATUS_*
        ];
        return $statusTexts[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status ?? 'Chưa xác định'));
    }
}