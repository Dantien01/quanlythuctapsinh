<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\InternshipSlot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class StudentScheduleApiController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $student */
        $student = Auth::user();

        Log::info('--- DEBUG API: Student Schedules ---');
        if ($student) {
            Log::info('Student ID đang truy cập: ' . $student->id);
            $schedulesCuaStudent = $student->schedules()->get(); // Giả sử User model có quan hệ schedules() là BelongsToMany
            if ($schedulesCuaStudent->isEmpty()) {
                Log::info('Sinh viên này KHÔNG được gán vào Schedule nào.');
            } else {
                Log::info('Các Schedule IDs mà sinh viên này được gán vào: ' . $schedulesCuaStudent->pluck('id')->implode(', '));
                foreach ($schedulesCuaStudent as $sch) {
                    Log::info('  Schedule ID: ' . $sch->id . ', Title: ' . $sch->title . ', Start: ' . ($sch->overall_start_date ? Carbon::parse($sch->overall_start_date)->toDateTimeString() : 'NULL') . ', End: ' . ($sch->overall_end_date ? Carbon::parse($sch->overall_end_date)->toDateTimeString() : 'NULL'));
                }
            }
        } else {
            Log::error('KHÔNG LẤY ĐƯỢC THÔNG TIN SINH VIÊN! Token có vấn đề?');
            return response()->json(['success' => false, 'message' => 'Lỗi xác thực người dùng.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'view_mode' => 'sometimes|in:day,week,month',
            'date' => 'sometimes|date_format:Y-m-d',
            'start_date' => 'sometimes|date_format:Y-m-d|required_with:end_date|before_or_equal:end_date',
            'end_date' => 'sometimes|date_format:Y-m-d|required_with:start_date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ.', 'errors' => $validator->errors()], 422);
        }

        $viewMode = $request->input('view_mode', 'week');
        $targetDateInput = $request->input('date');
        $referenceDate = $targetDateInput ? Carbon::parse($targetDateInput) : Carbon::now();

        $queryStartDate = null;
        $queryEndDate = null;
        $weekNumber = null;
        $weekStartDate = null;
        $weekEndDate = null;

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $queryStartDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $queryEndDate = Carbon::parse($request->input('end_date'))->endOfDay();
            $weekNumber = $queryStartDate->weekOfYear;
            $weekStartDate = $queryStartDate->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
            $weekEndDate = $queryStartDate->copy()->endOfWeek(Carbon::SUNDAY)->toDateString();
        } else {
            switch ($viewMode) {
                case 'day':
                    $queryStartDate = $referenceDate->copy()->startOfDay();
                    $queryEndDate = $referenceDate->copy()->endOfDay();
                    $weekNumber = $referenceDate->weekOfYear;
                    $weekStartDate = $referenceDate->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
                    $weekEndDate = $referenceDate->copy()->endOfWeek(Carbon::SUNDAY)->toDateString();
                    break;
                case 'month':
                    $queryStartDate = $referenceDate->copy()->startOfMonth()->startOfDay();
                    $queryEndDate = $referenceDate->copy()->endOfMonth()->endOfDay();
                    $weekNumber = $queryStartDate->weekOfYear;
                    $weekStartDate = $queryStartDate->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
                    $weekEndDate = $queryStartDate->copy()->endOfWeek(Carbon::SUNDAY)->toDateString();
                    break;
                case 'week':
                default:
                    $queryStartDate = $referenceDate->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
                    $queryEndDate = $referenceDate->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();
                    $weekNumber = $referenceDate->weekOfYear;
                    $weekStartDate = $queryStartDate->toDateString();
                    $weekEndDate = $queryEndDate->toDateString();
                    break;
            }
        }
        Log::info('Query Start Date: ' . $queryStartDate->toDateTimeString());
        Log::info('Query End Date: ' . $queryEndDate->toDateTimeString());

        $studentSchedules = $student->schedules()
            ->where(function ($query) use ($queryStartDate, $queryEndDate) {
                $query->where('overall_start_date', '<=', $queryEndDate)
                      ->where('overall_end_date', '>=', $queryStartDate);
            })
            ->with('creator:id,name')
            ->get();

        $studentSchedulesData = $studentSchedules->map(function (Schedule $schedule) {
            return [
                'id' => $schedule->id,
                'title' => $schedule->title,
                'description' => $schedule->description,
                'overall_start_time' => $schedule->overall_start_date ? ($schedule->overall_start_date instanceof Carbon ? $schedule->overall_start_date->toDateTimeString() : Carbon::parse($schedule->overall_start_date)->toDateTimeString()) : null,
                'overall_end_time' => $schedule->overall_end_date ? ($schedule->overall_end_date instanceof Carbon ? $schedule->overall_end_date->toDateTimeString() : Carbon::parse($schedule->overall_end_date)->toDateTimeString()) : null,
                'status' => $schedule->status,
                'status_text' => $schedule->status_text,
                'creator_name' => $schedule->creator->name ?? 'Không xác định',
            ];
        });

        $participatingScheduleIds = $studentSchedules->pluck('id');
        if ($participatingScheduleIds->isEmpty()) {
            Log::info('Không có participatingScheduleIds nào để lấy slots.');
        } else {
            Log::info('Participating Schedule IDs để lấy slots: ' . $participatingScheduleIds->implode(', '));
        }

        $eventsData = [];

        if ($participatingScheduleIds->isNotEmpty()) {
            $internshipSlots = InternshipSlot::whereIn('schedule_id', $participatingScheduleIds)
                                        ->with('schedule:id,title')
                                        ->get();
            if ($internshipSlots->isEmpty()) {
                Log::info('KHÔNG TÌM THẤY InternshipSlot nào cho các schedule ID trên.');
            } else {
                Log::info('Tìm thấy ' . $internshipSlots->count() . ' InternshipSlot(s):');
                foreach($internshipSlots as $is) {
                    Log::info('  Slot ID: ' . $is->id . ', Schedule ID: ' . $is->schedule_id . ', DayOfWeek: ' . $is->day_of_week . ', Start: ' . $is->start_time . ', End: ' . $is->end_time . ', Task: ' . $is->task_description);
                }
            }

            foreach ($studentSchedules as $schedule) {
                Log::info('--- Đang xử lý Schedule ID: ' . $schedule->id . ' ---');
                $scheduleOverallStart = Carbon::parse($schedule->overall_start_date);
                $scheduleOverallEnd = Carbon::parse($schedule->overall_end_date);
                $slotsForThisSchedule = $internshipSlots->where('schedule_id', $schedule->id);

                if ($slotsForThisSchedule->isNotEmpty()) {
                    $currentEventDate = $queryStartDate->copy()->max($scheduleOverallStart);
                    $loopEndDate = $queryEndDate->copy()->min($scheduleOverallEnd);

                    Log::info('  Schedule ID ' . $schedule->id . ' - Overall Start: ' . $scheduleOverallStart->toDateTimeString());
                    Log::info('  Schedule ID ' . $schedule->id . ' - Overall End: ' . $scheduleOverallEnd->toDateTimeString());
                    Log::info('  Schedule ID ' . $schedule->id . ' - Calculated currentEventDate (for loop start): ' . $currentEventDate->toDateTimeString());
                    Log::info('  Schedule ID ' . $schedule->id . ' - Calculated loopEndDate (for loop end): ' . $loopEndDate->toDateTimeString());
                    if (!$currentEventDate->lte($loopEndDate)) {
                        Log::warning('  Schedule ID ' . $schedule->id . ' - Vòng lặp WHILE SẼ KHÔNG CHẠY vì currentEventDate (' . $currentEventDate->toDateTimeString() . ') > loopEndDate (' . $loopEndDate->toDateTimeString() . ').');
                    }

                    while ($currentEventDate->lte($loopEndDate)) {
                        $dayOfWeek = $currentEventDate->dayOfWeekIso;
                        Log::info('    Đang kiểm tra ngày: ' . $currentEventDate->toDateString() . ' (DayOfWeekISO: ' . $dayOfWeek . ')');

                        foreach ($slotsForThisSchedule as $slot) {
                            Log::info('      Kiểm tra Slot ID: ' . $slot->id . ' (day_of_week của slot: ' . $slot->day_of_week . ') với $dayOfWeek=' . $dayOfWeek);
                            if ((int)$slot->day_of_week === $dayOfWeek) {
                                Log::info('        => MATCH FOUND! Tạo event cho Slot ID: ' . $slot->id . ' vào ngày ' . $currentEventDate->toDateString());
                                $eventStartTime = Carbon::parse($currentEventDate->toDateString() . ' ' . $slot->start_time);
                                $eventEndTime = Carbon::parse($currentEventDate->toDateString() . ' ' . $slot->end_time);
                                $eventsData[] = [
                                    'id' => $slot->id,
                                    'schedule_id' => $slot->schedule_id,
                                    'title' => $slot->schedule->title ?? $slot->task_description,
                                    'task_description' => $slot->task_description,
                                    'start_time' => $eventStartTime->toDateTimeString(),
                                    'end_time' => $eventEndTime->toDateTimeString(),
                                    'location' => $slot->location,
                                    'is_all_day' => false,
                                ];
                            }
                        }
                        $currentEventDate->addDay();
                    }
                } else {
                    Log::info('  Schedule ID ' . $schedule->id . ' - Không có slots nào được liên kết (trong $internshipSlots đã filter cho schedule_id này).');
                }
            }
        }
        usort($eventsData, function ($a, $b) {
            return strtotime($a['start_time']) - strtotime($b['start_time']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách lịch trình thành công.',
            'data' => [
                'schedules' => $studentSchedulesData,
                'events' => $eventsData,
                'view_info' => [
                    'mode' => $viewMode,
                    'query_start_date' => $queryStartDate->toDateString(),
                    'query_end_date' => $queryEndDate->toDateString(),
                    'week_number' => $weekNumber,
                    'week_start_date' => $weekStartDate,
                    'week_end_date' => $weekEndDate,
                    'reference_date' => $referenceDate->toDateString()
                ]
            ]
        ]);
    }


    public function showEvent(Request $request, $event_id)
    {
        /** @var User $student */
        $student = Auth::user();
        if (!$student) {
            Log::error('API showEvent - Unauthenticated access attempt for event_id: ' . $event_id);
            return response()->json(['success' => false, 'message' => 'Lỗi xác thực người dùng.'], 401);
        }
        Log::info('API showEvent - Called for event_id: ' . $event_id . ' by student ID: ' . $student->id . ' with date: ' . $request->input('date'));

        $eventSlot = InternshipSlot::with([
            'schedule:id,title,description,overall_start_date,overall_end_date,status,created_by,user_id',
            'schedule.creator:id,name',
            'schedule.students:id'
        ])->find($event_id);

        if (!$eventSlot) {
            Log::warning('API showEvent - Event slot not found for ID: ' . $event_id);
            return response()->json(['success' => false, 'message' => 'Không tìm thấy sự kiện.'], 404);
        }
        Log::info('API showEvent - Found eventSlot ID: ' . $eventSlot->id . ' for schedule_id: ' . $eventSlot->schedule_id . '. Task: ' . $eventSlot->task_description);

        $schedule = $eventSlot->schedule;
        if (!$schedule) {
            Log::error('API showEvent - Event slot ID: ' . $eventSlot->id . ' does not have an associated schedule object after eager loading.');
            return response()->json(['success' => false, 'message' => 'Sự kiện không thuộc lịch trình nào hợp lệ.'], 404);
        }
        Log::info('API showEvent - Schedule (ID: ' . $schedule->id . ') - Raw overall_start_date: ' . $schedule->getRawOriginal('overall_start_date'));
        Log::info('API showEvent - Schedule (ID: ' . $schedule->id . ') - Raw overall_end_date: ' . $schedule->getRawOriginal('overall_end_date'));
        Log::info('API showEvent - Schedule (ID: ' . $schedule->id . ') - Attribute overall_start_date: ' . ($schedule->overall_start_date ? ($schedule->overall_start_date instanceof Carbon ? $schedule->overall_start_date->toDateTimeString() : $schedule->overall_start_date) : 'NULL_ATTR'));
        Log::info('API showEvent - Schedule (ID: ' . $schedule->id . ') - Attribute overall_end_date: ' . ($schedule->overall_end_date ? ($schedule->overall_end_date instanceof Carbon ? $schedule->overall_end_date->toDateTimeString() : $schedule->overall_end_date) : 'NULL_ATTR'));

        $studentIdsInSchedule = $schedule->students->pluck('id')->toArray();
        Log::info('API showEvent - Student IDs in this schedule (' . $schedule->id . '): ' . implode(', ', $studentIdsInSchedule ?: ['None']));

        $isStudentInSchedule = $schedule->students->contains($student->id);
        Log::info('API showEvent - Is logged in student (' . $student->id . ') in this schedule? ' . ($isStudentInSchedule ? 'Yes' : 'No'));

        if (!$isStudentInSchedule) {
            Log::warning('API showEvent - Access DENIED for student ' . $student->id . ' to event ' . $event_id . ' (schedule ' . $schedule->id . ')');
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền xem sự kiện này.'], 403);
        }

        $specificDateInput = $request->input('date');
        $concreteStartTime = null;
        $concreteEndTime = null;
        Log::info('API showEvent - specificDateInput: ' . ($specificDateInput ?? 'NULL'));

        if ($specificDateInput) {
            Log::info('API showEvent - Processing specific_date: ' . $specificDateInput);
            try {
                $specificDate = Carbon::parse($specificDateInput);
                Log::info('API showEvent - Parsed specificDate: ' . $specificDate->toDateString() . ', DayOfWeekISO: ' . $specificDate->dayOfWeekIso . ' | EventSlot day_of_week: ' . $eventSlot->day_of_week);

                $scheduleStartDateForRange = $schedule->overall_start_date ? Carbon::parse($schedule->overall_start_date)->startOfDay() : null;
                $scheduleEndDateForRange = $schedule->overall_end_date ? Carbon::parse($schedule->overall_end_date)->endOfDay() : null;

                Log::info('API showEvent - Schedule effective start for range check: ' . ($scheduleStartDateForRange ? $scheduleStartDateForRange->toDateTimeString() : 'NULL'));
                Log::info('API showEvent - Schedule effective end for range check: ' . ($scheduleEndDateForRange ? $scheduleEndDateForRange->toDateTimeString() : 'NULL'));

                $isDayOfWeekMatch = ((int)$specificDate->dayOfWeekIso === (int)$eventSlot->day_of_week);
                $isWithinScheduleRange = ($scheduleStartDateForRange && $scheduleEndDateForRange &&
                                          $specificDate->betweenIncluded($scheduleStartDateForRange, $scheduleEndDateForRange));

                Log::info('API showEvent - Condition check: isDayOfWeekMatch = ' . ($isDayOfWeekMatch ? 'true' : 'false') . ', isWithinScheduleRange = ' . ($isWithinScheduleRange ? 'true' : 'false'));

                if ($isDayOfWeekMatch && $isWithinScheduleRange) {
                    $concreteStartTime = Carbon::parse($specificDate->toDateString() . ' ' . $eventSlot->start_time);
                    $concreteEndTime = Carbon::parse($specificDate->toDateString() . ' ' . $eventSlot->end_time);
                    Log::info('API showEvent - Calculated concrete times for specific_date: ' . ($concreteStartTime ? $concreteStartTime->toDateTimeString() : 'NULL_START') . ' - ' . ($concreteEndTime ? $concreteEndTime->toDateTimeString() : 'NULL_END'));
                } else {
                    Log::info('API showEvent - specific_date did NOT match slot day_of_week OR was outside schedule range (or schedule times were null).');
                }
            } catch (\Exception $e) {
                Log::error('API showEvent - Error parsing specific_date or in logic: ' . $e->getMessage());
            }
        }

        if (!$concreteStartTime) {
            Log::info('API showEvent - concreteStartTime is NULL, calculating firstOccurrenceDate as fallback...');
            $firstOccurrenceDate = null;
            if ($schedule->overall_start_date) {
                $tempDate = Carbon::parse($schedule->overall_start_date)->startOfDay();
                $scheduleOverallEndDate = $schedule->overall_end_date ? Carbon::parse($schedule->overall_end_date)->endOfDay() : $tempDate->copy()->addYear();
                Log::info('API showEvent - First Occurrence: loop start_time: '.$tempDate->toDateString().', scheduleOverallEndDate: '.$scheduleOverallEndDate->toDateString());

                while ($tempDate->lte($scheduleOverallEndDate)) {
                    Log::info('API showEvent - First Occurrence: checking tempDate: '.$tempDate->toDateString().' (DayOfWeekISO: '.$tempDate->dayOfWeekIso.') vs slot day_of_week: '.$eventSlot->day_of_week);
                    if ($tempDate->dayOfWeekIso == $eventSlot->day_of_week) {
                        $firstOccurrenceDate = $tempDate->copy();
                        Log::info('API showEvent - First Occurrence: MATCH FOUND on '.$firstOccurrenceDate->toDateString());
                        break;
                    }
                    $tempDate->addDay();
                }
                Log::info('API showEvent - Final firstOccurrenceDate: ' . ($firstOccurrenceDate ? $firstOccurrenceDate->toDateString() : 'Not found'));
            } else {
                 Log::info('API showEvent - Schedule has no overall_start_date, cannot calculate firstOccurrenceDate for fallback.');
            }
            if ($firstOccurrenceDate) {
                $concreteStartTime = Carbon::parse($firstOccurrenceDate->toDateString() . ' ' . $eventSlot->start_time);
                $concreteEndTime = Carbon::parse($firstOccurrenceDate->toDateString() . ' ' . $eventSlot->end_time);
            }
            Log::info('API showEvent - Fallback concreteStartTime: ' . ($concreteStartTime ? $concreteStartTime->toDateTimeString() : 'STILL NULL AFTER FALLBACK'));
        }

        $responseData = $eventSlot->toArray();
        $responseData['schedule_id'] = $schedule->id;
        $responseData['schedule_title'] = $schedule->title ?? 'N/A';
        $responseData['start_time'] = $concreteStartTime ? $concreteStartTime->toDateTimeString() : null;
        $responseData['end_time'] = $concreteEndTime ? $concreteEndTime->toDateTimeString() : null;
        $responseData['slot_start_time'] = $eventSlot->start_time;
        $responseData['slot_end_time'] = $eventSlot->end_time;
        $responseData['creator_name'] = $schedule->creator->name ?? 'Không xác định';
        $responseData['overall_schedule_description'] = $schedule->description ?? '';
        unset($responseData['schedule']); // Xóa object schedule lồng nhau nếu có

        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết sự kiện thành công.',
            'data' => $responseData
        ]);
    }
}