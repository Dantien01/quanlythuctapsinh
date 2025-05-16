@php
    use Illuminate\Support\Str;
    use Carbon\Carbon;
@endphp

{{-- resources/views/student/diaries/index.blade.php --}}
@extends('layouts.admin') {{-- Sử dụng layout admin (hoặc layouts.app nếu muốn) --}}

@section('title', 'Nhật ký Thực tập của bạn')

@section('content')

    {{-- Page Heading --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Nhật ký thực tập của bạn</h1>
        {{-- Nút Viết Nhật ký Mới --}}
        <a href="{{ route('student.diaries.create') }}" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Viết Nhật ký Mới
        </a>
    </div>

    {{-- Hiển thị thông báo --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            {{-- Nút đóng dùng class Bootstrap 5 --}}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    {{-- Bạn có thể thêm các khối @if khác cho session 'error', 'warning' nếu cần --}}


    {{-- Card chứa bảng danh sách nhật ký --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách nhật ký đã viết</h6>
            {{-- Có thể thêm bộ lọc theo ngày tháng ở đây nếu cần --}}
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableStudentDiaries" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Tiêu đề</th>
                            <th class="text-center">Ngày viết</th>
                            <th class="text-center">Trạng thái</th>
                            {{-- Bỏ style width cố định --}}
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Giả sử biến $diaries được truyền từ controller --}}
                        @forelse ($diaries ?? [] as $diary) {{-- Thêm ?? [] cho an toàn --}}
                            <tr>
                                <td>{{ $diary->title }}</td>
                                <td class="text-center">{{ $diary->diary_date ? $diary->diary_date->format('d/m/Y') : 'N/A' }}</td>
                                <td class="text-center">
                                    {{-- Hiển thị trạng thái bằng badge (Dùng @class và match) --}}
                                    <span @class([
                                        'badge', 'rounded-pill', // Thêm rounded-pill nếu muốn dạng viên thuốc
                                        'bg-secondary' => $diary->status === 'draft',
                                        'bg-warning text-dark' => $diary->status === 'submitted' || $diary->status === 'pending', // Chữ đen trên nền vàng
                                        'bg-info text-dark' => $diary->status === 'commented', // Chữ đen trên nền xanh dương nhạt
                                        'bg-success' => $diary->status === 'reviewed' || $diary->status === 'approved', // Chữ trắng/nhạt trên nền xanh lá
                                    ])>
                                         {{ match($diary->status) {
                                            'draft' => 'Bản nháp',
                                            'submitted' => 'Đã nộp',
                                            'pending' => 'Chờ duyệt', // Thêm pending nếu có
                                            'commented' => 'Đã xem/NX',
                                            'reviewed' => 'Đã đánh giá',
                                            'approved' => 'Đã duyệt', // Thêm approved nếu có
                                            default => ucfirst($diary->status ?? 'N/A')
                                        } }}
                                    </span>
                                </td>
                                {{-- ===== CẬP NHẬT CỘT HÀNH ĐỘNG ===== --}}
                                <td class="text-center">
                                    <div class="d-inline-flex justify-content-center align-items-center flex-wrap">
                                        {{-- Nút Xem --}}
                                        <a href="{{ route('student.diaries.show', $diary) }}" class="btn btn-info btn-sm m-1" title="Xem chi tiết">
                                            <i class="fas fa-eye fa-sm"></i> Xem
                                        </a>
                                        {{-- Nút Sửa --}}
                                        @if(!in_array($diary->status, ['reviewed', 'approved']))
                                             @can('update', $diary) {{-- Luôn kiểm tra quyền --}}
                                                <a href="{{ route('student.diaries.edit', $diary) }}" class="btn btn-warning btn-sm m-1" title="Chỉnh sửa">
                                                    <i class="fas fa-edit fa-sm"></i> Sửa
                                                </a>
                                             @endcan
                                        @endif
                                        {{-- Nút Xóa --}}
                                        @if($diary->status === 'draft' || $diary->status === null)
                                            @can('delete', $diary) {{-- Luôn kiểm tra quyền --}}
                                                <form action="{{ route('student.diaries.destroy', $diary) }}" method="POST" class="d-inline m-1" onsubmit="return confirm('Bạn chắc chắn muốn xóa nhật ký này?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Xóa">
                                                        <i class="fas fa-trash fa-sm"></i> Xóa
                                                    </button>
                                                </form>
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                                {{-- ===== KẾT THÚC CẬP NHẬT ===== --}}
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Bạn chưa viết nhật ký nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div> {{-- End table-responsive --}}

            {{-- Phân trang --}}
            <div class="mt-4 d-flex justify-content-center">
                 {{-- Kiểm tra kỹ hơn trước khi gọi links() --}}
                 @if(isset($diaries) && $diaries instanceof \Illuminate\Contracts\Pagination\Paginator)
                    {{ $diaries->links() }}
                 @endif
            </div>

        </div> {{-- End card-body --}}
    </div> {{-- End card --}}

@endsection