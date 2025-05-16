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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            // Khóa ngoại tới users (người gửi). Nếu user bị xóa, tin nhắn cũng xóa.
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            // Khóa ngoại tới users (người nhận). Nếu user bị xóa, tin nhắn cũng xóa.
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->text('content'); // Nội dung tin nhắn
            $table->timestamp('read_at')->nullable(); // Thời gian đọc tin nhắn, cho phép null
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
