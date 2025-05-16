<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Carbon\Carbon; // Cần cho xử lý ngày tháng

class AttendanceController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request) {
        $this->authorize('viewAny', Attendance::class);
        $query = Attendance::with('user')->orderBy('attendance_date', 'desc')->orderBy('check_in_time', 'desc');
        if ($request->filled('student_id')) { $query->where('user_id', $request->input('student_id')); }
        if ($request->filled('attendance_date')) {
            try {
                $date = Carbon::parse($request->input('attendance_date'))->format('Y-m-d');
                $query->whereDate('attendance_date', $date);
            } catch (\Exception $e) {}
        }
        $attendances = $query->paginate(20);
        $students = User::whereHas('role', fn($q) => $q->where('name', 'SinhVien'))->orderBy('name')->get(['id', 'name', 'mssv']);
        return view('admin.attendances.index', compact('attendances', 'students'));
    }
    
    public function edit(Attendance $attendance) {
        $this->authorize('update', $attendance);
        $attendance->load('user');
        return view('admin.attendances.edit', compact('attendance'));
    }
    
    public function update(Request $request, Attendance $attendance) {
        $this->authorize('update', $attendance);
        $validated = $request->validate([
            'check_in_time' => 'nullable|date_format:Y-m-d\TH:i',
            'check_out_time' => 'nullable|date_format:Y-m-d\TH:i|after_or_equal:check_in_time',
            'status' => 'required|string|in:on_time,late,absent,excused', // Thêm excused nếu muốn
            'notes' => 'nullable|string',
        ],[ 'check_out_time.after_or_equal' => 'Giờ check-out phải sau hoặc bằng giờ check-in.' ]);
    
        $checkIn = $validated['check_in_time'] ? Carbon::parse($validated['check_in_time']) : null;
        $checkOut = $validated['check_out_time'] ? Carbon::parse($validated['check_out_time']) : null;
    
        $attendance->update([
            'check_in_time' => $checkIn,
            'check_out_time' => $checkOut,
            'status' => $validated['status'],
            'notes' => $validated['notes'],
            'attendance_date' => $checkIn ? $checkIn->format('Y-m-d') : $attendance->attendance_date,
        ]);
    
        // Lấy tham số trang hiện tại để quay về đúng trang
        $query_params = request()->query('page') ? ['page' => request()->query('page')] : [];
        // Thêm các filter khác nếu có vào đây để giữ lại khi redirect
        if (request()->query('student_id')) $query_params['student_id'] = request()->query('student_id');
        if (request()->query('attendance_date')) $query_params['attendance_date'] = request()->query('attendance_date');
    
    
        return redirect()->route('admin.attendances.index', $query_params)
                         ->with('success', 'Cập nhật điểm danh thành công.');
    }
}
