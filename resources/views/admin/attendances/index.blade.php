{{-- resources/views/admin/attendances/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Quản lý Điểm danh')

@section('content')

    {{-- Page Heading --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quản lý Điểm danh</h1>
        {{-- Có thể thêm nút export ở đây nếu cần --}}
    </div>

    {{-- Form Filter --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Lọc kết quả Điểm danh</h6>
        </div>
        <div class="card-body">
            {{-- Giả sử bạn filter theo sinh viên và ngày --}}
            <form method="GET" action="{{ route('admin.attendances.index') }}">
                <div class="row align-items-end">
                    {{-- Lọc theo sinh viên --}}
                    <div class="col-md-5 mb-3 mb-md-0">
                        <label for="student_id" class="form-label">Sinh viên:</label>
                        <select name="student_id" id="student_id" class="form-select form-select-sm">
                            <option value="">-- Tất cả sinh viên --</option>
                            {{-- Giả sử có biến $students được truyền từ controller --}}
                            @isset($students)
                                @foreach ($students as $student)
                                    <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                        {{ $student->name }} ({{ $student->mssv ?? $student->email }})
                                    </option>
                                @endforeach
                            @endisset
                        </select>
                    </div>
                    {{-- Lọc theo ngày --}}
                    <div class="col-md-4 mb-3 mb-md-0">
                        <label for="attendance_date" class="form-label">Ngày:</label>
                        {{-- Sử dụng input type="date" --}}
                        <input type="date" name="attendance_date" id="attendance_date" class="form-control form-control-sm" value="{{ request('attendance_date', now()->format('Y-m-d')) }}">
                    </div>
                    {{-- Nút Lọc và Xóa lọc --}}
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-filter fa-sm"></i> Lọc
                        </button>
                        <a href="{{ route('admin.attendances.index') }}" class="btn btn-secondary btn-sm ml-2">
                            Xóa lọc
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Bảng Hiển thị Kết quả Điểm danh --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Kết quả Điểm danh</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableAttendances" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Sinh viên</th>
                            <th class="text-center">Ngày</th>
                            <th class="text-center">Check-in</th>
                            <th class="text-center">Check-out</th>
                            <th class="text-center">Trạng thái</th>
                            <th>Ghi chú</th>
                            <th class="text-center">Tùy chọn</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Giả sử $attendances được truyền từ controller --}}
                        @forelse ($attendances as $attendance)
                            <tr>
                                <td>
                                     {{-- Giả sử có relationship 'user' trong model Attendance --}}
                                    {{ $attendance->user->name ?? 'N/A' }} <br>
                                    <small class="text-muted">{{ $attendance->user->mssv ?? $attendance->user->email }}</small>
                                </td>
                                <td class="text-center">
                                    {{-- Giả sử có cột attendance_date hoặc check_in_time --}}
                                    {{ $attendance->attendance_date ? \Carbon\Carbon::parse($attendance->attendance_date)->format('d/m/Y') : ($attendance->check_in_time ? $attendance->check_in_time->format('d/m/Y') : 'N/A') }}
                                </td>
                                <td class="text-center">
                                    {{ $attendance->check_in_time ? $attendance->check_in_time->format('H:i:s') : '-' }}
                                </td>
                                <td class="text-center">
                                    {{ $attendance->check_out_time ? $attendance->check_out_time->format('H:i:s') : '-' }}
                                </td>
                                <td class="text-center">
                                     {{-- Hiển thị trạng thái bằng badge --}}
                                    <span @class([
                                        'badge',
                                        'bg-success' => $attendance->status === 'present' || $attendance->status === 'on_time',
                                        'bg-warning text-dark' => $attendance->status === 'late',
                                        'bg-info text-dark' => $attendance->status === 'excused',
                                        'bg-danger' => $attendance->status === 'absent',
                                        'bg-secondary' => !in_array($attendance->status, ['present', 'on_time', 'late', 'excused', 'absent'])
                                    ])>
                                        {{-- Chuyển đổi status sang tiếng Việt nếu cần --}}
                                        {{ match(strtolower($attendance->status ?? '')) {
                                            'present', 'on_time' => 'Có mặt',
                                            'late' => 'Đi trễ',
                                            'excused' => 'Có phép',
                                            'absent' => 'Vắng mặt',
                                            default => ucfirst($attendance->status ?? 'N/A')
                                        } }}
                                    </span>
                                </td>
                                <td class="small text-muted">
                                    {{-- Giả sử có cột 'notes' --}}
                                    {{ $attendance->notes }}
                                </td>
                                <td class="text-center">
                                    {{-- Nút Sửa (nếu cần) --}}
                                    <a href="{{ route('admin.attendances.edit', $attendance) }}" class="btn btn-warning btn-sm" title="Sửa/Thêm ghi chú">
                                        <i class="fas fa-edit fa-sm"></i>
                                    </a>
                                    {{-- Có thể thêm nút xóa nếu cần --}}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Không có dữ liệu điểm danh cho lựa chọn này.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div> {{-- End table-responsive --}}

             {{-- Phân trang --}}
            <div class="mt-4 d-flex justify-content-center">
                 @if($attendances instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    {{ $attendances->appends(request()->query())->links() }}
                 @endif
            </div>

        </div> {{-- End card-body --}}
    </div> {{-- End card --}}

@endsection

{{-- (Tùy chọn) Stack cho scripts/styles nếu dùng Datepicker hoặc DataTables JS --}}
{{-- @push('scripts') --}}
{{-- @endpush --}}
{{-- @push('styles') --}}
{{-- @endpush --}}