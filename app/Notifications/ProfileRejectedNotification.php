<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // Để gửi qua hàng đợi (tùy chọn)
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

class ProfileRejectedNotification extends Notification implements ShouldQueue // Implement ShouldQueue nếu muốn
{
    use Queueable;

    public string $reason; // Thuộc tính để lưu lý do từ chối

    /**
     * Create a new notification instance.
     * Truyền lý do từ chối vào khi tạo notification.
     *
     * @param string $reason Lý do hồ sơ bị từ chối
     */
    public function __construct(string $reason)
    {
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database']; // Chỉ lưu vào database
        // return ['database', 'mail']; // Nếu muốn gửi cả email
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            // Thông điệp chính, bao gồm cả lý do
            'message' => 'Rất tiếc! Hồ sơ của bạn đã bị từ chối. Lý do: ' . $this->reason,
            // (Tùy chọn) Lưu lý do riêng nếu muốn xử lý phức tạp hơn
            'rejection_reason' => $this->reason,
            // (Tùy chọn) Icon
            'icon' => 'fas fa-user-times text-danger',
            // (Quan trọng) URL trỏ đến trang profile để sinh viên sửa
            'url' => route('profile.edit'),
        ];
    }

    /**
     * Get the mail representation of the notification. (Tùy chọn)
     */
    // public function toMail(object $notifiable): MailMessage
    // {
    //     $url = route('profile.edit');

    //     return (new MailMessage)
    //                 ->subject('Thông báo về hồ sơ thực tập của bạn')
    //                 ->greeting('Chào ' . $notifiable->name . ',')
    //                 ->line('Chúng tôi rất tiếc phải thông báo rằng hồ sơ thực tập của bạn đã bị từ chối.')
    //                 ->line('Lý do: ' . $this->reason)
    //                 ->line('Vui lòng cập nhật lại thông tin hồ sơ của bạn.')
    //                 ->action('Cập nhật hồ sơ', $url)
    //                 ->line('Xin cảm ơn!');
    // }
}