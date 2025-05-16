<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Cho phép null vì hệ thống tự động tạo có thể không có user cụ thể
            $table->foreignId('created_by')->nullable()->after('notes')->constrained('users')->onDelete('set null');
            // Hoặc nếu bạn muốn liên kết với một user hệ thống cụ thể, bạn có thể không cho nullable
            // và đảm bảo gán ID user hệ thống đó trong command.
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });
    }
};