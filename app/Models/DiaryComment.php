<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiaryComment extends Model
{
    use HasFactory;

    protected $fillable = ['diary_id', 'user_id', 'content'];

    // Relationship: Lấy nhật ký mà comment này thuộc về
    public function diary(): BelongsTo
    {
        return $this->belongsTo(Diary::class);
    }

    // Relationship: Lấy người dùng (Admin/SinhVien) đã viết comment
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}