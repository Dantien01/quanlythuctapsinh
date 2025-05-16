{{-- resources/views/student/schedule/index.blade.php --}}
@extends('layouts.admin') {{-- Kế thừa từ layout admin --}}

@section('title', 'Lịch thực tập') {{-- Đặt tiêu đề trang --}}

@section('content')

    {{-- Page Heading --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Lịch trình thực tập của bạn</h1>
    </div>

     {{-- Hiển thị thông báo thành công/lỗi nếu có sau khi submit form --}}
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

    {{-- Bảng hiển thị lịch trình --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách lịch trình</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="scheduleTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>TIÊU ĐỀ</th>
                            <th>MÔ TẢ</th>
                            <th>BẮT ĐẦU</th>
                            <th>KẾT THÚC</th>
                            <th>TRẠNG THÁI</th>
                            <th>NGƯỜI TẠO</th>
                            <th>YÊU CẦU THAY ĐỔI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($schedules ?? [] as $schedule)
                            <tr>
                                <td>{{ $schedule->title }}</td>
                                <td>{{ $schedule->description ?? '-' }}</td>
                                <td>
                                    {{ $schedule->start_time ? \Carbon\Carbon::parse($schedule->start_time)->format('d/m/Y H:i') : 'N/A' }}
                                </td>
                                <td>
                                    {{ $schedule->end_time ? \Carbon\Carbon::parse($schedule->end_time)->format('d/m/Y H:i') : 'N/A' }}
                                </td>
                                <td>
                                    {{-- Hiển thị trạng thái bằng badge cho đẹp hơn --}}
                                     <span @class([
                                        'badge',
                                        'bg-info' => $schedule->status === 'updated' || $schedule->status === 'scheduled',
                                        'bg-warning text-dark' => $schedule->status === 'pending_change', // Trạng thái chờ duyệt thay đổi
                                        'bg-success' => $schedule->status === 'approved', // Đã duyệt
                                        'bg-danger' => $schedule->status === 'rejected_change', // Bị từ chối thay đổi
                                        'bg-secondary' => !in_array($schedule->status, ['updated', 'scheduled', 'pending_change', 'approved', 'rejected_change'])
                                    ])>
                                        {{-- Hiển thị text tương ứng với status --}}
                                        {{ match($schedule->status) {
                                            'updated' => 'Đã cập nhật',
                                            'scheduled' => 'Đã lên lịch',
                                            'pending_change' => 'Chờ duyệt YC',
                                            'approved' => 'Đã duyệt',
                                            'rejected_change' => 'Từ chối YC',
                                            default => ucfirst($schedule->status ?? 'N/A')
                                        } }}
                                    </span>
                                </td>
                                <td>
                                    {{-- Sử dụng relationship 'creator' đã định nghĩa trong model Schedule (nếu có) --}}
                                    {{ $schedule->creator->name ?? 'Không xác định' }}
                                </td>
                                <td class="text-center">
                                     {{-- ===== SỬA LẠI PHẦN NÀY ===== --}}

                                     {{-- Kiểm tra trạng thái để quyết định có cho yêu cầu hay không --}}
                                     @if(in_array($schedule->status, ['scheduled', 'updated', 'approved', 'rejected_change'])) {{-- Cho phép yêu cầu khi lịch đã được duyệt, cập nhật hoặc YC cũ bị từ chối --}}
                                         <form action="{{ route('student.schedule.requestChange', $schedule->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn gửi yêu cầu thay đổi cho lịch trình này?');">
                                             @csrf {{-- Quan trọng: Phải có CSRF token --}}
                                             {{-- Không cần @method('PUT') vì route là POST --}}
                                             {{-- Có thể thêm input ẩn hoặc textarea nếu cần sinh viên nhập lý do --}}
                                             {{-- <textarea name="reason" placeholder="Lý do yêu cầu thay đổi (nếu có)" class="form-control form-control-sm mb-1"></textarea> --}}
                                             <button type="submit" class="btn btn-sm btn-warning" title="Yêu cầu thay đổi lịch trình này">
                                                 <i class="fas fa-edit fa-sm"></i> Yêu cầu
                                             </button>
                                         </form>
                                     @elseif($schedule->status === 'pending_change')
                                          {{-- Hiển thị trạng thái đang chờ duyệt --}}
                                         <span class="badge bg-warning text-dark" title="Yêu cầu thay đổi của bạn đang chờ Admin duyệt">
                                             <i class="fas fa-clock fa-sm"></i> Đang chờ
                                         </span>
                                     @else
                                          {{-- Các trạng thái khác (vd: rejected) có thể không cho yêu cầu --}}
                                          <button class="btn btn-sm btn-secondary disabled" title="Không thể yêu cầu thay đổi với trạng thái này">
                                              <i class="fas fa-edit"></i> Yêu cầu
                                          </button>
                                     @endif
                                     {{-- =============================== --}}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Bạn chưa được gán lịch trình thực tập nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
{{-- Script cho thông báo tự biến mất (nếu cần) --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const successAlert = document.getElementById('success-alert');
        if (successAlert) { setTimeout(() => { if (typeof bootstrap !== 'undefined' && bootstrap.Alert) { const alertInstance = bootstrap.Alert.getOrCreateInstance(successAlert); if(alertInstance) { alertInstance.close(); } else { successAlert.style.display = 'none'; } } else { successAlert.style.display = 'none'; } }, 5000); }
        const errorAlert = document.getElementById('error-alert');
        if (errorAlert) { setTimeout(() => { if (typeof bootstrap !== 'undefined' && bootstrap.Alert) { const alertInstance = bootstrap.Alert.getOrCreateInstance(errorAlert); if(alertInstance) { alertInstance.close(); } else { errorAlert.style.display = 'none'; } } else { errorAlert.style.display = 'none'; } }, 8000); }
    });
</script>
@endpush

{{-- Không cần push styles nếu không dùng DataTables --}}