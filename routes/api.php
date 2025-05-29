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

// === IMPORT CONTROLLERS CHO MESSAGE API ===
use App\Http\Controllers\Api\Student\MessageApiController as StudentMessageApiController;
use App\Http\Controllers\Api\Admin\MessageApiController as AdminMessageApiController; // << Đã có import này
// ============================================

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // Thêm import này
use App\Models\User; // Thêm import này (cho closure của route test)
use Illuminate\Support\Facades\Log; // Thêm import này (cho closure của route test)


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
    Route::prefix('admin')->name('api.admin.')->middleware('role:Admin')->group(function () { // Đảm bảo chỉ Admin mới truy cập được
        // ... các route API khác của Admin có thể đặt ở đây ...

        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        // THÊM ROUTE KIỂM TRA XÁC THỰC ADMIN TẠI ĐÂY
        Route::get('/auth-check', function (Request $request) {
            $user = Auth::user(); // Hoặc $request->user();

            if ($user instanceof User) {
                // Kiểm tra vai trò một lần nữa (tùy chọn nếu middleware 'role:Admin' đã xử lý)
                // if (!$user->hasRole('Admin')) { // Giả sử bạn có phương thức hasRole()
                //     Log::warning('API /admin/auth-check: User is authenticated but not an Admin via direct role check.', ['user_id' => $user->id]);
                //     return response()->json(['success' => false, 'message' => 'Forbidden: User is authenticated but not an Admin.'], 403);
                // }

                Log::info('API /admin/auth-check: Authenticated admin user retrieved successfully.', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'user_roles' => $user->relationLoaded('roles') ? $user->roles->pluck('name')->toArray() : 'Roles not loaded or no roles relationship',
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'Authenticated admin user retrieved.',
                    'data' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'roles' => $user->relationLoaded('roles') ? $user->roles->pluck('name')->toArray() : [],
                        // Thêm các thông tin khác của user bạn muốn kiểm tra
                    ]
                ]);
            } elseif ($user === null) {
                Log::warning('API /admin/auth-check: Auth::user() returned NULL. Authentication failed or no user associated with token.');
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication failed: User is null (token might be invalid or no user attached).'
                ], 401);
            } else {
                Log::error('API /admin/auth-check: Auth::user() returned an unexpected type.', [
                    'type_returned' => gettype($user)
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication error: Unexpected user type retrieved.',
                    'type_returned' => gettype($user)
                ], 500);
            }
        })->name('auth.check'); // Đặt tên cho route nếu muốn
        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<


        // --- Message Routes for Admin ---
        Route::prefix('messages')->name('messages.')->group(function () {
            Route::get('/conversations', [AdminMessageApiController::class, 'getAllConversations'])->name('conversations.index');
            // {student} sẽ tự động inject User model nếu Route Model Binding được bật và ID hợp lệ
            // và User model có key là 'id' (mặc định).
            // Controller method cần có type hint: User $student
            Route::get('/conversations/student/{student}', [AdminMessageApiController::class, 'getConversationWithStudent'])->name('conversations.show');
            Route::post('/send-to-student/{student}', [AdminMessageApiController::class, 'sendMessageToStudent'])->name('sendToStudent');
            Route::post('/conversations/student/{student}/mark-as-read', [AdminMessageApiController::class, 'markConversationAsRead'])->name('markConversationAsRead');
        });
    });
    // =====================================================================

});