<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider; // Dùng để lấy đường dẫn trang HOME
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Facade Auth
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * Chuyển hướng người dùng đã đăng nhập khỏi các trang như login, register.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string[]  ...$guards Các guard cần kiểm tra (mặc định là 'web')
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        // Nếu không có guard nào được chỉ định, dùng guard mặc định (thường là 'web')
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            // Kiểm tra xem người dùng có đang đăng nhập với guard này không
            if (Auth::guard($guard)->check()) {
                // Nếu đã đăng nhập, chuyển hướng đến trang HOME
                // HOME được định nghĩa trong app/Providers/RouteServiceProvider.php
                // Thường là '/dashboard'
                return redirect()->route('dashboard'); // <<< Sửa thành dòng này
            }
        }

        // Nếu chưa đăng nhập với bất kỳ guard nào, cho phép đi tiếp
        return $next($request);
    }
}