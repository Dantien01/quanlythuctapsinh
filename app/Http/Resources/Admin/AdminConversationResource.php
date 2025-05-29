<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Conversation;
use Illuminate\Support\Facades\Log;

class AdminConversationResource extends JsonResource
{
    /**
     * The resource instance.
     * @var \App\Models\Conversation
     */
    public $resource;

    /**
     * The admin user context.
     * @var \App\Models\User
     */
    protected User $adminUser;

    /**
     * Create a new resource instance.
     *
     * @param  \App\Models\Conversation $resource The conversation model instance.
     * @param  \App\Models\User $adminUser The authenticated admin user.
     * @return void
     */
    public function __construct(Conversation $resource, User $adminUser) // << TYPE-HINTS ĐÚNG
    {
        parent::__construct($resource);
        $this->adminUser = $adminUser;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $admin = $this->adminUser;
        $conversation = $this->resource;

        $studentParticipant = null;
        if ($conversation->relationLoaded('participants')) {
            $studentParticipant = $conversation->participants->firstWhere('id', '!=', $admin->id);
        } else {
            Log::warning('AdminConversationResource toArray(): Participants relation was not eager loaded.', ['conversation_id' => $conversation->id]);
        }

        return [
            'conversation_id' => $conversation->id,
            'student' => ($studentParticipant instanceof User) ? new UserResource($studentParticipant) : null,
            'last_message' => $this->whenLoaded('lastMessage', function () use ($admin, $conversation) {
                if ($conversation->lastMessage) {
                    $isSenderAdmin = $conversation->lastMessage->sender_id === $admin->id;
                    $senderName = 'Unknown Sender';
                    if ($conversation->lastMessage->relationLoaded('sender')) {
                        $senderName = $isSenderAdmin ? 'Admin' : ($conversation->lastMessage->sender->name ?? 'Student');
                    } else {
                         Log::warning('AdminConversationResource: lastMessage.sender relation not loaded.', ['message_id' => $conversation->lastMessage->id]);
                    }
                    return [
                        'id' => $conversation->lastMessage->id,
                        'content' => Str::limit($conversation->lastMessage->content, 70),
                        'sent_at' => $conversation->lastMessage->created_at->toIso8601String(),
                        'is_sender_admin' => $isSenderAdmin,
                        'sender_name' => $senderName,
                    ];
                }
                return null;
            }),
            'unread_from_student_count' => $conversation->unread_messages_count ?? 0,
            'updated_at' => $conversation->updated_at->toIso8601String(),
        ];
    }
}