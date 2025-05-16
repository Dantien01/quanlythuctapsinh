{{-- resources/views/admin/schedules/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Quản lý Lịch Thực Tập')

@section('content')

    {{-- Page Heading --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quản lý Lịch Thực Tập</h1>
        {{-- Nút Thêm Lịch Mới --}}
        <a href="{{ route('admin.schedules.create') }}" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Thêm Lịch Mới
        </a>
    </div>

    {{-- Hiển thị thông báo thành công/lỗi (nếu có) --}}
     @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
     @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
     @endif


    {{-- Bộ lọc theo trạng thái (Ví dụ đơn giản bằng links) --}}
    <div class="mb-3">
        <span>Lọc theo trạng thái:</span>
        <a href="{{ route('admin.schedules.index') }}" class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-secondary' }} mx-1">Tất cả</a>
        <a href="{{ route('admin.schedules.index', ['status' => 'scheduled']) }}" class="btn btn-sm {{ request('status') == 'scheduled' ? 'btn-primary' : 'btn-outline-secondary' }} mx-1">Đã lên lịch</a>
        <a href="{{ route('admin.schedules.index', ['status' => 'pending_change']) }}" class="btn btn-sm {{ request('status') == 'pending_change' ? 'btn-primary' : 'btn-outline-secondary' }} mx-1">Chờ duyệt thay đổi</a>
        <a href="{{ route('admin.schedules.index', ['status' => 'updated']) }}" class="btn btn-sm {{ request('status') == 'updated' ? 'btn-primary' : 'btn-outline-secondary' }} mx-1">Đã cập nhật</a>
        <a href="{{ route('admin.schedules.index', ['status' => 'completed']) }}" class="btn btn-sm {{ request('status') == 'completed' ? 'btn-primary' : 'btn-outline-secondary' }} mx-1">Đã hoàn thành</a>
        <a href="{{ route('admin.schedules.index', ['status' => 'cancelled']) }}" class="btn btn-sm {{ request('status') == 'cancelled' ? 'btn-primary' : 'btn-outline-secondary' }} mx-1">Đã hủy</a>
        {{-- Thêm các trạng thái khác nếu cần --}}
    </div>


    {{-- Card chứa bảng dữ liệu --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách Lịch trình</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableSchedules" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Sinh viên</th>
                            <th>Tiêu đề</th>
                            <th>Bắt đầu</th>
                            <th>Kết thúc</th>
                            <th>Trạng thái</th>
                            <th>Lý do yêu cầu thay đổi</th> {{-- Lý do yêu cầu thay đổi --}}
                            <th>Người tạo</th>
                            <th class="text-center">Tùy chọn</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($schedules as $schedule)
                            <tr>
                                <td>{{ $schedule->id }}</td>
                                <td>
                                    @if($schedule->student)
                                        {{ $schedule->student->name ?? 'N/A' }} <br>
                                        <small class="text-muted">{{ $schedule->student->mssv ?? $schedule->student->email }}</small>
                                    @else
                                        <span class="text-muted italic">Chưa gán</span>
                                    @endif
                                </td>
                                <td>{{ $schedule->title }}</td>
                                <td>{{ $schedule->start_time ? $schedule->start_time->format('d/m/Y H:i') : '--' }}</td>
                                <td>{{ $schedule->end_time ? $schedule->end_time->format('d/m/Y H:i') : '--' }}</td>
                                <td>
                                    {{-- Sử dụng Bootstrap Badges --}}
                                    <span @class([
                                        'badge',
                                        'bg-primary' => $schedule->status === 'scheduled',
                                        'bg-warning text-dark' => $schedule->status === 'pending_change',
                                        'bg-info text-dark' => $schedule->status === 'updated',
                                        'bg-success' => $schedule->status === 'completed',
                                        'bg-danger' => $schedule->status === 'cancelled',
                                        'bg-secondary' => !in_array($schedule->status, ['scheduled', 'pending_change', 'updated', 'completed', 'cancelled'])
                                    ])>
                                        {{-- Chuyển đổi sang tiếng Việt nếu cần --}}
                                        {{ match($schedule->status) {
                                            'scheduled' => 'Đã lên lịch',
                                            'pending_change' => 'Chờ duyệt đổi',
                                            'updated' => 'Đã cập nhật',
                                            'completed' => 'Hoàn thành',
                                            'cancelled' => 'Đã hủy',
                                            default => ucfirst($schedule->status ?? 'N/A')
                                        } }}
                                    </span>
                                </td>
                                {{-- Hiển thị lý do yêu cầu thay đổi (nếu có) --}}
                                <td class="text-muted small" title="{{ $schedule->change_reason }}">
                                    {{ $schedule->change_reason ? Str::limit($schedule->change_reason, 30) : '--' }}
                                </td>
                                <td>{{ $schedule->creator->name ?? 'N/A' }}</td> {{-- Người tạo lịch --}}
                                <td class="text-center">
                                    {{-- Nút Sửa --}}
                                    <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-warning btn-sm me-1" title="Sửa">
                                        <i class="fas fa-edit fa-sm"></i>
                                    </a>
                                    {{-- Nút Xóa --}}
                                    <form action="{{ route('admin.schedules.destroy', $schedule) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn chắc chắn muốn xóa lịch này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Xóa">
                                            <i class="fas fa-trash fa-sm"></i>
                                        </button>
                                    </form>
                                     {{-- Có thể thêm nút Xem ở đây nếu cần route('admin.schedules.show', $schedule) --}}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Không có lịch thực tập nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div> {{-- End table-responsive --}}

            {{-- Phân trang --}}
            <div class="mt-4 d-flex justify-content-center">
                 {{-- Đảm bảo $schedules là Paginator và gọi appends --}}
                 @if($schedules instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    {{ $schedules->appends(request()->query())->links() }}
                 @endif
            </div>

        </div> {{-- End card-body --}}
    </div> {{-- End card --}}

@endsection

{{-- (Tùy chọn) Stack cho scripts/styles nếu dùng DataTables JS --}}
{{-- @push('scripts') --}}
    {{-- <script> $(document).ready(function() { $('#dataTableSchedules').DataTable(); }); </script> --}}
{{-- @endpush --}}
{{-- @push('styles') --}}
    {{-- <link ...> --}}
{{-- @endpush --}}