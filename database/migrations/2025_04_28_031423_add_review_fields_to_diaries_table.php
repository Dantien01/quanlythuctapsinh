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
        Schema::table('diaries', function (Blueprint $table) {
            // Thêm cột để lưu nhận xét của Admin
            $table->text('admin_comment')->nullable()->after('content'); // Thêm sau cột content cho dễ nhìn
            // Thêm cột để lưu điểm (nếu bạn muốn chấm điểm) - kiểu số nguyên hoặc thập phân tùy yêu cầu
            $table->unsignedTinyInteger('grade')->nullable()->after('admin_comment'); // Ví dụ điểm 0-10 hoặc thang khác
            // Thêm cột lưu thời gian admin nhận xét
            $table->timestamp('reviewed_at')->nullable()->after('grade');
            // Thêm cột lưu ID của admin đã nhận xét (khóa ngoại tới bảng users)
            $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')
                  ->constrained('users') // Ràng buộc với bảng 'users'
                  ->onDelete('set null'); // Nếu admin bị xóa, giữ lại nhận xét nhưng không biết ai nhận xét nữa
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diaries', function (Blueprint $table) {
            // Xóa các cột theo thứ tự ngược lại hoặc dùng dropForeign trước
            // Cẩn thận khi dùng dropForeign trong MySQL cũ
            if (Schema::hasColumn('diaries', 'reviewed_by')) { // Kiểm tra trước khi xóa khóa ngoại
                 // Cú pháp chuẩn để xóa khóa ngoại dựa trên tên cột (Laravel 9+)
                 try { // Thêm try-catch để tránh lỗi nếu tên constraint không chuẩn
                     $table->dropForeign(['reviewed_by']);
                 } catch (\Exception $e) {
                     // Nếu tên constraint khác, bạn có thể cần xóa thủ công hoặc tìm tên constraint
                     // $foreignKeys = Schema::getConnection()->getDoctrineSchemaManager()->listTableForeignKeys('diaries');
                     // Log::error("Could not drop foreign key 'reviewed_by': " . $e->getMessage());
                 }
                 $table->dropColumn('reviewed_by');
             }
            if (Schema::hasColumn('diaries', 'reviewed_at')) {
                $table->dropColumn('reviewed_at');
            }
            if (Schema::hasColumn('diaries', 'grade')) {
                $table->dropColumn('grade');
            }
            if (Schema::hasColumn('diaries', 'admin_comment')) {
                $table->dropColumn('admin_comment');
            }
        });
    }
};