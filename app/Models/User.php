<?php

namespace App\Models;

// ... các use statements khác ...
use Illuminate\Foundation\Auth\User as Authenticatable; // <-- THÊM DÒNG NÀY
use Illuminate\Support\Str;

class User extends Authenticatable
{
    // ... code khác của bạn (use HasFactory, Notifiable, $fillable, relationships...) ...

    /**
     * Lấy chữ cái đầu của tên người dùng.
     *
     * @return string
     */
    public function initials(): string
    {
        $nameParts = explode(' ', trim($this->name ?? '')); // Lấy tên, tách thành các từ
        $firstName = array_shift($nameParts); // Lấy từ đầu tiên
        $lastName = array_pop($nameParts); // Lấy từ cuối cùng (nếu có)

        $initials = (Str::length($firstName) > 0 ? Str::upper(Str::substr($firstName, 0, 1)) : ''); // Chữ cái đầu của từ đầu tiên

        if ($lastName && Str::length($lastName) > 0) {
             $initials .= Str::upper(Str::substr($lastName, 0, 1)); // Thêm chữ cái đầu của từ cuối cùng
        } elseif (Str::length($firstName) > 1) {
            // Nếu chỉ có 1 từ và dài hơn 1 ký tự, lấy 2 chữ cái đầu
            // Hoặc bạn có thể chỉ lấy 1 chữ cái đầu bằng cách bỏ điều kiện else if này
             $initials = Str::upper(Str::substr($firstName, 0, 2));
        }

        return $initials ?: '??'; // Trả về ?? nếu không có tên
    }
}