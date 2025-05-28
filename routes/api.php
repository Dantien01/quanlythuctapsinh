<?php

// Các use statements cho các controller của bạn
use App\Http\Controllers\InternController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController; // Giả sử đây là Admin TaskController
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\ScheduleController; // Giả sử đây là Admin/Chung ScheduleController

// Controller cho API Auth
use App\Http\Controllers\Api\AuthController;

// Controller cho API Task của Sinh viên
use App\Http\Controllers\Api\Student\TaskApiController;

// Controller cho API Attendance của Sinh viên
use App\Http\Controllers\Api\Student\StudentAttendanceApiController;

// === THÊM IMPORT CHO STUDENT SCHEDULE API CONTROLLER ===
use App\Http\Controllers\Api\Student\StudentScheduleApiController;
// ========================================================


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Route công khai cho Đăng nhập (KHÔNG cần token)
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

// Các route yêu cầu xác thực bằng Sanctum token
Route::middleware('auth:sanctum')->group(function () {
    // Route Đăng xuất
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');

    // Route lấy thông tin người dùng hiện tại đang đăng nhập
    Route::get('/user', [AuthController::class, 'user'])->name('api.user');

    // =====================================================================
    // CÁC API RESOURCE HIỆN TẠI CỦA BẠN (GIỮ NGUYÊN)
    // =====================================================================
    Route::apiResource('interns', InternController::class);
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('evaluations', EvaluationController::class);
    Route::apiResource('schedules', ScheduleController::class);

    // =====================================================================
    // API ENDPOINTS CHO MOBILE APP CỦA SINH VIÊN
    // =====================================================================
    Route::prefix('student')->name('api.student.')->group(function () {
        // --- Task Routes ---
        Route::get('/tasks', [TaskApiController::class, 'index'])->name('tasks.index');
        Route::get('/tasks/{task}', [TaskApiController::class, 'show'])->name('tasks.show');
        Route::post('/tasks/{task}/update-status', [TaskApiController::class, 'updateTaskStatus'])->name('tasks.updateStatus');
        Route::post('/tasks/{task}/progress', [TaskApiController::class, 'storeProgress'])->name('tasks.progress.store');
        Route::put('/tasks/{task}/progress/{taskProgress}', [TaskApiController::class, 'updateProgress'])->name('tasks.progress.update');
        Route::delete('/tasks/{task}/progress/{taskProgress}', [TaskApiController::class, 'destroyProgress'])->name('tasks.progress.destroy');

        // --- Attendance Routes ---
        Route::post('/attendance/clock-in', [StudentAttendanceApiController::class, 'clockIn'])->name('attendance.clockin');
        Route::post('/attendance/clock-out', [StudentAttendanceApiController::class, 'clockOut'])->name('attendance.clockout');
        Route::get('/attendance/history', [StudentAttendanceApiController::class, 'getAttendanceHistory'])->name('attendance.history');
        Route::get('/attendance/status', [StudentAttendanceApiController::class, 'getCurrentAttendanceStatus'])->name('attendance.status');

        // === CẬP NHẬT: ROUTES CHO SCHEDULE CỦA SINH VIÊN ===
        Route::get('/schedules', [StudentScheduleApiController::class, 'index'])->name('schedules.index');
        Route::get('/schedules/events/{event_id}', [StudentScheduleApiController::class, 'showEvent'])->name('schedules.events.show');
        // ====================================================


        // ... các route khác của sinh viên có thể thêm ở đây ...
    });
    // =====================================================================

});