<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Task; // Model Task
use App\Models\User; // Model User (cho sinh viên)

class StudentUpdatedTaskStatus extends Notification implements ShouldQueue // (Tùy chọn) Implement ShouldQueue nếu muốn gửi qua hàng đợi
{
    use Queueable;

    public Task $task;
    public User $student;

    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task, User $student)
    {
        $this->task = $task;
        $this->student = $student;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Chọn kênh gửi thông báo: 'mail', 'database', 'broadcast', 'slack', etc.
        // Ở đây chúng ta dùng 'database' để lưu vào DB và 'mail' để gửi email (tùy chọn)
        $channels = ['database'];
        if (config('mail.from.address')) { // Chỉ gửi mail nếu đã cấu hình mail
            $channels[] = 'mail';
        }
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $taskUrl = route('admin.tasks.show', $this->task); // Link đến trang chi tiết task của admin

        return (new MailMessage)
                    ->subject('Cập nhật trạng thái công việc: ' . $this->task->title)
                    ->greeting('Chào ' . $notifiable->name . ',') // $notifiable ở đây là Admin nhận thông báo
                    ->line('Sinh viên ' . $this->student->name . ' vừa cập nhật trạng thái công việc:')
                    ->line('**Công việc:** ' . $this->task->title)
                    ->line('**Trạng thái mới:** ' . $this->task->status_text) // Dùng accessor
                    ->action('Xem chi tiết công việc', $taskUrl)
                    ->line('Cảm ơn bạn đã sử dụng ứng dụng!');
    }

    /**
     * Get the array representation of the notification. (Dùng cho kênh 'database')
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'student_id' => $this->student->id,
            'student_name' => $this->student->name,
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'new_status' => $this->task->status, // Lưu key của status
            'new_status_text' => $this->task->status_text, // Lưu text dễ đọc
            'message' => 'Sinh viên ' . $this->student->name . ' đã cập nhật trạng thái công việc "' . $this->task->title . '" thành "' . $this->task->status_text . '".',
            'url' => route('admin.tasks.show', $this->task->id), // URL để admin click vào xem
        ];
    }

    /**
     * (Tùy chọn) Get the broadcastable representation of the notification.
     * Dùng cho real-time notifications với Laravel Echo.
     */
    // public function toBroadcast(object $notifiable): BroadcastMessage
    // {
    //     return new BroadcastMessage([
    //         // Dữ liệu tương tự toArray
    //     ]);
    // }
}