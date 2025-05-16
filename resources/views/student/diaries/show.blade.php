{{-- resources/views/student/diaries/show.blade.php --}}
@extends('layouts.admin')

@section('title', $diary->title)

@section('content')

    {{-- Page Heading & Nút quay lại (Ở trên cùng) --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $diary->title }}</h1>
        {{-- Nút quay lại ở đây được giữ lại --}}
        <a href="{{ route('student.diaries.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại danh sách
        </a>
    </div>

     {{-- Hiển thị thông báo --}}
     @include('partials.alert')

    <div class="card shadow mb-4">
        <div class="card-header py-3">
             <h6 class="m-0 font-weight-bold text-primary">Chi tiết nhật ký</h6>
             <div class="text-muted small mt-1">
                 Ngày viết: {{ $diary->diary_date ? \Carbon\Carbon::parse($diary->diary_date)->format('d/m/Y') : 'N/A' }} |
                 Trạng thái:
                 @if(strtolower($diary->status) == 'reviewed')
                     <span class="badge badge-success">Đã xem xét</span>
                 @elseif(strtolower($diary->status) == 'pending' || strtolower($diary->status) == 'draft')
                     <span class="badge badge-warning">Chờ xem xét</span>
                 @elseif(strtolower($diary->status) == 'commented')
                     <span class="badge badge-info">Đã bình luận</span>
                 @else
                     <span class="badge badge-secondary">{{ ucfirst($diary->status ?? 'Chưa có') }}</span>
                 @endif
             </div>
        </div>
        <div class="card-body">
            {{-- Nội dung nhật ký --}}
            <div class="diary-content mb-4 border-bottom pb-3">
                 {!! nl2br(e($diary->content)) !!}
            </div>

            {{-- Đánh giá chính thức từ Admin --}}
            @if ($diary->reviewed_at && $diary->admin_comment)
                <div class="admin-review mb-4 border rounded p-3 bg-light">
                    <h5 class="mb-3"><i class="fas fa-user-shield text-primary me-2"></i>Đánh giá từ Người hướng dẫn</h5>
                    @php
                        $reviewer = $diary->reviewer;
                        $reviewerName = $reviewer->name ?? 'Admin';
                        $reviewerAvatar = $reviewer->avatar_url ?? asset('img/undraw_profile_2.svg');
                    @endphp
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0 me-3">
                            <img src="{{ $reviewerAvatar }}" alt="{{ $reviewerName }}" class="rounded-circle" width="50" height="50">
                        </div>
                        <div class="flex-grow-1">
                             <h6 class="mt-0 mb-1">
                                {{ $reviewerName }}
                                <small class="text-muted float-right">{{ \Carbon\Carbon::parse($diary->reviewed_at)->diffForHumans() }}</small>
                            </h6>
                            <p class="mb-1">{!! nl2br(e($diary->admin_comment)) !!}</p>
                            @if(!is_null($diary->grade))
                                <p class="mb-0"><strong>Điểm:</strong> <span class="badge bg-info">{{ $diary->grade }}/10</span></p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Thảo luận chung --}}
            <h5 class="mb-3">{{ ($diary->reviewed_at && $diary->admin_comment) ? 'Thảo luận thêm' : 'Nhận xét & Phản hồi' }}</h5>
            <div class="comment-list mb-4">
                @forelse ($diary->comments ?? [] as $comment)
                    <div class="d-flex mb-4 pb-3 border-bottom">
                        <div class="flex-shrink-0 me-3">
                             <img src="{{ $comment->user->avatar_url ?? asset('img/undraw_profile.svg') }}" alt="{{ $comment->user->name ?? 'User' }}" class="rounded-circle" width="50" height="50">
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mt-0 mb-1">
                                {{ $comment->user->name ?? 'Người dùng ẩn danh' }}
                                @if($comment->user && $comment->user->hasRole('Admin'))
                                    <span class="badge bg-primary text-white small ms-1">Admin</span>
                                @endif
                                <small class="text-muted float-right">{{ $comment->created_at->diffForHumans() }}</small>
                            </h6>
                            <p class="mb-0">{!! nl2br(e($comment->content)) !!}</p>
                            {{-- Nút xóa comment (nếu có) --}}
                        </div>
                    </div>
                @empty
                    @if (!($diary->reviewed_at && $diary->admin_comment))
                       <p class="text-muted"><em>Chưa có nhận xét hoặc đánh giá nào.</em></p>
                    @endif
                @endforelse
            </div>

            {{-- Form viết phản hồi --}}
            <div class="add-comment-form">
                <h6 class="mb-3">Viết phản hồi của bạn</h6>
                 <form action="{{ route('student.diaries.comments.store', $diary->id) }}" method="POST">
                     @csrf
                     <div class="form-group mb-3">
                         <textarea class="form-control" name="content" rows="3" placeholder="Nhập nội dung phản hồi..." required></textarea>
                     </div>
                     <button type="submit" class="btn btn-success btn-sm">
                         <i class="fas fa-paper-plane"></i> Gửi phản hồi
                     </button>
                 </form>
            </div>

        </div>
        {{-- ===== CARD FOOTER ĐÃ ĐƯỢC CẬP NHẬT ===== --}}
         <div class="card-footer text-right">
             {{-- Nút Quay lại danh sách đã được xóa khỏi đây --}}

             {{-- Nút sửa chỉ hiện khi được phép --}}
              @if(strtolower($diary->status) != 'reviewed')
                 @can('update', $diary)
                    <a href="{{ route('student.diaries.edit', $diary->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Chỉnh sửa nhật ký
                    </a>
                 @endcan
             @endif
         </div>
         {{-- ===== KẾT THÚC CẬP NHẬT CARD FOOTER ===== --}}
    </div>

@endsection