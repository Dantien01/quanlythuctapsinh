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
        Schema::create('diaries', function (Blueprint $table) {
            $table->id();
            // Khóa ngoại tới users (sinh viên viết nhật ký). Nếu user bị xóa, nhật ký cũng xóa.
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('diary_date'); // Ngày viết nhật ký
            $table->string('title')->nullable(); // Tiêu đề nhật ký, cho phép null
            $table->text('content'); // Nội dung nhật ký
            $table->string('status')->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diaries');
    }
};
