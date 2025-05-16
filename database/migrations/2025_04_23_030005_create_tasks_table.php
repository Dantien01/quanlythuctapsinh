<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intern_id')->constrained(table: 'users', indexName: 'tasks_intern_id_foreign')->onDelete('cascade');
            $table->string('title', 255); // Tiêu đề nhiệm vụ
            $table->text('description')->nullable(); // Mô tả chi tiết (có thể để trống)
            $table->date('due_date'); // Ngày đến hạn
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending'); // Trạng thái nhiệm vụ
            $table->timestamps(); // Thời gian tạo và cập nhật
        });
    }

    public function down()
    {
        Schema::dropIfExists('tasks');
    }
}