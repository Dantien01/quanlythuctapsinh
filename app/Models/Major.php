<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo
use Illuminate\Database\Eloquent\Relations\HasMany;   // Import HasMany

class Major extends Model
{
    use HasFactory;

    // Cho phép gán 'name', 'school_id' hàng loạt
    protected $fillable = ['name', 'school_id'];

    /**
     * Lấy trường học mà chuyên ngành này thuộc về.
     */
    public function school(): BelongsTo
    {
        // Major này 'thuộc về' một School
        return $this->belongsTo(School::class);
    }

    /**
     * Lấy tất cả người dùng (sinh viên) thuộc về chuyên ngành này.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}