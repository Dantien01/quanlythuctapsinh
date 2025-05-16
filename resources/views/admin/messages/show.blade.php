@extends('layouts.admin')
@section('title', 'Trò chuyện với ' . $user->name) {{-- $user ở đây là sinh viên --}}

@section('content')
<div class="container-fluid">
     <div class="d-sm-flex align-items-center justify-content-between mb-4">
         <h1 class="h3 mb-0 text-gray-800">Trò chuyện với: <span class="text-primary">{{ $user->name }}</span></h1>
         <a href="{{ route('admin.messages.index') }}" class="btn btn-sm btn-primary shadow-sm text-white">
             <i class="fas fa-arrow-left fa-sm text-white"></i> Quay lại Danh sách
         </a>
     </div>

     {{-- Hiển thị thông báo --}}
     @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
            {{ session('success') }}
             <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
     @endif
     @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-alert">
             {{ session('error') }}
             <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
     @endif

     <div class="row">
         <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Nội dung cuộc trò chuyện</h6>
                </div>
                {{-- Phần hiển thị tin nhắn --}}
                <div class="card-body" style="max-height: 500px; overflow-y: auto;" id="message-container">
                    {{-- Kiểm tra $messages có tồn tại và không rỗng --}}
                    @if(isset($messages) && $messages->count() > 0)
                        @foreach ($messages as $message)
                            {{-- Đặt biến kiểm tra người gửi cho dễ đọc --}}
                            @php $isAdminSender = ($message->sender_id == $admin->id); @endphp
                            <div class="d-flex mb-3 {{ $isAdminSender ? 'justify-content-end' : 'justify-content-start' }}">
                                <div class="p-3 rounded {{ $isAdminSender ? 'bg-primary text-white' : 'bg-light text-dark border' }}" style="max-width: 75%;">
                                    <p class="mb-1">
                                        <small>
                                            {{-- Đảm bảo $message->sender tồn tại trước khi truy cập name --}}
                                            <strong>{{ $isAdminSender ? 'Bạn' : ($message->sender->name ?? 'Không rõ') }}:</strong>
                                        </small>
                                    </p>
                                    {{-- Đảm bảo dùng đúng tên cột 'content' --}}
                                    <p class="mb-1">{!! nl2br(e($message->content ?? '')) !!}</p>
                                    {{-- Thẻ small hiển thị thời gian --}}
                                    <small @class([
                                        'd-block', 'text-end', // Luôn căn phải và là block
                                        'text-white-50' => $isAdminSender, // Màu trắng mờ nếu admin gửi
                                        'text-muted opacity-75' => !$isAdminSender // Màu xám mờ nếu sinh viên gửi
                                    ])>
                                        {{-- Hiển thị thời gian, kiểm tra null --}}
                                        {{ $message->created_at ? $message->created_at->format('H:i d/m') : '' }}
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    @else
                        {{-- Hiển thị khi không có tin nhắn nào --}}
                        <p class="text-center text-muted">Chưa có tin nhắn nào trong cuộc trò chuyện này.</p>
                    @endif
                </div>

                 {{-- Hiển thị phân trang nếu có nhiều tin nhắn --}}
                 @if(isset($messages) && $messages->hasPages())
                    <div class="card-footer d-flex justify-content-center">
                        {{ $messages->links() }}
                    </div>
                 @endif

                {{-- Phần form trả lời --}}
                <div class="card-footer">
                     {{-- Thêm ID form để script loading hoạt động --}}
                    <form action="{{ route('admin.messages.reply', $user->id) }}" method="POST" id="admin-message-reply-form">
                        @csrf
                        <div class="input-group">
                             {{-- Đảm bảo name="content" và @error('content') --}}
                            <textarea name="content" class="form-control @error('content') is-invalid @enderror" rows="2" placeholder="Nhập nội dung trả lời..." required>{{ old('content') }}</textarea>
                            <button class="btn btn-primary" type="submit" id="button-send">
                                <i class="fas fa-paper-plane"></i> Gửi
                            </button>
                        </div>
                         {{-- Đảm bảo @error('content') và lấy lỗi đúng --}}
                         @error('content') <div class="invalid-feedback d-block">{{ $errors->first('content') }}</div> @enderror
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {{-- Script ẩn alert và cuộn tin nhắn --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tự động ẩn thông báo
            const successAlert = document.getElementById('success-alert');
            if (successAlert) {
                setTimeout(() => {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                        const alertInstance = bootstrap.Alert.getOrCreateInstance(successAlert);
                        if(alertInstance) { alertInstance.close(); } else { successAlert.style.display = 'none'; }
                    } else { successAlert.style.display = 'none'; }
                }, 5000); // 5 giây
            }
            const errorAlert = document.getElementById('error-alert');
            if (errorAlert) {
                setTimeout(() => {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                        const alertInstance = bootstrap.Alert.getOrCreateInstance(errorAlert);
                        if(alertInstance) { alertInstance.close(); } else { errorAlert.style.display = 'none'; }
                    } else { errorAlert.style.display = 'none'; }
                }, 8000); // 8 giây
            }

            // Tự động cuộn xuống cuối cuộc trò chuyện
            const messageContainer = document.getElementById('message-container');
            if (messageContainer) {
                messageContainer.scrollTop = messageContainer.scrollHeight;
            }

            // Hiệu ứng loading cho nút gửi form này
             const replyForm = document.getElementById('admin-message-reply-form');
             if(replyForm) {
                 replyForm.addEventListener('submit', function() {
                     const btn = replyForm.querySelector('button[type="submit"]');
                     if (btn) {
                         btn.disabled = true;
                         // Thêm spinner và text, sử dụng class 'ms-1' của Bootstrap 5
                         btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span class="ms-1">Đang gửi...</span>';
                     }
                 });
             }
         });
     </script>
@endpush