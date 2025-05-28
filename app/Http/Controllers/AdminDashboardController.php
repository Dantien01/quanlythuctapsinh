<?php

namespace App\Http\Controllers; // Namespace của bạn có thể khác nếu AdminDashboardController nằm trong App\Http\Controllers\Admin

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Schedule;
use App\Models\Diary;
use App\Models\Attendance;
use App\Models\Major;
use Illuminate\Support\Facades\DB; // Đã có
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // --- 1. Summary Stats ---
        $totalStudents = User::whereHas('role', function ($query) {
            $query->where('name', 'SinhVien');
        })->count();

        // ===== PHẦN CẬP NHẬT - START: SỬ DỤNG TÊN CỘT ĐÚNG =====
        $activeSchedules = Schedule::whereIn('status', [
                                        Schedule::STATUS_SCHEDULED, // Nên dùng hằng số
                                        // Thêm các status khác nếu 'updated', 'approved' là string thực tế trong DB
                                        // Hoặc Schedule::STATUS_CHANGE_APPROVED, etc.
                                        'updated',
                                        'approved'
                                    ])
                                   ->where(function($query) {
                                       $query->where('overall_end_date', '>=', now()) // SỬA THÀNH overall_end_date
                                             ->orWhereNull('overall_end_date');      // SỬA THÀNH overall_end_date
                                   })
                                   ->count();
        // ===== PHẦN CẬP NHẬT - END =====

        $pendingScheduleRequests = Schedule::where('status', Schedule::STATUS_PENDING_CHANGE)->count(); // Nên dùng hằng số
        $pendingDiaries = Diary::where('status', 'commented')
                                ->orWhereNull('reviewed_at') // Giữ nguyên logic này nếu đúng
                                ->count();

        $stats = [
            'total_students' => $totalStudents,
            'active_schedules' => $activeSchedules,
            'pending_schedule_requests' => $pendingScheduleRequests,
            'pending_diaries' => $pendingDiaries,
        ];

        // --- 2. Chart Data ---

        // a) Activity Over Time
        $activityLabels = [];
        $checkInData = [];
        $diaryData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $activityLabels[] = $date->format('d/m');
            $checkIns = Attendance::whereDate('check_in_time', $date)->count();
            $diariesCount = Diary::whereDate('created_at', $date)->count();
            $checkInData[] = $checkIns;
            $diaryData[] = $diariesCount;
        }
        $activityChartData = [
             'labels' => $activityLabels,
             'datasets' => [
                 [ 'label' => 'Lượt điểm danh', 'data' => $checkInData, 'borderColor' => 'rgba(78, 115, 223, 1)', 'backgroundColor' => 'rgba(78, 115, 223, 0.1)', 'fill' => true, 'tension' => 0.3 ],
                 [ 'label' => 'Lượt tạo nhật ký', 'data' => $diaryData, 'borderColor' => 'rgba(28, 200, 138, 1)', 'backgroundColor' => 'rgba(28, 200, 138, 0.1)', 'fill' => true, 'tension' => 0.3 ]
             ]
         ];

        // b) Students by Major
        $studentsByMajorQuery = User::whereHas('role', fn($q)=>$q->where('name', 'SinhVien'))
                                  ->with('major') // Đảm bảo User model có quan hệ 'major'
                                  ->select('major_id', DB::raw('count(*) as total'))
                                  ->groupBy('major_id')->get();

         $majorLabels = []; $majorData = [];
         $majorBackgroundColors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69'];
         $majorHoverBackgroundColors = ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617', '#60616f', '#373840'];
         foreach ($studentsByMajorQuery as $data) {
             $majorName = $data->major->name ?? 'Chưa phân ngành'; // Truy cập major name
             $majorLabels[] = $majorName; $majorData[] = $data->total;
         }
         // Logic điều chỉnh màu sắc giữ nguyên
         while (count($majorBackgroundColors) < count($majorLabels)) { $majorBackgroundColors = array_merge($majorBackgroundColors, $majorBackgroundColors); $majorHoverBackgroundColors = array_merge($majorHoverBackgroundColors, $majorHoverBackgroundColors); }
         $majorChartData = [ 'labels' => $majorLabels, 'datasets' => [['data' => $majorData, 'backgroundColor' => array_slice($majorBackgroundColors, 0, count($majorLabels)), 'hoverBackgroundColor' => array_slice($majorHoverBackgroundColors, 0, count($majorLabels)), 'hoverBorderColor' => "rgba(234, 236, 244, 1)"]] ];


        // c) Internship Progress (Logic random giữ nguyên)
        $progressLabels = ['0-25%', '26-50%', '51-75%', '76-100%', 'Hoàn thành']; $progressData = [];
        if ($totalStudents > 0) { $progressData = [ rand(0, intval($totalStudents * 0.1)), rand(0, intval($totalStudents * 0.2)), rand(0, intval($totalStudents * 0.4)), rand(0, intval($totalStudents * 0.2)), rand(0, intval($totalStudents * 0.1)) ]; $currentSum = array_sum($progressData); if ($currentSum > $totalStudents) { $scale = $totalStudents / $currentSum; $progressData = array_map(fn($val) => floor($val * $scale), $progressData); $remainder = $totalStudents - array_sum($progressData); for($i = 0; $i < $remainder; $i++) { $progressData[$i % count($progressData)]++; } } elseif ($currentSum < $totalStudents) { $progressData[count($progressData) - 1] += ($totalStudents - $currentSum); } } else { $progressData = array_fill(0, count($progressLabels), 0); }
        $progressChartData = [ 'labels' => $progressLabels, 'datasets' => [[ 'label' => "Số lượng SV", 'backgroundColor' => "#4e73df", 'hoverBackgroundColor' => "#2e59d9", 'borderColor' => "#4e73df", 'data' => $progressData, 'maxBarThickness' => 25, ]] ];


        // --- 3. Recent Activity ---
        $recentCheckins = Attendance::with('user') // Đảm bảo Attendance model có quan hệ 'user'
                            ->orderBy('check_in_time', 'desc')
                            ->limit(5)
                            ->get();

        $recentDiaries = Diary::with('user') // Đảm bảo Diary model có quan hệ 'user'
                            ->orderBy('created_at', 'desc')
                            ->limit(5)
                            ->get();

        $combinedActivities = collect();
        foreach ($recentCheckins as $item) {
            if ($item->user) { /* ... code thêm vào combinedActivities ... */
                $combinedActivities->push([
                    'type' => 'checkin',
                    'user_name' => $item->user->name,
                    'time' => $item->check_in_time,
                    'text' => "Sinh viên <strong>{$item->user->name}</strong> đã điểm danh.",
                    'icon' => 'fas fa-map-marker-alt text-primary'
                 ]);
            }
        }
        foreach ($recentDiaries as $item) {
             if ($item->user) { /* ... code thêm vào combinedActivities ... */
                $combinedActivities->push([
                    'type' => 'diary',
                    'user_name' => $item->user->name,
                    'time' => $item->created_at,
                    'text' => "Sinh viên <strong>{$item->user->name}</strong> đã tạo nhật ký.",
                    'icon' => 'fas fa-book text-success',
                    'link' => route('admin.diaries.show', $item->id)
                ]);
             }
        }
        $recentActivities = $combinedActivities->sortByDesc('time')->take(10)->values();


        // --- 4. Alerts (Example) ---
        $alerts = collect();
        $cutoffDate = Carbon::today()->subDays(3);
        $inactiveStudents = User::whereHas('role', fn($q)=>$q->where('name','SinhVien'))
                               ->whereDoesntHave('attendances', fn($q)=>$q->where('check_in_time','>',$cutoffDate))
                               ->limit(5)->get();
        foreach($inactiveStudents as $studentAlert) { // Đổi tên biến để tránh nhầm lẫn
             $alerts->push([
                 'type' => 'no_checkin',
                 'user_name' => $studentAlert->name,
                 'text' => "SV <strong>{$studentAlert->name}</strong> chưa điểm danh trong 3 ngày qua.",
                 'icon' => 'fas fa-exclamation-triangle text-warning',
                 'link' => route('admin.students.show', $studentAlert->id)
             ]);
        }
        // ... (Phần overdueDiaries giữ nguyên logic comment out của bạn) ...

        // --- Pass data to the view ---
        return view('admin.dashboard', compact(
            'stats',
            'activityChartData',
            'majorChartData',
            'progressChartData',
            'recentActivities',
            'alerts'
        ));
    }
}