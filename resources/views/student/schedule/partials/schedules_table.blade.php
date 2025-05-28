{{-- resources/views/student/schedule/partials/schedules_table.blade.php --}}
<div class="table-responsive">
    <table class="table table-bordered table-hover" id="dataTableSchedules" width="100%" cellspacing="0">
        <thead class="thead-light">
            <tr>
                <th>TIÊU ĐỀ</th>
                <th style="width: 30%;">MÔ TẢ</th>
                <th>BẮT ĐẦU</th>
                <th>KẾT THÚC</th>
                <th>TRẠNG THÁI</th>
                <th>NGƯỜI TẠO</th>
                <th style="width: 180px;" class="text-center">HÀNH ĐỘNG</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($schedules as $schedule_item)
                <tr>
                    <td>{{ $schedule_item->title }}</td>
                    <td>{{ Str::limit($schedule_item->description ?? '-', 70) }}</td>
                    <td>{{ $schedule_item->start_time ? \Carbon\Carbon::parse($schedule_item->start_time)->format('d/m/Y H:i') : 'N/A' }}</td>
                    <td>{{ $schedule_item->end_time ? \Carbon\Carbon::parse($schedule_item->end_time)->format('d/m/Y H:i') : 'N/A' }}</td>
                    <td>
                        <span class="badge badge-{{ $schedule_item->status_badge_class ?? 'secondary' }}">{{ $schedule_item->status_text }}</span>
                    </td>
                    <td>{{ $schedule_item->creator->name ?? 'Không xác định' }}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-info btn-sm mb-1 view-schedule-detail"
                                title="Xem chi tiết lịch trình"
                                data-url="{{ route('student.schedules.detail', $schedule_item->id) }}"
                                data-bs-toggle="modal" data-bs-target="#scheduleDetailModal">
                            <i class="fas fa-eye fa-fw"></i> Xem
                        </button>

                        @if(in_array($schedule_item->status, [\App\Models\Schedule::STATUS_SCHEDULED, \App\Models\Schedule::STATUS_CHANGE_APPROVED, \App\Models\Schedule::STATUS_CHANGE_REJECTED]) && !$schedule_item->getHasPassedAttribute())
                            <form action="{{ route('student.schedule.requestChange', $schedule_item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn gửi yêu cầu thay đổi cho lịch trình này?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-warning mb-1" title="Yêu cầu thay đổi lịch trình này">
                                    <i class="fas fa-edit fa-fw"></i> YC Đổi
                                </button>
                            </form>
                        @elseif($schedule_item->status === \App\Models\Schedule::STATUS_PENDING_CHANGE)
                            <span class="badge badge-warning" title="Yêu cầu thay đổi của bạn đang chờ Admin duyệt">
                                <i class="fas fa-clock fa-fw"></i> Đang chờ
                            </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Không có lịch trình nào cho tuần này.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if(isset($schedules) && $schedules instanceof \Illuminate\Pagination\LengthAwarePaginator && $schedules->hasPages())
    <div class="mt-3 d-flex justify-content-center">
        {{-- AJAX pagination sẽ cần xử lý riêng nếu bạn muốn phân trang cho kết quả AJAX --}}
        {{-- Hiện tại, chúng ta giả định không phân trang qua AJAX cho đơn giản --}}
        {{-- Nếu controller trả về HTML có phân trang, nó sẽ hoạt động nhưng load lại cả trang --}}
        {{-- Để phân trang AJAX, bạn cần sửa loadSchedulesForWeek để xử lý link phân trang --}}
        {{-- $schedules->appends(request()->except('page'))->links() --}}
    </div>
@endif