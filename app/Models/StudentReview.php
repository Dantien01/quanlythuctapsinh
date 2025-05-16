<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentReview extends Model {
    use HasFactory;
    protected $fillable = ['user_id', 'reviewer_id', 'review_period', 'content'];

    // Sinh viên được nhận xét
    public function student(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Admin viết nhận xét
    public function reviewer(): BelongsTo {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}