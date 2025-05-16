<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Message; // Import Message

class NewMessageNotification extends Notification implements ShouldQueue // Implement ShouldQueue nếu muốn gửi qua hàng đợi
{
    use Queueable;

    public $message; // Thuộc tính để lưu tin nhắn

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    // Kênh gửi: chỉ lưu vào database
    public function via(object $notifiable): array
    {
        return ['database']; // Có thể thêm 'mail' nếu muốn gửi email
    }

    // Dữ liệu lưu vào cột 'data' của bảng notifications
    public function toDatabase(object $notifiable): array
    {
        return [
            'message_id' => $this->message->id,
            'sender_name' => $this->message->sender->name ?? 'N/A', // Lấy tên người gửi
            'message_subject' => $this->message->subject, // Có thể bỏ nếu không dùng subject
            'message_excerpt' => \Illuminate\Support\Str::limit($this->message->body, 50), // Đoạn trích ngắn
            // Message chính cho hiển thị dropdown
            'message' => "Bạn có tin nhắn mới từ " . ($this->message->sender->name ?? 'N/A'),
            // URL để khi click vào thông báo sẽ trỏ đến
            // (Cần logic để xác định đúng URL cho admin và student)
            'url' => $notifiable->hasRole('Admin')
                        ? route('admin.messages.show', $this->message->sender_id) // Admin xem cuộc trò chuyện với người gửi
                        : route('student.messages.index'), // Student về trang tin nhắn chung
        ];
    }

     // (Tùy chọn) Nếu muốn gửi cả Email
     // public function toMail(object $notifiable): MailMessage
     // {
     //     $url = $this->toDatabase($notifiable)['url']; // Lấy URL đã tạo
     //     return (new MailMessage)
     //                 ->subject('Bạn có tin nhắn mới')
     //                 ->line('Bạn có tin nhắn mới từ ' . ($this->message->sender->name ?? 'N/A') . '.')
     //                 ->action('Xem tin nhắn', $url)
     //                 ->line('Cảm ơn bạn đã sử dụng ứng dụng!');
     // }
}