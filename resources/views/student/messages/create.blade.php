@extends('layouts.admin') {{-- Hoặc layout của bạn --}}
@section('title', 'Gửi tin nhắn mới')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gửi tin nhắn cho Quản trị viên</h1>
         <a href="{{ route('student.messages.index') }}" class="btn btn-sm btn-secondary shadow-sm">
             <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại Hộp thư
         </a>
    </div>

     @if ($errors->any())
        <div class="alert alert-danger"> /* ... Hiển thị lỗi ... */ </div>
     @endif

    <div class="card shadow mb-4">
         <div class="card-header py-3">
             <h6 class="m-0 font-weight-bold text-primary">Soạn tin nhắn</h6>
         </div>
        <div class="card-body">
            <form action="{{ route('student.messages.store') }}" method="POST">
                @csrf
                {{-- Input ẩn chứa ID người nhận (Admin) --}}
                <input type="hidden" name="receiver_id" value="{{ $admin->id }}">

                {{-- Có thể thêm trường Subject nếu muốn --}}
                {{-- <div class="mb-3">
                    <label for="subject" class="form-label">Chủ đề (Tùy chọn)</label>
                    <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject" value="{{ old('subject') }}">
                     @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div> --}}

                <div class="mb-3">
                    <label for="content" class="form-label">Nội dung tin nhắn <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="6" required placeholder="Nhập nội dung tin nhắn của bạn ở đây...">{{ old('content') }}</textarea>
                    @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary btn-icon-split">
                     <span class="icon text-white-50">
                         <i class="fas fa-paper-plane"></i>
                     </span>
                     <span class="text">Gửi tin nhắn</span>
                 </button>
            </form>
        </div>
    </div>
</div>
@endsection