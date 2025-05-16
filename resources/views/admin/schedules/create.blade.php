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

                {{-- Hàng nhập Thời gian Bắt đầu và Kết thúc --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="start_time" class="form-label">Thời gian bắt đầu <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="start_time" id="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time') }}" required>
                        @error('start_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="end_time" class="form-label">Thời gian kết thúc <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="end_time" id="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time') }}" required>
                        @error('end_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

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