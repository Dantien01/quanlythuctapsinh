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
        Schema::table('attendances', function (Blueprint $table) {
            // Thêm cột schedule_id làm foreign key trỏ đến bảng schedules
            // Đặt sau cột user_id (hoặc một vị trí hợp lý khác)
            // Cho phép null nếu một bản ghi điểm danh có thể không liên quan đến lịch cụ thể (ít khả thi trong logic này)
            // Hoặc không cho phép null nếu mọi điểm danh PHẢI gắn với một lịch.
            $table->foreignId('schedule_id')
                  ->nullable() // Xem xét có cho phép null không dựa trên logic của bạn
                  ->after('user_id')
                  ->constrained('schedules') // Tự động tham chiếu đến cột 'id' của bảng 'schedules'
                  ->onDelete('cascade'); // Hoặc 'set null' nếu bạn muốn giữ attendance khi schedule bị xóa
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Quan trọng: Xóa foreign key constraint trước khi xóa cột
            $table->dropForeign(['schedule_id']); // Tên constraint thường là attendances_schedule_id_foreign
            $table->dropColumn('schedule_id');
        });
    }
};