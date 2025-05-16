<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon; // Import Carbon

class Schedule extends Model
{
    use HasFactory;

    // =========================================================================
    // Hằng số cho trạng thái lịch (STATUS)
    // =========================================================================
    const STATUS_SCHEDULED          = 'scheduled';          // Đã lên lịch, chờ diễn ra
    const STATUS_COMPLETED          = 'completed';          // Đã hoàn thành (có thể do sinh viên tham gia, hoặc admin đánh dấu)
    const STATUS_CANCELLED_BY_ADMIN = 'cancelled_admin';    // Admin hủy lịch
    const STATUS_CANCELLED_BY_STUDENT = 'cancelled_student';// Sinh viên yêu cầu và được chấp nhận hủy (nếu có chức năng này)
    const STATUS_PENDING_CHANGE     = 'pending_change';     // Sinh viên yêu cầu thay đổi, chờ admin duyệt
    const STATUS_CHANGE_APPROVED    = 'change_approved';    // Yêu cầu thay đổi được chấp nhận (lịch đã được cập nhật)
    const STATUS_CHANGE_REJECTED    = 'change_rejected';    // Yêu cầu thay đổi bị từ chối
    const STATUS_SYSTEM_PROCESSED   = 'system_processed';   // Lịch đã qua và hệ thống đã xử lý điểm danh/vắng mặt

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',                  // ID sinh viên (người được gán lịch)
        'created_by',               // ID người tạo lịch (Admin)
        'title',
        'description',
        'start_time',               // Kiểu datetime trong CSDL
        'end_time',                 // Kiểu datetime trong CSDL
        'status',                   // Trạng thái của lịch (sử dụng các hằng số trên)
        'is_mandatory_attendance',  // boolean: Buổi học có bắt buộc điểm danh không
        'change_reason',            // Lý do yêu cầu thay đổi (nếu có)
        // Thêm các trường khác bạn có trong bảng 'schedules' mà cần mass assignable
        // Ví dụ: 'location', 'requested_new_start_time', 'requested_new_end_time', 'change_request_status'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time'              => 'datetime',
        'end_time'                => 'datetime',
        'is_mandatory_attendance' => 'boolean',
        // Nếu bạn sử dụng Enum cho 'status', hãy thêm cast ở đây:
        // 'status' => \App\Enums\ScheduleStatus::class,
    ];

    // =========================================================================
    // Relationships
    // =========================================================================

    /**
     * Lấy thông tin sinh viên được gán lịch này.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Lấy thông tin người đã tạo lịch này (thường là Admin).
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Lấy các bản ghi điểm danh liên quan đến lịch này.
     * Một lịch có thể có một bản ghi điểm danh (nếu là lịch cá nhân cho user_id)
     * hoặc nhiều bản ghi (nếu user_id chỉ là người tham chiếu chính và lịch áp dụng cho một nhóm).
     * Điều chỉnh relationship nếu cần thiết cho logic của bạn.
     */
    public function attendances()
    {
        // Giả sử mỗi schedule_id trong bảng attendances là duy nhất cho một student_id cụ thể
        // Nếu một schedule (ví dụ do user_id tạo) có thể áp dụng cho nhiều sinh viên khác nhau
        // thì bạn cần một bảng trung gian hoặc cấu trúc khác.
        // Hiện tại, giả định schedule này là của user_id (student).
        return $this->hasMany(Attendance::class); // Hoặc hasOne nếu mỗi schedule chỉ có 1 record điểm danh của user_id này
    }


    // =========================================================================
    // Scopes Query
    // =========================================================================

    /**
     * Lấy các lịch đã qua (dựa trên end_time).
     */
    public function scopePast($query)
    {
        return $query->where('end_time', '<', Carbon::now());
    }

    /**
     * Lấy các lịch sắp tới (dựa trên start_time).
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>=', Carbon::now());
    }

    /**
     * Lấy các lịch đã bị hủy (bởi Admin hoặc Sinh viên).
     */
    public function scopeCancelled($query)
    {
        return $query->whereIn('status', [self::STATUS_CANCELLED_BY_ADMIN, self::STATUS_CANCELLED_BY_STUDENT]);
    }

    /**
     * Lấy các lịch đang ở trạng thái 'scheduled' (chờ diễn ra và chưa bị hủy).
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    /**
     * Lấy các lịch đang ở trạng thái 'pending_change'.
     */
    public function scopePendingChange($query)
    {
        return $query->where('status', self::STATUS_PENDING_CHANGE);
    }

    /**
     * Lấy các lịch đã được hệ thống xử lý điểm danh/vắng mặt.
     */
    public function scopeSystemProcessed($query)
    {
        return $query->where('status', self::STATUS_SYSTEM_PROCESSED);
    }

    /**
     * Lấy các lịch bắt buộc điểm danh.
     */
    public function scopeMandatoryAttendance($query)
    {
        return $query->where('is_mandatory_attendance', true);
    }

    // =========================================================================
    // Accessors & Mutators
    // =========================================================================

    /**
     * Lấy text mô tả cho trạng thái.
     */
    public function getStatusTextAttribute(): string
    {
        $statusTexts = [
            self::STATUS_SCHEDULED          => __('Đã lên lịch'),
            self::STATUS_COMPLETED          => __('Đã hoàn thành'),
            self::STATUS_CANCELLED_BY_ADMIN => __('Bị hủy (bởi Admin)'),
            self::STATUS_CANCELLED_BY_STUDENT => __('Bị hủy (bởi Sinh viên)'),
            self::STATUS_PENDING_CHANGE     => __('Chờ duyệt thay đổi'),
            self::STATUS_CHANGE_APPROVED    => __('Thay đổi đã duyệt'),
            self::STATUS_CHANGE_REJECTED    => __('Thay đổi bị từ chối'),
            self::STATUS_SYSTEM_PROCESSED   => __('Đã xử lý hệ thống'),
        ];
        return $statusTexts[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status ?? 'Chưa xác định'));
    }

    /**
     * Accessor để lấy phần ngày từ start_time.
     * Rất hữu ích nếu bạn cần hiển thị hoặc lọc chỉ theo ngày.
     */
    public function getScheduleDateAttribute(): ?string
    {
        if ($this->start_time) {
            // $this->start_time đã là đối tượng Carbon do $casts['start_time'] => 'datetime'
            return $this->start_time->toDateString(); // Trả về 'YYYY-MM-DD'
        }
        return null;
    }

    /**
     * (Tùy chọn) Kiểm tra xem lịch này có thể được chỉnh sửa hoặc hủy bởi người dùng hiện tại không.
     * Logic này có thể phức tạp hơn tùy theo quy tắc nghiệp vụ.
     */
    public function getCanBeModifiedAttribute(): bool
    {
        // Ví dụ đơn giản: chỉ lịch 'scheduled' và chưa bắt đầu mới có thể được sửa/hủy
        return $this->status === self::STATUS_SCHEDULED && $this->start_time && Carbon::parse($this->start_time)->isFuture();
    }

    /**
     * (Tùy chọn) Kiểm tra xem lịch này đã diễn ra hay chưa.
     */
    public function getHasPassedAttribute(): bool
    {
        return $this->end_time && Carbon::parse($this->end_time)->isPast();
    }
}