<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
// Các model bạn đã import
use App\Models\Role;
use App\Models\School;
use App\Models\Major;
use App\Models\Diary;
use App\Models\Attendance; // Đã có
use App\Models\Schedule;   // Đã có
use App\Models\Message;
use App\Models\DiaryComment;
use App\Models\StudentReview;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;
use App\Models\TaskProgress;

use Carbon\Carbon; // <<< THÊM IMPORT NÀY CHO LOGIC TÍNH TOÁN

/**
 * App\Models\User
 *
 * ... (các @property khác của bạn)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskProgress> $taskProgresses
 * @property-read int|null $task_progresses_count
 * @property-read float|null $attendance_rate // <<< THÊM PHPDOC CHO ACCESSOR MỚI
 * ...
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // Giả sử bạn không dùng Spatie/laravel-permission trực tiếp ở đây

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'mssv',
        'phone_number',
        'school_id',
        'major_id',
        'profile_status',
        'rejection_reason',
        'profile_photo_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected $appends = [
        'profile_photo_url',
        'attendance_rate', // <<< THÊM ACCESSOR VÀO APPENDS ĐỂ NÓ TỰ ĐỘNG XUẤT HIỆN KHI SERIALIZE MODEL
    ];

    public function role(): BelongsTo { return $this->belongsTo(Role::class); }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function major(): BelongsTo { return $this->belongsTo(Major::class); }
    public function diaries(): HasMany { return $this->hasMany(Diary::class); }
    public function attendances(): HasMany { return $this->hasMany(Attendance::class, 'user_id'); } // Chỉ rõ foreign key
    public function schedules(): HasMany { return $this->hasMany(Schedule::class, 'user_id'); }
    public function createdSchedules(): HasMany { return $this->hasMany(Schedule::class, 'created_by'); }
    public function sentMessages(): HasMany { return $this->hasMany(Message::class, 'sender_id'); }
    public function receivedMessages(): HasMany { return $this->hasMany(Message::class, 'receiver_id'); }
    public function diaryComments(): HasMany { return $this->hasMany(DiaryComment::class); }
    public function reviewsReceived(): HasMany { return $this->hasMany(StudentReview::class, 'user_id'); }
    public function reviewsWritten(): HasMany { return $this->hasMany(StudentReview::class, 'reviewer_id'); }

    public function lastMessage(): HasOne
    {
        return $this->hasOne(Message::class, 'id', 'last_message_id');
    }

    public function taskProgresses(): HasMany
    {
        return $this->hasMany(TaskProgress::class, 'user_id');
    }

    public function hasRole(string $roleName): bool
    {
        $this->loadMissing('role');
        return $this->role?->name === $roleName;
    }

    public function initials(): string
    {
        $nameParts = explode(' ', trim($this->name ?? ''));
        if (empty($nameParts)) { return '??'; }
        $firstName = array_shift($nameParts);
        $lastName = count($nameParts) > 0 ? array_pop($nameParts) : null;
        $initials = Str::upper(Str::substr($firstName, 0, 1));
        if ($lastName) { $initials .= Str::upper(Str::substr($lastName, 0, 1)); }
        return $initials;
    }

    public function isStudent(): bool
    {
        return $this->hasRole('SinhVien');
    }

    protected function profilePhotoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->profile_photo_path
                            ? Storage::disk('public')->url($this->profile_photo_path)
                            : $this->defaultProfilePhotoUrl(),
        );
    }

    protected function defaultProfilePhotoUrl()
    {
        $name = trim(collect(explode(' ', $this->name))->map(function ($segment) {
            return mb_substr($segment, 0, 1);
        })->join(' '));
        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&color=7F9CF5&background=EBF4FF';
    }

    // =========================================================================
    // ACCESSOR TÍNH TỶ LỆ CHUYÊN CẦN
    // =========================================================================
    /**
     * Tính toán và trả về tỷ lệ chuyên cần của sinh viên.
     * Kết quả là một số float (ví dụ: 95.5) hoặc null nếu không có buổi học nào.
     *
     * @return float|null
     */
    public function getAttendanceRateAttribute(): ?float
    {
        // 1. Lấy tổng số buổi học dự kiến BẮT BUỘC ĐIỂM DANH, KHÔNG BỊ HỦY và ĐÃ QUA
        $totalMandatoryPastScheduledSessions = $this->schedules()
            ->where('is_mandatory_attendance', true)
            ->whereNotIn('status', [
                Schedule::STATUS_CANCELLED_BY_ADMIN,
                Schedule::STATUS_CANCELLED_BY_STUDENT
            ])
            ->past() // Sử dụng scopePast()
            ->count();

        if ($totalMandatoryPastScheduledSessions == 0) {
            return null; // Hoặc 100.00 nếu bạn muốn
        }

        // 2. Tính tổng "điểm chuyên cần" đạt được
        //    - Có mặt, Đi trễ: 1 điểm
        //    - Vắng có phép: 0.5 điểm (hoặc trọng số bạn muốn)
        //    - Vắng không phép: 0 điểm
        $achievedAttendanceScore = 0;

        $validScheduleIds = $this->schedules()
            ->where('is_mandatory_attendance', true)
            ->whereNotIn('status', [
                Schedule::STATUS_CANCELLED_BY_ADMIN,
                Schedule::STATUS_CANCELLED_BY_STUDENT
            ])
            ->past()
            ->pluck('id');

        if ($validScheduleIds->isEmpty()){
             return 0.0;
        }

        // Lấy tất cả các bản ghi điểm danh liên quan đến các lịch hợp lệ
        $attendancesForValidSchedules = $this->attendances()
            ->whereIn('schedule_id', $validScheduleIds)
            ->get();

        foreach ($attendancesForValidSchedules as $attendance) {
            switch ($attendance->status) {
                case Attendance::STATUS_PRESENT:
                case Attendance::STATUS_LATE:
                    $achievedAttendanceScore += 1.0; // Được 1 điểm
                    break;
                case Attendance::STATUS_ABSENT_WITH_PERMISSION:
                    $achievedAttendanceScore += 0.5; // Được 0.5 điểm (trừ một nửa)
                    break;
                case Attendance::STATUS_ABSENT_WITHOUT_PERMISSION:
                    // Không cộng điểm (0 điểm)
                    break;
                // Thêm các case khác nếu có (ví dụ: STATUS_EARLY_LEAVE có thể được 0.75 điểm)
            }
        }

        // Tỷ lệ chuyên cần sẽ là (tổng điểm đạt được / tổng số buổi) * 100
        // Tổng số buổi ở đây vẫn là $totalMandatoryPastScheduledSessions vì mỗi buổi được tính là 1 "điểm tối đa"
        $attendanceRate = ($achievedAttendanceScore / $totalMandatoryPastScheduledSessions) * 100;

        return round($attendanceRate, 2);
    }
}