<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInternshipSlotsTable extends Migration
{
    public function up()
    {
        Schema::create('internship_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade'); // Hoặc 'internship_id' nếu bảng chính của bạn là 'internships'
            $table->tinyInteger('day_of_week'); // 1: Thứ 2, 2: Thứ 3, ..., 7: Chủ Nhật (hoặc 0-6 nếu bạn thích)
            $table->time('start_time');
            $table->time('end_time');
            $table->text('task_description')->nullable(); // Mô tả công việc cụ thể cho buổi đó (nếu có)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('internship_slots');
    }
}