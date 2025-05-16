{{-- resources/views/admin/tasks/_form.blade.php --}}
@csrf {{-- Token CSRF của Laravel --}}
<div class="row">
    <div class="col-md-8">
        {{-- Tiêu đề công việc --}}
        <div class="form-group">
            <label for="title">{{ __('Tiêu đề công việc') }} <span class="text-danger">*</span></label>
            <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror"
                   value="{{ old('title', $task->title) }}" required autofocus>
            @error('title')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        {{-- Mô tả chi tiết --}}
        <div class="form-group">
            <label for="description">{{ __('Mô tả chi tiết') }}</label>
            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
                      rows="5">{{ old('description', $task->description) }}</textarea>
            @error('description')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    </div>
    <div class="col-md-4">
        {{-- Giao cho Sinh viên --}}
        <div class="form-group">
            <label for="intern_id">{{ __('Giao cho Sinh viên') }} <span class="text-danger">*</span></label>
            <select name="intern_id" id="intern_id" class="form-control select2-interns @error('intern_id') is-invalid @enderror" required data-placeholder="{{ __('Chọn sinh viên') }}">
                <option></option> {{-- Option trống cho placeholder của Select2 --}}
                @foreach($interns as $intern)
                    <option value="{{ $intern->id }}" {{ old('intern_id', $task->intern_id) == $intern->id ? 'selected' : '' }}>
                        {{ $intern->name }} ({{ $intern->mssv ?? __('Chưa có MSSV') }})
                    </option>
                @endforeach
            </select>
            @error('intern_id')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        {{-- Hạn chót --}}
        <div class="form-group">
            <label for="due_date">{{ __('Hạn chót') }} <span class="text-danger">*</span></label>
            <input type="date" name="due_date" id="due_date" class="form-control @error('due_date') is-invalid @enderror"
                   value="{{ old('due_date', $task->due_date ? $task->due_date->format('Y-m-d') : '') }}" required
                   min="{{ $task->exists && $task->due_date && $task->due_date->isPast() && !$task->due_date->isToday() ? $task->due_date->format('Y-m-d') : now()->format('Y-m-d') }}">
                   {{-- Thuộc tính min: Nếu đang sửa task đã qua hạn, cho phép giữ nguyên ngày đó. Nếu task mới hoặc chưa qua hạn, min là ngày hiện tại --}}
            @error('due_date')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        {{-- Trạng thái --}}
        <div class="form-group">
            <label for="status">{{ __('Trạng thái') }} <span class="text-danger">*</span></label>
            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                @foreach($statuses as $key => $value)
                    <option value="{{ $key }}" {{ old('status', $task->status) == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
            @error('status')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        {{-- Độ ưu tiên --}}
        <div class="form-group">
            <label for="priority">{{ __('Độ ưu tiên') }}</label>
            <select name="priority" id="priority" class="form-control @error('priority') is-invalid @enderror">
                 <option value="">-- {{ __('Không đặt') }} --</option>
                @foreach($priorities as $key => $value)
                    <option value="{{ $key }}" {{ old('priority', $task->priority) == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
            @error('priority')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    </div>
</div>

<div class="form-group mt-4">
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save mr-1"></i> {{ $task->exists ? __('Cập nhật Công việc') : __('Giao việc Mới') }}
    </button>
    <a href="{{ route('admin.tasks.index') }}" class="btn btn-secondary">
        <i class="fas fa-times mr-1"></i> {{ __('Hủy') }}
    </a>
</div>