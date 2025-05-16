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
        Schema::table('tasks', function (Blueprint $table) {
            // Thêm cột assigner_id, có thể null (nếu công việc có thể không có người giao cụ thể)
            // Đặt sau cột 'intern_id' hoặc một vị trí hợp lý
            $table->foreignId('assigner_id')
                  ->nullable() // Quyết định xem có cho phép null không
                              // Nếu công việc LUÔN phải có người giao, bỏ ->nullable()
                  ->after('intern_id') // Tùy chọn vị trí
                  ->constrained('users') // Tạo khóa ngoại tham chiếu đến cột id của bảng users
                  ->onDelete('set null'); // Hoặc 'cascade', 'restrict' tùy theo logic của bạn
                                        // 'set null': nếu user bị xóa, assigner_id sẽ thành NULL
                                        // 'cascade': nếu user bị xóa, task này cũng bị xóa
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Quan trọng: Xóa khóa ngoại trước khi xóa cột
            $table->dropForeign(['assigner_id']);
            $table->dropColumn('assigner_id');
        });
    }
};