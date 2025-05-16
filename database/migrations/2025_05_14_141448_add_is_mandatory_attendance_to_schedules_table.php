<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Thêm cột để xác định buổi học có bắt buộc điểm danh không
            // Đặt sau cột 'status' hiện có
            $table->boolean('is_mandatory_attendance')->default(true)->after('status')->comment('Buổi học có bắt buộc điểm danh không');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn('is_mandatory_attendance');
        });
    }
};