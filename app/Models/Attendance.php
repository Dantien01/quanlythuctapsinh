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
 * @property int|null $schedule_id
 * @property \Illuminate\Support\Carbon|null $check_in_time
 * @property \Illuminate\Support\Carbon|null $check_out_time
 * @property string $status
 * @property string|null $image_path
 * @property string|null $notes
 * @property string|null $created_by
 * @property \Illuminate\Support\Carbon|null $attendance_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @property-read \App\Models\Schedule|null $schedule
 * @property-read float|null $work_duration_in_hours
 * @property-read string $status_text
 * // PHPDoc sẽ tự động cập nhật khi bạn chạy `php artisan ide-helper:models` sau khi thêm hằng số mới
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance query()
 * @mixin \Eloquent
 */
class Attendance extends Model
{
    use HasFactory;

    // =========================================================================
    // Hằng số cho trạng thái điểm danh
    // =========================================================================
    // Các hằng số này có thể dùng cho cả Admin và Student,
    // nhưng API của Student có thể chỉ sử dụng một vài trong số này.
    const STATUS_PRESENT                   = 'present';                     // Có mặt (Có thể là kết quả của on_time hoặc admin set)
    const STATUS_LATE                      = 'late';                        // Đi trễ
    const STATUS_ABSENT_WITH_PERMISSION    = 'absent_with_permission';    // Vắng có phép
    const STATUS_ABSENT_WITHOUT_PERMISSION = 'absent_without_permission'; // Vắng không phép
    const STATUS_EARLY_LEAVE               = 'early_leave';               // Về sớm

    // === Hằng số THÊM MỚI cho logic API Clock In/Out của Sinh viên ===
    const STATUS_CLOCKED_IN                = 'clocked_in';                // Sinh viên đã clock in, đang trong phiên
    const STATUS_CLOCKED_OUT               = 'clocked_out';               // Sinh viên đã clock out, kết thúc phiên
    // Bạn có thể chọn chỉ dùng CLOCKED_IN và CLOCKED_OUT cho API sinh viên,
    // và các status như PRESENT, LATE sẽ được tính toán/cập nhật bởi admin hoặc một job sau đó.
    // Hoặc API clockIn có thể tự quyết định là PRESENT hay LATE dựa trên thời gian.
    // Để đơn giản cho API ban đầu, CLOCKED_IN và CLOCKED_OUT là đủ.
    // Hoặc bạn có thể dùng lại logic của Student\AttendanceController để set 'on_time', 'late'
    const STATUS_ON_TIME                   = 'on_time'; // Thêm nếu bạn muốn API clockIn tự set on_time/late
    // const STATUS_LATE                      = 'late'; // Đã có ở trên, dùng chung được

    /**
     * Các thuộc tính có thể được gán hàng loạt.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'schedule_id',
        'check_in_time',
        'check_out_time',
        'status',
        'image_path',
        'notes',
        'attendance_date',
        'created_by',
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
    ];

    /**
     * Lấy người dùng (sinh viên) của lượt điểm danh này.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Lấy lịch trình (schedule) liên quan đến lượt điểm danh này.
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

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
     */
    public function getStatusTextAttribute(): string
    {
        $statusTexts = [
            self::STATUS_PRESENT                   => __('Có mặt'), // Present có thể là kết quả của on_time
            self::STATUS_LATE                      => __('Đi trễ'),
            self::STATUS_ABSENT_WITH_PERMISSION    => __('Vắng có phép'),
            self::STATUS_ABSENT_WITHOUT_PERMISSION => __('Vắng không phép'),
            self::STATUS_EARLY_LEAVE               => __('Về sớm'),
            self::STATUS_CLOCKED_IN                => __('Đã vào ca'), // Thêm cho API
            self::STATUS_CLOCKED_OUT               => __('Đã kết thúc ca'), // Thêm cho API
            self::STATUS_ON_TIME                   => __('Đúng giờ'), // Thêm nếu API tự set
        ];
        return $statusTexts[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status ?? 'Chưa xác định'));
    }
}