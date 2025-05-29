@extends('layouts.admin') {{-- Hoặc layout của bạn --}}
@section('title', 'Gửi tin nhắn mới cho Quản trị viên')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Soạn tin nhắn cho Quản trị viên</h1>
        <a href="{{ route('student.messages.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại Hộp thư
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Ôi, có lỗi xảy ra!</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($admin) {{-- Chỉ hiển thị form nếu có admin --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Gửi tới: {{ $admin->name }}</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('student.messages.store') }}" method="POST" id="student-create-message-form">
                @csrf
                {{-- Không cần input receiver_id ẩn vì controller store sẽ tự xác định Admin --}}

                {{-- Bỏ trường Subject nếu không còn dùng --}}
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

                <button type="submit" class="btn btn-primary btn-icon-split" id="button-send-create">
                    <span class="icon text-white-50">
                        <i class="fas fa-paper-plane"></i>
                    </span>
                    <span class="text">Gửi tin nhắn</span>
                </button>
            </form>
        </div>
    </div>
    @else
        <div class="alert alert-warning">Không tìm thấy thông tin quản trị viên để gửi tin nhắn.</div>
    @endif
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Hiệu ứng loading cho nút gửi form này
            const createMessageForm = document.getElementById('student-create-message-form');
            if(createMessageForm) {
                createMessageForm.addEventListener('submit', function() {
                    const btn = createMessageForm.querySelector('button[type="submit"]');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span class="ms-1">Đang gửi...</span>';
                    }
                });
            }
        });
    </script>
@endpush