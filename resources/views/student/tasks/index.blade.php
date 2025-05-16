{{-- resources/views/student/tasks/index.blade.php --}}
@extends('layouts.admin') {{-- Hoặc tên layout chính của sinh viên bạn đang dùng --}}

@section('title', __('Công việc của tôi'))

@push('styles')
    {{-- Bạn có thể copy các style badge từ admin/tasks/index.blade.php nếu cần --}}
    {{-- Hoặc định nghĩa lại nếu giao diện sinh viên khác biệt --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-5-theme/1.3.0/select2-bootstrap-5-theme.min.css" />
    <style>
        .table th, .table td { vertical-align: middle; }
        .task-card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            border-left-width: 0.25rem; /* Độ dày của border bên trái */
        }
        .task-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1)!important;
        }
        .task-card-title a {
            color: #4A5568; /* text-gray-700 */
            text-decoration: none;
            font-weight: 600;
        }
        .task-card-title a:hover {
            color: #4e73df; /* primary color from SB Admin 2 */
        }

        /* Màu sắc cho border và badge (tương tự SB Admin 2) */
        .border-left-todo { border-left-color: #4e73df !important; }
        .border-left-in_progress { border-left-color: #36b9cc !important; }
        .border-left-completed { border-left-color: #1cc88a !important; }
        .border-left-overdue { border-left-color: #e74a3b !important; }

        .badge-status-todo { background-color: #4e73df; color: white; }
        .badge-status-in_progress { background-color: #36b9cc; color: white; }
        .badge-status-completed { background-color: #1cc88a; color: white; }
        .badge-status-overdue { background-color: #e74a3b; color: white; }

        .badge-priority-high { background-color: #e74a3b; color: white; }
        .badge-priority-medium { background-color: #f6c23e; color: #5a5c69; }
        .badge-priority-low { background-color: #1cc88a; color: white; }
    </style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
        {{-- Có thể thêm nút ở đây nếu sinh viên có quyền tạo gì đó liên quan đến task --}}
    </div>

    @include('partials.alerts') {{-- Đảm bảo file partials/alerts.blade.php tồn tại --}}

    <!-- Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('Lọc công việc') }}</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('student.tasks.index') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="search_keyword" class="form-label">{{ __('Từ khóa') }}</label>
                        <input type="text" name="search_keyword" id="search_keyword" class="form-control"
                               value="{{ request('search_keyword') }}" placeholder="{{ __('Nhập tiêu đề, mô tả...') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="status_filter" class="form-label">{{ __('Trạng thái') }}</label>
                        <select name="status_filter" id="status_filter" class="form-select select2-filter" data-placeholder="{{ __('Tất cả trạng thái') }}">
                            <option></option>
                            @foreach($statuses as $key => $value)
                                <option value="{{ $key }}" {{ request('status_filter') == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="priority_filter" class="form-label">{{ __('Độ ưu tiên') }}</label>
                        <select name="priority_filter" id="priority_filter" class="form-select select2-filter" data-placeholder="{{ __('Tất cả') }}">
                            <option></option>
                            @foreach($priorities as $key => $value)
                                <option value="{{ $key }}" {{ request('priority_filter') == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="due_date_filter" class="form-label">{{ __('Hạn chót') }}</label>
                        <input type="date" name="due_date_filter" id="due_date_filter" class="form-control" value="{{ request('due_date_filter') }}">
                    </div>
                    <div class="col-md-2 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-info mr-2 w-100"><i class="fas fa-filter"></i> {{ __('Lọc') }}</button>
                        {{-- <a href="{{ route('student.tasks.index') }}" class="btn btn-secondary"><i class="fas fa-sync-alt"></i></a> --}}
                    </div>
                </div>
                 @if(request()->hasAny(['search_keyword', 'status_filter', 'priority_filter', 'due_date_filter']))
                    <div class="row">
                        <div class="col-12 text-right">
                             <a href="{{ route('student.tasks.index') }}" class="btn btn-sm btn-light border mt-2"><i class="fas fa-times"></i> {{ __('Xóa bộ lọc') }}</a>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Tasks List/Grid -->
    @if($tasks->isEmpty())
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle mr-2"></i> {{ __('Bạn chưa có công việc nào được giao hoặc không có kết quả nào phù hợp với bộ lọc.') }}
        </div>
    @else
        <div class="row">
            @foreach ($tasks as $task)
            <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                {{-- Xác định class border dựa trên trạng thái --}}
                @php
                    $borderClass = 'border-left-secondary'; // Mặc định
                    if ($task->status === \App\Models\Task::STATUS_COMPLETED) {
                        $borderClass = 'border-left-completed';
                    } elseif ($task->is_overdue) {
                        $borderClass = 'border-left-overdue';
                    } elseif ($task->status === \App\Models\Task::STATUS_IN_PROGRESS) {
                        $borderClass = 'border-left-in_progress';
                    } elseif ($task->status === \App\Models\Task::STATUS_TODO) {
                        $borderClass = 'border-left-todo';
                    }
                @endphp
                <div class="card shadow-sm h-100 task-card {{ $borderClass }}">
                    <div class="card-body d-flex flex-column">
                        <div class="row no-gutters align-items-start mb-2">
                            <div class="col">
                                <h5 class="card-title mb-1 task-card-title">
                                    <a href="{{ route('student.tasks.show', $task) }}">{{ Str::limit($task->title, 50) }}</a>
                                </h5>
                            </div>
                            @if($task->priority)
                                <div class="col-auto">
                                    <span class="badge badge-priority-{{ $task->priority }} px-2 py-1 small">{{ $task->priority_text }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="text-xs font-weight-bold text-uppercase mb-2">
                             <span class="badge badge-status-{{ str_replace(' ', '_', $task->status) }} p-1">{{ $task->status_text }}</span>
                        </div>

                        @if($task->description)
                        <p class="card-text text-gray-700 small mb-2 flex-grow-1">
                            {{ Str::limit($task->description, 100) }}
                        </p>
                        @else
                        <div class="flex-grow-1"></div> {{-- Để giữ chiều cao card đồng đều --}}
                        @endif


                        <div class="mt-auto">
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted small">
                                    <i class="fas fa-calendar-alt fa-fw"></i>
                                    <span class="{{ $task->is_overdue && $task->status !== \App\Models\Task::STATUS_COMPLETED ? 'text-danger font-weight-bold' : '' }}">
                                        {{ $task->due_date->format('d/m/Y') }}
                                    </span>
                                     @if($task->is_overdue && $task->status !== \App\Models\Task::STATUS_COMPLETED)
                                        <span class="text-danger small">({{ __('Quá hạn') }})</span>
                                    @endif
                                </div>
                                <a href="{{ route('student.tasks.show', $task) }}" class="btn btn-sm btn-outline-primary">
                                    {{ __('Chi tiết') }} <i class="fas fa-arrow-right fa-sm"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Phân trang --}}
        @if($tasks->hasPages())
        <div class="mt-2 d-flex justify-content-center">
            {{ $tasks->links() }}
        </div>
        @endif
    @endif
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2-filter').select2({
                theme: "bootstrap-5", // Hoặc "bootstrap" nếu dùng Bootstrap 4
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
                placeholder: $(this).data('placeholder'),
                allowClear: true
            });
        });
    </script>
@endpush