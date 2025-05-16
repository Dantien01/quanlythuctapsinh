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
        Schema::create('diary_comments', function (Blueprint $table) {
            $table->id();
            // Liên kết với bảng diaries, tự động xóa comment nếu diary bị xóa
            $table->foreignId('diary_id')->constrained('diaries')->onDelete('cascade');
            // Liên kết với bảng users, tự động xóa comment nếu user bị xóa (cân nhắc)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('content'); // Nội dung nhận xét/phản hồi
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diary_comments');
    }
};