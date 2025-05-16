{{-- Đổi layout kế thừa thành layout admin mới --}}
@extends('layouts.admin')

@section('title', 'Quản lý Nhật ký Thực tập')

@section('content')

    {{-- Sử dụng cấu trúc Page Heading của SB Admin 2 --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quản lý Nhật ký Thực tập</h1>
        {{-- Có thể thêm nút ở đây nếu cần --}}
    </div>

    {{-- Form Filter - Sử dụng Card và Form của Bootstrap/SB Admin 2 --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Bộ lọc Nhật ký</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.diaries.index') }}">
                {{-- Sử dụng Bootstrap grid row/col --}}
                <div class="row align-items-end">
                    <div class="col-md-5 mb-3 mb-md-0">
                        <label for="student_id" class="form-label">Lọc theo sinh viên:</label>
                        {{-- Sử dụng class form-select của Bootstrap --}}
                        <select name="student_id" id="student_id" class="form-select form-select-sm">
                            <option value="">-- Tất cả sinh viên --</option>
                            @isset($students)
                                @foreach ($students as $student)
                                    <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                        {{ $student->name }} ({{ $student->mssv ?? $student->email }})
                                    </option>
                                @endforeach
                            @endisset
                        </select>
                    </div>
                     <div class="col-md-4 mb-3 mb-md-0">
                        <label for="status" class="form-label">Lọc theo trạng thái:</label>
                        <select name="status" id="status" class="form-select form-select-sm">
                            <option value="">-- Tất cả trạng thái --</option>
                            <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Chờ nhận xét</option>
                            <option value="commented" {{ request('status') == 'commented' ? 'selected' : '' }}>Đã nhận xét nhanh</option>
                            <option value="reviewed" {{ request('status') == 'reviewed' ? 'selected' : '' }}>Đã đánh giá</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        {{-- Sử dụng class btn của Bootstrap --}}
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-filter fa-sm"></i> Lọc
                        </button>
                        <a href="{{ route('admin.diaries.index') }}" class="btn btn-secondary btn-sm ml-2">
                            Xóa lọc
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Bảng Dữ liệu - Sử dụng Card và Table của Bootstrap/SB Admin 2 --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách Nhật ký</h6>
        </div>
        <div class="card-body">
            {{-- Div cho phép cuộn ngang bảng trên màn hình nhỏ --}}
            <div class="table-responsive">
                {{-- Sử dụng class table của Bootstrap --}}
                <table class="table table-bordered table-hover" id="dataTableDiaries" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            {{-- class text-xs không có sẵn trong Bootstrap, dùng small hoặc mặc định --}}
                            <th>Sinh viên</th>
                            <th>Tiêu đề</th>
                            <th>Ngày viết</th>
                            <th>Trạng thái</th>
                            <th class="text-center">Điểm</th>
                            <th>Người Đánh giá</th>
                            <th>Ngày Đánh giá</th>
                            <th class="text-right">Hành động</th> {{-- text-right của Bootstrap --}}
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($diaries as $diary)
                            <tr>
                                {{-- Sử dụng text-muted, small của Bootstrap --}}
                                <td>
                                    {{ $diary->user->name ?? 'N/A' }} <br>
                                    <small class="text-muted">{{ $diary->user->mssv ?? $diary->user->email }}</small>
                                </td>
                                <td class="font-weight-bold">{{ Str::limit($diary->title, 45) }}</td>
                                <td>{{ $diary->diary_date ? $diary->diary_date->format('d/m/Y') : 'N/A' }}</td>
                                <td>
                                    {{-- Sử dụng class badge của Bootstrap --}}
                                    <span @class([
                                        'badge',
                                        'bg-warning text-dark' => $diary->status === 'submitted' || $diary->status === 'pending',
                                        'bg-info text-dark' => $diary->status === 'commented',
                                        'bg-success' => $diary->status === 'reviewed' || $diary->status === 'approved',
                                        'bg-secondary' => !in_array($diary->status, ['submitted', 'pending', 'commented', 'reviewed', 'approved'])
                                    ])>
                                         {{ match($diary->status) {
                                            'submitted' => 'Chờ NX',
                                            'commented' => 'Đã NX nhanh',
                                            'reviewed' => 'Đã ĐG',
                                            default => ucfirst($diary->status ?? 'Draft')
                                        } }}
                                    </span>
                                </td>
                                {{-- Sử dụng class font-weight-bold, text-success/danger/muted của Bootstrap --}}
                                <td class="text-center font-weight-bold {{ $diary->grade !== null ? ($diary->grade >= 5 ? 'text-success' : 'text-danger') : 'text-muted' }}">
                                    {{ $diary->grade ?? '-' }}
                                </td>
                                <td>{{ $diary->reviewer->name ?? '-' }}</td>
                                <td>{{ $diary->reviewed_at ? $diary->reviewed_at->format('d/m/y H:i') : '-' }}</td>
                                <td class="text-right">
                                    {{-- Sử dụng class btn của Bootstrap và icon FontAwesome --}}
                                    <a href="{{ route('admin.diaries.show', $diary) }}" class="btn btn-info btn-sm" title="Xem / Đánh giá">
                                        <i class="fas fa-eye fa-sm"></i>
                                    </a>
                                    {{-- Có thể thêm nút xóa nếu cần --}}
                                     {{-- <form action="{{ route('admin.diaries.destroy', $diary) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn chắc chắn muốn xóa?');">
                                         @csrf
                                         @method('DELETE')
                                         <button type="submit" class="btn btn-danger btn-sm" title="Xóa">
                                             <i class="fas fa-trash fa-sm"></i>
                                         </button>
                                     </form> --}}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    Không tìm thấy nhật ký nào phù hợp với bộ lọc của bạn.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div> {{-- End table-responsive --}}

            {{-- Phân trang - dùng d-flex để căn giữa nếu cần --}}
            <div class="mt-4 d-flex justify-content-center">
                {{ $diaries->appends(request()->query())->links() }}
            </div>

        </div> {{-- End card-body --}}
    </div> {{-- End card --}}

@endsection

{{-- (Tùy chọn) Stack cho scripts nếu bạn muốn dùng DataTables JS --}}
@push('scripts')
    {{-- Ví dụ nếu bạn muốn dùng DataTables --}}
    {{-- <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script> --}}
    {{-- <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script> --}}
    {{-- <script> --}}
    {{-- $(document).ready(function() { --}}
    {{--   $('#dataTableDiaries').DataTable(); --}}
    {{-- }); --}}
    {{-- </script> --}}
@endpush

{{-- (Tùy chọn) Stack cho styles nếu cần CSS riêng cho DataTables --}}
@push('styles')
    {{-- <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet"> --}}
@endpush