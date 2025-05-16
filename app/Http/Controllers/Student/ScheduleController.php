<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;           // <<< Import lớp Request
use Illuminate\Support\Facades\Auth;    // Để lấy user đang đăng nhập
use App\Models\Schedule;                // Import model Schedule
use Illuminate\Validation\ValidationException; // Import để xử lý lỗi validation và error bag

class ScheduleController extends Controller
{
    /**
     * Display the student's assigned schedule.
     * Hiển thị lịch trình được gán cho sinh viên.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Lấy thông tin người dùng đang đăng nhập
        $student = Auth::user();

        // Lấy danh sách lịch trình được gán cho sinh viên này
        // Sử dụng eager loading 'creator' để lấy thông tin người tạo (Admin) nếu cần hiển thị
        // Cũng tải kèm cột 'change_reason' để hiển thị nếu có
        $schedules = $student->schedules() // Gọi quan hệ 'schedules' đã định nghĩa trong User model
                             ->with('creator') // Tải kèm thông tin người tạo
                             ->orderBy('start_time', 'asc') // Sắp xếp theo thời gian bắt đầu tăng dần
                             ->paginate(10); // Phân trang nếu muốn

        // Trả về view và truyền dữ liệu schedules qua
        // View sẽ nằm ở resources/views/student/schedule/index.blade.php
        return view('student.schedule.index', compact('schedules'));
    }

    /**
     * Handle the request to change a schedule.
     * Xử lý yêu cầu thay đổi lịch trình.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Schedule  $schedule // <<< Nhận đối tượng Schedule nhờ Route Model Binding
     * @return \Illuminate\Http\RedirectResponse
     */
    public function requestChange(Request $request, Schedule $schedule)
    {
        // 1. Kiểm tra quyền: Sinh viên này có sở hữu lịch trình này không?
        if ($schedule->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền yêu cầu thay đổi lịch này.'); // Forbidden
        }

        // 2. Kiểm tra trạng thái: Chỉ cho phép yêu cầu khi đang 'scheduled'
        if ($schedule->status !== 'scheduled') {
             return back()->with('error', 'Không thể yêu cầu thay đổi cho lịch trình ở trạng thái "' . $schedule->status . '".');
        }

        // 3. Validate dữ liệu gửi lên (lý do)
        //    Sử dụng try-catch để bắt ValidationException và gán lỗi vào error bag cụ thể
        try {
            $validated = $request->validate([
                'change_reason' => ['required', 'string', 'min:10', 'max:500'], // Yêu cầu nhập, là chuỗi, tối thiểu 10, tối đa 500 ký tự
            ], [], [ // (Tùy chọn) Đặt tên thuộc tính tiếng Việt cho thông báo lỗi
                'change_reason' => 'Lý do thay đổi'
            ]);
        } catch (ValidationException $e) {
            // Nếu validation thất bại, ném lại exception với error bag cụ thể
            // để lỗi chỉ hiển thị đúng form trên view
            throw ValidationException::withMessages($e->errors())->errorBag("schedule_{$schedule->id}");
        }


        // 4. Cập nhật trạng thái và lý do vào bản ghi Schedule
        $schedule->status = 'pending_change';
        $schedule->change_reason = $validated['change_reason'];
        $schedule->save(); // Lưu thay đổi vào DB

        // 5. (Nâng cao/Ngày sau) Gửi notification cho Admin
        //    Ví dụ:
        //    $adminUsers = User::whereHas('role', fn($q) => $q->where('name', 'Admin'))->get();
        //    if ($adminUsers->isNotEmpty()) {
        //        Notification::send($adminUsers, new \App\Notifications\ScheduleChangeRequested($schedule));
        //    }

        // 6. Chuyển hướng về trang lịch với thông báo thành công
        return redirect()->route('student.schedule.index')
                         ->with('success', 'Yêu cầu thay đổi lịch trình đã được gửi thành công.');
    }
}