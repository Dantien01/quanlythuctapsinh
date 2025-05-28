<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task; // Sử dụng Model Task của bạn
use App\Models\User; // Import User để gửi thông báo
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Notifications\StudentUpdatedTaskStatus; // Import Notification
use Illuminate\Support\Facades\Log;
use App\Models\TaskProgress; // <<< ĐẢM BẢO IMPORT NÀY ĐÃ CÓ

class TaskApiController extends Controller
{
    /**
     * Trả về danh sách công việc của sinh viên đang đăng nhập.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $student = Auth::user();

        $tasks = Task::where('intern_id', $student->id)
                     ->with('assigner:id,name')
                     ->orderBy('due_date', 'asc')
                     ->latest()
                     ->paginate(10);

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

        if ($task->intern_id !== $student->id) {
            return response()->json(['message' => 'Không được phép truy cập công việc này.'], 403);
        }

        $task->load(['assigner:id,name', 'progressEntries' => function ($query) {
            $query->with('student:id,name')->orderBy('created_at', 'desc');
        }]);

        return response()->json($task);
    }

    /**
     * Cập nhật trạng thái của một công việc cụ thể cho sinh viên đang đăng nhập.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Task $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateTaskStatus(Request $request, Task $task)
    {
        $student = Auth::user();

        if ($task->intern_id !== $student->id) {
            return response()->json(['message' => 'Không được phép cập nhật trạng thái cho công việc này.'], 403);
        }

        $validated = $request->validate([
            'status' => [
                'required',
                'string',
                Rule::in([Task::STATUS_IN_PROGRESS, Task::STATUS_COMPLETED]),
            ],
        ]);

        $newStatus = $validated['status'];
        $oldStatus = $task->status;

        if ($oldStatus === Task::STATUS_COMPLETED || $oldStatus === Task::STATUS_OVERDUE) {
            return response()->json(['message' => 'Không thể cập nhật trạng thái của công việc đã hoàn thành hoặc đã quá hạn.'], 400);
        }

        if ($oldStatus === Task::STATUS_TODO && $newStatus !== Task::STATUS_IN_PROGRESS) {
            return response()->json(['message' => 'Bạn chỉ có thể chuyển công việc này sang "Đang làm".'], 400);
        }

        if ($oldStatus === Task::STATUS_IN_PROGRESS && $newStatus !== Task::STATUS_COMPLETED) {
             if ($newStatus === Task::STATUS_IN_PROGRESS) {
                return response()->json([
                    'message' => 'Trạng thái công việc không có thay đổi.',
                    'task' => $task->fresh()->load(['assigner:id,name', 'progressEntries' => function ($query) {
                                    $query->with('student:id,name')->orderBy('created_at', 'desc');
                                }])
                ], 200);
             }
            return response()->json(['message' => 'Bạn chỉ có thể chuyển công việc này sang "Hoàn thành".'], 400);
        }

        if ($oldStatus !== $newStatus) {
            $task->update(['status' => $newStatus]);
            Log::info("[API] Student ID: {$student->id} updated Task ID: {$task->id} status from '{$oldStatus}' to '{$newStatus}'.");

            if ($task->assigner_id) {
                $assigner = User::find($task->assigner_id);
                if ($assigner) {
                    try {
                        $assigner->notifyNow(new StudentUpdatedTaskStatus($task, $student));
                        Log::info("[API] Notification sent to assigner {$assigner->name} (ID: {$assigner->id}) for task ID {$task->id} status update by Student ID: {$student->id}.");
                    } catch (\Exception $e) {
                        Log::error("[API] Failed to send notification for task ID {$task->id} status update. Error: " . $e->getMessage());
                    }
                } else {
                    Log::warning("[API] Assigner with ID: {$task->assigner_id} not found for Task ID: {$task->id}. Notification not sent.");
                }
            } else {
                Log::info("[API] Task ID: {$task->id} has no assigner_id. Notification not sent.");
            }
            $updatedTask = $task->fresh()->load(['assigner:id,name', 'progressEntries' => function ($query) {
                $query->with('student:id,name')->orderBy('created_at', 'desc');
            }]);
            return response()->json([
                'message' => 'Đã cập nhật trạng thái công việc thành công!',
                'task' => $updatedTask
            ], 200);
        } else {
            return response()->json([
                'message' => 'Trạng thái công việc không có thay đổi.',
                'task' => $task->load(['assigner:id,name', 'progressEntries' => function ($query) {
                                $query->with('student:id,name')->orderBy('created_at', 'desc');
                            }])
            ], 200);
        }
    }

    /**
     * Lưu một cập nhật tiến độ mới cho một công việc.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeProgress(Request $request, Task $task)
    {
        $student = Auth::user();

        if ($task->intern_id !== $student->id) {
            return response()->json(['message' => 'Không được phép thêm tiến độ cho công việc này.'], 403);
        }

        if (in_array($task->status, [Task::STATUS_COMPLETED, Task::STATUS_OVERDUE])) {
            return response()->json(['message' => 'Không thể thêm tiến độ cho công việc đã hoàn thành hoặc quá hạn.'], 400);
        }

        $validatedData = $request->validate([
            'progress_percentage' => 'required|integer|min:0|max:100',
            'notes' => 'nullable|string|max:10000',
        ]);

        try {
            $progress = $task->progressEntries()->create([
                'user_id' => $student->id,
                'progress_percentage' => $validatedData['progress_percentage'],
                'notes' => $validatedData['notes'] ?? null,
            ]);

            Log::info("[API] Student ID: {$student->id} added progress for Task ID: {$task->id}. Progress ID: {$progress->id}");

            return response()->json([
                'message' => 'Đã thêm cập nhật tiến độ thành công!',
                'progress' => $progress->load('student:id,name')
            ], 201);

        } catch (\Exception $e) {
            Log::error("[API] Error adding progress for Task ID: {$task->id} by Student ID: {$student->id}. Error: " . $e->getMessage());
            return response()->json(['message' => 'Đã có lỗi xảy ra khi thêm tiến độ. Vui lòng thử lại.'], 500);
        }
    }

    /**
     * Cập nhật một bản ghi tiến độ công việc đã có.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Task  $task
     * @param  \App\Models\TaskProgress  $taskProgress
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProgress(Request $request, Task $task, TaskProgress $taskProgress)
    {
        $student = Auth::user();

        // 1. Kiểm tra quyền:
        if ($task->intern_id !== $student->id) {
            return response()->json(['message' => 'Không được phép truy cập công việc này.'], 403);
        }
        if ($taskProgress->task_id !== $task->id) {
            return response()->json(['message' => 'Bản ghi tiến độ không thuộc về công việc được chỉ định.'], 403);
        }
        if ($taskProgress->user_id !== $student->id) {
            return response()->json(['message' => 'Bạn không có quyền sửa bản ghi tiến độ này.'], 403);
        }

        // 2. Kiểm tra trạng thái công việc (tùy chọn)
        if (in_array($task->status, [Task::STATUS_COMPLETED, Task::STATUS_OVERDUE])) {
            return response()->json(['message' => 'Không thể sửa tiến độ cho công việc đã hoàn thành hoặc quá hạn.'], 400);
        }

        // 3. Validate dữ liệu đầu vào
        $validatedData = $request->validate([
            'progress_percentage' => 'required|integer|min:0|max:100',
            'notes' => 'nullable|string|max:10000',
        ]);

        // 4. Cập nhật bản ghi TaskProgress
        try {
            $taskProgress->update([
                'progress_percentage' => $validatedData['progress_percentage'],
                'notes' => $validatedData['notes'] ?? $taskProgress->notes,
            ]);

            Log::info("[API] Student ID: {$student->id} updated TaskProgress ID: {$taskProgress->id} for Task ID: {$task->id}.");

            return response()->json([
                'message' => 'Đã cập nhật tiến độ thành công!',
                'progress' => $taskProgress->fresh()->load('student:id,name')
            ], 200);

        } catch (\Exception $e) {
            Log::error("[API] Error updating TaskProgress ID: {$taskProgress->id}. Student ID: {$student->id}. Error: " . $e->getMessage());
            return response()->json(['message' => 'Đã có lỗi xảy ra khi cập nhật tiến độ. Vui lòng thử lại.'], 500);
        }
    }

    // === PHẦN THÊM MỚI CHO destroyProgress BẮT ĐẦU TỪ ĐÂY ===
    /**
     * Xóa một bản ghi tiến độ công việc đã có.
     *
     * @param  \App\Models\Task  $task
     * @param  \App\Models\TaskProgress  $taskProgress
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyProgress(Task $task, TaskProgress $taskProgress)
    {
        $student = Auth::user();

        // 1. Kiểm tra quyền (tương tự như updateProgress):
        if ($task->intern_id !== $student->id) {
            return response()->json(['message' => 'Không được phép truy cập công việc này.'], 403);
        }
        if ($taskProgress->task_id !== $task->id) {
            return response()->json(['message' => 'Bản ghi tiến độ không thuộc về công việc được chỉ định.'], 403);
        }
        if ($taskProgress->user_id !== $student->id) {
            return response()->json(['message' => 'Bạn không có quyền xóa bản ghi tiến độ này.'], 403);
        }

        // 2. Thực hiện xóa bản ghi TaskProgress
        try {
            $taskProgressId = $taskProgress->id; // Lưu lại ID trước khi xóa để ghi log
            $taskProgress->delete();

            Log::info("[API] Student ID: {$student->id} deleted TaskProgress ID: {$taskProgressId} for Task ID: {$task->id}.");

            return response()->json(['message' => 'Đã xóa cập nhật tiến độ thành công.'], 200);
            // Hoặc: return response()->noContent(); // Trả về 204 No Content

        } catch (\Exception $e) {
            Log::error("[API] Error deleting TaskProgress ID: {$taskProgress->id}. Student ID: {$student->id}. Error: " . $e->getMessage());
            return response()->json(['message' => 'Đã có lỗi xảy ra khi xóa tiến độ. Vui lòng thử lại.'], 500);
        }
    }
    // === PHẦN THÊM MỚI CHO destroyProgress KẾT THÚC TẠI ĐÂY ===
}