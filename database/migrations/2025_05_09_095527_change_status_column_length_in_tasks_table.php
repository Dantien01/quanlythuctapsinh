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
            // Thay đổi độ dài cột status, ví dụ thành VARCHAR(50)
            // Đảm bảo độ dài này đủ lớn cho giá trị trạng thái dài nhất của bạn
            $table->string('status', 50)->default('todo')->change();
        });
    }

    /**
     * Reverse the migrations.
     * Trong phương thức down, bạn có thể revert về độ dài cũ nếu muốn,
     * nhưng thường thì việc này không cần thiết.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Ví dụ: nếu độ dài cũ là 20
            // $table->string('status', 20)->default('todo')->change();
            // Hoặc đơn giản là không làm gì nếu bạn không muốn revert chính xác
        });
    }
};