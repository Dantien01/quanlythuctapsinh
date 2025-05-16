@extends('layouts.admin')
@section('title', 'Tin nhắn từ Sinh viên')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tin nhắn từ Sinh viên</h1>
    </div>

    @if (session('success')) <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">...</div> @endif
    @if (session('error')) <div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-alert">...</div> @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách cuộc trò chuyện</h6>
        </div>
        <div class="card-body">
             @if($conversations->count() > 0)
            <div class="list-group list-group-flush">
                @foreach ($conversations as $student)
                    <a href="{{ route('admin.messages.show', $student->id) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                             <div class="fw-bold">{{ $student->name }}</div>
                             <small class="text-muted">MSSV: {{ $student->mssv ?? 'N/A' }}</small>
                             {{-- Hiển thị tin nhắn cuối cùng (nếu cần) --}}
                              {{-- @php
                                  $lastSent = $student->sentMessages->first();
                                  $lastReceived = $student->receivedMessages->first();
                                  $lastMessageText = '...'; // Logic lấy text cuối
                              @endphp
                              <small class="d-block text-truncate" style="max-width: 300px;">{{ $lastMessageText }}</small> --}}
                        </div>
                        <div class="text-end">
                            {{-- Hiển thị thời gian của tin nhắn cuối cùng --}}
                            @if($student->lastMessage)
                                     <small class="text-muted d-block mb-1" title="{{ $student->lastMessage->created_at->format('d/m/Y H:i') }}">
                                        {{ $student->lastMessage->created_at->diffForHumans() }}
                                </small>
                            @endif

                        </div>
                    </a>
                @endforeach
            </div>
             @else
                 <p class="text-center text-muted">Chưa có cuộc trò chuyện nào.</p>
             @endif
        </div>
         {{-- Phân trang --}}
         <div class="card-footer d-flex justify-content-center">
              {{ $conversations->links() }}
         </div>
    </div>
</div>
@endsection

 @push('scripts')
    <script> /* ... Script ẩn alert ... */ </script>
@endpush