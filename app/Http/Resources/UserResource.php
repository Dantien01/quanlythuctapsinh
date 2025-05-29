<?php

namespace App\Http\Resources; // Đảm bảo namespace đúng

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Chỉ trả về những thông tin cần thiết của người dùng cho message sender
        return [
            'id' => $this->id,
            'name' => $this->name,
            // Thêm profile_photo_url nếu User model của bạn có accessor này
            // và bạn muốn hiển thị avatar trong API tin nhắn
            'avatar_url' => $this->when(isset($this->profile_photo_url), $this->profile_photo_url),
            // Bạn có thể thêm các trường khác nếu cần, ví dụ: 'mssv' nếu cần thiết
            // 'mssv' => $this->when(isset($this->mssv), $this->mssv),
        ];
    }
}