<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Schedule extends Model
{
    use HasFactory;

    // =========================================================================
    // Hằng số cho trạng thái lịch (STATUS) - GIỮ NGUYÊN
    // =========================================================================
    const STATUS_SCHEDULED          = 'scheduled';
    const STATUS_COMPLETED          = 'completed';
    const STATUS_CANCELLED_BY_ADMIN = 'cancelled_admin';
    const STATUS_CANCELLED_BY_STUDENT = 'cancelled_student';
    const STATUS_PENDING_CHANGE     = 'pending_change';
    const STATUS_CHANGE_APPROVED    = 'change_approved';
    const STATUS_CHANGE_REJECTED    = 'change_rejected';
    const STATUS_SYSTEM_PROCESSED   = 'system_processed';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'created_by',
        'title',
        'description',
        'overall_start_date', // <<<< CẬP NHẬT TÊN CỘT
        'overall_end_date',   // <<<< CẬP NHẬT TÊN CỘT
        'status',
        'is_mandatory_attendance',
        'change_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'overall_start_date'      => 'datetime', // <<<< CẬP NHẬT TÊN CỘT
        'overall_end_date'        => 'datetime', // <<<< CẬP NHẬT TÊN CỘT
        'is_mandatory_attendance' => 'boolean',
    ];

    // =========================================================================
    // Relationships - GIỮ NGUYÊN (Quan hệ students đã được thêm ở lần trước)
    // =========================================================================
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function slots(): HasMany
    {
        return $this->hasMany(InternshipSlot::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'schedule_user', 'schedule_id', 'user_id')
                    ->withTimestamps();
    }

    // =========================================================================
    // Scopes Query - CẬP NHẬT TÊN CỘT
    // =========================================================================
    public function scopePast($query)
    {
        return $query->where('overall_end_date', '<', Carbon::now()); // <<<< CẬP NHẬT TÊN CỘT
    }

    public function scopeUpcoming($query)
    {
        return $query->where('overall_start_date', '>=', Carbon::now()); // <<<< CẬP NHẬT TÊN CỘT
    }

    // Các scope khác giữ nguyên nếu không dùng start_time/end_time trực tiếp
    public function scopeCancelled($query)
    {
        return $query->whereIn('status', [self::STATUS_CANCELLED_BY_ADMIN, self::STATUS_CANCELLED_BY_STUDENT]);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function scopePendingChange($query)
    {
        return $query->where('status', self::STATUS_PENDING_CHANGE);
    }

    public function scopeSystemProcessed($query)
    {
        return $query->where('status', self::STATUS_SYSTEM_PROCESSED);
    }

    public function scopeMandatoryAttendance($query)
    {
        return $query->where('is_mandatory_attendance', true);
    }

    // =========================================================================
    // Accessors & Mutators - CẬP NHẬT TÊN CỘT
    // =========================================================================
    public function getStatusTextAttribute(): string
    {
        // ... (Giữ nguyên logic) ...
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

    public function getScheduleDateAttribute(): ?string // Có thể cần đổi tên accessor này cho rõ nghĩa hơn
    {
        if ($this->overall_start_date) { // <<<< CẬP NHẬT TÊN CỘT
            return $this->overall_start_date->toDateString();
        }
        return null;
    }

    public function getCanBeModifiedAttribute(): bool
    {
        // <<<< CẬP NHẬT TÊN CỘT
        return $this->status === self::STATUS_SCHEDULED && $this->overall_start_date && Carbon::parse($this->overall_start_date)->isFuture();
    }

    public function getHasPassedAttribute(): bool
    {
        // <<<< CẬP NHẬT TÊN CỘT
        return $this->overall_end_date && Carbon::parse($this->overall_end_date)->isPast();
    }

    // =========================================================================
    // Helper function - GIỮ NGUYÊN
    // =========================================================================
    public static function getDayName($dayOfWeekNumber): string
    {
        $days = [
            1 => 'Thứ Hai',
            2 => 'Thứ Ba',
            3 => 'Thứ Tư',
            4 => 'Thứ Năm',
            5 => 'Thứ Sáu',
            6 => 'Thứ Bảy',
            7 => 'Chủ Nhật',
        ];
        return $days[$dayOfWeekNumber] ?? 'Không xác định';
    }
}