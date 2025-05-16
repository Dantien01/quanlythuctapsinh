<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // << Thêm dòng này
use Symfony\Component\HttpFoundation\Response;

class CheckProfileApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        Log::debug('[Middleware CheckProfileApproved] Handling request for: ' . $request->path() . '. User ID: ' . (Auth::check() ? Auth::id() : 'Guest'));

        if (Auth::check() && Auth::user()->role && Auth::user()->role->name === 'SinhVien') {
            Log::debug('[Middleware CheckProfileApproved] User is SinhVien. Profile status: ' . Auth::user()->profile_status);
            if (Auth::user()->profile_status !== 'approved') {
                Log::warning('[Middleware CheckProfileApproved] SinhVien profile not approved.');
                if (!$request->routeIs('profile.edit')) {
                    Log::warning('[Middleware CheckProfileApproved] Not on profile.edit route. Logging out and redirecting to login for path: ' . $request->path());
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    // Ghi log TRƯỚC KHI redirect
                    Log::info('[Middleware CheckProfileApproved] Redirecting to login page.');
                    return redirect()->route('login')->with('error', 'Hồ sơ của bạn đang chờ duyệt hoặc đã bị từ chối. Vui lòng liên hệ quản trị viên.');
                } else {
                    Log::debug('[Middleware CheckProfileApproved] On profile.edit route. Allowing access.');
                }
            } else {
                Log::debug('[Middleware CheckProfileApproved] SinhVien profile is approved.');
            }
        } else {
            Log::debug('[Middleware CheckProfileApproved] User is not SinhVien or not logged in, or no role defined.');
        }

        Log::debug('[Middleware CheckProfileApproved] Proceeding to next middleware/controller for: ' . $request->path());
        return $next($request);
    }
}