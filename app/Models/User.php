<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne; // Giữ lại nếu bạn có dùng ở đâu đó
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
use App\Models\Conversation;
use App\Models\MessageReadStatus;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;
use App\Models\TaskProgress;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        // 'unread_messages_count', // Nếu muốn tự động thêm vào JSON/array
    ];

    public function role(): BelongsTo { return $this->belongsTo(Role::class); }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function major(): BelongsTo { return $this->belongsTo(Major::class); }
    public function diaries(): HasMany { return $this->hasMany(Diary::class); }
    public function attendances(): HasMany { return $this->hasMany(Attendance::class, 'user_id'); }

    public function schedules(): BelongsToMany
    {
        return $this->belongsToMany(Schedule::class, 'schedule_user', 'user_id', 'schedule_id')
                    ->withTimestamps();
    }

    public function createdSchedules(): HasMany { return $this->hasMany(Schedule::class, 'created_by'); }

    // Các mối quan hệ message cũ có thể không còn cần thiết nếu đã chuyển hoàn toàn sang conversation
    // public function sentMessages(): HasMany { return $this->hasMany(Message::class, 'sender_id'); }
    // public function receivedMessages(): HasMany { return $this->hasMany(Message::class, 'receiver_id'); }
    // public function lastMessage(): HasOne { return $this->hasOne(Message::class, 'id', 'last_message_id'); }


    public function taskProgresses(): HasMany
    {
        return $this->hasMany(TaskProgress::class, 'user_id');
    }
    public function diaryComments(): HasMany { return $this->hasMany(DiaryComment::class); }
    public function reviewsReceived(): HasMany { return $this->hasMany(StudentReview::class, 'user_id'); }
    public function reviewsWritten(): HasMany { return $this->hasMany(StudentReview::class, 'reviewer_id'); }


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

    // ==================================================
    //   ===== PHẦN CẬP NHẬT getAttendanceRateAttribute =====
    // ==================================================
    public function getAttendanceRateAttribute(): ?float
    {
        // Lấy các ID của lịch trình mà người dùng này tham gia
        // và thỏa mãn các điều kiện
        $validScheduleIds = $this->schedules() // Đây là BelongsToMany relationship
            // Các điều kiện where này sẽ được áp dụng lên bảng 'schedules' (bảng chính của relationship)
            ->where('is_mandatory_attendance', true)
            ->whereNotIn('status', [
                'cancelled_admin', // Giả sử đây là các giá trị string trong DB
                'cancelled_student' // Hoặc sử dụng Schedule::STATUS_CANCELLED_BY_ADMIN nếu bạn định nghĩa hằng số
            ])
            // SỬ DỤNG TÊN CỘT ĐÚNG: overall_end_date
            ->where('overall_end_date', '<', Carbon::now())
            ->pluck('schedules.id'); // Chỉ định rõ schedules.id để tránh nhầm lẫn

        if ($validScheduleIds->isEmpty()){
             return 0.0;
        }

        $totalMandatoryPastScheduledSessions = $validScheduleIds->count();
         if ($totalMandatoryPastScheduledSessions == 0) {
            // Trả về null hoặc 0.0 tùy theo logic bạn muốn hiển thị khi không có buổi học bắt buộc nào trong quá khứ
            return 0.0;
        }

        $achievedAttendanceScore = 0;
        // Lấy các bản ghi điểm danh của user này cho các lịch trình hợp lệ đã lấy ở trên
        $attendancesForValidSchedules = $this->attendances() // Đây là HasMany relationship
            ->whereIn('schedule_id', $validScheduleIds)
            ->get();

        foreach ($attendancesForValidSchedules as $attendance) {
            // Giả sử bạn có các hằng số định nghĩa status trong Model Attendance
            // Ví dụ: Attendance::STATUS_PRESENT, Attendance::STATUS_LATE, etc.
            // Nếu không, sử dụng giá trị string trực tiếp từ DB
            switch ($attendance->status) {
                case 'present': // Thay 'present' bằng giá trị thực tế hoặc hằng số
                case 'late':    // Thay 'late' bằng giá trị thực tế hoặc hằng số
                    $achievedAttendanceScore += 1.0;
                    break;
                case 'absent_with_permission': // Thay bằng giá trị thực tế hoặc hằng số
                    $achievedAttendanceScore += 0.5;
                    break;
                case 'absent_without_permission': // Thay bằng giá trị thực tế hoặc hằng số
                    // Không cộng điểm
                    break;
            }
        }

        $attendanceRate = ($achievedAttendanceScore / $totalMandatoryPastScheduledSessions) * 100;
        return round($attendanceRate, 2);
    }
    // ==================================================


    // ==================================================
    //   ===== PHẦN TIN NHẮN ĐÃ CẬP NHẬT =====
    // ==================================================
    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants', 'user_id', 'conversation_id')
                    ->withTimestamps();
    }

    protected function unreadMessagesCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->calculateUnreadMessages(),
        );
    }

    public function calculateUnreadMessages(): int
    {
        $unreadCount = 0;
        $conversations = $this->conversations()->with([
            'messages' => function ($query) {
                $query->with('readStatuses');
            }
        ])->get();

        foreach ($conversations as $conversation) {
            $unreadCount += $conversation->messages
                ->where('sender_id', '!=', $this->id)
                ->filter(function ($message) {
                    $hasBeenRead = $message->readStatuses
                                    ->where('user_id', $this->id)
                                    ->whereNotNull('read_at')
                                    ->isNotEmpty();
                    return !$hasBeenRead;
                })->count();
        }
        return $unreadCount;
    }
    // ==================================================
}