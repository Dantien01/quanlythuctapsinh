<?php

// 1. Import các lớp cần thiết
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
// Admin Controllers
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\MajorController;
use App\Http\Controllers\Admin\ScheduleController as AdminScheduleController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Admin\DiaryController as AdminDiaryController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\StudentReviewController as AdminStudentReviewController;
use App\Http\Controllers\Admin\MessageController as AdminMessageController;
use App\Http\Controllers\Admin\TaskController;
// Student Controllers
use App\Http\Controllers\Student\ScheduleController as StudentScheduleController;
use App\Http\Controllers\Student\DiaryController as StudentDiaryController;
use App\Http\Controllers\Student\AttendanceController as StudentAttendanceController;
use App\Http\Controllers\Student\MessageController as StudentMessageController;
use App\Http\Controllers\Student\StudentTaskController;
// Thêm import này nếu bạn chọn Cách 2 và có StudentDashboardController
// use App\Http\Controllers\Student\StudentDashboardController;
// Notification Controller
use App\Http\Controllers\NotificationController;
// Models (Có thể không cần trực tiếp)
use App\Models\Schedule;
use App\Models\User;
use App\Models\Diary;
// use App\Models\TaskProgress; // Sẽ cần nếu dùng Route Model Binding cho TaskProgress


/* ... Phần mô tả Routes ... */

// 2. Route Trang Chủ Công Khai
Route::get('/', function () {
    return redirect()->route('login'); // Giả sử route đăng nhập có tên là 'login'
})->name('home');

// 3. Route Dashboard chung
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// 4. Routes Profile/Settings chung
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 5. ==================================================
//   ===== KHU VỰC ADMIN =====
//   ==================================================
Route::middleware(['auth', 'role:Admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::redirect('/', '/admin/dashboard', 301)->name('index');
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('schools', SchoolController::class);
    Route::resource('majors', MajorController::class);
    Route::resource('users', UserController::class)->except(['create', 'store']);
    Route::put('/users/{user}/approve', [UserController::class, 'approve'])->name('users.approve');
    Route::put('/users/{user}/reject', [UserController::class, 'reject'])->name('users.reject');
    Route::get('/students/{user}', [UserController::class, 'show'])->name('students.show');

    Route::resource('schedules', AdminScheduleController::class);
    Route::get('/schedules/pending', [AdminScheduleController::class, 'pendingRequests'])->name('schedules.pending');
    Route::put('schedules/{schedule}/approve-change', [AdminScheduleController::class, 'approveChange'])->name('schedules.approveChange');
    Route::put('schedules/{schedule}/reject-change', [AdminScheduleController::class, 'rejectChange'])->name('schedules.rejectChange');

    Route::resource('attendances', AdminAttendanceController::class)->only(['index', 'edit', 'update']);
    Route::resource('reviews', AdminStudentReviewController::class)->except(['edit', 'update']);

    Route::get('diaries', [AdminDiaryController::class, 'index'])->name('diaries.index');
    Route::get('diaries/{diary}', [AdminDiaryController::class, 'show'])->name('diaries.show');
    Route::post('diaries/{diary}/comments', [AdminDiaryController::class, 'storeComment'])->name('diaries.comments.store');
    Route::post('diaries/{diary}/review', [AdminDiaryController::class, 'storeReview'])->name('diaries.review.store');

    Route::get('/messages', [AdminMessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{user}', [AdminMessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{user}', [AdminMessageController::class, 'reply'])->name('messages.reply');

    Route::resource('tasks', TaskController::class);

});
// ==================================================

// 6. ==================================================
//   ===== KHU VỰC SINH VIÊN =====
//   ==================================================
Route::middleware(['auth', 'role:SinhVien'])->prefix('student')->name('student.')->group(function () {

    // ĐỊNH NGHĨA ROUTE student.dashboard Ở ĐÂY
    // Cách 1: Trỏ đến trang danh sách công việc
    Route::get('/dashboard', [StudentTaskController::class, 'index'])->name('dashboard');
    // Cách 2: (Nếu bạn có StudentDashboardController riêng, hãy bỏ comment dòng dưới và comment dòng trên)
    // Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');


    // Nhóm các route yêu cầu hồ sơ đã được duyệt
    Route::middleware(['profile.approved'])->group(function() {
        Route::get('/schedule', [StudentScheduleController::class, 'index'])->name('schedule.index');
        Route::post('/schedule/{schedule}/request-change', [StudentScheduleController::class, 'requestChange'])->name('schedule.requestChange');

        Route::resource('diaries', StudentDiaryController::class);
        Route::post('diaries/{diary}/comments', [StudentDiaryController::class, 'storeComment'])->name('diaries.comments.store');

        Route::post('/attendance/check-in', [StudentAttendanceController::class, 'checkIn'])->name('attendance.checkin');
        Route::post('/attendance/check-out', [StudentAttendanceController::class, 'checkOut'])->name('attendance.checkout');

        Route::get('/messages', [StudentMessageController::class, 'index'])->name('messages.index');
        Route::get('/messages/create', [StudentMessageController::class, 'create'])->name('messages.create');
        Route::post('/messages', [StudentMessageController::class, 'store'])->name('messages.store');

        // === ROUTES CHO QUẢN LÝ CÔNG VIỆC CỦA SINH VIÊN ===
        Route::get('tasks', [StudentTaskController::class, 'index'])->name('tasks.index');
        Route::get('tasks/{task}', [StudentTaskController::class, 'show'])->name('tasks.show');
        Route::post('tasks/{task}/update-status', [StudentTaskController::class, 'updateStatus'])->name('tasks.updateStatus'); // Đã sửa tên route

        // Routes cho Sinh viên thêm/sửa ghi chú tiến độ (Task Progress)
        Route::get('tasks/{task}/progress/create', [StudentTaskController::class, 'createProgress'])->name('tasks.progress.create');
        Route::post('tasks/{task}/progress', [StudentTaskController::class, 'storeProgress'])->name('tasks.progress.store');
        Route::get('tasks/{task}/progress/{taskProgress}/edit', [StudentTaskController::class, 'editProgress'])->name('tasks.progress.edit');
        Route::put('tasks/{task}/progress/{taskProgress}', [StudentTaskController::class, 'updateProgress'])->name('tasks.progress.update');
        Route::delete('tasks/{task}/progress/{taskProgress}', [StudentTaskController::class, 'destroyProgress'])->name('tasks.progress.destroy');
        // ======================================================

     });
});
// ==================================================

// 7. ==================================================
//   ===== ROUTE THÔNG BÁO CHUNG =====
//   ==================================================
Route::middleware('auth')->group(function () {
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllAsRead');});
// ==================================================


// 8. Route xác thực
require __DIR__.'/auth.php';