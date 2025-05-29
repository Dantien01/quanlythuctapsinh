@extends('layouts.admin')
@section('title', 'Trò chuyện với ' . $student->name) {{-- Đổi $user thành $student cho rõ nghĩa --}}

@section('content')
<div class="container-fluid">
     <div class="d-sm-flex align-items-center justify-content-between mb-4">
         {{-- Đổi $user thành $student --}}
         <h1 class="h3 mb-0 text-gray-800">Trò chuyện với: <span class="text-primary">{{ $student->name }}</span></h1>
         <a href="{{ route('admin.messages.index') }}" class="btn btn-sm btn-primary shadow-sm text-white">
             <i class="fas fa-arrow-left fa-sm text-white"></i> Quay lại Danh sách
         </a>
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

     <div class="row">
         <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Nội dung cuộc trò chuyện</h6>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;" id="message-container">
                    @if(isset($messages) && $messages->count() > 0)
                        @foreach ($messages as $message)
                            @php $isAdminSender = ($message->sender_id == $admin->id); @endphp
                            <div class="d-flex mb-3 {{ $isAdminSender ? 'justify-content-end' : 'justify-content-start' }}">
                                <div class="p-3 rounded {{ $isAdminSender ? 'bg-primary text-white' : 'bg-light text-dark border' }}" style="max-width: 75%;">
                                    <p class="mb-1">
                                        <small>
                                            <strong>{{ $isAdminSender ? 'Bạn' : ($message->sender->name ?? 'Không rõ') }}:</strong>
                                        </small>
                                    </p>
                                    <p class="mb-1">{!! nl2br(e($message->content ?? '')) !!}</p>
                                    <small @class([
                                        'd-block', 'text-end',
                                        'text-white-50' => $isAdminSender,
                                        'text-muted opacity-75' => !$isAdminSender
                                    ])>
                                        {{ $message->created_at ? $message->created_at->format('H:i d/m') : '' }}
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-center text-muted">Chưa có tin nhắn nào trong cuộc trò chuyện này.</p>
                    @endif
                </div>

                 @if(isset($messages) && $messages->hasPages())
                    <div class="card-footer d-flex justify-content-center">
                        {{ $messages->links() }}
                    </div>
                 @endif

                <div class="card-footer">
                    {{-- Đổi $user thành $student trong action của form --}}
                    <form action="{{ route('admin.messages.reply', $student->id) }}" method="POST" id="admin-message-reply-form">
                        @csrf
                        <div class="input-group">
                            <textarea name="content" class="form-control @error('content') is-invalid @enderror" rows="2" placeholder="Nhập nội dung trả lời..." required>{{ old('content') }}</textarea>
                            <button class="btn btn-primary" type="submit" id="button-send">
                                <i class="fas fa-paper-plane"></i> Gửi
                            </button>
                        </div>
                         @error('content') <div class="invalid-feedback d-block">{{ $errors->first('content') }}</div> @enderror
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {{-- Script giữ nguyên --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const successAlert = document.getElementById('success-alert'); if (successAlert) { setTimeout(() => { if (typeof bootstrap !== 'undefined' && bootstrap.Alert) { const alertInstance = bootstrap.Alert.getOrCreateInstance(successAlert); if(alertInstance) { alertInstance.close(); } else { successAlert.style.display = 'none'; } } else { successAlert.style.display = 'none'; } }, 5000); }
            const errorAlert = document.getElementById('error-alert'); if (errorAlert) { setTimeout(() => { if (typeof bootstrap !== 'undefined' && bootstrap.Alert) { const alertInstance = bootstrap.Alert.getOrCreateInstance(errorAlert); if(alertInstance) { alertInstance.close(); } else { errorAlert.style.display = 'none'; } } else { errorAlert.style.display = 'none'; } }, 8000); }
            const messageContainer = document.getElementById('message-container'); if (messageContainer) { messageContainer.scrollTop = messageContainer.scrollHeight; }
            const replyForm = document.getElementById('admin-message-reply-form'); if(replyForm) { replyForm.addEventListener('submit', function() { const btn = replyForm.querySelector('button[type="submit"]'); if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span class="ms-1">Đang gửi...</span>'; } }); }
        });
    </script>
@endpush