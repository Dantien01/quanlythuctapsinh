<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\User; // Cần để lấy danh sách SV
use App\Models\Role;  // Cần để lọc SV
use Illuminate\Http\Request;
use App\Http\Requests\StoreScheduleRequest; // Sẽ tạo ở bước sau
use App\Http\Requests\UpdateScheduleRequest; // Sẽ tạo ở bước sau
use Illuminate\Support\Facades\Auth; // Để lấy admin id
use Illuminate\Support\Facades\Notification; // Nếu dùng Notification
use App\Notifications\ScheduleChangeApproved; // Tạo notification này sau
use App\Notifications\ScheduleChangeRejected; // Tạo notification này sau
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // Để dùng authorize

class ScheduleController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Schedule::with(['student', 'creator'])->latest(); // Giữ nguyên, đã đúng

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $schedules = $query->paginate(15)->withQueryString();

        return view('admin.schedules.index', compact('schedules'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $studentRole = Role::where('name', 'SinhVien')->first();
        if (!$studentRole) {
             abort(500, 'Student role not found.');
        }
        $students = User::where('role_id', $studentRole->id)
                        ->where('profile_status', 'approved')
                        ->orderBy('name')
                        ->get();

        return view('admin.schedules.create', compact('students'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreScheduleRequest $request)
    {
        $validatedData = $request->validated();
        $validatedData['created_by'] = Auth::id();
        $validatedData['status'] = 'scheduled';

        Schedule::create($validatedData);

        return redirect()->route('admin.schedules.index')
                         ->with('success', 'Tạo lịch thực tập thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Schedule $schedule)
    {
        return redirect()->route('admin.schedules.edit', $schedule);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Schedule $schedule)
    {
        $studentRole = Role::where('name', 'SinhVien')->first();
        if (!$studentRole) {
            abort(500, 'Student role not found.');
        }
       $students = User::where('role_id', $studentRole->id)
                       ->where('profile_status', 'approved')
                       ->orderBy('name')
                       ->get();

       $schedule->loadMissing(['student', 'creator']); // Giữ nguyên, đã đúng

       return view('admin.schedules.edit', compact('schedule', 'students'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateScheduleRequest $request, Schedule $schedule)
    {
        $validatedData = $request->validated();
        $schedule->update($validatedData);

        return redirect()->route('admin.schedules.index')
                         ->with('success', 'Cập nhật lịch thực tập thành công.');
    }

    /**
     * Approve the schedule change request.
     */
    public function approveChange(Schedule $schedule)
    {
        $this->authorize('update', $schedule);

        if ($schedule->status !== 'pending_change') {
            return redirect()->back()->with('error', 'Lịch này không ở trạng thái chờ duyệt thay đổi.');
        }

        $schedule->status = 'updated'; // Hoặc 'scheduled'
        $schedule->save();

        // === THAY ĐỔI Ở ĐÂY ===
        if ($schedule->student) { // Sử dụng relationship 'student'
             // Notification::send($schedule->student, new ScheduleChangeApproved($schedule));
             \Log::info("Thông báo (Fake): Yêu cầu đổi lịch ID {$schedule->id} của SV {$schedule->student->name} đã được duyệt."); // Log tạm
        }
        // ======================

        return redirect()->route('admin.schedules.pending') // Chuyển hướng về trang pending sau khi xử lý
                         ->with('success', 'Đã phê duyệt yêu cầu thay đổi lịch.');
    }

    /**
     * Reject the schedule change request.
     */
    public function rejectChange(Request $request, Schedule $schedule)
    {
        $this->authorize('update', $schedule);

        if ($schedule->status !== 'pending_change') {
            return redirect()->back()->with('error', 'Lịch này không ở trạng thái chờ duyệt thay đổi.');
        }

        // $request->validate(['rejection_reason' => 'nullable|string|max:255']);

        $schedule->status = 'scheduled';
        // $schedule->change_rejection_reason = $request->input('rejection_reason');
        $schedule->save();

        // === THAY ĐỔI Ở ĐÂY ===
        if ($schedule->student) { // Sử dụng relationship 'student'
             // Notification::send($schedule->student, new ScheduleChangeRejected($schedule, $request->input('rejection_reason')));
             \Log::info("Thông báo (Fake): Yêu cầu đổi lịch ID {$schedule->id} của SV {$schedule->student->name} đã bị từ chối."); // Log tạm
        }
        // ======================

        return redirect()->route('admin.schedules.pending') // Chuyển hướng về trang pending sau khi xử lý
                         ->with('success', 'Đã từ chối yêu cầu thay đổi lịch.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Schedule $schedule)
    {
        $this->authorize('delete', $schedule);
        $schedule->delete();
        return redirect()->route('admin.schedules.index')
                         ->with('success', 'Xóa lịch thực tập thành công.');
    }

    /**
     * Hiển thị danh sách các yêu cầu thay đổi lịch đang chờ duyệt.
     */
    public function pendingRequests()
    {
        $this->authorize('viewAny', Schedule::class); // Nên dùng policy chuẩn của resource

        $pendingSchedules = Schedule::where('status', 'pending_change')
                                    // === THAY ĐỔI Ở ĐÂY ===
                                    ->with('student') // Sử dụng relationship 'student'
                                    // ======================
                                    ->orderBy('updated_at', 'asc')
                                    ->paginate(15);

        // Đảm bảo tên view đúng là 'admin.schedules.pending_requests' như bạn đã cung cấp
        return view('admin.schedules.pending_requests', compact('pendingSchedules'));
    }
}