<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class StudentAttendanceApiController extends Controller
{
    /**
     * Xử lý hành động Clock In của sinh viên qua API.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clockIn(Request $request)
    {
        $student = Auth::user();
        $now = Carbon::now();
        $today = $now->toDateString(); // Lấy ngày hiện tại Y-m-d

        // 1. Kiểm tra xem sinh viên đã có bản ghi điểm danh nào trong ngày hôm nay
        //    mà chưa check_out hay không. Logic này khác với controller web của bạn một chút
        //    để phù hợp hơn với API (không cho clock-in nhiều lần nếu chưa clock-out).
        $activeAttendance = Attendance::where('user_id', $student->id)
                                      ->whereNull('check_out_time')
                                      ->latest('check_in_time') // Lấy lần check-in cuối cùng chưa check-out
                                      ->first();

        if ($activeAttendance) {
            return response()->json([
                'message' => 'Bạn đã clock in vào lúc ' . Carbon::parse($activeAttendance->check_in_time)->format('H:i:s d/m/Y') . ' và chưa clock out. Vui lòng clock out trước khi clock in mới.',
                'attendance' => $activeAttendance
            ], 400); // Bad Request - Đã có phiên làm việc đang mở
        }

        // 2. Validate dữ liệu đầu vào (nếu có, ví dụ 'notes', 'image_path' từ mobile)
        $validatedData = $request->validate([
            'notes' => 'nullable|string|max:500',
            'image_path' => 'nullable|string|max:255',
            // Bạn có thể thêm các trường khác như latitude, longitude nếu mobile gửi lên
        ]);

        // 3. Nếu chưa có phiên làm việc nào đang mở, tạo bản ghi mới
        // Xác định trạng thái dựa trên giờ check-in (Ví dụ: trước 9:00 là on_time)
        // Logic này có thể giống với controller web của bạn
        $onTimeDeadline = Carbon::today()->setHour(9)->setMinute(0)->setSecond(0); // Ví dụ 9:00 AM
        $status = $now->lte($onTimeDeadline) ? Attendance::STATUS_ON_TIME : Attendance::STATUS_LATE;
        // Đảm bảo bạn có các hằng số STATUS_ON_TIME, STATUS_LATE trong model Attendance

        try {
            $attendance = Attendance::create([
                'user_id' => $student->id,
                'check_in_time' => $now,
                'status' => $status, // Hoặc bạn có thể đặt một status mặc định như 'clocked_in'
                'attendance_date' => $today,
                'notes' => $validatedData['notes'] ?? null,
                'image_path' => $validatedData['image_path'] ?? null,
            ]);

            Log::info("[API] Student ID: {$student->id} clocked IN. Attendance ID: {$attendance->id}");

            return response()->json([
                'message' => 'Clock in thành công!',
                'attendance' => $attendance->load('user:id,name') // Trả về kèm thông tin user nếu cần
            ], 201);

        } catch (\Exception $e) {
            Log::error("[API] Error during clock IN for Student ID: {$student->id}. Error: " . $e->getMessage());
            return response()->json(['message' => 'Đã có lỗi xảy ra khi clock in. Vui lòng thử lại.'], 500);
        }
    }

    /**
     * Xử lý hành động Clock Out của sinh viên qua API.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clockOut(Request $request)
    {
        $student = Auth::user();
        $now = Carbon::now();

        // 1. Tìm bản ghi điểm danh của sinh viên này đã check-in nhưng chưa check-out
        //    Chúng ta sẽ lấy bản ghi check-in mới nhất chưa check-out
        $attendance = Attendance::where('user_id', $student->id)
                              ->whereNotNull('check_in_time')
                              ->whereNull('check_out_time')
                              ->latest('check_in_time') // Lấy bản ghi check_in gần nhất
                              ->first();

        // 2. Kiểm tra xem có tìm thấy bản ghi phù hợp không
        if (!$attendance) {
            return response()->json(['message' => 'Không tìm thấy lượt clock in nào đang mở để clock out.'], 400); // Bad Request
        }

        // 3. Validate dữ liệu đầu vào cho clock out (nếu có, ví dụ 'notes')
        $validatedData = $request->validate([
            'notes_checkout' => 'nullable|string|max:500', // Ví dụ, ghi chú khi clock out
        ]);

        // 4. Cập nhật giờ check-out
        // Logic trạng thái khi check-out có thể phức tạp hơn, ví dụ: 'completed_on_time', 'completed_early'
        // Hiện tại, chúng ta có thể chỉ cập nhật check_out_time và giữ nguyên status từ lúc check-in,
        // hoặc cập nhật sang một trạng thái mới như 'clocked_out'.
        try {
            $updateData = [
                'check_out_time' => $now,
                'status' => Attendance::STATUS_CLOCKED_OUT, // Giả sử có hằng số này
            ];

            if (isset($validatedData['notes_checkout'])) {
                // Nếu bạn muốn ghép notes hoặc có cột notes riêng cho check-out
                $attendance->notes = ($attendance->notes ? $attendance->notes . "\n" : '') . "[OUT]: " . $validatedData['notes_checkout'];
                // Hoặc nếu có cột `notes_checkout` riêng:
                // $updateData['notes_checkout'] = $validatedData['notes_checkout'];
            }

            $attendance->update($updateData);
            // Nếu bạn đã cập nhật notes riêng, thì save() sau update()
            if (isset($validatedData['notes_checkout']) && ($attendance->notes ? $attendance->notes . "\n" : '') . "[OUT]: " . $validatedData['notes_checkout']) {
                 $attendance->save();
            }


            Log::info("[API] Student ID: {$student->id} clocked OUT. Attendance ID: {$attendance->id}");

            return response()->json([
                'message' => 'Clock out thành công!',
                'attendance' => $attendance->fresh()->load('user:id,name')
            ], 200);

        } catch (\Exception $e) {
            Log::error("[API] Error during clock OUT for Student ID: {$student->id}, Attendance ID: {$attendance->id}. Error: " . $e->getMessage());
            return response()->json(['message' => 'Đã có lỗi xảy ra khi clock out. Vui lòng thử lại.'], 500);
        }
    }

    /**
     * Lấy lịch sử điểm danh của sinh viên đang đăng nhập.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAttendanceHistory(Request $request)
    {
        $student = Auth::user();

        $request->validate([
            'from_date' => 'nullable|date_format:Y-m-d',
            'to_date' => 'nullable|date_format:Y-m-d|after_or_equal:from_date',
            'month' => 'nullable|date_format:Y-m', // Ví dụ: 2023-11
        ]);

        $query = Attendance::where('user_id', $student->id)
                           ->orderBy('attendance_date', 'desc')
                           ->orderBy('check_in_time', 'desc');

        if ($request->filled('from_date')) {
            $query->where('attendance_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('attendance_date', '<=', $request->to_date);
        }
        if ($request->filled('month')) {
            $query->whereYear('attendance_date', Carbon::parse($request->month)->year)
                  ->whereMonth('attendance_date', Carbon::parse($request->month)->month);
        }

        $attendances = $query->paginate($request->input('per_page', 15)); // Cho phép client chỉ định số lượng item/trang

        return response()->json($attendances);
    }

    /**
     * Lấy trạng thái điểm danh hiện tại của sinh viên.
     * (Đã clock in hay chưa, nếu rồi thì thông tin là gì)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCurrentAttendanceStatus()
    {
        $student = Auth::user();

        $activeAttendance = Attendance::where('user_id', $student->id)
                                      ->whereNull('check_out_time')
                                      ->latest('check_in_time')
                                      ->first();
        if ($activeAttendance) {
            return response()->json([
                'status' => 'clocked_in',
                'message' => 'Bạn đang trong một phiên làm việc, đã clock in vào lúc ' . Carbon::parse($activeAttendance->check_in_time)->format('H:i:s d/m/Y') . '.',
                'attendance' => $activeAttendance->load('user:id,name')
            ], 200);
        } else {
            return response()->json([
                'status' => 'not_clocked_in',
                'message' => 'Bạn hiện chưa clock in.'
            ], 200);
        }
    }
}