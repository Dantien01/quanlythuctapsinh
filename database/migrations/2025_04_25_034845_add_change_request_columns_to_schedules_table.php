<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Thêm cột để lưu lý do sinh viên yêu cầu thay đổi
            $table->text('change_reason')->nullable()->after('status');
            // (Tùy chọn) Thêm cột để lưu chi tiết đề xuất khác nếu cần
            // $table->text('requested_change_details')->nullable()->after('change_reason');
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // $table->dropColumn('requested_change_details'); // Xóa nếu đã thêm ở trên
            $table->dropColumn('change_reason');
        });
    }
};