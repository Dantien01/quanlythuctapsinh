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

{{-- Hàng nhập Thời gian Bắt đầu và Kết thúc --}}
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="start_time" class="form-label">Thời gian bắt đầu <span class="text-danger">*</span></label>
        {{-- Lấy giá trị cũ, định dạng lại cho input datetime-local --}}
        @php
            $startTimeValue = old('start_time', $schedule->start_time ?? null);
            $startTimeFormatted = $startTimeValue ? \Carbon\Carbon::parse($startTimeValue)->format('Y-m-d\TH:i') : '';
        @endphp
        <input type="datetime-local" name="start_time" id="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ $startTimeFormatted }}" required>
        @error('start_time')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="end_time" class="form-label">Thời gian kết thúc <span class="text-danger">*</span></label>
         {{-- Lấy giá trị cũ, định dạng lại cho input datetime-local --}}
         @php
            $endTimeValue = old('end_time', $schedule->end_time ?? null);
            $endTimeFormatted = $endTimeValue ? \Carbon\Carbon::parse($endTimeValue)->format('Y-m-d\TH:i') : '';
         @endphp
        <input type="datetime-local" name="end_time" id="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ $endTimeFormatted }}" required>
        @error('end_time')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

{{-- Không cần nút submit ở đây vì nó đã có trong edit.blade.php và create.blade.php --}}