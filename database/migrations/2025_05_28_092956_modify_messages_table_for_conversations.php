<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB; // Sử dụng DB facade cho một số thao tác schema raw nếu cần
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('messages', function (Blueprint $table) {
            Log::info("Running 'up' method for modify_messages_table_for_conversations migration.");

            // --- 1. XÓA KHÓA NGOẠI CŨ CỦA receiver_id ---
            if (Schema::hasColumn('messages', 'receiver_id')) {
                // Cố gắng tìm và xóa khóa ngoại một cách an toàn
                // Tên khóa ngoại có thể là 'messages_receiver_id_foreign' hoặc được tự sinh khác
                // Chúng ta sẽ cố gắng tìm nó dựa trên cột
                $connection = Schema::getConnection();
                $doctrineSchemaManager = $connection->getDoctrineSchemaManager();
                $foreignKeys = $doctrineSchemaManager->listTableForeignKeys('messages');

                foreach ($foreignKeys as $foreignKey) {
                    if (in_array('receiver_id', $foreignKey->getLocalColumns())) {
                        try {
                            $table->dropForeign($foreignKey->getName());
                            Log::info("Migration (up): Dropped foreign key '{$foreignKey->getName()}' for 'receiver_id'.");
                        } catch (\Exception $e) {
                            Log::warning("Migration (up): Could not drop foreign key '{$foreignKey->getName()}' for 'receiver_id'. It might have been dropped already. Error: " . $e->getMessage());
                        }
                        break; // Giả định chỉ có một FK cho receiver_id
                    }
                }
                // --- 2. XÓA CỘT receiver_id ---
                $table->dropColumn('receiver_id');
                Log::info("Migration (up): Dropped column 'receiver_id'.");
            } else {
                Log::info("Migration (up): Column 'receiver_id' does not exist, skipping drop.");
            }


            // --- 3. XÓA CỘT subject ---
            if (Schema::hasColumn('messages', 'subject')) {
                $table->dropColumn('subject');
                Log::info("Migration (up): Dropped column 'subject'.");
            } else {
                Log::info("Migration (up): Column 'subject' does not exist, skipping drop.");
            }

            // --- 4. XÓA CỘT read_at ---
            if (Schema::hasColumn('messages', 'read_at')) {
                $table->dropColumn('read_at');
                Log::info("Migration (up): Dropped column 'read_at'.");
            } else {
                Log::info("Migration (up): Column 'read_at' does not exist, skipping drop.");
            }

            // --- 5. HOÀN THIỆN CỘT conversation_id ---
            if (Schema::hasColumn('messages', 'conversation_id')) {
                // Giả sử id của bảng 'conversations' là BIGINT UNSIGNED (tạo bằng $table->id())
                // và conversation_id trong 'messages' cũng nên là BIGINT UNSIGNED
                $table->unsignedBigInteger('conversation_id')->nullable(false)->change();
                Log::info("Migration (up): Changed 'conversation_id' to NOT NULL.");

                // Nếu id của bảng 'conversations' là UUID:
                // $table->uuid('conversation_id')->nullable(false)->change();
                // Log::info("Migration (up): Changed 'conversation_id' (UUID) to NOT NULL.");

                // Thêm khóa ngoại cho conversation_id trỏ đến conversations.id
                // Đặt tên tường minh cho khóa ngoại để dễ quản lý
                $conversationFkName = 'messages_conversation_id_foreign';
                $hasConversationFk = false;
                $doctrineSchemaManagerConv = Schema::getConnection()->getDoctrineSchemaManager();
                $foreignKeysConv = $doctrineSchemaManagerConv->listTableForeignKeys('messages');
                foreach ($foreignKeysConv as $foreignKey) {
                    if ($foreignKey->getName() === $conversationFkName || (in_array('conversation_id', $foreignKey->getLocalColumns()) && $foreignKey->getForeignTableName() == 'conversations')) {
                        $hasConversationFk = true;
                        break;
                    }
                }

                if (!$hasConversationFk) {
                    $table->foreign('conversation_id', $conversationFkName)
                          ->references('id')
                          ->on('conversations')
                          ->onDelete('cascade');
                    Log::info("Migration (up): Added foreign key '{$conversationFkName}'.");
                } else {
                    Log::info("Migration (up): Foreign key for 'conversation_id' to 'conversations' table already exists.");
                }
            } else {
                Log::warning("Migration (up): Column 'conversation_id' does not exist. Cannot make it NOT NULL or add foreign key.");
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            Log::info("Running 'down' method for modify_messages_table_for_conversations migration.");

            // --- 1. ROLLBACK HOÀN THIỆN conversation_id ---
            if (Schema::hasColumn('messages', 'conversation_id')) {
                // Xóa khóa ngoại của conversation_id trước
                $conversationFkName = 'messages_conversation_id_foreign';
                $doctrineSchemaManagerConv = Schema::getConnection()->getDoctrineSchemaManager();
                $foreignKeysConv = $doctrineSchemaManagerConv->listTableForeignKeys('messages');
                foreach ($foreignKeysConv as $foreignKey) {
                    if ($foreignKey->getName() === $conversationFkName || (in_array('conversation_id', $foreignKey->getLocalColumns()) && $foreignKey->getForeignTableName() == 'conversations')) {
                        try {
                            $table->dropForeign($foreignKey->getName());
                            Log::info("Migration (down): Dropped foreign key '{$foreignKey->getName()}' for 'conversation_id'.");
                        } catch (\Exception $e) {
                            Log::warning("Migration (down): Could not drop foreign key for 'conversation_id'. Error: " . $e->getMessage());
                        }
                        break;
                    }
                }
                // Thay đổi conversation_id thành nullable trở lại
                $table->unsignedBigInteger('conversation_id')->nullable()->change();
                // Nếu là uuid:
                // $table->uuid('conversation_id')->nullable()->change();
                Log::info("Migration (down): Changed 'conversation_id' back to NULLABLE.");
            }

            // --- 2. ROLLBACK VIỆC XÓA CÁC CỘT CŨ ---
            // Thêm lại cột read_at
            if (!Schema::hasColumn('messages', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('content')->comment('Rollback: Original read_at');
                Log::info("Migration (down): Re-added column 'read_at'.");
            }
            // Thêm lại cột subject
            if (!Schema::hasColumn('messages', 'subject')) {
                $table->string('subject')->nullable()->after('sender_id')->comment('Rollback: Original subject');
                Log::info("Migration (down): Re-added column 'subject'.");
            }
            // Thêm lại cột receiver_id và khóa ngoại của nó
            if (!Schema::hasColumn('messages', 'receiver_id')) {
                $table->foreignId('receiver_id')->nullable()->after('sender_id')->comment('Rollback: Original receiver ID');
                Log::info("Migration (down): Re-added column 'receiver_id'.");
                try {
                    // Sử dụng một tên khác cho khóa ngoại rollback để tránh xung đột nếu nó chưa được xóa hoàn toàn trước đó
                    $receiverFkName = 'messages_receiver_id_foreign_rollback_final';
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