<?php
// database/migrations/2025_05_28_095440_add_conversation_id_to_messages_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('messages', function (Blueprint $table) {
            if (!Schema::hasColumn('messages', 'conversation_id')) { // KIỂM TRA Ở ĐÂY
                // Chọn kiểu dữ liệu cho conversation_id phù hợp với id của bảng conversations
                // GIẢ SỬ conversations.id là $table->id() (bigIncrements)
                $table->foreignId('conversation_id')->nullable()->after('id')->comment('FK to conversations');

                // Nếu conversations.id là $table->uuid('id'), hãy dùng dòng dưới thay thế:
                // $table->uuid('conversation_id')->nullable()->after('id')->comment('FK to conversations');
            }
        });
    }

    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            if (Schema::hasColumn('messages', 'conversation_id')) {
                // Ở bước này, chúng ta chưa thêm khóa ngoại cho conversation_id,
                // nên chỉ cần xóa cột.
                $table->dropColumn('conversation_id');
            }
        });
    }
};