{{-- resources/views/admin/schedules/edit.blade.php --}}
@extends('layouts.admin') {{-- Kế thừa từ layout admin mới --}}
@section('title', 'Chỉnh Sửa Lịch Thực Tập')
@section('content')
{{-- Page Heading --}}
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Chỉnh Sửa Lịch Thực Tập</h1>
     {{-- Nút quay lại danh sách --}}
     <a href="{{ route('admin.schedules.index') }}" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại Danh sách
    </a>
</div>

{{-- Card chứa form --}}
<div class="card shadow mb-4">
    <div class="card-header py-3">
         {{-- Hiển thị tiêu đề lịch đang sửa --}}
        <h6 class="m-0 font-weight-bold text-primary">Chỉnh sửa: {{ $schedule->title }}</h6>
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

        {{-- Form trỏ đến route update, sử dụng method PUT --}}
        <form action="{{ route('admin.schedules.update', $schedule) }}" method="POST">
            @csrf {{-- CSRF Token --}}
            @method('PUT') {{-- Giả mạo phương thức PUT --}}

            {{-- Include form dùng chung, truyền các biến cần thiết --}}
            {{-- Đảm bảo controller đã truyền $schedule và $students --}}
            {{-- và _form.blade.php đã có phần slots --}}
            @include('admin.schedules._form', ['schedule' => $schedule, 'students' => $students])

            {{-- Nút Submit và Hủy --}}
            <div class="mt-4">
                <button type="submit" class="btn btn-warning btn-icon-split"> {{-- Màu vàng cho cập nhật --}}
                    <span class="icon text-white-50">
                        <i class="fas fa-save"></i>
                    </span>
                    <span class="text">Cập nhật</span>
                </button>
                <a href="{{ route('admin.schedules.index') }}" class="btn btn-secondary ml-2">Hủy</a>
            </div>

        </form>
    </div> {{-- End card-body --}}
</div> {{-- End card --}}

{{-- ========================================================================= --}}
{{-- PHẦN CẬP NHẬT - START: JAVASCRIPT CHO SLOTS (GIỐNG HỆT CREATE.BLADE.PHP) --}}
{{-- ========================================================================= --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('schedule_slots_container');
    const addButton = document.getElementById('add_slot_btn');
    const templateNode = document.getElementById('schedule_slot_template');

    // Nếu các element không tồn tại trên trang này (ví dụ _form không được include đúng cách) thì không chạy JS
    if (!container || !addButton || !templateNode) {
        // console.warn('Một hoặc nhiều element cho việc quản lý slot không được tìm thấy.');
        return;
    }

    // Xác định slotIndex ban đầu dựa trên số lượng slot đã render từ Blade
    // (bao gồm cả slot từ old() hoặc từ $schedule->slots)
    let slotIndex = container.querySelectorAll('.schedule-slot:not(#schedule_slot_template)').length;

    addButton.addEventListener('click', function () {
        addSlotRow();
    });

    function addSlotRow() {
        const newSlot = templateNode.cloneNode(true);
        newSlot.style.display = 'flex'; // Hoặc 'grid' hoặc 'block' tùy theo CSS của bạn
        newSlot.removeAttribute('id');
        newSlot.classList.add('added-dynamic-slot');
        newSlot.dataset.index = slotIndex; // Gán data-index cho slot mới

        newSlot.querySelectorAll('select, input').forEach(element => {
            const nameTemplate = element.dataset.name;
            const idTemplate = element.dataset.idTemplate;
            const labelForTemplate = element.closest('.col-md-2, .col-md-3, .col-md-4')?.querySelector('label[data-for-template]');

            if (nameTemplate) {
                element.name = nameTemplate.replace('REPLACE_INDEX', slotIndex);
            }
            if (idTemplate) {
                const newId = idTemplate.replace('REPLACE_INDEX', slotIndex);
                element.id = newId;
                if (labelForTemplate && labelForTemplate.dataset.forTemplate === element.classList[0].split('-')[0]) { // Đơn giản hóa việc tìm label
                     labelForTemplate.setAttribute('for', newId);
                }
            }
            element.disabled = false;
        });

        const removeButton = newSlot.querySelector('.remove-slot-btn');
        if (removeButton) {
            removeButton.addEventListener('click', function() {
                newSlot.remove();
                // Nếu bạn cần re-index các slot còn lại, logic đó sẽ ở đây
            });
        }
        container.appendChild(newSlot);
        slotIndex++;
    }

    // Gắn sự kiện xóa cho các nút xóa của slot đã có sẵn (từ Blade)
    container.querySelectorAll('.schedule-slot:not(#schedule_slot_template) .remove-slot-btn').forEach(button => {
        button.addEventListener('click', function() {
            button.closest('.schedule-slot').remove();
        });
    });
});
</script>
@endpush
{{-- ========================================================================= --}}
{{-- PHẦN CẬP NHẬT - END                                                       --}}
{{-- ========================================================================= --}}
@endsection {{-- Kết thúc @section('content') --}}