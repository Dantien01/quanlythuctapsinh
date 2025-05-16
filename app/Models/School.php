<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // Import HasMany

class School extends Model
{
    use HasFactory;

    // Cho phép gán 'name', 'address' hàng loạt
    protected $fillable = ['name', 'address'];

    /**
     * Lấy tất cả chuyên ngành thuộc về trường này.
     */
    public function majors(): HasMany
    {
        return $this->hasMany(Major::class);
    }

    /**
     * Lấy tất cả người dùng (sinh viên) thuộc về trường này.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}