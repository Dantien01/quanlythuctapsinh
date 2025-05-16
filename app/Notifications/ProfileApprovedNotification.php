<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // Để gửi qua hàng đợi (tùy chọn)
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User; // Import User nếu cần lấy thông tin

class ProfileApprovedNotification extends Notification implements ShouldQueue // Implement ShouldQueue nếu muốn
{
    use Queueable;

    /**
     * Create a new notification instance.
     * Không cần truyền dữ liệu gì thêm vào đây vì thông báo này
     * được gửi ĐẾN người dùng có hồ sơ được duyệt.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     * Xác định kênh gửi thông báo. Ở đây ta chỉ cần lưu vào database.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database']; // Chỉ lưu vào bảng notifications
        // return ['database', 'mail']; // Nếu muốn gửi cả email
    }

    /**
     * Get the array representation of the notification for database storage.
     * Dữ liệu này sẽ được lưu vào cột `data` trong bảng `notifications` dưới dạng JSON.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        // $notifiable ở đây chính là đối tượng User (sinh viên) nhận thông báo
        return [
            // Thông điệp chính sẽ hiển thị cho người dùng
            'message' => 'Chúc mừng! Hồ sơ của bạn đã được quản trị viên duyệt.',
            // (Tùy chọn) Icon để hiển thị cùng thông báo trong UI
            'icon' => 'fas fa-user-check text-success', // Ví dụ icon Font Awesome
            // (Quan trọng) URL để người dùng click vào sẽ được chuyển đến
            // Thông thường sẽ trỏ đến trang profile để họ xem lại
            'url' => route('profile.edit'), // Sử dụng route profile.edit có sẵn
        ];
    }

    /**
     * Get the mail representation of the notification. (Tùy chọn)
     * Nếu bạn muốn gửi cả email thông báo.
     */
    // public function toMail(object $notifiable): MailMessage
    // {
    //     $url = route('profile.edit'); // Link đến trang profile

    //     return (new MailMessage)
    //                 ->subject('Hồ sơ của bạn đã được duyệt!')
    //                 ->greeting('Chào ' . $notifiable->name . ',')
    //                 ->line('Hồ sơ thực tập của bạn trên hệ thống đã được quản trị viên duyệt thành công.')
    //                 ->line('Bây giờ bạn có thể bắt đầu sử dụng các chức năng khác của hệ thống.')
    //                 ->action('Xem hồ sơ', $url)
    //                 ->line('Cảm ơn bạn!');
    // }

    /**
     * Get the array representation of the notification. (Ít dùng hơn toDatabase)
     *
     * @return array<string, mixed>
     */
    // public function toArray(object $notifiable): array
    // {
    //     return [
    //         // Dữ liệu dạng mảng đơn giản nếu cần
    //     ];
    // }
}