<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Requests\StoreScheduleRequest;   // Đảm bảo FormRequest này dùng overall_start_date, overall_end_date
use App\Http\Requests\UpdateScheduleRequest; // Đảm bảo FormRequest này dùng overall_start_date, overall_end_date
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
// use Carbon\Carbon; // Không thấy dùng trực tiếp ở đây, nhưng FormRequest có thể dùng

class ScheduleController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        // ===== PHẦN CẬP NHẬT - START: SỬ DỤNG TÊN CỘT ĐÚNG =====
        $query = Schedule::with(['student', 'creator'])
                         ->orderBy('overall_start_date', 'desc') // SỬA THÀNH overall_start_date
                         ->latest('created_at'); // Thêm sắp xếp phụ theo created_at để ổn định hơn nếu overall_start_date giống nhau
        // ===== PHẦN CẬP NHẬT - END =====

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        // Thêm các filter khác nếu cần, ví dụ theo user_id, title
        if ($request->filled('user_id_filter')) {
            $query->where('user_id', $request->input('user_id_filter'));
        }
        if ($request->filled('title_filter')) {
            $query->where('title', 'like', '%' . $request->input('title_filter') . '%');
        }


        $schedules = $query->paginate(15)->withQueryString();

        return view('admin.schedules.index', compact('schedules'));
    }

    public function create()
    {
        $studentRole = Role::where('name', 'SinhVien')->first();
        if (!$studentRole) {
             Log::error('Role "SinhVien" không tìm thấy trong hệ thống khi tạo Schedule.');
             return redirect()->back()->with('error', 'Lỗi cấu hình: Vai trò Sinh Viên không tồn tại.');
        }
        $students = User::where('role_id', $studentRole->id)
                        ->where('profile_status', 'approved')
                        ->orderBy('name')
                        ->get(['id', 'name', 'mssv', 'email']);

        return view('admin.schedules.create', compact('students'));
    }

    public function store(StoreScheduleRequest $request) // Đảm bảo StoreScheduleRequest dùng overall_start_date, overall_end_date
    {
        $validatedData = $request->validated();

        DB::beginTransaction();
        try {
            // ===== PHẦN CẬP NHẬT - START: SỬ DỤNG TÊN CỘT ĐÚNG =====
            $scheduleData = [
                'user_id' => $validatedData['user_id'],
                'title' => $validatedData['title'],
                'description' => $validatedData['description'] ?? null,
                'overall_start_date' => $validatedData['overall_start_date'], // SỬA THÀNH overall_start_date
                'overall_end_date' => $validatedData['overall_end_date'],     // SỬA THÀNH overall_end_date
                'created_by' => Auth::id(),
                'status' => Schedule::STATUS_SCHEDULED,
                'is_mandatory_attendance' => $validatedData['is_mandatory_attendance'] ?? false,
            ];
            // ===== PHẦN CẬP NHẬT - END =====

            $schedule = Schedule::create($scheduleData);

            $slotsData = $validatedData['slots'] ?? $request->input('slots');

            if (!empty($slotsData) && is_array($slotsData)) {
                foreach ($slotsData as $slotInput) {
                    if (isset($slotInput['day_of_week']) && isset($slotInput['start_time']) && isset($slotInput['end_time'])) {
                        $schedule->slots()->create([
                            'day_of_week' => $slotInput['day_of_week'],
                            'start_time' => $slotInput['start_time'],
                            'end_time' => $slotInput['end_time'],
                            'task_description' => $slotInput['task_description'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.schedules.index')
                             ->with('success', 'Tạo lịch thực tập thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi tạo lịch thực tập: ' . $e->getMessage() . ' --- Stack: ' . $e->getTraceAsString());
            return back()->withInput()->with('error', 'Có lỗi xảy ra trong quá trình tạo lịch. Vui lòng thử lại.');
        }
    }

    public function show(Schedule $schedule)
    {
        return redirect()->route('admin.schedules.edit', $schedule);
    }

    public function edit(Schedule $schedule)
    {
        $studentRole = Role::where('name', 'SinhVien')->first();
        if (!$studentRole) {
            Log::error('Role "SinhVien" không tìm thấy trong hệ thống khi edit Schedule.');
            return redirect()->back()->with('error', 'Lỗi cấu hình: Vai trò Sinh Viên không tồn tại.');
        }
       $students = User::where('role_id', $studentRole->id)
                       ->where('profile_status', 'approved')
                       ->orderBy('name')
                       ->get(['id', 'name', 'mssv', 'email']);

       $schedule->loadMissing(['student', 'creator', 'slots' => function ($query) {
           $query->orderBy('day_of_week')->orderBy('start_time');
       }]);

       return view('admin.schedules.edit', compact('schedule', 'students'));
    }

    public function update(UpdateScheduleRequest $request, Schedule $schedule) // Đảm bảo UpdateScheduleRequest dùng overall_start_date, overall_end_date
    {
        $validatedData = $request->validated();

        DB::beginTransaction();
        try {
            // ===== PHẦN CẬP NHẬT - START: SỬ DỤNG TÊN CỘT ĐÚNG =====
            $scheduleDataToUpdate = [
                'user_id' => $validatedData['user_id'],
                'title' => $validatedData['title'],
                'description' => $validatedData['description'] ?? null,
                'overall_start_date' => $validatedData['overall_start_date'], // SỬA THÀNH overall_start_date
                'overall_end_date' => $validatedData['overall_end_date'],     // SỬA THÀNH overall_end_date
                'status' => $validatedData['status'] ?? $schedule->status, // Cập nhật status nếu có
                'is_mandatory_attendance' => $validatedData['is_mandatory_attendance'] ?? $schedule->is_mandatory_attendance,
            ];
            // ===== PHẦN CẬP NHẬT - END =====
            $schedule->update($scheduleDataToUpdate);

            $schedule->slots()->delete();
            $slotsData = $validatedData['slots'] ?? $request->input('slots');

            if (!empty($slotsData) && is_array($slotsData)) {
                foreach ($slotsData as $slotInput) {
                    if (isset($slotInput['day_of_week']) && isset($slotInput['start_time']) && isset($slotInput['end_time'])) {
                        $schedule->slots()->create([
                            'day_of_week' => $slotInput['day_of_week'],
                            'start_time' => $slotInput['start_time'],
                            'end_time' => $slotInput['end_time'],
                            'task_description' => $slotInput['task_description'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.schedules.index')
                             ->with('success', 'Cập nhật lịch thực tập thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi cập nhật lịch thực tập: ' . $e->getMessage() . ' --- Stack: ' . $e->getTraceAsString());
            return back()->withInput()->with('error', 'Có lỗi xảy ra trong quá trình cập nhật. Vui lòng thử lại.');
        }
    }

    // ... (approveChange, rejectChange, destroy, pendingRequests giữ nguyên nếu không dùng start_time/end_time trực tiếp từ Schedule)
    // Nếu các hàm này có query đến Schedule và dùng start_time/end_time, cũng cần sửa.
    // Ví dụ, nếu pendingRequests có filter theo ngày, cần đảm bảo dùng đúng tên cột.

    public function approveChange(Schedule $schedule)
    {
        // $this->authorize('update', $schedule); // Nên dùng Policy

        if ($schedule->status !== Schedule::STATUS_PENDING_CHANGE) {
            return redirect()->back()->with('error', 'Lịch này không ở trạng thái chờ duyệt thay đổi.');
        }
        $schedule->status = Schedule::STATUS_SCHEDULED; // Hoặc STATUS_CHANGE_APPROVED
        $schedule->save();
        // ... (Notification logic)
        Log::info("Thông báo (Fake): Yêu cầu đổi lịch ID {$schedule->id} của SV {$schedule->student->name} đã được duyệt.");
        return redirect()->route('admin.schedules.pendingRequests')
                         ->with('success', 'Đã phê duyệt yêu cầu thay đổi lịch.');
    }

    public function rejectChange(Request $request, Schedule $schedule)
    {
        // $this->authorize('update', $schedule);

        if ($schedule->status !== Schedule::STATUS_PENDING_CHANGE) {
            return redirect()->back()->with('error', 'Lịch này không ở trạng thái chờ duyệt thay đổi.');
        }
        $schedule->status = Schedule::STATUS_SCHEDULED; // Hoặc STATUS_CHANGE_REJECTED
        $schedule->save();
        // ... (Notification logic)
        Log::info("Thông báo (Fake): Yêu cầu đổi lịch ID {$schedule->id} của SV {$schedule->student->name} đã bị từ chối.");
        return redirect()->route('admin.schedules.pendingRequests')
                         ->with('success', 'Đã từ chối yêu cầu thay đổi lịch.');
    }

     public function destroy(Schedule $schedule)
    {
        // $this->authorize('delete', $schedule);
        DB::beginTransaction();
        try {
            $schedule->slots()->delete();
            $schedule->delete();
            DB::commit();
            return redirect()->route('admin.schedules.index')
                             ->with('success', 'Xóa lịch thực tập thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi xóa lịch thực tập: ' . $e->getMessage());
            return redirect()->route('admin.schedules.index')
                             ->with('error', 'Có lỗi xảy ra khi xóa lịch thực tập.');
        }
    }

    public function pendingRequests()
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }

        $pendingSchedules = Schedule::where('status', Schedule::STATUS_PENDING_CHANGE)
                                    ->with(['student:id,name,mssv', 'creator:id,name'])
                                    ->orderBy('updated_at', 'asc') // Sắp xếp theo thời gian yêu cầu được cập nhật
                                    ->paginate(15);

        return view('admin.schedules.pending_requests', compact('pendingSchedules'));
    }
}