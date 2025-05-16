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
        Schema::create('majors', function (Blueprint $table) {
            $table->id(); // Khóa chính tự tăng
            // Khóa ngoại tham chiếu đến cột 'id' trên bảng 'schools'
            // Nếu trường bị xóa (onDelete), chuyên ngành thuộc trường đó cũng bị xóa ('cascade')
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->string('name'); // Tên chuyên ngành
            $table->timestamps(); // Tạo cột created_at và updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('majors');
    }
};
