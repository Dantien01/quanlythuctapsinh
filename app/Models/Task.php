<?php
// app/Models/Task.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon; // Import Carbon
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany; // <<< THÊM IMPORT NÀY

/**
* @property \Illuminate\Support\Carbon $due_date
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int $id
 * @property int $intern_id
 * @property int|null $assigner_id
 * @property string $title
 * @property string|null $description
 * @property string $status
 * @property string|null $priority
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $assigner
 * @property-read string $is_overdue
 * @property-read string $priority_text
 * @property-read string $status_text
 * @property-read \App\Models\User $intern
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskProgress> $progressEntries // <<< THÊM CHO RELATIONSHIP MỚI
 * @property-read int|null $progress_entries_count // <<< THÊM CHO RELATIONSHIP MỚI
 * @method static \Database\Factories\TaskFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Task newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Task newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Task query()
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereAssignerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereInternId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task onlyTrashed() // Thêm cho SoftDeletes
 * @method static \Illuminate\Database\Eloquent\Builder|Task withTrashed() // Thêm cho SoftDeletes
 * @method static \Illuminate\Database\Eloquent\Builder|Task withoutTrashed() // Thêm cho SoftDeletes
 * @mixin \Eloquent
 */
class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'intern_id',
        'assigner_id',
        'title',
        'description',
        'due_date',
        'status',
        'priority',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public const STATUS_TODO = 'todo';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_OVERDUE = 'overdue';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';

    public static function statuses()
    {
        return [
            self::STATUS_TODO => __('Cần làm'),
            self::STATUS_IN_PROGRESS => __('Đang làm'),
            self::STATUS_COMPLETED => __('Hoàn thành'),
            self::STATUS_OVERDUE => __('Quá hạn'),
        ];
    }

    public static function priorities()
    {
        return [
            self::PRIORITY_LOW => __('Thấp'),
            self::PRIORITY_MEDIUM => __('Trung bình'),
            self::PRIORITY_HIGH => __('Cao'),
        ];
    }

    public function intern()
    {
        return $this->belongsTo(User::class, 'intern_id');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigner_id');
    }

    public function getStatusTextAttribute()
    {
        return self::statuses()[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getPriorityTextAttribute()
    {
        return self::priorities()[$this->priority] ?? ucfirst($this->priority);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date->isPast() && $this->status !== self::STATUS_COMPLETED;
    }

    // <<< THÊM RELATIONSHIP VỚI TASKPROGRESS >>>
    /**
     * Lấy tất cả các cập nhật tiến độ cho task này.
     */
    public function progressEntries(): HasMany
    {
        return $this->hasMany(TaskProgress::class)->orderBy('submitted_at', 'desc');
    }
    // <<< KẾT THÚC THÊM RELATIONSHIP >>>
}