{{-- resources/views/admin/schedules/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Tạo Lịch Thực Tập Mới')

@section('content')

    {{-- Page Heading --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tạo Lịch Thực Tập Mới</h1>
         {{-- Nút quay lại danh sách - ĐỔI THÀNH btn-primary --}}
         <a href="{{ route('admin.schedules.index', request()->query()) }}" class="btn btn-sm btn-primary shadow-sm text-white">
            <i class="fas fa-arrow-left fa-sm text-white"></i> Quay lại Danh sách
        </a>
    </div>

    {{-- Card chứa form --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Thông tin Lịch trình</h6>
        </div>
        <div class="card-body">
            {{-- Hiển thị lỗi validation nếu có --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.schedules.store') }}" method="POST">
                @csrf

                {{-- Sử dụng Bootstrap grid cho layout form --}}
                <div class="row">
                    {{-- Cột chọn Sinh viên --}}
                    <div class="col-md-6 mb-3">
                        <label for="user_id" class="form-label">Sinh viên <span class="text-danger">*</span></label>
                        <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                            <option value="">-- Chọn sinh viên --</option>
                            @isset($students)
                                @foreach ($students as $student)
                                    <option value="{{ $student->id }}" {{ old('user_id') == $student->id ? 'selected' : '' }}>
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
                        <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Hàng nhập Mô tả --}}
                <div class="mb-3">
                    <label for="description" class="form-label">Mô tả</label>
                    <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Hàng nhập Thời gian Bắt đầu và Kết thúc TỔNG THỂ --}}
                {{-- LƯU Ý: Nếu bạn đã đổi tên cột trong DB và Model Schedule thành overall_start_date và overall_end_date --}}
                {{-- thì hãy cập nhật name của các input này cho phù hợp --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="start_time" class="form-label">Thời gian bắt đầu tổng thể <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="start_time" id="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time') }}" required>
                        {{-- Ví dụ nếu đã đổi: name="overall_start_date" id="overall_start_date" --}}
                        @error('start_time') {{-- Hoặc @error('overall_start_date') --}}
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="end_time" class="form-label">Thời gian kết thúc tổng thể <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="end_time" id="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time') }}" required>
                        {{-- Ví dụ nếu đã đổi: name="overall_end_date" id="overall_end_date" --}}
                        @error('end_time') {{-- Hoặc @error('overall_end_date') --}}
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- ========================================================================= --}}
                {{-- PHẦN CẬP NHẬT - START: CHI TIẾT BUỔI THỰC TẬP HÀNG TUẦN                 --}}
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
                            {{-- Hiển thị lại các slot đã nhập nếu có lỗi validation --}}
                            @if(old('slots'))
                                @foreach(old('slots') as $index => $slot)
                                <div class="row schedule-slot align-items-center border rounded p-3 mb-3" data-index="{{ $index }}">
                                    <div class="col-md-3 mb-2 mb-md-0">
                                        <label for="slots_{{ $index }}_day_of_week" class="form-label small">Ngày trong tuần <span class="text-danger">*</span></label>
                                        <select name="slots[{{ $index }}][day_of_week]" id="slots_{{ $index }}_day_of_week" class="form-select form-select-sm @error('slots.'.$index.'.day_of_week') is-invalid @enderror">
                                            <option value="">-- Chọn ngày --</option>
                                            <option value="1" {{ ($slot['day_of_week'] ?? '') == '1' ? 'selected' : '' }}>Thứ Hai</option>
                                            <option value="2" {{ ($slot['day_of_week'] ?? '') == '2' ? 'selected' : '' }}>Thứ Ba</option>
                                            <option value="3" {{ ($slot['day_of_week'] ?? '') == '3' ? 'selected' : '' }}>Thứ Tư</option>
                                            <option value="4" {{ ($slot['day_of_week'] ?? '') == '4' ? 'selected' : '' }}>Thứ Năm</option>
                                            <option value="5" {{ ($slot['day_of_week'] ?? '') == '5' ? 'selected' : '' }}>Thứ Sáu</option>
                                            <option value="6" {{ ($slot['day_of_week'] ?? '') == '6' ? 'selected' : '' }}>Thứ Bảy</option>
                                            <option value="7" {{ ($slot['day_of_week'] ?? '') == '7' ? 'selected' : '' }}>Chủ Nhật</option>
                                        </select>
                                        @error('slots.'.$index.'.day_of_week') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-2 mb-2 mb-md-0">
                                        <label for="slots_{{ $index }}_start_time" class="form-label small">Giờ bắt đầu <span class="text-danger">*</span></label>
                                        <input type="time" name="slots[{{ $index }}][start_time]" id="slots_{{ $index }}_start_time" value="{{ $slot['start_time'] ?? '' }}" class="form-control form-control-sm @error('slots.'.$index.'.start_time') is-invalid @enderror">
                                        @error('slots.'.$index.'.start_time') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-2 mb-2 mb-md-0">
                                        <label for="slots_{{ $index }}_end_time" class="form-label small">Giờ kết thúc <span class="text-danger">*</span></label>
                                        <input type="time" name="slots[{{ $index }}][end_time]" id="slots_{{ $index }}_end_time" value="{{ $slot['end_time'] ?? '' }}" class="form-control form-control-sm @error('slots.'.$index.'.end_time') is-invalid @enderror">
                                        @error('slots.'.$index.'.end_time') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 mb-2 mb-md-0">
                                        <label for="slots_{{ $index }}_task_description" class="form-label small">Mô tả công việc</label>
                                        <input type="text" name="slots[{{ $index }}][task_description]" id="slots_{{ $index }}_task_description" value="{{ $slot['task_description'] ?? '' }}" class="form-control form-control-sm @error('slots.'.$index.'.task_description') is-invalid @enderror">
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
                                    <option value="1">Thứ Hai</option>
                                    <option value="2">Thứ Ba</option>
                                    <option value="3">Thứ Tư</option>
                                    <option value="4">Thứ Năm</option>
                                    <option value="5">Thứ Sáu</option>
                                    <option value="6">Thứ Bảy</option>
                                    <option value="7">Chủ Nhật</option>
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


                {{-- Nút Submit và Hủy --}}
                <div class="mt-4">
                    {{-- Nút Tạo mới giữ nguyên màu xanh lá (success) --}}
                    <button type="submit" class="btn btn-success btn-icon-split">
                        <span class="icon text-white">
                            <i class="fas fa-check"></i>
                        </span>
                        <span class="text">Tạo mới</span>
                    </button>
                    {{-- Nút Hủy - ĐỔI THÀNH btn-danger --}}
                    <a href="{{ route('admin.schedules.index') }}" class="btn btn-danger ml-2 text-white">
                        <i class="fas fa-times fa-sm text-white mr-1"></i> {{-- Thêm icon nếu muốn --}}
                        Hủy
                    </a>
                </div>

            </form>
        </div> {{-- End card-body --}}
    </div> {{-- End card --}}

@endsection

{{-- ========================================================================= --}}
{{-- PHẦN CẬP NHẬT - START: JAVASCRIPT CHO SLOTS                             --}}
{{-- ========================================================================= --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('schedule_slots_container');
    const addButton = document.getElementById('add_slot_btn');
    const templateNode = document.getElementById('schedule_slot_template');

    // Xác định slotIndex ban đầu dựa trên số lượng slot đã có (từ old input)
    let slotIndex = container.querySelectorAll('.schedule-slot').length;
    // Hoặc nếu bạn muốn index không bị trùng lặp tuyệt đối (ví dụ sau khi xóa):
    // let slotIndex = container.querySelectorAll('.schedule-slot').length > 0
    //                ? Math.max(0, ...Array.from(container.querySelectorAll('.schedule-slot')).map(el => parseInt(el.dataset.index || -1))) + 1
    //                : 0;


    if (addButton && templateNode) {
        addButton.addEventListener('click', function () {
            addSlotRow();
        });
    } else {
        console.error('Không tìm thấy nút "Thêm Buổi" hoặc template cho slot.');
        return;
    }

    function addSlotRow(slotData = null) { // slotData dùng cho việc load slot đã có (trang edit)
        const newSlot = templateNode.cloneNode(true);
        newSlot.style.display = 'flex'; // Hoặc 'grid' hoặc 'block' tùy thuộc vào class .row và CSS của bạn
        newSlot.removeAttribute('id');
        newSlot.classList.add('added-dynamic-slot'); // Để phân biệt với slot từ old()
        newSlot.dataset.index = slotIndex; // Lưu trữ index hiện tại

        // Cập nhật name và id cho các input, select trong slot mới
        newSlot.querySelectorAll('select, input').forEach(element => {
            const nameTemplate = element.dataset.name;
            const idTemplate = element.dataset.idTemplate; // Lấy template cho ID
            const labelForTemplate = element.closest('.col-md-2, .col-md-3, .col-md-4')?.querySelector('label[data-for-template]');


            if (nameTemplate) {
                element.name = nameTemplate.replace('REPLACE_INDEX', slotIndex);
            }
            if (idTemplate) {
                const newId = idTemplate.replace('REPLACE_INDEX', slotIndex);
                element.id = newId;
                // Cập nhật 'for' của label nếu có
                if (labelForTemplate && labelForTemplate.dataset.forTemplate === element.classList[0].split('-')[0]) { // Đơn giản hóa việc tìm label
                     labelForTemplate.setAttribute('for', newId);
                }
            }


            element.disabled = false;

            // Điền dữ liệu nếu có (dùng cho trang edit sau này)
            if(slotData){
                if(element.classList.contains('day-of-week-select')) element.value = slotData.day_of_week || '';
                if(element.classList.contains('start-time-input')) element.value = slotData.start_time || '';
                if(element.classList.contains('end-time-input')) element.value = slotData.end_time || '';
                if(element.classList.contains('task-description-input')) element.value = slotData.task_description || '';
            }
        });

        // Thêm sự kiện cho nút xóa
        const removeButton = newSlot.querySelector('.remove-slot-btn');
        if (removeButton) {
            removeButton.addEventListener('click', function() {
                newSlot.remove();
                // Bạn có thể muốn cập nhật lại slotIndex hoặc re-index các slot còn lại ở đây nếu cần
            });
        }

        container.appendChild(newSlot);
        slotIndex++;
    }

    // Gắn sự kiện xóa cho các nút xóa của slot đã có sẵn (từ old input)
    container.querySelectorAll('.schedule-slot:not(#schedule_slot_template) .remove-slot-btn').forEach(button => {
        button.addEventListener('click', function() {
            button.closest('.schedule-slot').remove();
        });
    });

    // (Tùy chọn) Nếu đây là trang edit, bạn sẽ gọi addSlotRow ở đây với dữ liệu đã có
    // Ví dụ:
    // let existingScheduleSlots = @json($schedule->slots ?? []); // Giả sử $schedule và slots của nó được truyền vào view
    // if (existingScheduleSlots.length > 0 && !document.querySelector('.schedule-slot[data-index="0"]')) { // Chỉ chạy nếu không có old input
    //     existingScheduleSlots.forEach(slot => addSlotRow(slot));
    // }
});
</script>
@endpush
{{-- ========================================================================= --}}
{{-- PHẦN CẬP NHẬT - END                                                       --}}
{{-- ========================================================================= --}}