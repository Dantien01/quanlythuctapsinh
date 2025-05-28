<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User; // Nếu cần dùng model User
use App\Models\Message;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\MessageReadStatus; // Nếu bạn dùng
use Illuminate\Support\Str; // Cho UUID
use Illuminate\Support\Facades\Log; // Thêm Log facade

class MigrateOldMessagesData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messages:migrate-old-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates old messages data to the new conversation structure.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting migration of old messages data...');
        Log::info('MIGRATION_COMMAND: Starting migration of old messages data...'); // Log vào file

        DB::transaction(function () {
            // --- Phần 1: Tạo Conversations và ConversationParticipants ---
            $this->info('Processing unique sender-receiver pairs to create conversations...');
            Log::info('MIGRATION_COMMAND: Processing unique sender-receiver pairs...');

            $uniquePairs = DB::table('messages')
                ->selectRaw('LEAST(sender_id, receiver_id) as user1_id, GREATEST(sender_id, receiver_id) as user2_id')
                ->whereNotNull('sender_id')
                ->whereNotNull('receiver_id')
                ->distinct()
                ->get();

            $this->info("MIGRATION_COMMAND: Found {$uniquePairs->count()} unique sender-receiver pairs.");
            Log::info("MIGRATION_COMMAND: Found {$uniquePairs->count()} unique sender-receiver pairs.");
            // if ($uniquePairs->count() > 0) {
            //     $this->line("MIGRATION_COMMAND: First pair details - User1: {$uniquePairs->first()->user1_id}, User2: {$uniquePairs->first()->user2_id}");
            //     Log::info("MIGRATION_COMMAND: First pair details - User1: {$uniquePairs->first()->user1_id}, User2: {$uniquePairs->first()->user2_id}");
            // }


            if ($uniquePairs->isEmpty()) {
                $this->warn('MIGRATION_COMMAND: No unique pairs found. No conversations will be created or messages updated based on pairs.');
                Log::warning('MIGRATION_COMMAND: No unique pairs found. No conversations will be created or messages updated based on pairs.');
                // Không cần chạy progress bar nếu không có cặp nào
            } else {
                $progressBarPairs = $this->output->createProgressBar($uniquePairs->count());
                $progressBarPairs->start();

                foreach ($uniquePairs as $pair) {
                    $this->line(''); // Dòng trống để dễ đọc output
                    $this->line("MIGRATION_COMMAND: Processing pair - User1: {$pair->user1_id}, User2: {$pair->user2_id}");
                    Log::info("MIGRATION_COMMAND: Processing pair - User1: {$pair->user1_id}, User2: {$pair->user2_id}");

                    if (empty($pair->user1_id) || empty($pair->user2_id)) {
                        $this->warn("MIGRATION_COMMAND: Skipping invalid pair: user1_id={$pair->user1_id}, user2_id={$pair->user2_id}");
                        Log::warning("MIGRATION_COMMAND: Skipping invalid pair: user1_id={$pair->user1_id}, user2_id={$pair->user2_id}");
                        $progressBarPairs->advance();
                        continue;
                    }

                    $existingConversation = Conversation::whereHas('participants', function ($query) use ($pair) {
                        $query->where('user_id', $pair->user1_id);
                    })->whereHas('participants', function ($query) use ($pair) {
                        $query->where('user_id', $pair->user2_id);
                    })->where(function ($query) {
                        $query->has('participants', '=', 2);
                    })->first();

                    $conversationId = null; // Khởi tạo

                    if ($existingConversation) {
                        $conversationId = $existingConversation->id;
                        $this->line("MIGRATION_COMMAND: Conversation already exists for users {$pair->user1_id} & {$pair->user2_id}. ID: {$conversationId}");
                        Log::info("MIGRATION_COMMAND: Conversation already exists for users {$pair->user1_id} & {$pair->user2_id}. ID: {$conversationId}");
                    } else {
                        try {
                            $conversation = Conversation::create([
                                // Nếu id của conversations là auto-increment, không cần truyền id
                                // Nếu id là UUID và Model Conversation dùng HasUuids trait, cũng không cần truyền
                                // Chỉ truyền nếu bạn tự quản lý UUID và không dùng HasUuids
                                // 'id' => (string) Str::uuid(), // Bỏ comment nếu id là UUID và bạn tự tạo
                            ]);
                            $conversationId = $conversation->id;

                            ConversationParticipant::create([
                                'conversation_id' => $conversationId,
                                'user_id' => $pair->user1_id,
                            ]);
                            ConversationParticipant::create([
                                'conversation_id' => $conversationId,
                                'user_id' => $pair->user2_id,
                            ]);
                            $this->line("MIGRATION_COMMAND: Created new conversation for users {$pair->user1_id} & {$pair->user2_id}. ID: {$conversationId}");
                            Log::info("MIGRATION_COMMAND: Created new conversation for users {$pair->user1_id} & {$pair->user2_id}. ID: {$conversationId}");
                        } catch (\Exception $e) {
                            $this->error("MIGRATION_COMMAND: Error creating conversation or participants for users {$pair->user1_id} & {$pair->user2_id}. Error: " . $e->getMessage());
                            Log::error("MIGRATION_COMMAND: Error creating conversation or participants for users {$pair->user1_id} & {$pair->user2_id}. Error: " . $e->getMessage());
                            $progressBarPairs->advance();
                            continue; // Bỏ qua cặp này nếu có lỗi
                        }
                    }

                    if ($conversationId) {
                        $this->line("MIGRATION_COMMAND: Attempting to update messages with conversation_id = {$conversationId} for pair User1: {$pair->user1_id}, User2: {$pair->user2_id}");
                        Log::info("MIGRATION_COMMAND: Attempting to update messages with conversation_id = {$conversationId} for pair User1: {$pair->user1_id}, User2: {$pair->user2_id}");

                        $updatedCount = Message::where(function ($query) use ($pair) {
                            $query->where('sender_id', $pair->user1_id)
                                  ->where('receiver_id', $pair->user2_id);
                        })->orWhere(function ($query) use ($pair) {
                            $query->where('sender_id', $pair->user2_id)
                                  ->where('receiver_id', $pair->user1_id);
                        })
                        // Chỉ cập nhật những tin nhắn chưa có conversation_id để tránh ghi đè nếu chạy lại
                        ->whereNull('conversation_id')
                        ->update(['conversation_id' => $conversationId]);

                        $this->line("MIGRATION_COMMAND: Updated {$updatedCount} messages with conversation_id = {$conversationId}");
                        Log::info("MIGRATION_COMMAND: Updated {$updatedCount} messages with conversation_id = {$conversationId}");
                    } else {
                         $this->warn("MIGRATION_COMMAND: No conversationId obtained for pair User1: {$pair->user1_id}, User2: {$pair->user2_id}. Skipping message update for this pair.");
                         Log::warning("MIGRATION_COMMAND: No conversationId obtained for pair User1: {$pair->user1_id}, User2: {$pair->user2_id}. Skipping message update for this pair.");
                    }

                    $progressBarPairs->advance();
                }
                $progressBarPairs->finish();
            } // End else ($uniquePairs->isEmpty())

            $this->info("\nFinished processing conversations and updating messages.");
            Log::info('MIGRATION_COMMAND: Finished processing conversations and updating messages.');


            // --- Phần 3: Di chuyển dữ liệu read_at ---
            $this->info('Migrating read_at data to message_read_statuses...');
            Log::info('MIGRATION_COMMAND: Migrating read_at data to message_read_statuses...');
            $messagesToUpdateReadStatus = DB::table('messages')
                                        ->whereNotNull('read_at')
                                        ->whereNotNull('receiver_id')
                                        ->select('id as message_id', 'receiver_id as user_id_who_read', 'read_at', 'conversation_id') // Thêm conversation_id
                                        ->whereNotNull('conversation_id') // Chỉ xử lý tin nhắn đã có conversation_id
                                        ->get();

            $this->info("MIGRATION_COMMAND: Found {$messagesToUpdateReadStatus->count()} messages with read_at data and conversation_id to migrate.");
            Log::info("MIGRATION_COMMAND: Found {$messagesToUpdateReadStatus->count()} messages with read_at data and conversation_id to migrate.");

            if ($messagesToUpdateReadStatus->count() > 0) {
                $progressBarReadStatus = $this->output->createProgressBar($messagesToUpdateReadStatus->count());
                $progressBarReadStatus->start();
                $readStatusesToInsert = [];

                foreach ($messagesToUpdateReadStatus as $msg) {
                    if (empty($msg->conversation_id)) { // Kiểm tra thêm để đảm bảo
                        Log::warning("MIGRATION_COMMAND: Skipping message_id {$msg->message_id} for read_status migration due to missing conversation_id.");
                        $progressBarReadStatus->advance();
                        continue;
                    }
                    $exists = MessageReadStatus::where('message_id', $msg->message_id)
                                               ->where('user_id', $msg->user_id_who_read)
                                               ->exists();
                    if (!$exists) {
                        $readStatusesToInsert[] = [
                            'message_id' => $msg->message_id,
                            'user_id' => $msg->user_id_who_read,
                            'read_at' => $msg->read_at,
                            // 'conversation_id' => $msg->conversation_id, // Cân nhắc thêm nếu bảng MessageReadStatus có cột này
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    $progressBarReadStatus->advance();
                }
                $progressBarReadStatus->finish();

                if (!empty($readStatusesToInsert)) {
                    MessageReadStatus::insert($readStatusesToInsert);
                    $this->info("\nInserted " . count($readStatusesToInsert) . " records into message_read_statuses.");
                    Log::info("MIGRATION_COMMAND: Inserted " . count($readStatusesToInsert) . " records into message_read_statuses.");
                } else {
                    $this->info("\nNo new read statuses to insert (possibly already migrated or no unread messages matching criteria).");
                    Log::info("MIGRATION_COMMAND: No new read statuses to insert.");
                }
            } else {
                $this->info('No messages with read_at data (and valid conversation_id) found to migrate.');
                Log::info('MIGRATION_COMMAND: No messages with read_at data (and valid conversation_id) found to migrate.');
            }

        }); // End DB::transaction

        $this->info('Old messages data migration completed successfully!');
        Log::info('MIGRATION_COMMAND: Old messages data migration completed successfully!');
        return Command::SUCCESS;
    }
}