<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Schedule;
use App\Models\InternshipSlot;
use Carbon\Carbon;
// Bỏ Illuminate\Validation\ValidationException nếu không dùng
// use Illuminate\Validation\ValidationException;


class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $student = Auth::user();
        $referenceDate = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::now();

        $startOfWeek = $referenceDate->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $endOfWeek = $referenceDate->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        // ===== PHẦN CẬP NHẬT - START: SỬ DỤNG TÊN CỘT ĐÚNG =====
        $schedulesQuery = $student->schedules() // Đảm bảo User model có quan hệ schedules() đúng (BelongsToMany)
                             ->where(function ($query) use ($startOfWeek, $endOfWeek) {
                                 $query->where('overall_start_date', '<=', $endOfWeek) // SỬA THÀNH overall_start_date
                                       ->where('overall_end_date', '>=', $startOfWeek); // SỬA THÀNH overall_end_date
                             })
                             ->with('creator:id,name') // Chỉ lấy id, name của creator cho tối ưu
                             ->orderBy('overall_start_date', 'asc'); // SỬA THÀNH overall_start_date
        // ===== PHẦN CẬP NHẬT - END =====


        if ($request->ajax() || $request->input('is_ajax')) {
            $schedules = $schedulesQuery->get();
            return view('student.schedule.partials.schedules_table', compact('schedules'))->render();
        }

        $schedules = $schedulesQuery->paginate(10);
        return view('student.schedule.index', compact('schedules', 'referenceDate'));
    }

    // ... (Phương thức requestChange và getScheduleDetail giữ nguyên như phiên bản đã sửa lỗi cột trước đó)
    public function requestChange(Request $request, Schedule $schedule)
    {
        if ($schedule->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền yêu cầu thay đổi lịch này.');
        }

        // Sử dụng tên cột đúng nếu bạn kiểm tra $schedule->overall_start_date ở đây
        $allowedStatuses = [
            Schedule::STATUS_SCHEDULED,
            Schedule::STATUS_CHANGE_APPROVED,
            Schedule::STATUS_CHANGE_REJECTED
        ];
        // Giả sử getHasPassedAttribute trong Schedule model đã được cập nhật để dùng overall_end_date
        if (!in_array($schedule->status, $allowedStatuses) || $schedule->getHasPassedAttribute()) {
             return back()->with('error', 'Không thể yêu cầu thay đổi cho lịch trình ở trạng thái hiện tại hoặc lịch đã qua.');
        }

        $schedule->status = Schedule::STATUS_PENDING_CHANGE;
        $schedule->save();

        return redirect()->route('student.schedule.index')
                         ->with('success', 'Yêu cầu thay đổi lịch trình đã được gửi thành công.');
    }

    public function getScheduleDetail(Request $request, Schedule $schedule)
    {
        if ($schedule->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'error' => 'Bạn không có quyền xem lịch trình này.'], 403);
        }

        $schedule->load([
            'slots' => function($query) {
                $query->orderBy('day_of_week')->orderBy('start_time');
            },
            'creator:id,name'
        ]);

        $referenceDateInput = $request->input('selected_week_date');
        // Sử dụng tên cột đúng ở đây
        $referenceDateForWeek = $referenceDateInput
                                ? Carbon::parse($referenceDateInput)
                                : ($schedule->overall_start_date ? Carbon::parse($schedule->overall_start_date) : Carbon::now());

        $weekNumber = $referenceDateForWeek->weekOfYear;
        $weekStartDate = $referenceDateForWeek->copy()->startOfWeek(Carbon::MONDAY);
        $weekEndDate = $referenceDateForWeek->copy()->endOfWeek(Carbon::SUNDAY);

        $scheduleDetailData = [
            'id' => $schedule->id,
            'title' => $schedule->title,
            'description' => $schedule->description ?? 'Không có mô tả',
            // Sử dụng tên cột đúng ở đây
            'overall_start_time' => $schedule->overall_start_date ? Carbon::parse($schedule->overall_start_date)->format('d/m/Y H:i') : 'N/A',
            'overall_end_time' => $schedule->overall_end_date ? Carbon::parse($schedule->overall_end_date)->format('d/m/Y H:i') : 'N/A',
            'status_text' => $schedule->status_text,
            'assigner_name' => $schedule->creator->name ?? 'Không xác định',
            'weekly_slots' => array_fill(1, 7, [])
        ];

        foreach ($schedule->slots as $slot) {
            $day = (int)$slot->day_of_week;
            if ($day >= 1 && $day <= 7) {
                $scheduleDetailData['weekly_slots'][$day][] = [
                    'start_time' => Carbon::parse($slot->start_time)->format('H:i'),
                    'end_time' => Carbon::parse($slot->end_time)->format('H:i'),
                    'task' => $slot->task_description ?? 'Thực tập',
                ];
            }
        }

        $daysOfWeekNames = [];
        for ($i = 1; $i <= 7; $i++) {
            $daysOfWeekNames[$i] = Schedule::getDayName($i);
        }

        return response()->json([
            'success' => true,
            'schedule' => $scheduleDetailData,
            'daysOfWeekNames' => $daysOfWeekNames,
            'week_info' => [
                'number' => $weekNumber,
                'start_date_formatted' => $weekStartDate->format('d/m/Y'),
                'end_date_formatted' => $weekEndDate->format('d/m/Y'),
            ],
        ]);
    }
}