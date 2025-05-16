<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Cần để lấy user đang đăng nhập
use App\Models\Attendance;           // Import model Attendance
use Carbon\Carbon;                   // Import Carbon

class AttendanceController extends Controller
{
    /**
     * Xử lý hành động check-in của sinh viên.
     */
    public function checkIn(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->format('Y-m-d');

        // 1. Kiểm tra xem sinh viên đã check-in trong ngày hôm nay chưa
        $existingAttendance = Attendance::where('user_id', $user->id)
                                        ->whereDate('attendance_date', $today) // Chỉ kiểm tra trong ngày hôm nay
                                        ->first();

        if ($existingAttendance) {
            // Nếu đã tồn tại bản ghi điểm danh cho ngày hôm nay
            if ($existingAttendance->check_in_time) {
                // Nếu đã có giờ check-in -> Báo lỗi đã check-in
                 return back()->with('error', 'Bạn đã check-in hôm nay rồi vào lúc ' . $existingAttendance->check_in_time->format('H:i'));
            } else {
                // Trường hợp lạ: có bản ghi nhưng chưa có check_in_time (có thể do lỗi trước đó)
                // Cập nhật lại check_in_time cho bản ghi này
                 $currentTime = Carbon::now();
                 // Xác định trạng thái dựa trên giờ check-in (Ví dụ: trước 9:00 là on_time)
                 $onTimeDeadline = Carbon::today()->setHour(9)->setMinute(0)->setSecond(0);
                 $status = $currentTime->lte($onTimeDeadline) ? 'on_time' : 'late';

                 $existingAttendance->update([
                    'check_in_time' => $currentTime,
                    'status' => $status,
                 ]);
                 return back()->with('success', 'Check-in thành công lúc ' . $currentTime->format('H:i'));
            }
        }

        // 2. Nếu chưa check-in hôm nay, tạo bản ghi mới
        $currentTime = Carbon::now();
        // Xác định trạng thái dựa trên giờ check-in (Ví dụ: trước 9:00 là on_time)
        $onTimeDeadline = Carbon::today()->setHour(9)->setMinute(0)->setSecond(0);
        $status = $currentTime->lte($onTimeDeadline) ? 'on_time' : 'late';

        Attendance::create([
            'user_id' => $user->id,
            'check_in_time' => $currentTime,
            'status' => $status,
            'attendance_date' => $today, // Lưu ngày điểm danh
            // check_out_time, notes, image_path sẽ là null ban đầu
        ]);

        return back()->with('success', 'Check-in thành công lúc ' . $currentTime->format('H:i'));
    }

     // (Tùy chọn) Thêm hàm checkOut nếu muốn Sinh viên tự check-out
    /**
     * Xử lý hành động check-out của sinh viên.
     */
    public function checkOut(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->format('Y-m-d');
        $now = Carbon::now();

        // 1. Tìm bản ghi điểm danh của ngày hôm nay đã check-in nhưng chưa check-out
        $attendance = Attendance::where('user_id', $user->id)
                              ->whereDate('attendance_date', $today)
                              ->whereNotNull('check_in_time') // Đã check-in
                              ->whereNull('check_out_time')   // Chưa check-out
                              ->first();

        // 2. Kiểm tra xem có tìm thấy bản ghi phù hợp không
        if (!$attendance) {
            return back()->with('error', 'Không tìm thấy lượt check-in hợp lệ để check-out hôm nay.');
        }

        // 3. (Tùy chọn) Kiểm tra giới hạn thời gian check-out
        // Ví dụ: Chỉ cho check-out sau 16:00
        $minCheckoutTime = Carbon::today()->setHour(16)->setMinute(0)->setSecond(0);
        if ($now->lt($minCheckoutTime)) {
            // return back()->with('error', 'Chưa đến giờ check-out (Sau ' . $minCheckoutTime->format('H:i') . ').');
        }
         // Ví dụ: Chỉ cho check-out trong 15 phút cuối giờ (vd: 17:15 - 17:30)
         $endWorkTime = Carbon::today()->setHour(17)->setMinute(30);
         $earlyCheckoutLimit = $endWorkTime->copy()->subMinutes(15); // 17:15
         // if (!$now->between($earlyCheckoutLimit, $endWorkTime->addMinutes(5))) { // Cho phép trễ 5p
         //    return back()->with('error', 'Chỉ được check-out từ ' . $earlyCheckoutLimit->format('H:i') . ' đến ' . $endWorkTime->format('H:i'));
         // }


        // 4. Cập nhật giờ check-out
        $attendance->update([
            'check_out_time' => $now,
            // Có thể cập nhật lại status nếu cần (ví dụ: 'completed')
        ]);

         // 5. (Tùy chọn) Ghi lại IP, User Agent
         // $attendance->update([
         //     'check_out_ip_address' => $request->ip(),
         //     'check_out_user_agent' => $request->userAgent(),
         // ]);
         // (Bạn cần thêm các cột này vào migration và model Attendance nếu muốn làm)

        return back()->with('success', 'Check-out thành công lúc ' . $now->format('H:i'));
    }
     // public function checkOut(Request $request) { ... }
}