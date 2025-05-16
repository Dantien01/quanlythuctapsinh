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
use App\Http\Controllers\Api\Student\TaskApiController; // <<< ĐÃ CÓ IMPORT NÀY

// (Tùy chọn) Controllers cho các API cụ thể khác của Sinh viên
// use App\Http\Controllers\Api\Student\StudentScheduleApiController;
// use App\Http\Controllers\Api\Student\StudentAttendanceApiController;

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
    Route::apiResource('tasks', TaskController::class); // Nếu đây là TaskController của Admin, cần phân quyền
    Route::apiResource('evaluations', EvaluationController::class);
    Route::apiResource('schedules', ScheduleController::class); // Nếu đây là ScheduleController của Admin, cần phân quyền

    // =====================================================================
    // API ENDPOINTS CHO MOBILE APP CỦA SINH VIÊN
    // =====================================================================
    Route::prefix('student')->name('api.student.')->group(function () { // <<< THÊM GROUP NÀY
        // Routes cho Task của Sinh viên
        Route::get('/tasks', [TaskApiController::class, 'index'])->name('tasks.index');
        Route::get('/tasks/{task}', [TaskApiController::class, 'show'])->name('tasks.show');
        // Ví dụ: Thêm route để sinh viên cập nhật tiến độ công việc qua API
        // Route::post('/tasks/{task}/progress', [TaskApiController::class, 'storeProgress'])->name('tasks.progress.store');
        // Ví dụ: Thêm route để sinh viên cập nhật trạng thái công việc qua API
        // Route::post('/tasks/{task}/update-status', [TaskApiController::class, 'updateTaskStatus'])->name('tasks.updateStatus');

        // Ví dụ: Routes cho Schedule của Sinh viên (nếu bạn tạo StudentScheduleApiController)
        // Route::get('/schedules', [StudentScheduleApiController::class, 'index'])->name('schedules.index');

        // Ví dụ: Routes cho Attendance của Sinh viên (nếu bạn tạo StudentAttendanceApiController)
        // Route::post('/attendance/checkin', [StudentAttendanceApiController::class, 'checkIn'])->name('attendance.checkin');
        // Route::post('/attendance/checkout', [StudentAttendanceApiController::class, 'checkOut'])->name('attendance.checkout');

        // ... các route khác của sinh viên có thể thêm ở đây ...
    });
    // =====================================================================

});

// Route mặc định của Sanctum để lấy thông tin user (đã bị comment out, bạn đang dùng AuthController@user)
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
// return $request->user();
// });