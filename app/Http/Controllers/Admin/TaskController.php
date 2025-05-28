<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User; // Cần để lấy danh sách sinh viên cho form và hiển thị tên sinh viên của progress
use Illuminate\Http\Request;
// SỬ DỤNG FORM REQUESTS ĐÃ TẠO
use App\Http\Requests\Admin\StoreTaskRequest;
use App\Http\Requests\Admin\UpdateTaskRequest;
use Illuminate\Support\Facades\Auth;

// KHÔNG cần import TaskProgress ở đây nếu không dùng trực tiếp trong controller này

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     * Hiển thị danh sách các công việc.
     */
    public function index(Request $request)
    {
        // Xây dựng query cơ bản, eager load thông tin sinh viên và người giao
        $query = Task::with(['intern:id,name,mssv', 'assigner:id,name'])
                     ->latest(); // Sắp xếp công việc mới nhất lên đầu

        // Lấy danh sách sinh viên (đã được duyệt) để dùng trong bộ lọc
        $interns = User::whereHas('role', function ($queryRole) {
                            $queryRole->where('name', 'SinhVien'); // Giả sử bảng roles có cột 'name' chứa tên vai trò
                       })
                       ->where('profile_status', 'approved')
                       ->orderBy('name')
                       ->get(['id', 'name', 'mssv']); // Chỉ lấy các cột cần thiết

        if ($request->filled('intern_id_filter')) {
            $query->where('intern_id', $request->intern_id_filter);
        }
        if ($request->filled('status_filter')) {
            $query->where('status', $request->status_filter);
        }
        if ($request->filled('priority_filter')) {
            $query->where('priority', $request->priority_filter);
        }
        if ($request->filled('due_date_filter')) {
            $query->whereDate('due_date', $request->due_date_filter);
        }

        $tasks = $query->paginate(10)->withQueryString(); // Phân trang và giữ lại query string

        // Truyền dữ liệu sang view
        return view('admin.tasks.index', [
            'tasks' => $tasks,
            'interns' => $interns, // Để dùng cho dropdown filter
            'statuses' => Task::statuses(), // Để dùng cho dropdown filter
            'priorities' => Task::priorities(), // Để dùng cho dropdown filter
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * Hiển thị form để tạo công việc mới.
     */
    public function create()
    {
        $task = new Task([ // Đặt giá trị mặc định cho form nếu cần
            'status' => Task::STATUS_TODO,
            'priority' => Task::PRIORITY_MEDIUM
        ]);
        $interns = User::whereHas('role', function ($queryRole) {
                            $queryRole->where('name', 'SinhVien');
                       })
                       ->where('profile_status', 'approved')
                       ->orderBy('name')
                       ->get(['id', 'name', 'mssv']);

        return view('admin.tasks.create', [
            'task' => $task,
            'interns' => $interns,
            'statuses' => Task::statuses(),
            'priorities' => Task::priorities(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * Lưu công việc mới được tạo vào database.
     * Sẽ sử dụng StoreTaskRequest để validate.
     */
    public function store(StoreTaskRequest $request) // SỬ DỤNG StoreTaskRequest
    {
        // Dữ liệu đã được validate bởi StoreTaskRequest
        $validatedData = $request->validated();

        $validatedData['assigner_id'] = Auth::id(); // Admin hiện tại là người giao

        Task::create($validatedData);

        return redirect()->route('admin.tasks.index')
                         ->with('success', __('Đã giao công việc thành công!'));
    }

    /**
     * Display the specified resource.
     * Hiển thị chi tiết một công việc.
     */
    public function show(Task $task)
    {
        // === CẬP NHẬT Ở ĐÂY ĐỂ EAGER LOAD progressEntries VÀ student TẠO RA NÓ ===
        $task->load([
            'intern:id,name,mssv', // Sinh viên được giao task (đã có)
            'assigner:id,name',    // Người giao task (đã có)
            // Thêm school và major nếu bạn muốn hiển thị chúng từ $task trong view show
            // 'school:id,name',
            // 'major:id,name',
            'progressEntries' => function ($query) {
                $query->with('student:id,name') // Lấy ID và tên của sinh viên đã tạo progress entry này
                      ->orderBy('submitted_at', 'desc') // Sắp xếp progress mới nhất lên đầu
                      ->orderBy('created_at', 'desc'); // Sắp xếp phụ nếu submitted_at giống nhau
            }
        ]);
        // ======================================================================

        // Lấy danh sách sinh viên (nếu bạn cần cho form edit task, không liên quan trực tiếp đến show progress)
        // Đoạn này có thể không cần thiết nếu bạn chỉ hiển thị thông tin và không có form edit ở trang show.
        // $students = User::role('SinhVien')->where('school_id', $task->school_id)->orderBy('name')->get();

        // return view('admin.tasks.show', compact('task', 'students'));
        // Bỏ 'students' nếu không dùng trong view admin.tasks.show
        return view('admin.tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified resource.
     * Hiển thị form để chỉnh sửa công việc.
     */
    public function edit(Task $task)
    {
        $interns = User::whereHas('role', function ($queryRole) {
                            $queryRole->where('name', 'SinhVien');
                       })
                       ->where('profile_status', 'approved')
                       ->orderBy('name')
                       ->get(['id', 'name', 'mssv']);

        return view('admin.tasks.edit', [
            'task' => $task,
            'interns' => $interns,
            'statuses' => Task::statuses(),
            'priorities' => Task::priorities(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     * Cập nhật công việc trong database.
     * Sẽ sử dụng UpdateTaskRequest để validate.
     */
    public function update(UpdateTaskRequest $request, Task $task) // SỬ DỤNG UpdateTaskRequest
    {
        // Dữ liệu đã được validate bởi UpdateTaskRequest
        $validatedData = $request->validated();

        $task->update($validatedData);

        return redirect()->route('admin.tasks.index')
                         ->with('success', __('Đã cập nhật công việc thành công!'));
    }

    /**
     * Remove the specified resource from storage.
     * Xóa công việc khỏi database.
     */
    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->route('admin.tasks.index')
                         ->with('success', __('Đã xóa công việc thành công!'));
    }
}