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
        Schema::create('schedule_user', function (Blueprint $table) {
            // $table->id(); // Không cần thiết nếu bạn dùng khóa chính phức hợp
            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade'); // Khóa ngoại đến bảng 'schedules'
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');       // Khóa ngoại đến bảng 'users'

            // Thiết lập khóa chính phức hợp để đảm bảo mỗi cặp (schedule_id, user_id) là duy nhất
            $table->primary(['schedule_id', 'user_id']);

            $table->timestamps(); // Tùy chọn, nếu bạn muốn theo dõi khi nào một sinh viên được gán/bỏ gán
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_user');
    }
};