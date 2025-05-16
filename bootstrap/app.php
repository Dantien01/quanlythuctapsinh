<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware; // Namespace cho Middleware configuration
use Illuminate\Contracts\Events\Dispatcher;         // <<< Namespace cho Dispatcher (nên có)

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',          // <<< THÊM DÒNG NÀY
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',  
    )
    ->withMiddleware(function (Middleware $middleware) {
        // --- Đăng ký Middleware Aliases ---
        $middleware->alias([
            // Aliases mặc định (giữ lại hoặc thêm nếu cần)
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            // 'guest' => \Illuminate\Auth\Middleware\RedirectIfAuthenticated::class, // Có thể thêm lại nếu cần

            // Aliases của bạn
            'role' => \App\Http\Middleware\CheckRole::class, // <<< Giữ lại 1 dòng này thôi
            'profile.approved' => \App\Http\Middleware\CheckProfileApproved::class,

            // Các alias khác nếu có...
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            // 'can' => \Illuminate\Auth\Middleware\Authorize::class,
            // 'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            // 'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
            // 'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        ]);

        // --- Đăng ký Middleware Groups hoặc Global (nếu cần) ---
        // $middleware->web(append: [
        //     // Thêm middleware vào group 'web'
        // ]);
        // $middleware->api(prepend: [
        //     // Thêm middleware vào đầu group 'api'
        // ]);
        // $middleware->use([
        //     // Thêm middleware global chạy cho mọi request
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Cấu hình xử lý Exceptions ở đây
    })
    // ->withEvents([ // <<< Đăng ký Event Listeners
    //     // Sự kiện Authenticated -> Chạy Listener kiểm tra trạng thái SV
    //     \Illuminate\Auth\Events\Authenticated::class => [
    //         \App\Listeners\CheckStudentProfileStatus::class,
    //     ],
    //     // Sự kiện Registered -> Gửi email xác thực (mặc định của Breeze)
    //     \Illuminate\Auth\Events\Registered::class => [
    //          \Illuminate\Auth\Listeners\SendEmailVerificationNotification::class,
    //      ],
    //     // Các event listener khác
    // ])
    ->create(); // Kết thúc cấu hình và tạo application instance