<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Schedule; // <-- Thêm lại import này
use App\Models\Diary; // <-- Thêm lại import này
use App\Models\StudentReview; // Giả sử bạn có model này hoặc điều chỉnh tên
use Carbon\Carbon;
use Carbon\CarbonPeriod; // <-- Thêm import này
class DashboardController extends Controller
{
/**
* Hiển thị dashboard phù hợp hoặc chuyển hướng dựa trên vai trò người dùng.
*/
public function index()
{
$user = Auth::user();
if ($user->hasRole('Admin')) {
        // Chuyển hướng Admin đến dashboard của họ
        return redirect()->route('admin.dashboard');
    }

    if ($user->hasRole('SinhVien')) {
        // Lấy lịch trình hiện tại hoặc liên quan nhất của sinh viên
        $activeSchedule = $user->schedules()
                               ->where(function($q) {
                                   $q->where('end_time', '>=', now())->orWhereNull('end_time'); // Giả sử cột là end_time
                               })
                               ->orderBy('start_time', 'desc') // Giả sử cột là start_time
                               ->first();

        // --- Lấy dữ liệu cơ bản ---
        $allAttendances = $user->attendances()->whereNotNull('check_in_time')->get();
        $totalWorkHours = round($allAttendances->sum(function ($att) {
             return $att->work_duration_in_hours ?? 0; // Sử dụng accessor nếu có
         }), 2);
        $latestReview = $user->reviewsReceived()->with('reviewer')->latest()->first(); // Đảm bảo relationship tồn tại
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

        for ($date = $startDate7Days->copy(); $date->lte($endDate7Days); $date->addDay()) {
            $formattedDate = $date->format('Y-m-d');
            $chartLabels7Days[] = $date->format('d/m');
            $chartDataHours7Days[] = $dailyHoursMap7Days->get($formattedDate, 0);
        }

        // --- 1. Biểu đồ tiến độ thực tập ---
        $completedHours = $totalWorkHours;
        // *** CẦN LOGIC THỰC TẾ: Lấy tổng số giờ/buổi yêu cầu ***
        // Ví dụ lấy từ schedule hoặc giá trị mặc định
        $totalRequiredHours = $activeSchedule->total_hours ?? ($activeSchedule ? 40 * Carbon::parse($activeSchedule->start_time)->diffInWeeks($activeSchedule->end_time ?? now()) : 160); // Ước tính 40h/tuần nếu có lịch
        $progressPercentage = ($totalRequiredHours > 0) ? round(($completedHours / $totalRequiredHours) * 100) : 0;
        $progressData = [
            'percentage' => min($progressPercentage, 100), // Đảm bảo không vượt quá 100%
            'completed' => $completedHours,
            'total' => $totalRequiredHours,
            'unit' => 'giờ' // Hoặc 'buổi'
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
            $attendanceChartData[] = $attendancesLast30Days->has($formattedDate) ? 1 : 0; // 1 = có, 0 = không
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
        // Giả sử lấy điểm 'grade' từ Diary
        $reviewsData = Diary::where('user_id', $user->id)
                            ->whereNotNull('grade')
                            ->whereNotNull('reviewed_at')
                            ->pluck('grade');

        $averageGrade = $reviewsData->count() > 0 ? round($reviewsData->avg(), 1) : null;
        $feedbackChartLabels = ['Điểm TB']; // Đơn giản nhất là 1 cột điểm TB
        $feedbackChartData = [$averageGrade ?? 0];
        $feedbackChart = [
             'labels' => $feedbackChartLabels,
             'data' => $feedbackChartData,
             'average' => $averageGrade
        ];


        // Trả về view dashboard cho Sinh viên với tất cả dữ liệu
        // Bỏ $performanceStats nếu không dùng nữa
        return view('dashboard', compact(
            'user',
            'totalWorkHours',
            'latestReview',
            'chartLabels7Days', // Dữ liệu biểu đồ giờ làm 7 ngày
            'chartDataHours7Days',// Dữ liệu biểu đồ giờ làm 7 ngày
            'todayAttendance',
            'progressData', // Dữ liệu biểu đồ tiến độ
            'attendanceChart', // Dữ liệu biểu đồ điểm danh
            'diaryChart', // Dữ liệu biểu đồ nhật ký
            'feedbackChart' // Dữ liệu biểu đồ đánh giá
        ));
    }

    // Trường hợp mặc định
    return view('dashboard'); // Đảm bảo view 'dashboard' này tồn tại
    }
}