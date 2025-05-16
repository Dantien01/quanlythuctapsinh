{{-- resources/views/student/tasks/show.blade.php --}}
@extends('layouts.admin') {{-- Hoặc tên layout chính của sinh viên --}}

@php
    // Đưa use statements lên đầu cho dễ quản lý
    use Carbon\Carbon;
    use App\Models\Task; // Hoặc TaskStatus enum nếu bạn dùng
@endphp

@section('title', __('Chi tiết Công việc') . ': ' . Str::limit($task->title, 40))

@push('styles')
    {{-- Copy các style badge từ student/tasks/index.blade.php nếu bạn muốn đồng bộ --}}
    <style>
        .task-detail-card .list-group-item { border: none; padding-left: 0; padding-right: 0; }
        .task-detail-card .list-group-item strong { min-width: 130px; display: inline-block; color: #5a5c69;}
        .description-content {
            white-space: pre-wrap; /* Giữ lại định dạng xuống dòng và khoảng trắng */
            background-color: #f8f9fc;
            padding: 15px;
            border-radius: 0.35rem;
            border: 1px solid #e3e6f0;
            max-height: 300px; /* Giới hạn chiều cao, thêm scroll nếu cần */
            overflow-y: auto;
        }
        .status-update-buttons .btn {
            margin-right: 5px;
            margin-bottom: 5px;
        }

        /* Màu sắc cho badge (tương tự index) */
        .badge-status-todo { background-color: #4e73df; color: white; }
        .badge-status-in_progress { background-color: #36b9cc; color: white; }
        .badge-status-completed { background-color: #1cc88a; color: white; }
        .badge-status-overdue { background-color: #e74a3b; color: white; }

        .badge-priority-high { background-color: #e74a3b; color: white; }
        .badge-priority-medium { background-color: #f6c23e; color: #5a5c69; }
        .badge-priority-low { background-color: #1cc88a; color: white; }

        /* Style cho progress item list (nếu muốn tùy chỉnh thêm) */
        .task-progress-item .notes-content {
            /* Có thể thêm style cho phần ghi chú nếu cần */
        }
        .task-progress-item .actions {
            /* Style cho khu vực nút sửa/xóa */
        }
    </style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
        <a href="{{ route('student.tasks.index') }}" class="btn btn-sm btn-outline-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm"></i> {{ __('Quay lại danh sách') }}
        </a>
    </div>

    @include('partials.alerts')

    <div class="row">
        {{-- Cột Thông tin Công việc --}}
        <div class="col-lg-8">
            <div class="card shadow mb-4 task-detail-card">
                <div class="card-header py-3">
                    <h5 class="m-0 font-weight-bold text-primary">{{ $task->title }}</h5>
                </div>
                <div class="card-body">
                    <h6 class="text-muted mb-2">{{ __('Mô tả chi tiết') }}:</h6>
                    @if($task->description)
                        <div class="description-content mb-4">
                            {!! nl2br(e($task->description)) !!}
                        </div>
                    @else
                        <p class="text-muted font-italic">{{ __('Không có mô tả chi tiết.') }}</p>
                    @endif

                    {{-- ================================================== --}}
                    {{-- ===== KHU VỰC HIỂN THỊ TASK PROGRESS ĐÃ CẬP NHẬT ===== --}}
                    {{-- ================================================== --}}
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-muted mb-0">{{ __('Lịch sử Cập nhật Tiến độ') }}</h6>
                        {{-- Nút "Thêm cập nhật" sẽ chỉ hiển thị nếu task chưa hoàn thành hoặc quá hạn --}}
                        @if($task->status !== Task::STATUS_COMPLETED && $task->status !== Task::STATUS_OVERDUE)
                        <a href="{{ route('student.tasks.progress.create', $task) }}" class="btn btn-sm btn-success">
                            <i class="fas fa-plus fa-sm"></i> {{ __('Thêm cập nhật') }}
                        </a>
                        @endif
                    </div>

                    @if($task->progressEntries->isNotEmpty())
                        <div class="list-group list-group-flush">
                            {{-- Giả định $task->progressEntries đã được sắp xếp theo submitted_at desc từ Model/Controller --}}
                            @foreach ($task->progressEntries as $progress)
                                <div class="list-group-item px-0 py-3 task-progress-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <div class="notes-content mb-1" style="white-space: pre-wrap;">{!! nl2br(e($progress->notes)) !!}</div>
                                        {{-- Chỉ cho sửa/xóa nếu sinh viên đó tạo progress này --}}
                                        @if($progress->user_id === Auth::id())
                                        <div class="actions btn-group-sm flex-shrink-0 ms-2" role="group">
                                            <a href="{{ route('student.tasks.progress.edit', ['task' => $task, 'taskProgress' => $progress]) }}" class="btn btn-sm btn-outline-primary py-0 px-1" title="Sửa"><i class="fas fa-edit fa-xs"></i></a>
                                            <form action="{{ route('student.tasks.progress.destroy', ['task' => $task, 'taskProgress' => $progress]) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa cập nhật này?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1" title="Xóa"><i class="fas fa-trash fa-xs"></i></button>
                                            </form>
                                        </div>
                                        @endif
                                    </div>
                                    <small class="text-muted d-flex align-items-center">
                                        <i class="fas fa-calendar-alt fa-fw me-1"></i>
                                        <span>{{ $progress->submitted_at ? Carbon::parse($progress->submitted_at)->format('d/m/Y H:i') : $progress->created_at->format('d/m/Y H:i') }}</span>
                                        @if(!is_null($progress->progress_percentage))
                                            <div class="progress ms-2 me-1" style="width: 120px; height: 18px; flex-shrink: 0;">
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
                        <p class="text-muted font-italic">{{ __('Chưa có cập nhật tiến độ nào.') }}</p>
                    @endif
                    {{-- ================================================== --}}
                    {{-- ===== KẾT THÚC KHU VỰC TASK PROGRESS ===== --}}
                    {{-- ================================================== --}}

                </div>
            </div>
        </div>

        {{-- Cột Thông tin chi tiết & Hành động --}}
        <div class="col-lg-4">
            <div class="card shadow mb-4 task-detail-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Thông tin & Trạng thái') }}</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush mb-3">
                        <li class="list-group-item">
                            <strong>{{ __('Người giao') }}:</strong> {{ $task->assigner->name ?? __('N/A') }}
                        </li>
                        <li class="list-group-item">
                            <strong>{{ __('Ngày giao') }}:</strong> {{ $task->created_at->format('d/m/Y') }}
                        </li>
                        <li class="list-group-item {{ $task->is_overdue && $task->status !== Task::STATUS_COMPLETED ? 'text-danger font-weight-bold' : '' }}">
                            <strong>{{ __('Hạn chót') }}:</strong> {{ $task->due_date->format('d/m/Y') }}
                            @if($task->is_overdue && $task->status !== Task::STATUS_COMPLETED)
                                <span class="badge badge-danger ms-1">{{ __('Quá hạn') }}</span> {{-- Thay ml-1 thành ms-1 cho Bootstrap 5 --}}
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
                            <strong>{{ __('Trạng thái hiện tại') }}:</strong>
                            {{-- Sử dụng str_replace để đảm bảo status có dấu _ nếu nó có khoảng trắng, nhưng tốt hơn là giá trị status trong DB nên là slug (không có khoảng trắng) --}}
                            <span class="badge badge-status-{{ str_replace(' ', '_', $task->status) }} p-2">{{ $task->status_text }}</span>
                        </li>
                    </ul>

                    {{-- Form cập nhật trạng thái --}}
                    @if($task->status !== Task::STATUS_COMPLETED && $task->status !== Task::STATUS_OVERDUE)
                        <hr>
                        <h6 class="text-muted mb-2">{{ __('Cập nhật trạng thái của bạn') }}:</h6>
                        <form method="POST" action="{{ route('student.tasks.updateStatus', $task) }}" class="status-update-buttons">
                            @csrf
                            @if($task->status === Task::STATUS_TODO)
                                <button type="submit" name="status" value="{{ Task::STATUS_IN_PROGRESS }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-play me-1"></i> {{ __('Bắt đầu làm') }}
                                </button>
                            @elseif($task->status === Task::STATUS_IN_PROGRESS)
                                <button type="submit" name="status" value="{{ Task::STATUS_COMPLETED }}" class="btn btn-success btn-sm">
                                    <i class="fas fa-check-circle me-1"></i> {{ __('Đánh dấu Hoàn thành') }}
                                </button>
                            @endif
                        </form>
                    @elseif($task->status === Task::STATUS_COMPLETED)
                        <div class="alert alert-success small py-2 text-center">
                            <i class="fas fa-check-circle me-1"></i> {{ __('Bạn đã hoàn thành công việc này.') }}
                        </div>
                    @elseif($task->status === Task::STATUS_OVERDUE)
                         <div class="alert alert-danger small py-2 text-center">
                            <i class="fas fa-exclamation-triangle me-1"></i> {{ __('Công việc này đã quá hạn và đang chờ Admin xử lý.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {{-- Không cần JS đặc biệt cho trang này trừ khi bạn thêm các tính năng phức tạp như AJAX --}}
@endpush