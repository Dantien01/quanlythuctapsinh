<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate; // Import Gate nếu cần dùng Gate
use App\Models\User; // Import Model User
use App\Policies\UserPolicy; // Import UserPolicy
use App\Models\School; // <<< Import School
use App\Policies\SchoolPolicy; // <<< Import SchoolPolicy
use App\Models\Major; // <<< Import Major
use App\Policies\MajorPolicy; // <<< Import MajorPolicy
use App\Models\Schedule; 
use App\Policies\SchedulePolicy; // 

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Đăng ký UserPolicy cho Model User ở đây
        User::class => UserPolicy::class,
        \App\Models\User::class => \App\Policies\UserPolicy::class, // <<< Thêm dòng này
        // Thêm các policy khác vào đây nếu cần
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        \App\Models\Diary::class => \App\Policies\DiaryPolicy::class, // Thêm dòng này
        \App\Models\Attendance::class => \App\Policies\AttendancePolicy::class,
        \App\Models\StudentReview::class => \App\Policies\StudentReviewPolicy::class, // <<< THÊM DÒNG NÀY
        \App\Models\Schedule::class => \App\Policies\SchedulePolicy::class,
        // Đăng ký đã có
        User::class => UserPolicy::class,

        // ===> THÊM ĐĂNG KÝ MỚI VÀO ĐÂY <===
        School::class => SchoolPolicy::class,
        Major::class => MajorPolicy::class,
        Schedule::class => SchedulePolicy::class,
        // ====================================
        // 'App\Models\Post' => 'App\Policies\PostPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Bạn có thể định nghĩa Gates ở đây nếu cần
        // Gate::define('edit-settings', function (User $user) {
        //     return $user->isAdmin;
        // });
    }
}