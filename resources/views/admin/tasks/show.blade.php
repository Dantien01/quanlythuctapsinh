{{-- resources/views/admin/tasks/show.blade.php --}}
@extends('layouts.admin')

@php
    // Đưa use statements lên đầu cho dễ quản lý
    use Carbon\Carbon;
    // Không cần use App\Models\Task; ở đây vì $task đã là instance rồi
@endphp

@section('title', __('Chi tiết Công việc') . ': ' . Str::limit($task->title, 50))

@push('styles')
    {{-- Copy các style badge từ index.blade.php nếu cần --}}
    <style>
        .task-detail-card .list-group-item { border: none; padding-left: 0; padding-right: 0; }
        .task-detail-card .list-group-item strong { min-width: 120px; display: inline-block;} /* Căn chỉnh label */
        .badge-priority-high { background-color: #e74a3b; color: white; }
        .badge-priority-medium { background-color: #f6c23e; color: #5a5c69; }
        .badge-priority-low { background-color: #1cc88a; color: white; }
        .badge-status-todo { background-color: #4e73df; color: white; }
        .badge-status-in_progress { background-color: #36b9cc; color: white; }
        .badge-status-completed { background-color: #1cc88a; color: white; }
        .badge-status-overdue { background-color: #e74a3b; color: white; }

        /* Style cho khu vực tiến độ (tương tự trang sinh viên) */
        .task-progress-item .notes-content {
            /* Có thể thêm style cho phần ghi chú nếu cần */
        }
    </style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
        <div>
            <a href="{{ route('admin.tasks.edit', $task) }}" class="btn btn-primary btn-sm shadow-sm me-2"> {{-- Sửa mr-2 thành me-2 cho Bootstrap 5 --}}
                <i class="fas fa-edit fa-sm text-white-50"></i> {{ __('Chỉnh sửa') }}
            </a>
            <a href="{{ route('admin.tasks.index') }}" class="btn btn-secondary btn-sm shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> {{ __('Quay lại danh sách') }}
            </a>
        </div>
    </div>

    @include('partials.alerts')

    <div class="card shadow mb-4 task-detail-card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h4 class="font-weight-bold text-primary mb-3">{{ $task->title }}</h4>
                    <hr>
                    <p class="text-muted small mb-1">{{ __('Mô tả công việc') }}:</p>
                    <div style="white-space: pre-wrap;" class="mb-4">{!! nl2br(e($task->description ?? 'Chưa có mô tả.')) !!}</div>
                    {{-- nl2br giữ lại xuống dòng, e() để escape HTML entities --}}

                    {{-- ========================================================== --}}
                    {{-- ===== KHU VỰC HIỂN THỊ LỊCH SỬ CẬP NHẬT TIẾN ĐỘ ===== --}}
                    {{-- ========================================================== --}}
                    <hr class="my-4"> {{-- Thêm đường kẻ phân cách rõ ràng hơn --}}
                    <h5 class="text-gray-700 mb-3">{{ __('Lịch sử cập nhật tiến độ') }}</h5>

                    @if($task->progressEntries && $task->progressEntries->isNotEmpty())
                        <div class="list-group list-group-flush">
                            @foreach ($task->progressEntries as $progress)
                                <div class="list-group-item px-0 py-3 task-progress-item border-bottom"> {{-- Thêm border-bottom cho mỗi item --}}
                                    <div class="d-flex w-100 justify-content-between">
                                        <div class="notes-content mb-1" style="white-space: pre-wrap;">{!! nl2br(e($progress->notes)) !!}</div>
                                        {{-- Admin không có nút sửa/xóa progress ở đây theo mặc định --}}
                                    </div>
                                    <small class="text-muted d-flex align-items-center flex-wrap"> {{-- Thêm flex-wrap để xuống dòng nếu không đủ chỗ --}}
                                        <span class="me-3">
                                            <i class="fas fa-user fa-fw me-1" title="Cập nhật bởi"></i>
                                            <span class="fw-bold">{{ $progress->student->name ?? __('N/A') }}</span>
                                        </span>
                                        <span class="me-3">
                                            <i class="fas fa-calendar-alt fa-fw me-1"></i>
                                            <span>{{ $progress->submitted_at ? Carbon::parse($progress->submitted_at)->format('d/m/Y H:i') : ($progress->created_at ? Carbon::parse($progress->created_at)->format('d/m/Y H:i') : 'N/A') }}</span>
                                        </span>
                                        @if(!is_null($progress->progress_percentage))
                                            <div class="progress me-1" style="width: 120px; height: 18px; flex-shrink: 0;">
                                                @php
                                                    $bgColor = 'bg-primary'; // Màu mặc định
                                                    if ($progress->progress_percentage == 100) $bgColor = 'bg-success';
                                                    elseif ($progress->progress_percentage < 50 && $progress->progress_percentage > 0) $bgColor = 'bg-warning';
                                                @endphp
                                                <div class="progress-bar progress-bar-striped {{ $bgColor }}" role="progressbar"
                                                     style="width: {{ $progress->progress_percentage }}%; font-size: 0.75rem;"
                                                     aria-valuenow="{{ $progress->progress_percentage }}" aria-valuemin="0" aria-valuemax="100">
                                                     {{ $progress->progress_percentage }}%
                                                </div>
                                            </div>
                                        @endif
                                    </small>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted font-italic">{{ __('Chưa có cập nhật tiến độ nào cho công việc này.') }}</p>
                    @endif
                    {{-- ========================================================== --}}
                    {{-- ===== KẾT THÚC KHU VỰC LỊCH SỬ CẬP NHẬT TIẾN ĐỘ ===== --}}
                    {{-- ========================================================== --}}

                </div>
                <div class="col-md-4 border-left">
                    <h5 class="text-gray-700 mb-3">{{ __('Thông tin chi tiết') }}</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <strong>{{ __('ID Công việc') }}:</strong> #{{ $task->id }}
                        </li>
                        <li class="list-group-item">
                            <strong>{{ __('Sinh viên') }}:</strong>
                            @if($task->intern)
                                <a href="{{ route('admin.users.show', $task->intern_id) }}">{{ $task->intern->name }}</a>
                                ({{ $task->intern->mssv ?? 'N/A' }})
                            @else
                                {{ __('N/A') }}
                            @endif
                        </li>
                        <li class="list-group-item">
                            <strong>{{ __('Người giao') }}:</strong>
                            @if($task->assigner)
                                {{ $task->assigner->name }}
                            @else
                                {{ __('N/A') }}
                            @endif
                        </li>
                        <li class="list-group-item">
                            <strong>{{ __('Ngày giao') }}:</strong> {{ $task->created_at ? Carbon::parse($task->created_at)->format('d/m/Y H:i') : 'N/A' }}
                        </li>
                        <li class="list-group-item {{ $task->is_overdue && $task->status !== \App\Models\Task::STATUS_COMPLETED ? 'text-danger font-weight-bold' : '' }}">
                            <strong>{{ __('Hạn chót') }}:</strong> {{ $task->due_date ? Carbon::parse($task->due_date)->format('d/m/Y') : 'N/A' }}
                        </li>
                        <li class="list-group-item">
                            <strong>{{ __('Trạng thái') }}:</strong>
                            <span class="badge badge-status-{{ str_replace(' ', '_', $task->status) }} p-2">{{ $task->status_text }}</span>
                            @if($task->is_overdue && $task->status !== \App\Models\Task::STATUS_COMPLETED)
                                <span class="badge badge-danger ms-1">{{ __('Quá hạn') }}</span> {{-- Sửa ml-1 thành ms-1 --}}
                            @endif
                        </li>
                        <li class="list-group-item">
                            <strong>{{ __('Ưu tiên') }}:</strong>
                            @if($task->priority)
                                <span class="badge badge-priority-{{ $task->priority }} p-2">{{ $task->priority_text }}</span>
                            @else
                                 <span class="text-muted small">N/A</span>
                            @endif
                        </li>
                         <li class="list-group-item">
                            <strong>{{ __('Cập nhật cuối') }}:</strong> {{ $task->updated_at ? Carbon::parse($task->updated_at)->diffForHumans() : 'N/A' }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {{-- Không cần JS đặc biệt trừ khi Admin có hành động tương tác với progress --}}
@endpush