<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskProgressTable extends Migration
{
    public function up()
    {
        Schema::create('task_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade'); // Liên kết với bảng tasks
            $table->foreignId('intern_id')->constrained('interns')->onDelete('cascade'); // Liên kết với bảng interns
            $table->integer('progress_percentage')->unsigned()->default(0); // Phần trăm hoàn thành (0-100)
            $table->text('notes')->nullable(); // Ghi chú về tiến độ (có thể để trống)
            $table->timestamp('updated_at')->useCurrent(); // Thời gian cập nhật tiến độ
        });
    }

    public function down()
    {
        Schema::dropIfExists('task_progress');
    }
}
