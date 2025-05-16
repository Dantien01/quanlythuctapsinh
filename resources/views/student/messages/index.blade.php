@extends('layouts.admin') {{-- Hoặc layout của bạn --}}
@section('title', 'Hộp thư đến')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tin nhắn với Quản trị viên</h1>
         @if($admin)
         <a href="{{ route('student.messages.create') }}" class="btn btn-sm btn-primary shadow-sm">
             <i class="fas fa-plus fa-sm text-white-50"></i> Gửi tin nhắn mới
         </a>
         @endif
    </div>

    {{-- ===== SỬA PHẦN HIỂN THỊ THÔNG BÁO ===== --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
            {{ session('success') }} {{-- <<< Hiển thị nội dung session 'success' --}}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
     @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-alert">
            {{ session('error') }} {{-- <<< Hiển thị nội dung session 'error' --}}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    {{-- ====================================== --}}


    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Lịch sử trò chuyện</h6>
        </div>
        <div class="card-body">
            @forelse ($messages as $message)
                {{-- Nội dung hiển thị tin nhắn giữ nguyên --}}
                <div class="message-item mb-4 p-3 rounded {{ $message->sender_id == $user->id ? 'bg-light border-left-primary' : 'bg-white border-left-info' }}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="message-sender fw-bold">
                            @if ($message->sender_id == $user->id)
                                <i class="fas fa-arrow-circle-right text-primary me-1"></i> Bạn
                            @else
                                <i class="fas fa-arrow-circle-left text-info me-1"></i> {{ $message->sender->name ?? 'Admin' }}
                            @endif
                        </div>
                        <small class="message-time text-muted">
                            {{ $message->created_at->diffForHumans() }}
                            ({{ $message->created_at->format('d/m/Y H:i') }})
                             @if($message->receiver_id == $user->id && $message->read_at)
                                 <i class="fas fa-check-double text-info ms-1" title="Đã đọc lúc {{ $message->read_at->format('H:i d/m') }}"></i>
                             @elseif($message->receiver_id == $user->id && !$message->read_at)
                                  <i class="fas fa-check text-muted ms-1" title="Đã gửi"></i>
                             @endif
                        </small>
                    </div>
                    @if($message->subject)
                        <p class="mb-1"><strong>Chủ đề:</strong> {{ $message->subject }}</p>
                    @endif
                    <div class="message-body">
                        {{-- Sử dụng content thay vì body --}}
                        {!! nl2br(e($message->content)) !!}
                    </div>
                </div>
            @empty
                <p class="text-center text-muted">Chưa có tin nhắn nào.</p>
            @endforelse

            {{-- Phân trang --}}
            <div class="mt-4 d-flex justify-content-center">
                 {{ $messages->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {{-- Script ẩn thông báo (giữ nguyên) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const successAlert = document.getElementById('success-alert'); if (successAlert) { setTimeout(() => { if (typeof bootstrap !== 'undefined' && bootstrap.Alert) { const alertInstance = bootstrap.Alert.getOrCreateInstance(successAlert); if(alertInstance) { alertInstance.close(); } else { successAlert.style.display = 'none'; } } else { successAlert.style.display = 'none'; } }, 5000); }
            const errorAlert = document.getElementById('error-alert'); if (errorAlert) { setTimeout(() => { if (typeof bootstrap !== 'undefined' && bootstrap.Alert) { const alertInstance = bootstrap.Alert.getOrCreateInstance(errorAlert); if(alertInstance) { alertInstance.close(); } else { errorAlert.style.display = 'none'; } } else { errorAlert.style.display = 'none'; } }, 8000); }
        });
    </script>
@endpush