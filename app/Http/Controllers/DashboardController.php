<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Diary;
use App\Models\StudentReview; // Giả sử bạn có model này
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class DashboardController extends Controller
{
    /**
    * Hiển thị dashboard phù hợp hoặc chuyển hướng dựa trên vai trò người dùng.
    */
    public function index()
    {
        $user = Auth::user();
        if ($user->hasRole('Admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('SinhVien')) {
            // Lấy lịch trình hiện tại hoặc liên quan nhất của sinh viên
            // ===== PHẦN CẬP NHẬT - START: SỬ DỤNG TÊN CỘT ĐÚNG =====
            $activeSchedule = $user->schedules() // Đảm bảo User model có quan hệ 'schedules()' đúng (BelongsToMany)
                                   ->where(function($q) {
                                       $q->where('overall_end_date', '>=', now()) // SỬA THÀNH overall_end_date
                                         ->orWhereNull('overall_end_date');      // SỬA THÀNH overall_end_date
                                   })
                                   ->orderBy('overall_start_date', 'desc') // SỬA THÀNH overall_start_date
                                   ->first();
            // ===== PHẦN CẬP NHẬT - END =====

            // --- Lấy dữ liệu cơ bản ---
            $allAttendances = $user->attendances()->whereNotNull('check_in_time')->get();
            $totalWorkHours = round($allAttendances->sum(function ($att) {
                 return $att->work_duration_in_hours ?? 0;
             }), 2);
            $latestReview = $user->reviewsReceived()->with('reviewer')->latest()->first();
            $todayAttendance = $user->attendances()->whereDate('check_in_time', Carbon::today())->first();

            // --- Biểu đồ Giờ làm (7 ngày qua) ---
            $chartLabels7Days = [];
            $chartDataHours7Days = [];
            $endDate7Days = Carbon::today();
            $startDate7Days = Carbon::today()->subDays(6);

            $dailyHoursMap7Days = $user->attendances()
                ->whereNotNull('check_in_time')
                ->whereNotNull('check_out_time')
                ->whereBetween('check_in_time', [$startDate7Days->startOfDay(), $endDate7Days->endOfDay()])
                ->selectRaw('DATE(check_in_time) as date, SUM(TIMESTAMPDIFF(SECOND, check_in_time, check_out_time)) as total_seconds')
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get()
                ->pluck('total_seconds', 'date')
                ->map(fn ($seconds) => round($seconds / 3600, 2));

            for ($dateLoop = $startDate7Days->copy(); $dateLoop->lte($endDate7Days); $dateLoop->addDay()) { // Sửa tên biến lặp
                $formattedDate = $dateLoop->format('Y-m-d');
                $chartLabels7Days[] = $dateLoop->format('d/m');
                $chartDataHours7Days[] = $dailyHoursMap7Days->get($formattedDate, 0);
            }

            // --- 1. Biểu đồ tiến độ thực tập ---
            $completedHours = $totalWorkHours;
            // Cần logic thực tế để lấy tổng số giờ yêu cầu từ activeSchedule
            $totalRequiredHours = 160; // Giá trị mặc định nếu không có activeSchedule
            if ($activeSchedule && $activeSchedule->overall_start_date && $activeSchedule->overall_end_date) {
                // Ước tính dựa trên thời gian lịch trình, ví dụ 40 giờ/tuần
                // Hoặc nếu bạn có một trường total_hours trong model Schedule thì dùng nó
                // $totalRequiredHours = $activeSchedule->total_hours ?? (40 * Carbon::parse($activeSchedule->overall_start_date)->diffInWeeksFiltered(function(Carbon $date) {
                //     return !$date->isWeekend(); // Chỉ tính ngày trong tuần
                // }, Carbon::parse($activeSchedule->overall_end_date)));
                // Cách đơn giản hơn nếu chỉ dựa vào số tuần:
                 $totalRequiredHours = $activeSchedule->total_hours ?? (40 * Carbon::parse($activeSchedule->overall_start_date)->diffInWeeks(Carbon::parse($activeSchedule->overall_end_date)->endOfDay()));
                 if ($totalRequiredHours <= 0 && $activeSchedule) $totalRequiredHours = 40; // Ít nhất 1 tuần
            }

            $progressPercentage = ($totalRequiredHours > 0) ? round(($completedHours / $totalRequiredHours) * 100) : 0;
            $progressData = [
                'percentage' => min($progressPercentage, 100),
                'completed' => $completedHours,
                'total' => $totalRequiredHours,
                'unit' => 'giờ'
            ];

            // --- 2. Biểu đồ điểm danh (30 ngày qua) ---
            $attendanceChartLabels = [];
            $attendanceChartData = [];
            $attendanceStartDate = Carbon::today()->subDays(29);
            $attendanceEndDate = Carbon::today();
            $attendancesLast30Days = $user->attendances()
                                        ->whereNotNull('check_in_time')
                                        ->whereBetween('check_in_time', [$attendanceStartDate->startOfDay(), $attendanceEndDate->endOfDay()])
                                        ->get()
                                        ->keyBy(fn ($item) => Carbon::parse($item->check_in_time)->format('Y-m-d'));

            $period30Days = CarbonPeriod::create($attendanceStartDate, $attendanceEndDate);
            foreach ($period30Days as $date) {
                $formattedDate = $date->format('Y-m-d');
                $attendanceChartLabels[] = $date->format('d/m');
                $attendanceChartData[] = $attendancesLast30Days->has($formattedDate) ? 1 : 0;
            }
            $attendanceChart = [
                 'labels' => $attendanceChartLabels,
                 'data' => $attendanceChartData
            ];

            // --- 3. Biểu đồ nhật ký thực tập (30 ngày qua) ---
            $diaryChartLabels = [];
            $diaryChartData = [];
            $diaryStartDate = Carbon::today()->subDays(29);
            $diaryEndDate = Carbon::today();
            $diariesLast30Days = Diary::where('user_id', $user->id)
                                    ->whereBetween('created_at', [$diaryStartDate->startOfDay(), $diaryEndDate->endOfDay()])
                                    ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                                    ->groupBy('date')
                                    ->pluck('count', 'date');

            $diaryPeriod30Days = CarbonPeriod::create($diaryStartDate, $diaryEndDate);
             foreach ($diaryPeriod30Days as $date) {
                 $formattedDate = $date->format('Y-m-d');
                 $diaryChartLabels[] = $date->format('d/m');
                 $diaryChartData[] = $diariesLast30Days->get($formattedDate, 0);
             }
             $diaryChart = [
                 'labels' => $diaryChartLabels,
                 'data' => $diaryChartData
             ];

            // --- 4. Biểu đồ đánh giá / phản hồi ---
            $reviewsData = Diary::where('user_id', $user->id)
                                ->whereNotNull('grade')
                                ->whereNotNull('reviewed_at')
                                ->pluck('grade');

            $averageGrade = $reviewsData->count() > 0 ? round($reviewsData->avg(), 1) : null;
            $feedbackChartLabels = ['Điểm TB'];
            $feedbackChartData = [$averageGrade ?? 0];
            $feedbackChart = [
                 'labels' => $feedbackChartLabels,
                 'data' => $feedbackChartData,
                 'average' => $averageGrade
            ];

            return view('dashboard', compact(
                'user',
                'totalWorkHours',
                'latestReview',
                'chartLabels7Days',
                'chartDataHours7Days',
                'todayAttendance',
                'progressData',
                'attendanceChart',
                'diaryChart',
                'feedbackChart',
                'activeSchedule' // Truyền activeSchedule sang view nếu cần hiển thị thông tin của nó
            ));
        }

        // Trường hợp mặc định nếu không phải Admin hay SinhVien (hoặc SinhVien chưa có vai trò được gán đúng)
        // Hoặc nếu $user->hasRole('SinhVien') là false
        return view('dashboard'); // Đảm bảo view 'dashboard' này tồn tại và phù hợp cho các vai trò khác hoặc khách
    }
}