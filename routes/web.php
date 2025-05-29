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
use App\Http\Controllers\Admin\MessageController as AdminMessageController; // Đã import
use App\Http\Controllers\Admin\TaskController;
// Student Controllers
use App\Http\Controllers\Student\ScheduleController as StudentScheduleController;
use App\Http\Controllers\Student\DiaryController as StudentDiaryController;
use App\Http\Controllers\Student\AttendanceController as StudentAttendanceController;
use App\Http\Controllers\Student\MessageController as StudentMessageController; // Đã import
use App\Http\Controllers\Student\StudentTaskController;
// Notification Controller
use App\Http\Controllers\NotificationController;


/* ... Phần mô tả Routes ... */

// 2. Route Trang Chủ Công Khai
Route::get('/', function () {
    return redirect()->route('login');
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
    Route::get('/students/{user}', [UserController::class, 'show'])->name('students.show'); // Lưu ý: UserController@show cho student details

    Route::resource('schedules', AdminScheduleController::class);
    Route::get('/schedules/pending', [AdminScheduleController::class, 'pendingRequests'])->name('schedules.pendingRequests');
    Route::put('schedules/{schedule}/approve-change', [AdminScheduleController::class, 'approveChange'])->name('schedules.approveChange');
    Route::put('schedules/{schedule}/reject-change', [AdminScheduleController::class, 'rejectChange'])->name('schedules.rejectChange');

    Route::resource('attendances', AdminAttendanceController::class)->only(['index', 'edit', 'update']);
    Route::resource('reviews', AdminStudentReviewController::class)->except(['edit', 'update']);

    Route::get('diaries', [AdminDiaryController::class, 'index'])->name('diaries.index');
    Route::get('diaries/{diary}', [AdminDiaryController::class, 'show'])->name('diaries.show');
    Route::post('diaries/{diary}/comments', [AdminDiaryController::class, 'storeComment'])->name('diaries.comments.store');
    Route::post('diaries/{diary}/review', [AdminDiaryController::class, 'storeReview'])->name('diaries.review.store');

    // --- ROUTES CHO TIN NHẮN CỦA ADMIN ---
    Route::get('/messages', [AdminMessageController::class, 'index'])->name('messages.index');
    // {student} sẽ tự động inject User model nếu Route Model Binding được bật và ID hợp lệ
    // Đảm bảo tên tham số trong route khớp với tên biến trong phương thức controller (ví dụ: {student} và User $student)
    Route::get('/messages/{student}', [AdminMessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{student}/reply', [AdminMessageController::class, 'reply'])->name('messages.reply'); // Đổi tên từ {user} thành {student} cho rõ nghĩa

    Route::resource('tasks', TaskController::class);
});
// ==================================================

// 6. ==================================================
//   ===== KHU VỰC SINH VIÊN =====
//   ==================================================
Route::middleware(['auth', 'role:SinhVien'])->prefix('student')->name('student.')->group(function () {

    Route::get('/dashboard', [StudentTaskController::class, 'index'])->name('dashboard'); // Giả sử dashboard của SV là trang task

    Route::middleware(['profile.approved'])->group(function() {
        Route::get('/schedule', [StudentScheduleController::class, 'index'])->name('schedule.index');
        Route::post('/schedule/{schedule}/request-change', [StudentScheduleController::class, 'requestChange'])->name('schedule.requestChange');
        Route::get('/schedules/{schedule}/detail', [StudentScheduleController::class, 'getScheduleDetail'])->name('schedules.detail');

        Route::resource('diaries', StudentDiaryController::class);
        Route::post('diaries/{diary}/comments', [StudentDiaryController::class, 'storeComment'])->name('diaries.comments.store');

        Route::post('/attendance/check-in', [StudentAttendanceController::class, 'checkIn'])->name('attendance.checkin');
        Route::post('/attendance/check-out', [StudentAttendanceController::class, 'checkOut'])->name('attendance.checkout');

        // --- ROUTES CHO TIN NHẮN CỦA SINH VIÊN ---
        Route::get('/messages', [StudentMessageController::class, 'index'])->name('messages.index');
        // Route create có thể không cần thiết nếu form gửi tin nhắn nằm ngay trên trang index
        Route::get('/messages/create', [StudentMessageController::class, 'create'])->name('messages.create');
        Route::post('/messages', [StudentMessageController::class, 'store'])->name('messages.store');

        Route::get('tasks', [StudentTaskController::class, 'index'])->name('tasks.index');
        Route::get('tasks/{task}', [StudentTaskController::class, 'show'])->name('tasks.show');
        Route::post('tasks/{task}/update-status', [StudentTaskController::class, 'updateStatus'])->name('tasks.updateStatus');

        Route::get('tasks/{task}/progress/create', [StudentTaskController::class, 'createProgress'])->name('tasks.progress.create');
        Route::post('tasks/{task}/progress', [StudentTaskController::class, 'storeProgress'])->name('tasks.progress.store');
        Route::get('tasks/{task}/progress/{taskProgress}/edit', [StudentTaskController::class, 'editProgress'])->name('tasks.progress.edit');
        Route::put('tasks/{task}/progress/{taskProgress}', [StudentTaskController::class, 'updateProgress'])->name('tasks.progress.update');
        Route::delete('tasks/{task}/progress/{taskProgress}', [StudentTaskController::class, 'destroyProgress'])->name('tasks.progress.destroy');
     });
});
// ==================================================

// 7. ==================================================
//   ===== ROUTE THÔNG BÁO CHUNG =====
//   ==================================================
Route::middleware('auth')->group(function () {
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllAsRead');
});
// ==================================================

// 8. Route xác thực
require __DIR__.'/auth.php';