<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Models\TaskProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Notifications\StudentUpdatedTaskStatus;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Http\Requests\Student\StoreTaskProgressRequest;
use App\Http\Requests\Student\UpdateTaskProgressRequest;

class StudentTaskController extends Controller
{
    // ... (index, show, updateStatus, createProgress methods giữ nguyên như bạn cung cấp) ...
    public function index(Request $request)
    {
        $studentId = Auth::id();

        $query = Task::where('intern_id', $studentId)
                     ->with('assigner:id,name')
                     ->orderBy('due_date', 'asc')
                     ->latest();

        if ($request->filled('status_filter')) {
            $query->where('status', $request->status_filter);
        }
        if ($request->filled('priority_filter')) {
            $query->where('priority', $request->priority_filter);
        }
        if ($request->filled('due_date_filter')) {
            $query->whereDate('due_date', $request->due_date_filter);
        }
        if ($request->filled('search_keyword')) {
            $keyword = $request->search_keyword;
            $query->where(function($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        $tasks = $query->paginate(9);
        $tasks->withQueryString();

        return view('student.tasks.index', [
            'tasks' => $tasks,
            'statuses' => Task::statuses(),
            'priorities' => Task::priorities(),
        ]);
    }

    public function show(Task $task)
    {
        if ($task->intern_id !== Auth::id()) {
            abort(403, 'Unauthorized action. This task is not assigned to you.');
        }
        $task->load(['assigner:id,name', 'progressEntries' => function ($query) {
            $query->orderBy('submitted_at', 'desc')->orderBy('created_at', 'desc');
        }]);

        return view('student.tasks.show', [
            'task' => $task,
        ]);
    }

    public function updateStatus(Request $request, Task $task)
    {
        Log::info("StudentTaskController@updateStatus: Attempting to update status for Task ID {$task->id}. Requested new status: " . $request->input('status'));

        if ($task->intern_id !== Auth::id()) {
            Log::warning("StudentTaskController@updateStatus: Unauthorized attempt to update Task ID {$task->id} by User ID " . Auth::id());
            abort(403, 'Unauthorized action.');
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

        Log::info("StudentTaskController@updateStatus: Task ID {$task->id} - Old status: {$oldStatus}, New status: {$newStatus}");

        if ($task->status === Task::STATUS_COMPLETED || $task->status === Task::STATUS_OVERDUE) {
            Log::info("StudentTaskController@updateStatus: Task ID {$task->id} is already completed or overdue. Update denied.");
            return back()->with('error', __('Không thể cập nhật trạng thái của công việc đã hoàn thành hoặc đã quá hạn.'));
        }

        if ($task->status === Task::STATUS_TODO && $newStatus !== Task::STATUS_IN_PROGRESS) {
            Log::info("StudentTaskController@updateStatus: Task ID {$task->id} - Invalid transition from TODO to {$newStatus}. Update denied.");
            return back()->with('error', __('Bạn chỉ có thể chuyển công việc này sang "Đang làm".'));
        }

        if ($task->status === Task::STATUS_IN_PROGRESS && $newStatus !== Task::STATUS_COMPLETED) {
             if ($newStatus === Task::STATUS_IN_PROGRESS) {
                Log::info("StudentTaskController@updateStatus: Task ID {$task->id} - Status remains IN_PROGRESS. No actual change.");
                return redirect()->route('student.tasks.show', $task)
                                 ->with('info', __('Trạng thái công việc không có thay đổi.'));
             } else {
                Log::info("StudentTaskController@updateStatus: Task ID {$task->id} - Invalid transition from IN_PROGRESS to {$newStatus}. Update denied.");
                return back()->with('error', __('Bạn chỉ có thể chuyển công việc này sang "Hoàn thành".'));
             }
        }
        if ($oldStatus !== $newStatus) {
            Log::info("StudentTaskController@updateStatus: Task ID {$task->id} - Proceeding with status update.");
            $task->update(['status' => $newStatus]);

            if ($task->assigner_id) {
                Log::info("StudentTaskController@updateStatus: Task ID {$task->id} - Assigner ID found: {$task->assigner_id}");
                $assigner = User::find($task->assigner_id);
                if ($assigner) {
                    Log::info("StudentTaskController@updateStatus: Task ID {$task->id} - Assigner found: {$assigner->name} (ID: {$assigner->id})");
                    $student = Auth::user();
                    Log::info("StudentTaskController@updateStatus: Task ID {$task->id} - Student performing update: {$student->name} (ID: {$student->id})");
                    try {
                        $assigner->notifyNow(new StudentUpdatedTaskStatus($task, $student));
                        Log::info("StudentTaskController@updateStatus: Notification sent to assigner {$assigner->name} for task ID {$task->id}.");
                    } catch (\Exception $e) {
                        Log::error("StudentTaskController@updateStatus: Failed to send notification for task ID {$task->id}. Error: " . $e->getMessage(), ['exception' => $e]);
                    }
                } else {
                    Log::warning("StudentTaskController@updateStatus: Assigner with ID {$task->assigner_id} not found for task ID {$task->id}. Notification not sent.");
                }
            } else {
                Log::warning("StudentTaskController@updateStatus: No assigner_id for task ID {$task->id}. Notification not sent.");
            }
            return redirect()->route('student.tasks.show', $task)
                             ->with('success', __('Đã cập nhật trạng thái công việc thành công!'));
        } else {
            Log::info("StudentTaskController@updateStatus: Task ID {$task->id} - Status did not change (old: {$oldStatus}, new: {$newStatus}). No update or notification.");
            return redirect()->route('student.tasks.show', $task)
                             ->with('info', __('Trạng thái công việc không có thay đổi.'));
        }
    }

    public function createProgress(Task $task)
    {
        if ($task->intern_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        if ($task->status === Task::STATUS_COMPLETED || $task->status === Task::STATUS_OVERDUE) {
            return redirect()->route('student.tasks.show', $task)->with('warning', 'Không thể thêm tiến độ cho công việc đã hoàn thành hoặc quá hạn.');
        }

        $taskProgress = new TaskProgress(['task_id' => $task->id, 'submitted_at' => now()]);
        return view('student.tasks.progress.create', compact('task', 'taskProgress'));
    }

    /**
     * Lưu một cập nhật tiến độ mới vào database.
     */
    public function storeProgress(StoreTaskProgressRequest $request, Task $task)
    {
        // Giữ nguyên các kiểm tra ban đầu của bạn
        if ($task->intern_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        if ($task->status === Task::STATUS_COMPLETED || $task->status === Task::STATUS_OVERDUE) {
            return redirect()->route('student.tasks.show', $task)->with('warning', 'Không thể thêm tiến độ cho công việc đã hoàn thành hoặc quá hạn.');
        }

        // $validated = $request->validate([...]) đã được thay thế bằng $request->validated() vì dùng FormRequest
        $validated = $request->validated();

        // === CẬP NHẬT LOGIC CHO submitted_at THEO CÁCH 2 ===
        $submittedAtValue = null;
        if (isset($validated['submitted_at'])) {
            $submittedAtValue = Carbon::parse($validated['submitted_at'])->setTimeFrom(now());
        } else {
            $submittedAtValue = now();
        }
        // =====================================================

        $task->progressEntries()->create([
            'user_id' => Auth::id(),
            'notes' => $validated['notes'],
            'progress_percentage' => $validated['progress_percentage'] ?? null,
            'submitted_at' => $submittedAtValue,
        ]);

        // (Phần tùy chọn cập nhật status task chính giữ nguyên như bạn có)
        // if (isset($validated['progress_percentage'])) {
        //     if ($validated['progress_percentage'] == 100 && $task->status !== Task::STATUS_COMPLETED) {
        //         // $task->update(['status' => Task::STATUS_COMPLETED]);
        //     } elseif ($validated['progress_percentage'] > 0 && $validated['progress_percentage'] < 100 && $task->status === Task::STATUS_TODO) {
        //         // $task->update(['status' => Task::STATUS_IN_PROGRESS]);
        //     }
        // }

        return redirect()->route('student.tasks.show', $task)->with('success', 'Đã thêm cập nhật tiến độ thành công!');
    }

    /**
     * Hiển thị form để sửa một cập nhật tiến độ.
     */
    public function editProgress(Task $task, TaskProgress $taskProgress)
    {
        // Giữ nguyên kiểm tra ban đầu của bạn
        if ($taskProgress->task_id !== $task->id || $task->intern_id !== Auth::id() || $taskProgress->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('student.tasks.progress.edit', compact('task', 'taskProgress'));
    }

    /**
     * Cập nhật một bản ghi tiến độ trong database.
     */
    public function updateProgress(UpdateTaskProgressRequest $request, Task $task, TaskProgress $taskProgress)
    {
        // Giữ nguyên kiểm tra ban đầu của bạn
        if ($taskProgress->task_id !== $task->id || $task->intern_id !== Auth::id() || $taskProgress->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // $validated = $request->validate([...]) đã được thay thế bằng $request->validated()
        $validated = $request->validated();

        // === CẬP NHẬT LOGIC CHO submitted_at THEO CÁCH 2 ===
        $submittedAtForUpdate = $taskProgress->submitted_at;
        if (isset($validated['submitted_at'])) {
            $newSubmittedDate = Carbon::parse($validated['submitted_at']);
            if (is_null($taskProgress->submitted_at) || !$newSubmittedDate->isSameDay(Carbon::parse($taskProgress->submitted_at))) {
                $submittedAtForUpdate = $newSubmittedDate->setTimeFrom(now());
            }
            // Nếu ngày không đổi, $submittedAtForUpdate vẫn giữ giá trị cũ $taskProgress->submitted_at
        }
        // =====================================================

        $taskProgress->update([
            'notes' => $validated['notes'],
            'progress_percentage' => $validated['progress_percentage'] ?? $taskProgress->progress_percentage,
            'submitted_at' => $submittedAtForUpdate,
        ]);

        // (Phần tùy chọn cập nhật status task chính giữ nguyên như bạn có)

        return redirect()->route('student.tasks.show', $task)->with('success', 'Đã cập nhật tiến độ thành công!');
    }

    /**
     * Xóa một bản ghi tiến độ.
     */
    public function destroyProgress(Task $task, TaskProgress $taskProgress)
    {
        // Giữ nguyên kiểm tra ban đầu của bạn
        if ($taskProgress->task_id !== $task->id || $task->intern_id !== Auth::id() || $taskProgress->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $taskProgress->delete();

        return redirect()->route('student.tasks.show', $task)->with('success', 'Đã xóa cập nhật tiến độ.');
    }
}