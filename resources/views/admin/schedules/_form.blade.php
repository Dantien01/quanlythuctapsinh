{{-- resources/views/admin/schedules/_form.blade.php --}}

{{-- Sử dụng Bootstrap grid cho layout form --}}
<div class="row">
    {{-- Cột chọn Sinh viên --}}
    <div class="col-md-6 mb-3">
        <label for="user_id" class="form-label">Sinh viên <span class="text-danger">*</span></label>
        <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
            <option value="">-- Chọn sinh viên --</option>
            @isset($students)
                @foreach ($students as $student)
                    {{-- Kiểm tra xem schedule có tồn tại và có user_id không (cho form edit) --}}
                    <option value="{{ $student->id }}" {{ old('user_id', $schedule->user_id ?? null) == $student->id ? 'selected' : '' }}>
                        {{ $student->name }} ({{ $student->mssv ?? $student->email }})
                    </option>
                @endforeach
            @endisset
        </select>
        @error('user_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Cột nhập Tiêu đề --}}
    <div class="col-md-6 mb-3">
        <label for="title" class="form-label">Tiêu đề <span class="text-danger">*</span></label>
        {{-- Lấy giá trị cũ từ validation hoặc từ $schedule (cho form edit) --}}
        <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $schedule->title ?? '') }}" required>
        @error('title')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

{{-- Hàng nhập Mô tả --}}
<div class="mb-3">
    <label for="description" class="form-label">Mô tả</label>
    {{-- Lấy giá trị cũ từ validation hoặc từ $schedule (cho form edit) --}}
    <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $schedule->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Hàng nhập Thời gian Bắt đầu và Kết thúc TỔNG THỂ --}}
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="start_time" class="form-label">Thời gian bắt đầu tổng thể <span class="text-danger">*</span></label>
        @php
            // Nếu có old('start_time'), dùng nó.
            // Nếu không, nếu đang edit ($schedule->start_time tồn tại), dùng giá trị đó.
            // Nếu không (đang create mới), không đặt giá trị ban đầu (trình duyệt sẽ tự xử lý hoặc bạn có thể đặt now())
            $startTimeValue = old('start_time', isset($schedule->start_time) ? \Carbon\Carbon::parse($schedule->start_time)->format('Y-m-d\TH:i') : '');
        @endphp
        <input type="datetime-local" name="start_time" id="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ $startTimeValue }}" required>
        @error('start_time')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="end_time" class="form-label">Thời gian kết thúc tổng thể <span class="text-danger">*</span></label>
         @php
            $endTimeValue = old('end_time', isset($schedule->end_time) ? \Carbon\Carbon::parse($schedule->end_time)->format('Y-m-d\TH:i') : '');
         @endphp
        <input type="datetime-local" name="end_time" id="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ $endTimeValue }}" required>
        @error('end_time')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

{{-- ========================================================================= --}}
{{-- PHẦN CẬP NHẬT - START: CHI TIẾT BUỔI THỰC TẬP HÀNG TUẦN                   --}}
{{-- ========================================================================= --}}
<div class="card shadow-sm mb-4 mt-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Chi Tiết Buổi Thực Tập Hàng Tuần</h6>
        <button type="button" id="add_slot_btn" class="btn btn-success btn-sm">
            <i class="fas fa-plus fa-sm"></i> Thêm Buổi
        </button>
    </div>
    <div class="card-body">
        <div id="schedule_slots_container">
            {{-- Hiển thị các slot đã có (cho edit) hoặc slot từ old input (khi create/edit lỗi) --}}
            @php
                $currentSlots = old('slots'); // Ưu tiên old input
                if (is_null($currentSlots) && isset($schedule) && $schedule->relationLoaded('slots') && $schedule->slots->isNotEmpty()) {
                    // Nếu không có old input VÀ đang edit VÀ schedule có slots đã load
                    $currentSlots = $schedule->slots->map(function($slotItem) { // Đổi tên biến $slot thành $slotItem để tránh xung đột
                        return [
                            // 'id' => $slotItem->id, // Giữ ID nếu cần cho logic update phức tạp hơn
                            'day_of_week' => $slotItem->day_of_week,
                            'start_time' => \Carbon\Carbon::parse($slotItem->start_time)->format('H:i'),
                            'end_time' => \Carbon\Carbon::parse($slotItem->end_time)->format('H:i'),
                            'task_description' => $slotItem->task_description,
                        ];
                    })->all();
                } elseif (is_null($currentSlots)) {
                    // Nếu là create mới và không có old input
                    $currentSlots = [];
                }
            @endphp

            @if(!empty($currentSlots))
                @foreach($currentSlots as $index => $slotItem) {{-- Đổi tên biến $slot thành $slotItem --}}
                <div class="row schedule-slot align-items-center border rounded p-3 mb-3" data-index="{{ $index }}">
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label for="slots_{{ $index }}_day_of_week" class="form-label small">Ngày trong tuần <span class="text-danger">*</span></label>
                        <select name="slots[{{ $index }}][day_of_week]" id="slots_{{ $index }}_day_of_week" class="form-select form-select-sm @error('slots.'.$index.'.day_of_week') is-invalid @enderror">
                            <option value="">-- Chọn ngày --</option>
                            @for ($i = 1; $i <= 7; $i++)
                                <option value="{{ $i }}" {{ ($slotItem['day_of_week'] ?? '') == $i ? 'selected' : '' }}>
                                    {{ \App\Models\Schedule::getDayName($i) }}
                                </option>
                            @endfor
                        </select>
                        @error('slots.'.$index.'.day_of_week') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label for="slots_{{ $index }}_start_time" class="form-label small">Giờ bắt đầu <span class="text-danger">*</span></label>
                        <input type="time" name="slots[{{ $index }}][start_time]" id="slots_{{ $index }}_start_time" value="{{ $slotItem['start_time'] ?? '' }}" class="form-control form-control-sm @error('slots.'.$index.'.start_time') is-invalid @enderror">
                        @error('slots.'.$index.'.start_time') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label for="slots_{{ $index }}_end_time" class="form-label small">Giờ kết thúc <span class="text-danger">*</span></label>
                        <input type="time" name="slots[{{ $index }}][end_time]" id="slots_{{ $index }}_end_time" value="{{ $slotItem['end_time'] ?? '' }}" class="form-control form-control-sm @error('slots.'.$index.'.end_time') is-invalid @enderror">
                        @error('slots.'.$index.'.end_time') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label for="slots_{{ $index }}_task_description" class="form-label small">Mô tả công việc</label>
                        <input type="text" name="slots[{{ $index }}][task_description]" id="slots_{{ $index }}_task_description" value="{{ $slotItem['task_description'] ?? '' }}" class="form-control form-control-sm @error('slots.'.$index.'.task_description') is-invalid @enderror">
                        @error('slots.'.$index.'.task_description') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-1 text-center">
                        <button type="button" class="btn btn-danger btn-sm remove-slot-btn mt-3 mt-md-0"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
                @endforeach
            @endif
        </div>
        {{-- Template ẩn để clone slot mới --}}
        <div id="schedule_slot_template" class="row schedule-slot align-items-center border rounded p-3 mb-3" style="display: none;">
            <div class="col-md-3 mb-2 mb-md-0">
                <label data-for-template="day_of_week" class="form-label small">Ngày trong tuần <span class="text-danger">*</span></label>
                <select data-name="slots[REPLACE_INDEX][day_of_week]" data-id-template="slots_REPLACE_INDEX_day_of_week" class="form-select form-select-sm day-of-week-select">
                    <option value="">-- Chọn ngày --</option>
                    @for ($i = 1; $i <= 7; $i++)
                        <option value="{{ $i }}">{{ \App\Models\Schedule::getDayName($i) }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2 mb-2 mb-md-0">
                <label data-for-template="start_time" class="form-label small">Giờ bắt đầu <span class="text-danger">*</span></label>
                <input type="time" data-name="slots[REPLACE_INDEX][start_time]" data-id-template="slots_REPLACE_INDEX_start_time" class="form-control form-control-sm start-time-input">
            </div>
            <div class="col-md-2 mb-2 mb-md-0">
                <label data-for-template="end_time" class="form-label small">Giờ kết thúc <span class="text-danger">*</span></label>
                <input type="time" data-name="slots[REPLACE_INDEX][end_time]" data-id-template="slots_REPLACE_INDEX_end_time" class="form-control form-control-sm end-time-input">
            </div>
            <div class="col-md-4 mb-2 mb-md-0">
                <label data-for-template="task_description" class="form-label small">Mô tả công việc</label>
                <input type="text" data-name="slots[REPLACE_INDEX][task_description]" data-id-template="slots_REPLACE_INDEX_task_description" class="form-control form-control-sm task-description-input">
            </div>
            <div class="col-md-1 text-center">
                <button type="button" class="btn btn-danger btn-sm remove-slot-btn mt-3 mt-md-0"><i class="fas fa-trash"></i></button>
            </div>
        </div>
        @error('slots') {{-- Lỗi chung cho mảng slots, ví dụ khi slots là bắt buộc nhưng không có --}}
            <div class="text-danger small mt-2">{{ $message }}</div>
         @enderror
    </div>
</div>
{{-- ========================================================================= --}}
{{-- PHẦN CẬP NHẬT - END                                                       --}}
{{-- ========================================================================= --}}