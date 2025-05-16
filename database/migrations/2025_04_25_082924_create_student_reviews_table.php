<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('student_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->comment('Sinh viên được nhận xét')->onDelete('cascade');
            $table->foreignId('reviewer_id')->constrained('users')->comment('Admin viết nhận xét')->onDelete('cascade');
            $table->string('review_period')->nullable()->comment('Kỳ nhận xét, VD: 2024-W17, 2024-04'); // Tuần hoặc tháng
            $table->text('content'); // Nội dung nhận xét
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('student_reviews');
    }
};