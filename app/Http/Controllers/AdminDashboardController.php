<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Schedule;
use App\Models\Diary;
use App\Models\Attendance;
use App\Models\Major;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // --- 1. Summary Stats ---
        $totalStudents = User::whereHas('role', function ($query) {
            $query->where('name', 'SinhVien');
        })->count();

        $activeSchedules = Schedule::whereIn('status', ['scheduled', 'updated', 'approved'])
                                   ->where(function($query) {
                                       $query->where('end_time', '>=', now())
                                             ->orWhereNull('end_time');
                                   })
                                   ->count();
        $pendingScheduleRequests = Schedule::where('status', 'pending_change')->count();
        $pendingDiaries = Diary::where('status', 'commented') // Hoặc logic trạng thái chờ của bạn
                                ->orWhereNull('reviewed_at')
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
            $diariesCount = Diary::whereDate('created_at', $date)->count(); // Đếm theo ngày tạo
            $checkInData[] = $checkIns;
            $diaryData[] = $diariesCount;
        }
        $activityChartData = [ /* ... giữ nguyên cấu trúc ... */
             'labels' => $activityLabels,
             'datasets' => [
                 [ 'label' => 'Lượt điểm danh', 'data' => $checkInData, 'borderColor' => 'rgba(78, 115, 223, 1)', 'backgroundColor' => 'rgba(78, 115, 223, 0.1)', 'fill' => true, 'tension' => 0.3 ],
                 [ 'label' => 'Lượt tạo nhật ký', 'data' => $diaryData, 'borderColor' => 'rgba(28, 200, 138, 1)', 'backgroundColor' => 'rgba(28, 200, 138, 0.1)', 'fill' => true, 'tension' => 0.3 ]
             ]
         ];

        // b) Students by Major
        $studentsByMajorQuery = User::whereHas('role', fn($q)=>$q->where('name', 'SinhVien'))
                                  ->with('major')
                                  ->select('major_id', DB::raw('count(*) as total'))
                                  ->groupBy('major_id')->get();
        // ... (Phần xử lý $majorChartData giữ nguyên) ...
         $majorLabels = []; $majorData = [];
         $majorBackgroundColors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69'];
         $majorHoverBackgroundColors = ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617', '#60616f', '#373840'];
         foreach ($studentsByMajorQuery as $data) { $majorName = $data->major->name ?? 'Chưa phân ngành'; $majorLabels[] = $majorName; $majorData[] = $data->total; }
         while (count($majorBackgroundColors) < count($majorLabels)) { $majorBackgroundColors = array_merge($majorBackgroundColors, $majorBackgroundColors); $majorHoverBackgroundColors = array_merge($majorHoverBackgroundColors, $majorHoverBackgroundColors); }
         $majorChartData = [ 'labels' => $majorLabels, 'datasets' => [['data' => $majorData, 'backgroundColor' => array_slice($majorBackgroundColors, 0, count($majorLabels)), 'hoverBackgroundColor' => array_slice($majorHoverBackgroundColors, 0, count($majorLabels)), 'hoverBorderColor' => "rgba(234, 236, 244, 1)"]] ];


        // c) Internship Progress
        // ... (Phần xử lý $progressChartData giữ nguyên) ...
        $progressLabels = ['0-25%', '26-50%', '51-75%', '76-100%', 'Hoàn thành']; $progressData = [];
        if ($totalStudents > 0) { /* ... logic random giữ nguyên ... */ $progressData = [ rand(0, intval($totalStudents * 0.1)), rand(0, intval($totalStudents * 0.2)), rand(0, intval($totalStudents * 0.4)), rand(0, intval($totalStudents * 0.2)), rand(0, intval($totalStudents * 0.1)) ]; $currentSum = array_sum($progressData); if ($currentSum > $totalStudents) { $scale = $totalStudents / $currentSum; $progressData = array_map(fn($val) => floor($val * $scale), $progressData); $remainder = $totalStudents - array_sum($progressData); for($i = 0; $i < $remainder; $i++) { $progressData[$i % count($progressData)]++; } } elseif ($currentSum < $totalStudents) { $progressData[count($progressData) - 1] += ($totalStudents - $currentSum); } } else { $progressData = array_fill(0, count($progressLabels), 0); }
        $progressChartData = [ 'labels' => $progressLabels, 'datasets' => [[ 'label' => "Số lượng SV", 'backgroundColor' => "#4e73df", 'hoverBackgroundColor' => "#2e59d9", 'borderColor' => "#4e73df", 'data' => $progressData, 'maxBarThickness' => 25, ]] ];


        // --- 3. Recent Activity ---
        $recentCheckins = Attendance::with('user')
                            ->orderBy('check_in_time', 'desc')
                            ->limit(5)
                            ->get();

        // <<< SỬA: Sử dụng relationship 'user' thay vì 'student' >>>
        $recentDiaries = Diary::with('user') // <- Đã đổi tên relationship
                            ->orderBy('created_at', 'desc')
                            ->limit(5)
                            ->get();

        $combinedActivities = collect();

        foreach ($recentCheckins as $item) {
            if ($item->user) {
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
             // <<< SỬA: Sử dụng $item->user thay vì $item->student >>>
             if ($item->user) { // <- Kiểm tra $item->user
                $combinedActivities->push([
                    'type' => 'diary',
                    'user_name' => $item->user->name, // <- Lấy tên từ $item->user
                    'time' => $item->created_at,
                    'text' => "Sinh viên <strong>{$item->user->name}</strong> đã tạo nhật ký.", // <- Lấy tên từ $item->user
                    'icon' => 'fas fa-book text-success',
                    'link' => route('admin.diaries.show', $item->id)
                ]);
             }
        }

        $recentActivities = $combinedActivities->sortByDesc('time')->take(10)->values();


        // --- 4. Alerts (Example) ---
        $alerts = collect();
        // a) Inactive Students
        $cutoffDate = Carbon::today()->subDays(3);
        $inactiveStudents = User::whereHas('role', fn($q)=>$q->where('name','SinhVien'))
                               ->whereDoesntHave('attendances', fn($q)=>$q->where('check_in_time','>',$cutoffDate))
                               ->limit(5)->get();

        foreach($inactiveStudents as $student) { // Biến $student ở đây là User model, không liên quan đến lỗi trước
             $alerts->push([
                 'type' => 'no_checkin',
                 'user_name' => $student->name,
                 'text' => "SV <strong>{$student->name}</strong> chưa điểm danh trong 3 ngày qua.",
                 'icon' => 'fas fa-exclamation-triangle text-warning',
                 'link' => route('admin.students.show', $student->id)
             ]);
        }

        // b) Overdue diaries (Example)
        /*
        $overdueDays = 7;
        $overdueDiaries = Diary::whereNull('reviewed_at')
                            ->where('created_at', '<', now()->subDays($overdueDays))
                            ->where('status', '!=', 'reviewed') // Hoặc logic status của bạn
                            // <<< SỬA NẾU CẦN: Sử dụng with('user') >>>
                            ->with('user') // <- Đổi thành user nếu dùng
                            ->limit(5)->get();

         foreach($overdueDiaries as $diary) {
             // <<< SỬA NẾU CẦN: Sử dụng $diary->user >>>
             if ($diary->user) { // <- Đổi thành user nếu dùng
                 $diaryIdentifier = $diary->diary_date ? Carbon::parse($diary->diary_date)->format('d/m/Y') : $diary->id;
                 $alerts->push([
                     'type' => 'overdue_diary',
                     // <<< SỬA NẾU CẦN: Sử dụng $diary->user->name >>>
                     'user_name' => $diary->user->name, // <- Đổi thành user nếu dùng
                     'text' => "Nhật ký ngày {$diaryIdentifier} của SV <strong>{$diary->user->name}</strong> có thể đã quá hạn.", // <- Đổi thành user nếu dùng
                     'icon' => 'fas fa-calendar-times text-danger',
                     'link' => route('admin.diaries.show', $diary->id)
                 ]);
             }
         }
        */

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