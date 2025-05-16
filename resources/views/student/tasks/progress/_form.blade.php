{{-- resources/views/student/tasks/progress/_form.blade.php --}}
@csrf
<div class="mb-3">
    <label for="notes" class="form-label">{{ __('Ghi chú tiến độ') }} <span class="text-danger">*</span></label>
    <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="5" required>{{ old('notes', $taskProgress->notes) }}</textarea>
    @error('notes')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row align-items-center">
    <div class="col-md-6 mb-3">
        {{-- === THAY THẾ INPUT NUMBER BẰNG SELECT CHO PHẦN TRĂM HOÀN THÀNH === --}}
        <label for="progress_percentage_select" class="form-label">{{ __('Phần trăm hoàn thành (%)') }}</label>
        <select name="progress_percentage" id="progress_percentage_select" class="form-select @error('progress_percentage') is-invalid @enderror">
            <option value="">{{ __('-- Chọn tiến độ (nếu có) --') }}</option> {{-- Cho phép giá trị rỗng/null --}}

            @php
                $currentPercentageValue = old('progress_percentage', $taskProgress->progress_percentage);
                // Đảm bảo so sánh kiểu string nếu $currentPercentageValue không phải là null
                if (!is_null($currentPercentageValue)) {
                    $currentPercentageValue = (string)$currentPercentageValue;
                }
            @endphp

            {{-- Các bước nhảy bạn muốn cho phần trăm --}}
            @foreach ([0, 10, 25, 50, 75, 90, 100] as $value)
                <option value="{{ $value }}" {{ $currentPercentageValue === (string)$value ? 'selected' : '' }}>
                    {{ $value }}%
                </option>
            @endforeach
        </select>
        @error('progress_percentage')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        {{-- Thanh tiến trình hiển thị đã bị loại bỏ vì đã dùng select. --}}
        {{-- Nếu bạn vẫn muốn hiển thị progress bar dựa trên giá trị select, cần thêm JS để lắng nghe sự kiện 'change' của select. --}}
        {{-- Ví dụ:
        <div class="progress mt-2" style="height: 20px;">
            <div id="form_progress_bar_display_from_select" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
        </div>
        --}}
    </div>
    <div class="col-md-6 mb-3">
        <label for="submitted_at" class="form-label">{{ __('Ngày cập nhật') }}</label>
        <input type="date" name="submitted_at" id="submitted_at" class="form-control @error('submitted_at') is-invalid @enderror"
               value="{{ old('submitted_at', $taskProgress->submitted_at ? \Carbon\Carbon::parse($taskProgress->submitted_at)->format('Y-m-d') : now()->format('Y-m-d')) }}">
        @error('submitted_at')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mt-3">
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i> {{ $taskProgress->exists ? __('Cập nhật Tiến độ') : __('Thêm Tiến độ') }}
    </button>
    <a href="{{ route('student.tasks.show', $task) }}" class="btn btn-secondary">{{ __('Hủy') }}</a>
</div>

{{-- XÓA HOẶC COMMENT OUT ĐOẠN SCRIPT CŨ CHO INPUT NUMBER VÀ PROGRESS BAR --}}
{{-- @push('scripts')
<script>
    // document.addEventListener('DOMContentLoaded', function () {
    //     const percentageInput = document.getElementById('progress_percentage'); // ID này không còn
    //     const progressBarDisplay = document.getElementById('progress_bar_display'); // ID này cũng không còn (trừ khi bạn thêm lại progress bar cho select)

    //     function updateProgressBar() {
    //         if (!percentageInput || !progressBarDisplay) return;
    //         let value = parseInt(percentageInput.value);
    //         if (isNaN(value) || value < 0) {
    //             value = 0;
    //         }
    //         if (value > 100) {
    //             value = 100;
    //         }
    //         progressBarDisplay.style.width = value + '%';
    //         progressBarDisplay.textContent = value + '%';
    //         progressBarDisplay.setAttribute('aria-valuenow', value);
    //     }

    //     if (percentageInput) {
    //         percentageInput.addEventListener('input', updateProgressBar);
    //         updateProgressBar();
    //     }
    // });
</script>
@endpush --}}

{{-- Nếu bạn muốn thêm progress bar cập nhật theo select, bạn sẽ cần JS mới ở đây. Ví dụ: --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const percentageSelect = document.getElementById('progress_percentage_select');
        // Giả sử bạn thêm lại progress bar với ID 'form_progress_bar_display_from_select'
        // const progressBar = document.getElementById('form_progress_bar_display_from_select');

        // function updateProgressBarFromSelect() {
        //     if (!percentageSelect || !progressBar) return;
        //     let value = parseInt(percentageSelect.value, 10);
        //     if (isNaN(value)) { // Nếu chọn option rỗng
        //         value = 0; // Hoặc giá trị mặc định bạn muốn cho progress bar
        //     }

        //     progressBar.style.width = value + '%';
        //     progressBar.textContent = value + '%';
        //     progressBar.setAttribute('aria-valuenow', value);

        //     // Cập nhật màu sắc nếu muốn (tương tự logic trước)
        //     if (value == 100) {
        //         progressBar.classList.remove('bg-primary', 'bg-warning');
        //         progressBar.classList.add('bg-success');
        //     } else if (value >= 50) {
        //         progressBar.classList.remove('bg-success', 'bg-warning');
        //         progressBar.classList.add('bg-primary');
        //     } else if (value > 0) {
        //         progressBar.classList.remove('bg-success', 'bg-primary');
        //         progressBar.classList.add('bg-warning');
        //     } else {
        //         progressBar.classList.remove('bg-success', 'bg-warning');
        //         progressBar.classList.add('bg-primary');
        //     }
        // }

        // if (percentageSelect) {
        //     percentageSelect.addEventListener('change', updateProgressBarFromSelect);
        //     // Cập nhật lần đầu nếu bạn muốn progress bar hiển thị giá trị mặc định của select
        //     // updateProgressBarFromSelect();
        // }
    });
</script>
@endpush