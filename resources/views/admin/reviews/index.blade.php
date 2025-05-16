{{-- resources/views/admin/reviews/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Quản lý Nhận xét Sinh viên')

@section('content')

    {{-- Page Heading --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Nhận xét Sinh viên Đã viết</h1>
        {{-- Nút Viết Nhận Xét Mới --}}
        <a href="{{ route('admin.reviews.create') }}" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Viết Nhận Xét Mới
        </a>
    </div>

     {{-- Hiển thị thông báo thành công/lỗi --}}
     @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
     @endif

    {{-- Filter Form - Đặt trong Card --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
             <h6 class="m-0 font-weight-bold text-primary">Lọc Nhận xét</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reviews.index') }}">
                <div class="row align-items-end">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label for="student_id" class="form-label">Lọc theo sinh viên:</label>
                        <select name="student_id" id="student_id" class="form-select form-select-sm">
                            <option value="">-- Tất cả sinh viên --</option>
                            {{-- Giả sử có biến $students từ Controller --}}
                            @isset($students)
                                @foreach ($students as $student)
                                    <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                        {{ $student->name }} ({{ $student->mssv ?? $student->email }})
                                    </option>
                                @endforeach
                            @endisset
                        </select>
                    </div>
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-filter fa-sm"></i> Lọc
                        </button>
                        <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary btn-sm ml-2">
                            Xóa lọc
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>


    {{-- Bảng danh sách Nhận xét --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách Nhận xét</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableReviews" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Sinh viên</th>
                            <th>Kỳ Nhận xét</th> {{-- Hoặc Tiêu đề Nhận xét --}}
                            <th>Nội dung (Trích đoạn)</th>
                            <th class="text-center">Ngày viết</th>
                            <th class="text-center">Tùy chọn</th>
                        </tr>
                    </thead>
                    <tbody>
                         {{-- Giả sử có biến $reviews từ Controller --}}
                        @forelse ($reviews as $review)
                            <tr>
                                <td>
                                    {{-- Giả sử có relationship 'student' trong model Review --}}
                                    {{ $review->student->name ?? 'N/A' }} <br>
                                    <small class="text-muted">{{ $review->student->mssv ?? $review->student->email }}</small>
                                </td>
                                <td>{{ $review->review_period ?? $review->title ?? 'N/A' }}</td> {{-- Hiển thị kỳ hoặc tiêu đề --}}
                                <td class="small">{{ Str::limit($review->content ?? '', 100) }}</td> {{-- Hiển thị trích đoạn nội dung --}}
                                <td class="text-center">{{ $review->created_at ? $review->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                <td class="text-center">
                                    {{-- Nút Sửa (Nếu có chức năng sửa) --}}
                                    {{-- <a href="{{ route('admin.reviews.edit', $review) }}" class="btn btn-warning btn-sm me-1" title="Sửa">
                                        <i class="fas fa-edit fa-sm"></i>
                                    </a> --}}
                                     {{-- Nút Xem (Có thể dẫn đến trang chi tiết hoặc modal) --}}
                                     <button type="button" class="btn btn-info btn-sm me-1" data-bs-toggle="modal" data-bs-target="#reviewDetailModal{{ $review->id }}" title="Xem chi tiết">
                                         <i class="fas fa-eye fa-sm"></i>
                                     </button>
                                    {{-- Nút Xóa --}}
                                    <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn chắc chắn muốn xóa nhận xét này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Xóa">
                                            <i class="fas fa-trash fa-sm"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                              <!-- Modal Xem Chi Tiết (Tạo cho mỗi review) -->
                             <div class="modal fade" id="reviewDetailModal{{ $review->id }}" tabindex="-1" aria-labelledby="reviewDetailModalLabel{{ $review->id }}" aria-hidden="true">
                               <div class="modal-dialog modal-lg modal-dialog-centered">
                                 <div class="modal-content">
                                   <div class="modal-header">
                                     <h5 class="modal-title" id="reviewDetailModalLabel{{ $review->id }}">Chi tiết Nhận xét - {{ $review->student->name ?? 'N/A' }} ({{ $review->review_period ?? $review->title ?? 'N/A' }})</h5>
                                     <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                   </div>
                                   <div class="modal-body" style="white-space: pre-wrap;"> {{-- Giữ lại xuống dòng --}}
                                     {{ $review->content ?? 'Không có nội dung.' }}
                                   </div>
                                   <div class="modal-footer">
                                     <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                   </div>
                                 </div>
                               </div>
                             </div>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Không có nhận xét nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div> {{-- End table-responsive --}}

            {{-- Phân trang --}}
            <div class="mt-4 d-flex justify-content-center">
                 @if($reviews instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    {{ $reviews->appends(request()->query())->links() }}
                 @endif
            </div>

        </div> {{-- End card-body --}}
    </div> {{-- End card --}}

@endsection