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

        DB::transaction(function () {
            // --- Phần 1: Tạo Conversations và ConversationParticipants ---
            $this->info('Processing unique sender-receiver pairs to create conversations...');

            // Lấy tất cả các cặp (sender_id, receiver_id) duy nhất từ bảng messages cũ
            // Sắp xếp ID để đảm bảo thứ tự (user1_id < user2_id) cho mỗi cặp
            $uniquePairs = DB::table('messages')
                ->selectRaw('LEAST(sender_id, receiver_id) as user1_id, GREATEST(sender_id, receiver_id) as user2_id')
                ->whereNotNull('sender_id') // Bỏ qua các dòng có thể bị lỗi dữ liệu
                ->whereNotNull('receiver_id')
                ->distinct()
                ->get();

            $progressBarPairs = $this->output->createProgressBar($uniquePairs->count());
            $progressBarPairs->start();

            foreach ($uniquePairs as $pair) {
                if (empty($pair->user1_id) || empty($pair->user2_id)) {
                    $this->warn("Skipping invalid pair: user1_id={$pair->user1_id}, user2_id={$pair->user2_id}");
                    $progressBarPairs->advance();
                    continue;
                }

                // Kiểm tra xem conversation cho cặp này đã tồn tại chưa (dựa trên participants)
                // Logic này có thể cần tối ưu nếu số lượng lớn
                $existingConversation = Conversation::whereHas('participants', function ($query) use ($pair) {
                    $query->where('user_id', $pair->user1_id);
                })->whereHas('participants', function ($query) use ($pair) {
                    $query->where('user_id', $pair->user2_id);
                })->where(function ($query) { // Đảm bảo chỉ có 2 participants
                    $query->has('participants', '=', 2);
                })->first();


                if ($existingConversation) {
                    $conversationId = $existingConversation->id;
                    $this->line("Conversation already exists for users {$pair->user1_id} & {$pair->user2_id}. ID: {$conversationId}");
                } else {
                    // 1. Tạo bản ghi mới trong `conversations`
                    $conversation = Conversation::create([
                        'id' => (string) Str::uuid(), // Hoặc để auto-increment nếu dùng int
                        // Thêm các trường khác cho conversation nếu có
                    ]);
                    $conversationId = $conversation->id;

                    // 2. Tạo hai bản ghi trong `conversation_participants`
                    ConversationParticipant::create([
                        'conversation_id' => $conversationId,
                        'user_id' => $pair->user1_id,
                    ]);
                    ConversationParticipant::create([
                        'conversation_id' => $conversationId,
                        'user_id' => $pair->user2_id,
                    ]);
                    $this->line("Created new conversation for users {$pair->user1_id} & {$pair->user2_id}. ID: {$conversationId}");
                }

                // --- Phần 2: Cập nhật conversation_id cho các tin nhắn cũ ---
                // Cập nhật cho cả hai chiều (A gửi B và B gửi A)
                Message::where(function ($query) use ($pair) {
                    $query->where('sender_id', $pair->user1_id)
                          ->where('receiver_id', $pair->user2_id);
                })->orWhere(function ($query) use ($pair) {
                    $query->where('sender_id', $pair->user2_id)
                          ->where('receiver_id', $pair->user1_id);
                })->update(['conversation_id' => $conversationId]);

                $progressBarPairs->advance();
            }
            $progressBarPairs->finish();
            $this->info("\nFinished processing conversations and updating messages.");


            // --- Phần 3: (Nếu xóa messages.read_at) Di chuyển dữ liệu read_at sang message_read_statuses ---
            // Giả định bạn có cột `original_receiver_id` và `original_read_at` trong Message model để dễ truy cập
            // Hoặc bạn có thể query trực tiếp từ DB trước khi cột receiver_id bị xóa hẳn
            // Để an toàn, hãy chạy phần này TRƯỚC khi migration xóa cột receiver_id và read_at chạy.
            // Hoặc, nếu đã chạy, bạn cần lấy thông tin này từ backup hoặc một cách nào đó.

            // Giả sử chúng ta chạy lệnh này SAU KHI migration thêm conversation_id
            // và TRƯỚC KHI migration xóa receiver_id và read_at
            $this->info('Migrating read_at data to message_read_statuses...');
            $messagesToUpdateReadStatus = DB::table('messages') // Query trực tiếp từ bảng
                                        ->whereNotNull('read_at')
                                        ->whereNotNull('receiver_id') // Cột này vẫn tồn tại ở bước này
                                        ->select('id as message_id', 'receiver_id as user_id_who_read', 'read_at')
                                        ->get();

            if ($messagesToUpdateReadStatus->count() > 0) {
                $progressBarReadStatus = $this->output->createProgressBar($messagesToUpdateReadStatus->count());
                $progressBarReadStatus->start();
                $readStatusesToInsert = [];

                foreach ($messagesToUpdateReadStatus as $msg) {
                    // Kiểm tra xem đã có bản ghi chưa (để tránh duplicate nếu chạy lại lệnh)
                    $exists = MessageReadStatus::where('message_id', $msg->message_id)
                                               ->where('user_id', $msg->user_id_who_read)
                                               ->exists();
                    if (!$exists) {
                        $readStatusesToInsert[] = [
                            'message_id' => $msg->message_id,
                            'user_id' => $msg->user_id_who_read,
                            'read_at' => $msg->read_at,
                            'created_at' => now(), // Thêm timestamp nếu bảng có
                            'updated_at' => now(), // Thêm timestamp nếu bảng có
                        ];
                    }
                    $progressBarReadStatus->advance();
                }
                $progressBarReadStatus->finish();

                if (!empty($readStatusesToInsert)) {
                    MessageReadStatus::insert($readStatusesToInsert); // Insert hàng loạt để hiệu quả hơn
                    $this->info("\nInserted " . count($readStatusesToInsert) . " records into message_read_statuses.");
                } else {
                    $this->info("\nNo new read statuses to insert.");
                }
            } else {
                $this->info('No messages with read_at data found to migrate.');
            }

        }); // End DB::transaction

        $this->info('Old messages data migration completed successfully!');
        return Command::SUCCESS;
    }
}