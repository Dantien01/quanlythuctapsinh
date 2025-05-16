{{-- resources/views/admin/reviews/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Viết Nhận xét Sinh viên')

@section('content')

    {{-- Page Heading --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Viết nhận xét thực tập sinh</h1>
         {{-- Nút quay lại danh sách --}}
         <a href="{{ route('admin.reviews.index') }}" class="btn btn-sm btn-primary shadow-sm text-white">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại Danh sách
        </a>
    </div>

    {{-- Card chứa form --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Nội dung Nhận xét</h6>
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

            <form action="{{ route('admin.reviews.store') }}" method="POST">
                @csrf

                {{-- Chọn Sinh viên --}}
                <div class="mb-3">
                    <label for="student_id" class="form-label">Chọn Sinh viên <span class="text-danger">*</span></label>
                    <select name="student_id" id="student_id" class="form-select @error('student_id') is-invalid @enderror" required>
                        <option value="">-- Vui lòng chọn sinh viên --</option>
                         {{-- Giả sử có biến $students từ Controller --}}
                         @isset($students)
                             @foreach ($students as $student)
                                 <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                     {{ $student->name }} ({{ $student->mssv ?? $student->email }})
                                 </option>
                             @endforeach
                         @endisset
                    </select>
                    @error('student_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Kỳ nhận xét / Tiêu đề --}}
                 <div class="mb-3">
                    <label for="review_period" class="form-label">Kỳ nhận xét (Ví dụ: Tuần 17 / Tháng 4-2024)</label>
                     {{-- Giả sử bạn có cột 'review_period' hoặc 'title' --}}
                    <input type="text" name="review_period" id="review_period" class="form-control @error('review_period') is-invalid @enderror" value="{{ old('review_period') }}" placeholder="Ví dụ: Tuần 17 / Tháng 4-2024">
                    @error('review_period')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Nội dung Nhận xét --}}
                <div class="mb-3">
                    <label for="content" class="form-label">Nội dung nhận xét <span class="text-danger">*</span></label>
                    <textarea name="content" id="content" rows="5" class="form-control @error('content') is-invalid @enderror" required placeholder="Nhập đánh giá, nhận xét về quá trình thực tập của sinh viên...">{{ old('content') }}</textarea>
                    @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Nút Submit và Hủy --}}
                <div class="mt-4">
                    <button type="submit" class="btn btn-success btn-icon-split">
                        <span class="icon text-white-50">
                            <i class="fas fa-save"></i>
                        </span>
                        <span class="text">Lưu Nhận xét</span>
                    </button>
                    <a href="{{ route('admin.reviews.index') }}" class="btn btn-danger ml-2 text-white">Hủy</a>
                </div>

            </form>
        </div> {{-- End card-body --}}
    </div> {{-- End card --}}

@endsection