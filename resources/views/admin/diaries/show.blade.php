{{-- resources/views/admin/diaries/show.blade.php --}}
@extends('layouts.admin') {{-- Kế thừa từ layout admin mới --}}

@section('title', 'Chi tiết Nhật ký: ' . $diary->title)

@section('content')

    {{-- Page Heading của SB Admin 2 --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        {{-- Hiển thị tiêu đề nhật ký làm tiêu đề trang --}}
        <h1 class="h3 mb-0 text-gray-800">Chi tiết Nhật ký: {{ Str::limit($diary->title, 50) }}</h1>
         {{-- Nút quay lại danh sách --}}
         <a href="{{ route('admin.diaries.index', request()->query()) }}" class="btn btn-sm btn-primary shadow-sm text-white">
            <i class="fas fa-arrow-left fa-sm"></i> Quay lại Danh sách
        </a>
    </div>

    <div class="row"> {{-- Sử dụng Bootstrap row để chia cột nếu cần --}}

        {{-- Cột chính (có thể dùng col-lg-8 hoặc để 12 nếu không chia cột) --}}
        <div class="col-lg-12">

            {{-- Card chứa thông tin chính của Nhật ký --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin Nhật ký & Sinh viên</h6>
                </div>
                <div class="card-body">
                    {{-- Thông tin Sinh viên và Nhật ký (chia 2 cột) --}}
                    <div class="row mb-4 pb-4 border-bottom">
                        <div class="col-md-6">
                            <h5 class="mb-3">Thông tin Sinh viên</h5>
                            <p><strong>Tên:</strong> {{ $diary->user->name ?? 'N/A' }}</p>
                            <p><strong>MSSV:</strong> {{ $diary->user->mssv ?? 'N/A' }}</p>
                            <p><strong>Email:</strong> {{ $diary->user->email ?? 'N/A' }}</p>
                            {{-- Thêm thông tin khác nếu cần --}}
                        </div>
                        <div class="col-md-6">
                             <h5 class="mb-3">Thông tin Nhật ký</h5>
                            <p><strong>Ngày viết:</strong> {{ $diary->diary_date ? $diary->diary_date->format('d/m/Y') : 'N/A' }}</p>
                            <p><strong>Ngày tạo:</strong> {{ $diary->created_at->format('d/m/Y H:i') }}</p>
                            <p><strong>Trạng thái:</strong>
                                {{-- Sử dụng Bootstrap Badges --}}
                                <span @class([
                                    'badge', // dùng 'badge' thay vì 'px-2 ... rounded-full'
                                    'bg-warning text-dark' => $diary->status === 'submitted' || $diary->status === 'pending',
                                    'bg-info text-dark' => $diary->status === 'commented',
                                    'bg-success' => $diary->status === 'reviewed' || $diary->status === 'approved',
                                    'bg-secondary' => !in_array($diary->status, ['submitted', 'pending', 'commented', 'reviewed', 'approved'])
                                ])>
                                    {{ match($diary->status) {
                                        'submitted' => 'Chờ nhận xét',
                                        'commented' => 'Đã nhận xét nhanh',
                                        'reviewed' => 'Đã đánh giá',
                                        default => ucfirst($diary->status ?? 'Bản nháp')
                                    } }}
                                </span>
                            </p>
                        </div>
                    </div>

                    {{-- Nội dung Nhật ký --}}
                    <h5 class="mb-2">Nội dung Nhật ký</h5>
                    {{-- Không cần class prose, dùng định dạng mặc định hoặc tùy chỉnh thêm --}}
                    <div class="text-gray-900 diary-content mb-4" style="white-space: pre-wrap;">{{-- Giữ lại xuống dòng --}}
                        {{ $diary->content }}
                    </div>
                </div>
            </div>

             {{-- Card cho Nhận xét & Đánh giá chính thức --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Nhận xét & Đánh giá Chính thức</h6>
                </div>
                <div class="card-body">
                    {{-- Hiển thị thông báo thành công/lỗi từ session (dùng alert của Bootstrap) --}}
                    @if (session('success') && (request()->routeIs('admin.diaries.review.store') || !request()->route()->hasParameter('comment'))) {{-- Chỉ hiện khi vừa submit review --}}
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    {{-- Hiển thị lỗi validation cho form review (nếu có) --}}
                    @if ($errors->any() && old('admin_comment')) {{-- Chỉ hiển thị lỗi nếu có submit form review --}}
                         <div class="alert alert-danger alert-dismissible fade show" role="alert">
                             <strong>Vui lòng kiểm tra lại:</strong>
                             <ul class="mb-0 mt-1">
                                 @foreach ($errors->all() as $error)
                                     <li>{{ $error }}</li>
                                 @endforeach
                             </ul>
                             <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                         </div>
                     @endif

                    {{-- Hiển thị đánh giá cũ nếu có --}}
                    @if ($diary->reviewed_at)
                        <div class="mb-4 p-3 border rounded bg-light"> {{-- Styling đơn giản cho phần hiển thị review cũ --}}
                            <p class="mb-1 text-muted small">
                                Đã đánh giá bởi: <strong>{{ $diary->reviewer->name ?? 'N/A' }}</strong>
                                lúc: {{ $diary->reviewed_at->format('d/m/Y H:i') }}
                            </p>
                            @if ($diary->grade !== null)
                                <p class="mb-1 h5"><strong>Điểm: {{ $diary->grade }}/10</strong></p>
                            @endif
                            <p class="mb-0" style="white-space: pre-wrap;">{{ $diary->admin_comment }}</p>
                        </div>
                        <p class="text-muted small mb-3"><em>Bạn có thể cập nhật đánh giá bằng form bên dưới.</em></p>
                    @else
                         <p class="text-muted small mb-3"><em>Nhật ký này chưa được đánh giá chính thức.</em></p>
                    @endif

                    {{-- Form thêm/cập nhật đánh giá - Sử dụng form Bootstrap --}}
                    <form action="{{ route('admin.diaries.review.store', $diary) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="admin_comment_review" class="form-label">Nội dung Đánh giá <span class="text-danger">*</span></label>
                            <textarea id="admin_comment_review" name="admin_comment" rows="5"
                                      class="form-control @error('admin_comment') is-invalid @enderror"
                                      placeholder="Nhập nhận xét chi tiết và đánh giá của bạn..."
                                      required>{{ old('admin_comment', $diary->admin_comment) }}</textarea>
                            @error('admin_comment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 row"> {{-- Dùng row/col để đặt label và input trên cùng hàng --}}
                            <label for="grade" class="col-sm-3 col-form-label col-form-label-sm">Điểm (tùy chọn, 0-10):</label>
                             <div class="col-sm-3">
                                <input type="number" id="grade" name="grade" min="0" max="10" step="1"
                                    class="form-control form-control-sm @error('grade') is-invalid @enderror"
                                    value="{{ old('grade', $diary->grade) }}"
                                    placeholder="0-10">
                                @error('grade')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-icon-split"> {{-- Button với icon --}}
                            <span class="icon text-white-50">
                                <i class="fas fa-save"></i>
                            </span>
                            <span class="text">{{ $diary->reviewed_at ? 'Cập nhật Đánh giá' : 'Lưu Đánh giá' }}</span>
                        </button>
                    </form>
                </div> {{-- End card-body cho Review --}}
            </div> {{-- End card cho Review --}}


            {{-- Card cho Trao đổi & Phản hồi nhanh --}}
            <div class="card shadow mb-4">
                 <div class="card-header py-3">
                     <h6 class="m-0 font-weight-bold text-primary">Trao đổi & Phản hồi nhanh</h6>
                 </div>
                 <div class="card-body">
                     {{-- Hiển thị thông báo thành công khi thêm comment chung --}}
                     @if (session('success') && request()->routeIs('admin.diaries.comments.store'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                     @endif

                    {{-- Danh sách comments --}}
                    <div class="mb-4" style="max-height: 400px; overflow-y: auto;"> {{-- Scroll nếu nhiều comment --}}
                         @if ($diary->comments && $diary->comments->count() > 0)
                             @foreach ($diary->comments as $comment)
                                 {{-- Phân biệt comment của admin và sinh viên --}}
                                 @php
                                     $isAdminComment = $comment->user?->hasRole('Admin'); // Kiểm tra an toàn
                                     $isMyComment = $comment->user_id === Auth::id();
                                 @endphp
                                <div class="mb-3 p-3 rounded border {{ $isAdminComment ? ($isMyComment ? 'bg-primary-subtle border-primary-subtle' : 'bg-secondary-subtle border-secondary-subtle') : 'bg-light border-light-subtle' }}">
                                    <div class="d-flex justify-content-between align-items-center mb-1 small text-muted">
                                         <span class="fw-bold {{ $isAdminComment ? 'text-primary' : '' }}">
                                             <i class="fas fa-user fa-fw me-1"></i>
                                             {{ $comment->user->name ?? '[Đã xóa]' }}
                                              @if($isAdminComment) (Admin) @endif
                                        </span>
                                        <span>{{ $comment->created_at ? $comment->created_at->locale('vi')->diffForHumans() : 'N/A' }}</span>
                                    </div>
                                    <p class="mb-0" style="white-space: pre-wrap;">{{ $comment->content }}</p>
                                </div>
                             @endforeach
                         @else
                             <p class="text-muted small"><em>Chưa có trao đổi nào.</em></p>
                         @endif
                     </div>

                    {{-- Form thêm nhận xét/comment nhanh của Admin --}}
                    <form action="{{ route('admin.diaries.comments.store', $diary) }}" method="POST" class="mt-4 border-top pt-3">
                        @csrf
                        <div class="mb-3">
                            <label for="admin_comment_content" class="form-label">Thêm phản hồi nhanh:</label>
                            <textarea name="content" id="admin_comment_content" rows="3" class="form-control @error('content') is-invalid @enderror" required placeholder="Nhập nội dung phản hồi...">{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="text-end"> {{-- Căn phải nút submit --}}
                            <button type="submit" class="btn btn-sm btn-primary shadow-sm text-white">
                                <span class="icon text-white-50">
                                    <i class="fas fa-reply"></i>
                                </span>
                                <span class="text">Gửi Phản hồi</span>
                            </button>
                        </div>
                    </form>
                </div> {{-- End card-body cho Comment --}}
            </div> {{-- End card cho Comment --}}

        </div> {{-- End Col --}}

         {{-- Có thể thêm cột phụ ở đây nếu cần (ví dụ: thông tin liên quan) --}}
         {{-- <div class="col-lg-4"> --}}
             {{-- Card thông tin phụ --}}
         {{-- </div> --}}

    </div> {{-- End Row --}}

@endsection