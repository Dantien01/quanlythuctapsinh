<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task; // Sử dụng Model Task của bạn
use Illuminate\Support\Facades\Auth;

class TaskApiController extends Controller
{
    /**
     * Trả về danh sách công việc của sinh viên đang đăng nhập.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $student = Auth::user(); // Lấy sinh viên đang đăng nhập (đã được xác thực bởi auth:sanctum)

        $tasks = Task::where('intern_id', $student->id)
                     ->with('assigner:id,name') // Lấy thông tin người giao (nếu cần)
                     // Lọc và sắp xếp tương tự như StudentTaskController@index cho web nếu muốn
                     ->orderBy('due_date', 'asc')
                     ->latest()
                     ->paginate(10); // Sử dụng phân trang

        // (Tùy chọn) Bạn có thể sử dụng API Resources để định dạng output JSON
        // return TaskResource::collection($tasks);
        // Hoặc trả về trực tiếp
        return response()->json($tasks);
    }

    /**
     * Trả về chi tiết một công việc cụ thể của sinh viên.
     * @param \App\Models\Task $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Task $task)
    {
        $student = Auth::user();

        // Kiểm tra xem công việc này có thuộc về sinh viên đang đăng nhập không
        if ($task->intern_id !== $student->id) {
            return response()->json(['message' => 'Không được phép truy cập công việc này.'], 403);
        }

        // Eager load các thông tin cần thiết cho mobile app
        $task->load(['assigner:id,name', 'progressEntries' => function ($query) {
            $query->with('student:id,name')->orderBy('submitted_at', 'desc');
        }]);

        // (Tùy chọn) Sử dụng API Resource
        // return new TaskResource($task);
        // Hoặc trả về trực tiếp
        return response()->json($task);
    }

    // Các phương thức khác như storeProgress, updateStatus cho API sẽ cần được tạo nếu mobile app có các chức năng đó
    // Ví dụ:
    // public function storeProgress(Request $request, Task $task) { ... }
    // public function updateTaskStatus(Request $request, Task $task) { ... }
}