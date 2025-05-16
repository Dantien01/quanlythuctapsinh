<?php

namespace App\Http\Middleware; // Đảm bảo namespace đúng là App\Http\Middleware

use Closure; // Import Closure
use Illuminate\Http\Request; // Import Request
use Illuminate\Support\Facades\Auth; // Import lớp Auth để kiểm tra đăng nhập
use Symfony\Component\HttpFoundation\Response; // Import Response

class CheckRole // Tên class là CheckRole
{
    /**
     * Handle an incoming request.
     *
     * Kiểm tra xem người dùng đã đăng nhập và có vai trò phù hợp không.
     * 
     * @param  \Illuminate\Http\Request  $request Request đến
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next Middleware tiếp theo trong chuỗi
     * @param  string  ...$roles Danh sách các tên vai trò được phép truy cập (được truyền từ route)
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Bước 1: Kiểm tra người dùng đã đăng nhập chưa?
        if (!Auth::check()) {
            // Nếu chưa đăng nhập, chuyển hướng về trang đăng nhập
            // Sử dụng route() để lấy URL từ tên route 'login' (do Breeze tạo ra)
            return redirect()->route('login');
        }

        // Bước 2: Lấy thông tin người dùng hiện tại và vai trò của họ
        // dùng ->load('role') để đảm bảo quan hệ 'role' đã được tải (Eager Loading)
        $user = Auth::user()->load('role');

        // Bước 3: Kiểm tra vai trò
        // Điều kiện: User phải có role (không phải null) VÀ tên của role đó phải nằm trong danh sách $roles được truyền vào
        if (!$user->role || !in_array($user->role->name, $roles)) {
            // Nếu không thỏa mãn, trả về lỗi 403 Forbidden (Không có quyền truy cập)
            abort(403, 'TRUY CẬP BỊ TỪ CHỐI. BẠN KHÔNG CÓ QUYỀN NÀY.'); // Có thể thay đổi thông báo lỗi
        }

        // Bước 4: Nếu mọi thứ hợp lệ, cho phép request đi tiếp đến controller hoặc middleware tiếp theo
        return $next($request);
    }
}