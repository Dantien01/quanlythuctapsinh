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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            // Khóa ngoại tới users (sinh viên được gán lịch). Nếu user bị xóa, lịch này cũng xóa.
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            // Khóa ngoại tới users (người tạo lịch - Admin). Nếu user admin bị xóa, lịch này cũng xóa.
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('title'); // Tiêu đề lịch trình/công việc
            $table->text('description')->nullable(); // Mô tả chi tiết, cho phép null
            $table->dateTime('start_time'); // Thời gian bắt đầu
            $table->dateTime('end_time'); // Thời gian kết thúc
            $table->string('status', 50); // <-- GIỮ LẠI DÒNG NÀY (hoặc không có độ dài 50 cũng được)
            // $table->enum('status', ['pending', 'approved', 'rejected', 'pending_change'])->default('approved'); // <-- XÓA DÒNG NÀY
            $table->timestamps();
        });
    }
};