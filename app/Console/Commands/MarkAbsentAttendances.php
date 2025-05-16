<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\User; // Nếu cần thông báo cho admin/sinh viên
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // Để ghi log quá trình chạy

class MarkAbsentAttendances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:mark-absent'; // Tên lệnh để gọi từ terminal

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically mark students as absent for past mandatory schedules if no attendance record exists.';

    /**
     * Execute the console command.
     */
    public function handle(): int // Sử dụng int return type cho Laravel 9+
    {
        $this->info('Starting to mark absent attendances...');
        Log::info('[MarkAbsentAttendances] Command started.');

        $processedCount = 0;
        $absentMarkedCount = 0;

        // Lấy tất cả các lịch bắt buộc điểm danh, đã qua, và có trạng thái 'scheduled'
        // (chưa bị hủy, chưa được xử lý bởi hệ thống trước đó)
        $schedulesToProcess = Schedule::query()
            ->where('is_mandatory_attendance', true)
            ->where('status', Schedule::STATUS_SCHEDULED) // Chỉ xử lý lịch đang chờ
            ->where('end_time', '<', Carbon::now())      // Lịch đã kết thúc
            ->whereDoesntHave('attendances', function ($queryAttendance) {
                // Loại trừ các schedule mà sinh viên được gán đã có bản ghi điểm danh (bất kể trạng thái)
                // Điều này giả định user_id trên schedule là sinh viên cần điểm danh
                $queryAttendance->whereColumn('attendances.user_id', 'schedules.user_id');
            })
            ->with('student') // Eager load thông tin sinh viên
            ->get();

        if ($schedulesToProcess->isEmpty()) {
            $this->info('No schedules found requiring absent marking.');
            Log::info('[MarkAbsentAttendances] No applicable schedules found.');
            return Command::SUCCESS; // Trả về 0 (thành công)
        }

        $this->info("Found " . $schedulesToProcess->count() . " schedules to process for absent marking.");

        foreach ($schedulesToProcess as $schedule) {
            $processedCount++;
            $student = $schedule->student; // Sinh viên được gán cho lịch này

            if (!$student) {
                Log::warning("[MarkAbsentAttendances] Schedule ID: {$schedule->id} has no student assigned (user_id: {$schedule->user_id}). Skipping.");
                $this->warn("Skipping schedule ID: {$schedule->id} - No student assigned.");
                continue;
            }

            // Kiểm tra kỹ một lần nữa xem đã có bản ghi điểm danh cho sinh viên này và lịch này chưa
            // (Mặc dù `whereDoesntHave` đã làm điều này, nhưng kiểm tra lại để chắc chắn hơn trước khi tạo)
            $existingAttendance = Attendance::where('schedule_id', $schedule->id)
                                            ->where('user_id', $student->id)
                                            ->first();

            if ($existingAttendance) {
                Log::info("[MarkAbsentAttendances] Student ID: {$student->id} already has an attendance record for Schedule ID: {$schedule->id}. Skipping duplicate marking.");
                $this->line("Student {$student->name} (ID: {$student->id}) already has attendance for Schedule ID: {$schedule->id}. Skipping.");
                // Cập nhật trạng thái lịch thành đã xử lý nếu chưa
                if ($schedule->status === Schedule::STATUS_SCHEDULED) {
                    $schedule->status = Schedule::STATUS_SYSTEM_PROCESSED;
                    $schedule->save();
                     Log::info("[MarkAbsentAttendances] Schedule ID: {$schedule->id} status updated to system_processed (found existing attendance).");
                }
                continue;
            }

            // Tạo bản ghi điểm danh "Vắng mặt không phép"
            try {
                Attendance::create([
                    'user_id' => $student->id,
                    'schedule_id' => $schedule->id,
                    'attendance_date' => Carbon::parse($schedule->start_time)->toDateString(), // Lấy ngày từ start_time của lịch
                    'status' => Attendance::STATUS_ABSENT_WITHOUT_PERMISSION, // Giả sử bạn có hằng số này trong Model Attendance
                    'check_in_time' => null,
                    'check_out_time' => null,
                    'notes' => 'Hệ thống tự động ghi nhận vắng mặt.',
                    'created_by' => null, // Hoặc ID của một user hệ thống nếu có
                ]);
                $absentMarkedCount++;
                $this->info("Marked student {$student->name} (ID: {$student->id}) as ABSENT for Schedule ID: {$schedule->id} on {$schedule->start_time->format('Y-m-d')}.");
                Log::info("[MarkAbsentAttendances] Marked student ID: {$student->id} as ABSENT for Schedule ID: {$schedule->id}.");

                // Cập nhật trạng thái của lịch này thành đã được hệ thống xử lý
                $schedule->status = Schedule::STATUS_SYSTEM_PROCESSED;
                $schedule->save();
                Log::info("[MarkAbsentAttendances] Schedule ID: {$schedule->id} status updated to system_processed.");

                // TODO (Tùy chọn): Gửi thông báo cho Admin hoặc Sinh viên về việc ghi nhận vắng mặt
                // if ($student->shouldReceiveAbsenceNotification()) {
                //     $student->notify(new StudentMarkedAbsentNotification($schedule));
                // }
                // if ($admin = User::findAdminToNotify()) { // Logic tìm admin
                //    $admin->notify(new StudentAbsenceDetectedNotification($student, $schedule));
                // }

            } catch (\Exception $e) {
                Log::error("[MarkAbsentAttendances] Error marking student ID: {$student->id} as absent for Schedule ID: {$schedule->id}. Error: " . $e->getMessage());
                $this->error("Error processing Schedule ID: {$schedule->id} for student {$student->name}. See logs.");
            }
        }

        $this->info("Processed {$processedCount} schedules. Marked {$absentMarkedCount} students as absent.");
        Log::info("[MarkAbsentAttendances] Command finished. Processed: {$processedCount}, Marked Absent: {$absentMarkedCount}.");
        return Command::SUCCESS; // Trả về 0 (thành công)
    }
}