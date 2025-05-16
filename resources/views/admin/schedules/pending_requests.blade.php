{{-- resources/views/admin/schedules/pending_requests.blade.php --}}
@extends('layouts.admin')

@section('title', 'Yêu cầu Thay đổi Lịch Chờ duyệt')

@section('content')

    {{-- Page Heading --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Yêu cầu Thay đổi Lịch Chờ duyệt</h1>
         {{-- Nút quay lại danh sách lịch chính --}}
         <a href="{{ route('admin.schedules.index') }}" class="btn btn-sm btn-primary shadow-sm text-white">
            <i class="fas fa-list fa-sm text-white"></i> Xem tất cả Lịch
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

    {{-- Card chứa bảng dữ liệu --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách Yêu cầu</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTablePendingSchedules" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Sinh viên</th>
                            <th>Lịch gốc</th>
                            <th>Lý do yêu cầu</th> {{-- Thêm cột lý do nếu có --}}
                            <th>Yêu cầu lúc</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Lặp qua $pendingSchedules được truyền từ controller --}}
                        @forelse ($pendingSchedules as $schedule)
                            <tr>
                                <td>
                                    {{-- Giả sử relationship là student --}}
                                    {{ $schedule->student->name ?? 'N/A' }} <br>
                                    <small class="text-muted">{{ $schedule->student->mssv ?? $schedule->student->email }}</small>
                                </td>
                                <td>
                                    {{ $schedule->title }} <br>
                                    <small class="text-muted">
                                        {{ $schedule->start_time ? $schedule->start_time->format('d/m/y H:i') : 'N/A' }} -
                                        {{ $schedule->end_time ? $schedule->end_time->format('d/m/y H:i') : 'N/A' }}
                                    </small>
                                    {{-- Hiển thị mô tả lịch gốc nếu cần --}}
                                     {{-- @if($schedule->description)
                                        <p class="small text-muted mt-1 fst-italic" title="{{ $schedule->description }}">Mô tả: {{ Str::limit($schedule->description, 50) }}</p>
                                     @endif --}}
                                </td>
                                {{-- Hiển thị lý do thay đổi (cần cột change_reason trong DB) --}}
                                <td class="small text-muted" title="{{ $schedule->change_reason ?? '' }}">
                                    {{ $schedule->change_reason ? Str::limit($schedule->change_reason, 50) : '--' }}
                                </td>
                                <td>
                                    {{ $schedule->updated_at ? $schedule->updated_at->format('d/m/Y H:i') : 'N/A' }} <br>
                                    <small class="text-muted">({{ $schedule->updated_at ? $schedule->updated_at->locale('vi')->diffForHumans() : '' }})</small>
                                </td>
                                <td class="text-center">
                                    {{-- Nút Approve --}}
                                    <form action="{{ route('admin.schedules.approveChange', $schedule) }}" method="POST" class="d-inline" onsubmit="return confirm('Phê duyệt yêu cầu thay đổi lịch này?');">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-success btn-sm me-1" title="Duyệt">
                                            <i class="fas fa-check fa-sm"></i> Duyệt
                                        </button>
                                    </form>
                                    {{-- Nút Reject --}}
                                     <form action="{{ route('admin.schedules.rejectChange', $schedule) }}" method="POST" class="d-inline" onsubmit="return confirm('Từ chối yêu cầu thay đổi lịch này?');">
                                        @csrf
                                        @method('PUT')
                                        {{-- TODO: Cân nhắc thêm input ẩn/modal để nhập lý do từ chối --}}
                                        <button type="submit" class="btn btn-danger btn-sm" title="Từ chối">
                                            <i class="fas fa-times fa-sm"></i> Từ chối
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Không có yêu cầu thay đổi lịch nào đang chờ duyệt.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div> {{-- End table-responsive --}}

            {{-- Phân trang --}}
            <div class="mt-4 d-flex justify-content-center">
                 @if($pendingSchedules instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    {{ $pendingSchedules->links() }}
                 @endif
            </div>

        </div> {{-- End card-body --}}
    </div> {{-- End card --}}

@endsection