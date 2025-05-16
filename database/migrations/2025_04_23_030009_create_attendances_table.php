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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            // Khóa ngoại tới users (sinh viên điểm danh). Nếu user bị xóa, dữ liệu điểm danh cũng xóa.
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('check_in_time')->nullable(); // Thời gian check-in, cho phép null
            // >>> CỘT NÀY ĐÃ CÓ SẴN TRONG FILE GỐC CỦA BẠN <<<
            $table->timestamp('check_out_time')->nullable(); // Thời gian check-out, cho phép null
            // Trạng thái điểm danh: vắng, có mặt, đi trễ, về sớm. Mặc định là vắng.
            $table->string('status')->default('absent'); // Bạn đã có status
            $table->string('image_path')->nullable(); // Đường dẫn ảnh điểm danh (cho tương lai), cho phép null
            $table->text('notes')->nullable(); // Ghi chú thêm, cho phép null
            $table->date('attendance_date'); // Lưu ngày điểm danh để dễ truy vấn
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};