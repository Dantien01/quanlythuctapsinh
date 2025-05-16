<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Authenticated; // Sự kiện được lắng nghe
use Illuminate\Contracts\Queue\ShouldQueue; // Có thể implement nếu muốn chạy bất đồng bộ
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth; // Facade Auth để đăng xuất
use Illuminate\Http\Request; // Import Request để xử lý session
use App\Models\User; // Import User model để kiểm tra instance

class CheckStudentProfileStatus // Không cần implements ShouldQueue nếu chạy đồng bộ
{
    protected $request;

    /**
     * Create the event listener.
     * Inject Request vào constructor.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the event.
     * Hàm này sẽ chạy ngay sau khi user xác thực thành công.
     */
    public function handle(Authenticated $event): void
    {
        // $event->user chứa thông tin user vừa đăng nhập
        // $event->guard chứa tên guard đã dùng (thường là 'web')

        // Kiểm tra xem user có phải là model User của mình và có role không
        if ($event->user instanceof User && $event->user->role) {

            // Nếu là SinhVien và hồ sơ chưa được duyệt ('approved')
            if ($event->user->role->name === 'SinhVien' && $event->user->profile_status !== 'approved') {

                // Lấy guard đã xác thực user
                $guard = Auth::guard($event->guard);

                // Đăng xuất user này ngay lập tức
                $guard->logout();

                // Hủy session hiện tại
                $this->request->session()->invalidate();

                // Tạo lại token CSRF
                $this->request->session()->regenerateToken();

                // Chuẩn bị thông báo lỗi
                $errorMessage = 'Hồ sơ của bạn đang chờ duyệt hoặc đã bị từ chối. Vui lòng liên hệ quản trị viên.';
                // (Tùy chọn) Thêm lý do từ chối nếu có
                if ($event->user->profile_status === 'rejected' && $event->user->rejection_reason) {
                     $errorMessage .= ' Lý do: ' . $event->user->rejection_reason;
                }

                // Lưu thông báo lỗi vào session để hiển thị ở trang login sau khi bị redirect
                // Sử dụng session()->flash() để thông báo chỉ hiển thị một lần
                 session()->flash('error', $errorMessage);

                // Laravel sẽ tự động chuyển hướng về trang login sau khi logout
                // Chúng ta không cần return redirect() từ listener
            }
        }
    }
}