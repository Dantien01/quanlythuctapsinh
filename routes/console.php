<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; // <<< THÊM DÒNG IMPORT NÀY

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// =================================================================
// LÊN LỊCH CHO COMMAND MarkAbsentAttendances CỦA BẠN Ở ĐÂY
// =================================================================
Schedule::command('attendance:mark-absent') // Sử dụng signature của command
         ->dailyAt('23:00')                 // Ví dụ: chạy vào 11 giờ tối mỗi ngày
         ->timezone('Asia/Ho_Chi_Minh')     // QUAN TRỌNG: Thay 'Asia/Ho_Chi_Minh' bằng múi giờ của bạn
         ->withoutOverlapping(10)           // Không chạy chồng chéo, timeout sau 10 phút (nếu command chạy quá lâu)
         ->onFailure(function () {
             // Ghi log hoặc gửi thông báo nếu command thất bại
             \Illuminate\Support\Facades\Log::error('[SCHEDULE_FAILURE] Command [attendance:mark-absent] failed to execute.');
             // Ví dụ: Gửi email thông báo
             // Mail::to('admin@example.com')->send(new \App\Mail\ScheduleJobFailed('attendance:mark-absent'));
         })
         ->onSuccess(function () {
            // (Tùy chọn) Log khi command chạy thành công
            \Illuminate\Support\Facades\Log::info('[SCHEDULE_SUCCESS] Command [attendance:mark-absent] executed successfully.');
         });
         // Bỏ ->emailOutputOnFailure('your-admin-email@example.com'); nếu bạn chưa cài đặt mail và không muốn nhận mail lỗi ngay.
         // Bạn có thể thêm lại sau khi đã cấu hình mail server.