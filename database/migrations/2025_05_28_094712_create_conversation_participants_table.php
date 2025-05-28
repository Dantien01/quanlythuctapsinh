<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->id(); // ID của bảng nối này

            // Khóa ngoại tới bảng conversations
            // Kiểu dữ liệu của conversation_id phải khớp với id của bảng conversations
            // Nếu conversations.id là $table->id() (bigIncrements):
            $table->foreignId('conversation_id')->constrained('conversations')->onDelete('cascade');
            // Nếu conversations.id là $table->uuid('id'):
            // $table->uuid('conversation_id');
            // $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');

            // Khóa ngoại tới bảng users
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->timestamps();
            $table->unique(['conversation_id', 'user_id']); // Đảm bảo một user chỉ tham gia một conversation một lần
        });
    }

    public function down()
    {
        Schema::dropIfExists('conversation_participants');
    }
};
