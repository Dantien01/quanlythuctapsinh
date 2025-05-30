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

// Controller cho API Schedule của Sinh viên
use App\Http\Controllers\Api\Student\StudentScheduleApiController;

// Controller cho API Message của Sinh viên
use App\Http\Controllers\Api\Student\MessageApiController as StudentMessageApiController;
// Controller cho API Message của Admin
use App\Http\Controllers\Api\Admin\MessageApiController as AdminMessageApiController;

// ============================================================================
// ===== THÊM IMPORT CHO NOTIFICATION VÀ USER PROFILE API CONTROLLERS =====
// ============================================================================
use App\Http\Controllers\Api\NotificationController; // Cho cả Admin và Student
use App\Http\Controllers\Api\UserProfileController;  // Cho cả Admin và Student
// ============================================================================

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;


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


    // ============================================================================
    // ===== API CHO USER PROFILE (ÁP DỤNG CHO NGƯỜI DÙNG HIỆN TẠI ĐÃ XÁC THỰC) =====
    // ============================================================================
    Route::get('/profile', [UserProfileController::class, 'show'])->name('api.profile.show');
    // Sử dụng POST cho update profile để dễ xử lý file upload (ảnh đại diện)
    // Laravel sẽ tự hiểu nếu bạn gửi _method=PUT trong form-data
    Route::post('/profile', [UserProfileController::class, 'update'])->name('api.profile.update');
    Route::put('/profile/password', [UserProfileController::class, 'changePassword'])->name('api.profile.password.update');
    // ============================================================================


    // ============================================================================
    // ===== API CHO NOTIFICATIONS (ÁP DỤNG CHO NGƯỜI DÙNG HIỆN TẠI ĐÃ XÁC THỰC) =====
    // ============================================================================
    Route::get('/notifications', [NotificationController::class, 'index'])->name('api.notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('api.notifications.unread-count');
    Route::put('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('api.notifications.mark_all_as_read');
    // {notification} sẽ là ID (UUID) của DatabaseNotification
    Route::put('/notifications/{notification}', [NotificationController::class, 'markAsRead'])->name('api.notifications.mark_as_read');
    // ============================================================================


    // =====================================================================
    // CÁC API RESOURCE HIỆN TẠI CỦA BẠN (GIỮ NGUYÊN HOẶC COMMENT OUT NẾU CHƯA DÙNG)
    // =====================================================================
    // Route::apiResource('interns', InternController::class);
    // Route::apiResource('projects', ProjectController::class);
    // Route::apiResource('tasks', TaskController::class);
    // Route::apiResource('evaluations', EvaluationController::class);
    // Route::apiResource('schedules', ScheduleController::class);

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

        // --- Schedule Routes ---
        Route::get('/schedules', [StudentScheduleApiController::class, 'index'])->name('schedules.index');
        Route::get('/schedules/events/{event_id}', [StudentScheduleApiController::class, 'showEvent'])->name('schedules.events.show');

        // --- Message Routes for Student ---
        Route::prefix('messages')->name('messages.')->group(function () {
            Route::get('/conversation-with-admin', [StudentMessageApiController::class, 'getConversationWithAdmin'])->name('conversationWithAdmin');
            Route::post('/send-to-admin', [StudentMessageApiController::class, 'sendMessageToAdmin'])->name('sendToAdmin');
            Route::post('/conversation-with-admin/mark-as-read', [StudentMessageApiController::class, 'markConversationAsRead'])->name('markConversationAsRead');
        });

        // ... các route khác của sinh viên có thể thêm ở đây ...
    });
    // =====================================================================


    // =====================================================================
    // API ENDPOINTS CHO ADMIN
    // =====================================================================
    Route::prefix('admin')->name('api.admin.')->middleware('role:Admin')->group(function () {
        // Route kiểm tra xác thực Admin đã có
        Route::get('/auth-check', function (Request $request) {
            $user = Auth::user();
            if ($user instanceof User) {
                Log::info('API /admin/auth-check: Authenticated admin user retrieved successfully.', [ /* ... */ ]);
                return response()->json([ 'success' => true, 'message' => 'Authenticated admin user retrieved.', 'data' => [ /* ... */ ] ]);
            } elseif ($user === null) {
                Log::warning('API /admin/auth-check: Auth::user() returned NULL.');
                return response()->json([ 'success' => false, 'message' => 'Authentication failed: User is null.' ], 401);
            } else {
                Log::error('API /admin/auth-check: Auth::user() returned an unexpected type.');
                return response()->json([ 'success' => false, 'message' => 'Authentication error: Unexpected user type retrieved.' ], 500);
            }
        })->name('auth.check');


        // --- Message Routes for Admin (Đã có) ---
        Route::prefix('messages')->name('messages.')->group(function () {
            Route::get('/conversations', [AdminMessageApiController::class, 'getAllConversations'])->name('conversations.index');
            Route::get('/conversations/student/{student}', [AdminMessageApiController::class, 'getConversationWithStudent'])->name('conversations.show');
            Route::post('/send-to-student/{student}', [AdminMessageApiController::class, 'sendMessageToStudent'])->name('sendToStudent');
            Route::post('/conversations/student/{student}/mark-as-read', [AdminMessageApiController::class, 'markConversationAsRead'])->name('markConversationAsRead');
        });

        // ... các route API khác của Admin có thể đặt ở đây ...
    });
    // =====================================================================

});