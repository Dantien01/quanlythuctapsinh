@extends('layouts.admin') {{-- Hoặc layout của bạn, ví dụ layouts.app nếu dùng chung --}}
@section('title', 'Hộp thư')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tin nhắn với Quản trị viên</h1>
        {{-- Nút gửi tin nhắn mới có thể không cần thiết ở đây nếu form gửi đã có sẵn bên dưới --}}
        {{-- Nếu bạn vẫn muốn nút "Gửi tin nhắn mới" và nó trỏ đến một trang create riêng biệt,
             bạn có thể giữ lại nút này. Tuy nhiên, logic hiện tại của StudentController
             là trang index sẽ hiển thị cuộc trò chuyện và form gửi luôn.
        --}}
        {{-- @if($admin)
        <a href="{{ route('student.messages.create') }}" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Gửi tin nhắn mới
        </a>
        @endif --}}
    </div>

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

    @if($admin && $conversation) {{-- Chỉ hiển thị nếu có admin và conversation --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Trò chuyện với {{ $admin->name }}</h6>
        </div>
        {{-- Phần hiển thị tin nhắn --}}
        <div class="card-body" style="max-height: 500px; overflow-y: auto;" id="message-container">
            @if(isset($messages) && $messages->count() > 0)
                @foreach ($messages as $message)
                    {{-- $student là Auth::user() được truyền từ controller --}}
                    @php $isStudentSender = ($message->sender_id == $student->id); @endphp
                    <div class="d-flex mb-3 {{ $isStudentSender ? 'justify-content-end' : 'justify-content-start' }}">
                        <div class="p-3 rounded {{ $isStudentSender ? 'bg-info text-white' : 'bg-light text-dark border' }}" style="max-width: 75%;">
                            <p class="mb-1">
                                <small>
                                    <strong>{{ $isStudentSender ? 'Bạn' : ($message->sender->name ?? 'Quản trị viên') }}:</strong>
                                </small>
                            </p>
                            <p class="mb-1">{!! nl2br(e($message->content ?? '')) !!}</p>
                            <small @class([
                                'd-block', 'text-end',
                                'text-white-50' => $isStudentSender,
                                'text-muted opacity-75' => !$isStudentSender
                            ])>
                                {{ $message->created_at ? $message->created_at->format('H:i d/m') : '' }}
                                {{-- Hiển thị trạng thái đã đọc cho tin nhắn sinh viên gửi (nếu cần) --}}
                                {{-- @if($isStudentSender && $message->isReadBy($admin->id)) --}}
                                {{-- <i class="fas fa-check-double text-white-50 ms-1" title="Admin đã đọc"></i> --}}
                                {{-- @elseif($isStudentSender) --}}
                                {{-- <i class="fas fa-check text-white-50 ms-1" title="Đã gửi"></i> --}}
                                {{-- @endif --}}
                            </small>
                        </div>
                    </div>
                @endforeach
            @else
                <p class="text-center text-muted">Chưa có tin nhắn nào. Hãy bắt đầu cuộc trò chuyện!</p>
            @endif
        </div>

        {{-- Hiển thị phân trang nếu có nhiều tin nhắn --}}
        @if(isset($messages) && $messages->hasPages())
            <div class="card-footer d-flex justify-content-center">
                {{ $messages->links() }}
            </div>
        @endif

        {{-- Phần form gửi tin nhắn của Sinh viên --}}
        <div class="card-footer">
            <form action="{{ route('student.messages.store') }}" method="POST" id="student-message-form">
                @csrf
                {{-- Không cần receiver_id nếu mặc định gửi cho Admin đã xác định trong controller --}}
                <div class="input-group">
                    <textarea name="content" class="form-control @error('content') is-invalid @enderror" rows="2" placeholder="Nhập tin nhắn của bạn..." required>{{ old('content') }}</textarea>
                    <button class="btn btn-primary" type="submit" id="button-send-student">
                        <i class="fas fa-paper-plane"></i> Gửi
                    </button>
                </div>
                @error('content') <div class="invalid-feedback d-block">{{ $errors->first('content') }}</div> @enderror
            </form>
        </div>
    </div>
    @else
        <div class="alert alert-info text-center">Hiện tại không thể liên hệ với quản trị viên.</div>
    @endif
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tự động ẩn thông báo
            const successAlert = document.getElementById('success-alert'); if (successAlert) { setTimeout(() => { if (typeof bootstrap !== 'undefined' && bootstrap.Alert) { const alertInstance = bootstrap.Alert.getOrCreateInstance(successAlert); if(alertInstance) { alertInstance.close(); } else { successAlert.style.display = 'none'; } } else { successAlert.style.display = 'none'; } }, 5000); }
            const errorAlert = document.getElementById('error-alert'); if (errorAlert) { setTimeout(() => { if (typeof bootstrap !== 'undefined' && bootstrap.Alert) { const alertInstance = bootstrap.Alert.getOrCreateInstance(errorAlert); if(alertInstance) { alertInstance.close(); } else { errorAlert.style.display = 'none'; } } else { errorAlert.style.display = 'none'; } }, 8000); }

            // Tự động cuộn xuống cuối cuộc trò chuyện
            const messageContainer = document.getElementById('message-container');
            if (messageContainer) {
                messageContainer.scrollTop = messageContainer.scrollHeight;
            }

            // Hiệu ứng loading cho nút gửi form của sinh viên
            const studentMessageForm = document.getElementById('student-message-form');
            if(studentMessageForm) {
                studentMessageForm.addEventListener('submit', function() {
                    const btn = studentMessageForm.querySelector('button[type="submit"]');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span class="ms-1">Đang gửi...</span>';
                    }
                });
            }
        });
    </script>
@endpush