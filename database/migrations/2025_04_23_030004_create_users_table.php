<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
         // ---- Các cột mới bạn cần thêm vào ----
            // Khóa ngoại tới bảng roles. Nếu role bị xóa, user cũng bị xóa (hoặc đổi thành onDelete('set null') nếu muốn giữ user)
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->string('mssv')->unique()->nullable(); // Mã số sinh viên, không trùng, cho phép null (vì Admin không có)
            $table->string('phone')->nullable(); // Số điện thoại, cho phép null
            // Khóa ngoại tới bảng schools. Nếu trường bị xóa, cột này ở user thành NULL. Cho phép null ban đầu.
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('set null');
            // Khóa ngoại tới bảng majors. Nếu ngành bị xóa, cột này ở user thành NULL. Cho phép null ban đầu.
            $table->foreignId('major_id')->nullable()->constrained('majors')->onDelete('set null');
            // Trạng thái hồ sơ: chờ duyệt, đã duyệt, bị từ chối. Mặc định là chờ duyệt.
            $table->enum('profile_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable(); // Lý do từ chối hồ sơ, cho phép null

            // ---- Cột timestamps có sẵn (GIỮ LẠI) ----
            $table->timestamps();
        });
    }
}