<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Đổi tên class nếu Laravel tự sinh ra tên khác, nhưng thường thì nó sẽ là CreateTaskProgressTable
// class CreateTaskProgressTable extends Migration // Dòng này có thể khác nếu tên file của bạn là chuẩn timestamp
return new class extends Migration // Cách viết mới hơn từ Laravel 9+
{
    public function up(): void
    {
        Schema::create('task_progress', function (Blueprint $table) {
            $table->id();

            // Khóa ngoại tới bảng tasks. Nếu task bị xóa, progress cũng xóa.
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');

            // Khóa ngoại tới bảng users (tham chiếu sinh viên). Nếu user bị xóa, progress cũng xóa.
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Ghi chú về tiến độ, cho phép null
            $table->text('notes')->nullable();

            // Phần trăm hoàn thành (0-100), không dấu, cho phép null
            $table->integer('progress_percentage')->unsigned()->nullable();

            // Thời điểm sinh viên gửi cập nhật này, cho phép null, mặc định là thời gian hiện tại khi tạo
            $table->timestamp('submitted_at')->nullable()->default(now());

            $table->timestamps(); // Tạo created_at và updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void // Thêm phương thức down để có thể rollback
    {
        Schema::dropIfExists('task_progress');
    }
};