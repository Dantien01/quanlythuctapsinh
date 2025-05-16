{{-- resources/views/student/diaries/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Viết nhật ký mới')

@section('content')

    {{-- Page Heading --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Viết nhật ký mới</h1>
         <a href="{{ route('student.diaries.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại danh sách
        </a>
    </div>

    {{-- Hiển thị lỗi validation --}}
    @include('partials.validation_errors')

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('student.diaries.store') }}" method="POST">
                @csrf

                {{-- Tiêu đề --}}
                <div class="form-group mb-3">
                    <label for="title">Tiêu đề <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}" required>
                </div>

                {{-- Ngày viết --}}
                <div class="form-group mb-3">
                    <label for="entry_date">Ngày viết <span class="text-danger">*</span></label>
                    {{-- Sử dụng input type date cho dễ chọn --}}
                    <input type="date" class="form-control" id="diary_date" name="diary_date" value="{{ old('diary_date', now()->format('Y-m-d')) }}" required>                </div>

                {{-- Nội dung --}}
                <div class="form-group mb-3">
                    <label for="content">Nội dung <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="content" name="content" rows="10" required>{{ old('content') }}</textarea>
                     <small class="form-text text-muted">Mô tả chi tiết công việc, kết quả đạt được, khó khăn và bài học kinh nghiệm trong ngày.</small>
                </div>

                {{-- Nút bấm --}}
                <div class="text-right"> {{-- Căn lề phải cho nút --}}
                    <a href="{{ route('student.diaries.index') }}" class="btn btn-secondary mr-2">Hủy</a>
                    <button type="submit" class="btn btn-primary">
                         <i class="fas fa-save"></i> Lưu nhật ký
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
{{-- Script cho CKEditor hoặc TinyMCE nếu dùng --}}
{{-- Ví dụ: <script src="https://cdn.ckeditor.com/4.16.0/standard/ckeditor.js"></script> --}}
{{-- <script> CKEDITOR.replace( 'content' ); </script> --}}
@endpush