<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskProgress extends Model
{
    use HasFactory;

    protected $table = 'task_progress'; // Khai báo tên bảng nếu không theo quy ước (task_progresses)

    protected $fillable = [
        'task_id',
        'user_id',
        'notes',
        'progress_percentage',
        'submitted_at',
    ];

    protected $casts = [
        'progress_percentage' => 'integer',
        'submitted_at' => 'datetime',
    ];

    /**
     * Lấy task mà tiến độ này thuộc về.
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Lấy sinh viên đã tạo tiến độ này.
     */
    public function student() // Hoặc user() tùy theo cách bạn muốn gọi
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}