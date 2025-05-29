<?php

namespace App\Http\Resources; 

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth; // Cần để lấy current user
use App\Http\Resources\UserResource;

// Import UserPublicResource nếu bạn có
// use App\Http\Resources\UserPublicResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $currentUser = Auth::user(); // Lấy người dùng hiện tại để xác định is_sender_current_user

        return [
            'id' => $this->id,
            'conversation_id' => $this->whenLoaded('conversation', function () { // Chỉ load nếu conversation được eager load
                return $this->conversation->id;
            }, $this->conversation_id), // Fallback về conversation_id trực tiếp nếu không eager load
            'content' => $this->content,
            'sent_at' => $this->created_at->toIso8601String(), // Định dạng ISO 8601 cho API
            'is_sender_current_user' => $currentUser ? ($this->sender_id === $currentUser->id) : null,
            // Sử dụng một User resource khác (ví dụ UserPublicResource) để chỉ trả về thông tin cần thiết
            // Thay thế UserPublicResource bằng tên resource user của bạn nếu có
            'sender' => new UserResource($this->whenLoaded('sender')), // Giả sử bạn đã có UserResource
            // 'read_by_recipient' => $this->isReadByOtherParticipantInConversation($currentUser), // Cần logic này trong Message model
            // 'updated_at' => $this->updated_at->toIso8601String(), // Nếu cần
        ];
    }
}