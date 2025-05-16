<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // Import HasMany

class Role extends Model
{
    use HasFactory;

    // Cho phép gán 'name' hàng loạt
    protected $fillable = ['name'];

    /**
     * Lấy tất cả người dùng thuộc về vai trò này.
     */
    public function users(): HasMany
    {
        // Role này 'có nhiều' Users
        return $this->hasMany(User::class);
    }
}