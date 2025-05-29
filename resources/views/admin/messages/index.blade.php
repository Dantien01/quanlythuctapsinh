@extends('layouts.admin')
@section('title', 'Tin nhắn từ Sinh viên')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tin nhắn từ Sinh viên</h1>
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

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách cuộc trò chuyện</h6>
        </div>
        <div class="card-body">
            @if($conversations->count() > 0)
            <div class="list-group list-group-flush">
                @foreach ($conversations as $conversation)
                    @php
                        // Lấy thông tin sinh viên từ participants (admin là người còn lại)
                        // Giả sử $admin là đối tượng Auth::user() đã được truyền vào hoặc có thể lấy lại
                        $student = $conversation->getOtherParticipant(Auth::id());
                    @endphp
                    @if($student) {{-- Đảm bảo tìm thấy sinh viên --}}
                        <a href="{{ route('admin.messages.show', $student->id) }}"
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $conversation->unread_messages_count > 0 ? 'list-group-item-warning' : '' }}">
                            <div>
                                <div class="fw-bold">{{ $student->name }}
                                    @if($conversation->unread_messages_count > 0)
                                        <span class="badge bg-danger rounded-pill ms-2">{{ $conversation->unread_messages_count }}</span>
                                    @endif
                                </div>
                                <small class="text-muted">MSSV: {{ $student->mssv ?? 'N/A' }}</small>

                                @if($conversation->lastMessage)
                                    <small class="d-block text-truncatefst-italic {{ $conversation->lastMessage->sender_id == Auth::id() ? 'text-muted' : 'text-dark' }}" style="max-width: 400px;">
                                        @if($conversation->lastMessage->sender_id == Auth::id())
                                            Bạn:
                                        @endif
                                        {{ Str::limit($conversation->lastMessage->content, 50) }}
                                    </small>
                                @else
                                    <small class="d-block text-muted fst-italic">Chưa có tin nhắn.</small>
                                @endif
                            </div>
                            <div class="text-end">
                                @if($conversation->lastMessage)
                                    <small class="text-muted d-block mb-1" title="{{ $conversation->lastMessage->created_at->format('d/m/Y H:i') }}">
                                        {{ $conversation->lastMessage->created_at->diffForHumans() }}
                                    </small>
                                @endif
                            </div>
                        </a>
                    @endif
                @endforeach
            </div>
            @else
                <p class="text-center text-muted">Chưa có cuộc trò chuyện nào.</p>
            @endif
        </div>
        {{-- Phân trang --}}
        @if($conversations->hasPages())
            <div class="card-footer d-flex justify-content-center">
                {{ $conversations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const successAlert = document.getElementById('success-alert'); if (successAlert) { setTimeout(() => { if (typeof bootstrap !== 'undefined' && bootstrap.Alert) { const alertInstance = bootstrap.Alert.getOrCreateInstance(successAlert); if(alertInstance) { alertInstance.close(); } else { successAlert.style.display = 'none'; } } else { successAlert.style.display = 'none'; } }, 5000); }
            const errorAlert = document.getElementById('error-alert'); if (errorAlert) { setTimeout(() => { if (typeof bootstrap !== 'undefined' && bootstrap.Alert) { const alertInstance = bootstrap.Alert.getOrCreateInstance(errorAlert); if(alertInstance) { alertInstance.close(); } else { errorAlert.style.display = 'none'; } } else { errorAlert.style.display = 'none'; } }, 8000); }
        });
    </script>
@endpush