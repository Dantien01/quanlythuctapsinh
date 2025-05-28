<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('messages', function (Blueprint $table) {
            Log::info("Running 'up' method for 2025_05_28_092956_modify_messages_table_for_conversations.php (Finalizing Schema - Corrected DB Select)");

            // --- 1. XÓA KHÓA NGOẠI CỦA receiver_id và CỘT receiver_id ---
            if (Schema::hasColumn('messages', 'receiver_id')) {
                // Lấy tên tất cả khóa ngoại của bảng messages cho cột receiver_id
                $sqlReceiverFk = "SELECT CONSTRAINT_NAME
                                  FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                                  WHERE TABLE_SCHEMA = DATABASE()
                                    AND TABLE_NAME = ?
                                    AND COLUMN_NAME = ?
                                    AND REFERENCED_TABLE_NAME IS NOT NULL";
                $foreignKeys = DB::select($sqlReceiverFk, ['messages', 'receiver_id']); // <<<<< ĐÃ SỬA

                // Lặp qua và xóa tất cả các khóa ngoại tìm thấy trên cột receiver_id
                foreach ($foreignKeys as $fk) {
                    $fkName = $fk->CONSTRAINT_NAME;
                    if (DB::getDriverName() !== 'sqlite') {
                        try {
                            DB::statement("ALTER TABLE messages DROP FOREIGN KEY `{$fkName}`");
                            Log::info("Migration (up): Successfully dropped foreign key '{$fkName}' on 'receiver_id' column.");
                        } catch (\Exception $e) {
                            Log::warning("Migration (up): Could not drop foreign key '{$fkName}' on 'receiver_id'. Error: " . $e->getMessage());
                        }
                    }
                }

                try {
                    $table->dropColumn('receiver_id');
                    Log::info("Migration (up): Dropped column 'receiver_id'.");
                } catch (\Exception $e) {
                    Log::error("Migration (up): FAILED to drop column 'receiver_id'. Error: " . $e->getMessage());
                    throw $e;
                }
            } else {
                Log::info("Migration (up): Column 'receiver_id' does not exist, skipping drop.");
            }

            // --- 2. XÓA CỘT subject ---
            if (Schema::hasColumn('messages', 'subject')) {
                $table->dropColumn('subject');
                Log::info("Migration (up): Dropped column 'subject'.");
            } else {
                Log::info("Migration (up): Column 'subject' does not exist, skipping drop.");
            }

            // --- 3. XÓA CỘT read_at ---
            if (Schema::hasColumn('messages', 'read_at')) {
                $table->dropColumn('read_at');
                Log::info("Migration (up): Dropped column 'read_at'.");
            } else {
                Log::info("Migration (up): Column 'read_at' does not exist, skipping drop.");
            }

            // --- 4. HOÀN THIỆN CỘT conversation_id ---
            if (Schema::hasColumn('messages', 'conversation_id')) {
                if (DB::table('messages')->whereNull('conversation_id')->exists()) {
                     $errorMessage = "Migration (up) ABORTED: Cannot make 'conversation_id' NOT NULL because there are still NULL values. Run data migration command 'php artisan messages:migrate-old-data' first and ensure all messages have a conversation_id.";
                     Log::error($errorMessage);
                     throw new \RuntimeException($errorMessage);
                }
                $table->unsignedBigInteger('conversation_id')->nullable(false)->change();
                Log::info("Migration (up): Changed 'conversation_id' to NOT NULL.");

                $conversationFkName = 'messages_conversation_id_foreign_final_v3';
                $hasConversationFk = false;
                $checkFkSql = "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME = ?";
                $existingFks = DB::select($checkFkSql, ['messages', 'conversation_id', 'conversations']); // <<<<< ĐÃ ĐÚNG Ở ĐÂY
                if (!empty($existingFks)) {
                    $hasConversationFk = true;
                }
                if (!$hasConversationFk) {
                    try {
                        $table->foreign('conversation_id', $conversationFkName)
                              ->references('id')
                              ->on('conversations')
                              ->onDelete('cascade');
                        Log::info("Migration (up): Added foreign key '{$conversationFkName}'.");
                    } catch (\Exception $e) {
                        Log::warning("Migration (up): Could not add foreign key '{$conversationFkName}'. Error: " . $e->getMessage());
                    }
                } else {
                    Log::info("Migration (up): Foreign key for 'conversation_id' to 'conversations' table likely already exists.");
                }
            } else {
                Log::warning("Migration (up): Column 'conversation_id' does not exist.");
            }
        });
    }

    // Hàm down() cũng cần được kiểm tra tương tự nếu có sử dụng DB::select(DB::raw(...))
    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            Log::info("Running 'down' method for 2025_05_28_092956_modify_messages_table_for_conversations.php (Reverting - Corrected DB Select)");

            // --- 1. ROLLBACK HOÀN THIỆN conversation_id ---
            if (Schema::hasColumn('messages', 'conversation_id')) {
                $conversationFkName = 'messages_conversation_id_foreign_final_v3';
                $fkToDrop = null;

                $checkFkSql = "SELECT CONSTRAINT_NAME
                               FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                               WHERE TABLE_SCHEMA = DATABASE()
                                 AND TABLE_NAME = ?
                                 AND COLUMN_NAME = ?
                                 AND REFERENCED_TABLE_NAME = ?";
                $existingFks = DB::select($checkFkSql, ['messages', 'conversation_id', 'conversations']); // <<<<< ĐÃ ĐÚNG Ở ĐÂY

                if (!empty($existingFks)) {
                    // Giả sử chỉ có một FK trên cột này đến bảng conversations,
                    // hoặc bạn muốn xóa cái đầu tiên tìm thấy khớp với tên quy ước
                    foreach ($existingFks as $existingFk) {
                        if ($existingFk->CONSTRAINT_NAME === $conversationFkName) {
                            $fkToDrop = $existingFk->CONSTRAINT_NAME;
                            break;
                        }
                    }
                    // Nếu không tìm thấy bằng tên chính xác, lấy cái đầu tiên khớp cột
                    if (!$fkToDrop && isset($existingFks[0])) {
                        $fkToDrop = $existingFks[0]->CONSTRAINT_NAME;
                    }
                }

                if ($fkToDrop && DB::getDriverName() !== 'sqlite') {
                    try {
                        $table->dropForeign($fkToDrop);
                        Log::info("Migration (down): Dropped foreign key '{$fkToDrop}'.");
                    } catch (\Exception $e) {
                        Log::warning("Migration (down): Could not drop foreign key '{$fkToDrop}'. Error: " . $e->getMessage());
                    }
                } elseif (DB::getDriverName() !== 'sqlite') {
                     Log::info("Migration (down): No specific foreign key found for conversation_id to drop by name (or name didn't match).");
                }

                $table->unsignedBigInteger('conversation_id')->nullable()->change();
                Log::info("Migration (down): Changed 'conversation_id' back to NULLABLE.");
            }

            // --- 2. ROLLBACK VIỆC XÓA CÁC CỘT CŨ ---
            if (!Schema::hasColumn('messages', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('content')->comment('Rollback: Original read_at');
            }
            if (!Schema::hasColumn('messages', 'subject')) {
                $table->string('subject', 255)->nullable()->after('sender_id')->comment('Rollback: Original subject');
            }
            if (!Schema::hasColumn('messages', 'receiver_id')) {
                $table->unsignedBigInteger('receiver_id')->nullable()->after('sender_id')->comment('Rollback: Original receiver ID');
                try {
                    $receiverFkName = 'messages_receiver_id_foreign_rollback_corrected';
                    $table->foreign('receiver_id', $receiverFkName)
                          ->references('id')
                          ->on('users')
                          ->onDelete('cascade');
                    Log::info("Migration (down): Re-added foreign key '{$receiverFkName}' for 'receiver_id'.");
                } catch (\Exception $e) {
                    Log::warning("Migration (down): Could not re-add foreign key for 'receiver_id'. Error: " . $e->getMessage());
                }
            }
        });
    }
};