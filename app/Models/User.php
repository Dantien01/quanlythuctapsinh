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
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Message;
use App\Models\DiaryComment;
use App\Models\StudentReview;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;
use App\Models\TaskProgress;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // THÊM IMPORT NÀY

/**
 * App\Models\User
 *
 * ...
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Schedule> $schedules // Cập nhật PHPDoc
 * @property-read int|null $schedules_count // Cập nhật PHPDoc
 * ...
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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
        'attendance_rate',
    ];

    public function role(): BelongsTo { return $this->belongsTo(Role::class); }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function major(): BelongsTo { return $this->belongsTo(Major::class); }
    public function diaries(): HasMany { return $this->hasMany(Diary::class); }
    public function attendances(): HasMany { return $this->hasMany(Attendance::class, 'user_id'); }

    // ===== PHẦN CẬP NHẬT - START: THAY ĐỔI QUAN HỆ schedules() THÀNH BelongsToMany =====
    /**
     * Các lịch trình (Schedules) mà người dùng/sinh viên này tham gia.
     * Đây là quan hệ Many-to-Many.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function schedules(): BelongsToMany
    {
        // Đảm bảo các tham số này khớp với định nghĩa trong Schedule model và bảng pivot 'schedule_user'
        // User::class -> Schedule::class
        // 'schedule_user' -> tên bảng pivot
        // 'user_id' -> khóa ngoại của User trong bảng pivot
        // 'schedule_id' -> khóa ngoại của Schedule trong bảng pivot
        return $this->belongsToMany(Schedule::class, 'schedule_user', 'user_id', 'schedule_id')
                    ->withTimestamps(); // (Tùy chọn) nếu bảng pivot có timestamps
    }
    // ===== PHẦN CẬP NHẬT - END =====

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

    public function getAttendanceRateAttribute(): ?float
    {
        // ... (logic accessor giữ nguyên) ...
        $validScheduleIds = $this->schedules() // Quan hệ này giờ là BelongsToMany
            ->where('is_mandatory_attendance', true)
            ->whereNotIn('status', [
                Schedule::STATUS_CANCELLED_BY_ADMIN,
                Schedule::STATUS_CANCELLED_BY_STUDENT
            ])
            ->past()
            ->pluck('id'); // Lấy ID từ kết quả của quan hệ

        if ($validScheduleIds->isEmpty()){
             return 0.0; // Hoặc null tùy theo logic bạn muốn
        }

        $totalMandatoryPastScheduledSessions = $validScheduleIds->count(); // Đếm số lịch hợp lệ
         if ($totalMandatoryPastScheduledSessions == 0) {
            return null;
        }


        $achievedAttendanceScore = 0;
        $attendancesForValidSchedules = $this->attendances()
            ->whereIn('schedule_id', $validScheduleIds)
            ->get();

        foreach ($attendancesForValidSchedules as $attendance) {
            switch ($attendance->status) {
                case Attendance::STATUS_PRESENT:
                case Attendance::STATUS_LATE:
                    $achievedAttendanceScore += 1.0;
                    break;
                case Attendance::STATUS_ABSENT_WITH_PERMISSION:
                    $achievedAttendanceScore += 0.5;
                    break;
                case Attendance::STATUS_ABSENT_WITHOUT_PERMISSION:
                    break;
            }
        }
        $attendanceRate = ($achievedAttendanceScore / $totalMandatoryPastScheduledSessions) * 100;
        return round($attendanceRate, 2);
    }
}