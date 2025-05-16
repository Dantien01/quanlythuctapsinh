{{-- resources/views/student/diaries/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Chỉnh sửa nhật ký')

@section('content')

    {{-- Page Heading --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Chỉnh sửa nhật ký</h1>
         <a href="{{ route('student.diaries.show', $diary->id) }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại chi tiết
        </a>
    </div>

    {{-- Hiển thị lỗi validation --}}
    @include('partials.validation_errors')

    <div class="card shadow mb-4">
         <div class="card-body">
             {{-- Form trỏ đến route update, dùng method PUT/PATCH --}}
             <form action="{{ route('student.diaries.update', $diary->id) }}" method="POST">
                 @csrf
                 @method('PUT') {{-- Hoặc PATCH --}}

                 {{-- Tiêu đề --}}
                 <div class="form-group mb-3">
                     <label for="title">Tiêu đề <span class="text-danger">*</span></label>
                     {{-- old() ưu tiên hơn giá trị cũ từ DB --}}
                     <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $diary->title) }}" required>
                 </div>

                 {{-- Ngày viết --}}
                 <div class="form-group mb-3">
                     <label for="entry_date">Ngày viết <span class="text-danger">*</span></label>
                     {{-- Định dạng lại ngày từ DB cho input date --}}
                     @php
                        $formattedDate = $diary->entry_date ? \Carbon\Carbon::parse($diary->entry_date)->format('Y-m-d') : now()->format('Y-m-d');
                     @endphp
                    <input type="date" class="form-control" id="diary_date" name="diary_date" value="{{ old('diary_date', $formattedDate) }}" required>                 </div>

                 {{-- Nội dung --}}
                 <div class="form-group mb-3">
                     <label for="content">Nội dung <span class="text-danger">*</span></label>
                     <textarea class="form-control" id="content" name="content" rows="10" required>{{ old('content', $diary->content) }}</textarea>
                     <small class="form-text text-muted">Mô tả chi tiết công việc, kết quả đạt được, khó khăn và bài học kinh nghiệm trong ngày.</small>
                 </div>

                 {{-- Nút bấm --}}
                 <div class="text-right">
                    <a href="{{ route('student.diaries.show', $diary->id) }}" class="btn btn-secondary mr-2">Hủy</a>
                    <button type="submit" class="btn btn-primary">
                         <i class="fas fa-sync-alt"></i> Cập nhật nhật ký
                     </button>
                 </div>
             </form>
         </div>
     </div>

@endsection

@push('scripts')
{{-- Script cho Editor nếu dùng --}}
@endpush