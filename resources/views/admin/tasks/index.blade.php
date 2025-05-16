{{-- resources/views/admin/tasks/index.blade.php --}}
@extends('layouts.admin')

@section('title', __('Quản lý Công việc'))

@push('styles')
    {{-- CSS cho Select2 (cho bộ lọc) --}}
    <style>
        .table th, .table td { vertical-align: middle; }
        /* Style cho các badge trạng thái và ưu tiên (tùy chỉnh màu sắc theo ý bạn) */
        .badge-priority-high { background-color: #e74a3b; color: white; }
        .badge-priority-medium { background-color: #f6c23e; color: #5a5c69; } /* Chữ tối để dễ đọc trên nền vàng */
        .badge-priority-low { background-color: #1cc88a; color: white; }

        .badge-status-todo { background-color: #4e73df; color: white; }
        .badge-status-in_progress { background-color: #36b9cc; color: white; }
        .badge-status-completed { background-color: #1cc88a; color: white; }
        .badge-status-overdue { background-color: #e74a3b; color: white; }
    </style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
        <a href="{{ route('admin.tasks.create') }}" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> {{ __('Giao việc mới') }}
        </a>
    </div>

    @include('partials.alerts') {{-- Hiển thị thông báo (thành công/lỗi) --}}

    <!-- Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('Bộ lọc công việc') }}</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.tasks.index') }}">
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label for="intern_id_filter">{{ __('Sinh viên') }}</label>
                        <select name="intern_id_filter" id="intern_id_filter" class="form-control select2-filter" data-placeholder="{{ __('Tất cả sinh viên') }}">
                            <option></option> {{-- Option trống cho placeholder --}}
                            @foreach($interns as $intern)
                                <option value="{{ $intern->id }}" {{ request('intern_id_filter') == $intern->id ? 'selected' : '' }}>
                                    {{ $intern->name }} ({{ $intern->mssv ?? ''}})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group"> {{-- Thay đổi col-md-2 thành col-md-3 cho cân đối hơn --}}
                        <label for="status_filter">{{ __('Trạng thái') }}</label>
                        <select name="status_filter" id="status_filter" class="form-control select2-filter" data-placeholder="{{ __('Tất cả trạng thái') }}">
                            <option></option>
                            @foreach($statuses as $key => $value)
                                <option value="{{ $key }}" {{ request('status_filter') == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 form-group">
                        <label for="priority_filter">{{ __('Độ ưu tiên') }}</label>
                        <select name="priority_filter" id="priority_filter" class="form-control select2-filter" data-placeholder="{{ __('Tất cả độ ưu tiên') }}">
                            <option></option>
                            @foreach($priorities as $key => $value)
                                <option value="{{ $key }}" {{ request('priority_filter') == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 form-group">
                        <label for="due_date_filter">{{ __('Hạn chót') }}</label>
                        <input type="date" name="due_date_filter" id="due_date_filter" class="form-control" value="{{ request('due_date_filter') }}">
                    </div>
                    <div class="col-md-2 form-group d-flex align-items-end">
                        <button type="submit" class="btn btn-info mr-2"><i class="fas fa-filter"></i> {{ __('Lọc') }}</button>
                        <a href="{{ route('admin.tasks.index') }}" class="btn btn-secondary"><i class="fas fa-sync-alt"></i> {{ __('Reset') }}</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tasks Table Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('Danh sách Công việc') }} ({{ $tasks->total() }})</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>{{ __('Tiêu đề') }}</th>
                            <th>{{ __('Sinh viên') }}</th>
                            <th>{{ __('Người giao') }}</th>
                            <th>{{ __('Hạn chót') }}</th>
                            <th>{{ __('Ưu tiên') }}</th>
                            <th>{{ __('Trạng thái') }}</th>
                            <th style="width:120px;">{{ __('Hành động') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tasks as $task)
                        <tr>
                            <td>{{ $loop->iteration + $tasks->firstItem() - 1 }}</td>
                            <td>
                                <a href="{{ route('admin.tasks.show', $task) }}">{{ Str::limit($task->title, 40) }}</a>
                                {{-- Str::limit giới hạn chiều dài chuỗi để không vỡ layout --}}
                            </td>
                            <td>{{ $task->intern->name ?? __('N/A') }}</td>
                            <td>{{ $task->assigner->name ?? __('N/A') }}</td>
                            <td class="{{ $task->is_overdue ? 'text-danger font-weight-bold' : '' }}">
                                {{ $task->due_date->format('d/m/Y') }}
                                @if($task->is_overdue)
                                    <span class="badge badge-danger ml-1">{{ __('Quá hạn') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($task->priority)
                                <span class="badge badge-priority-{{ $task->priority }} p-2">{{ $task->priority_text }}</span>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td>
                                {{-- Thay thế khoảng trắng bằng gạch dưới cho tên class CSS của status --}}
                                <span class="badge badge-status-{{ str_replace(' ', '_', $task->status) }} p-2">{{ $task->status_text }}</span>
                            </td>
                            <td>
                                <a href="{{ route('admin.tasks.show', $task) }}" class="btn btn-sm btn-success mb-1" title="{{ __('Xem') }}"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.tasks.edit', $task) }}" class="btn btn-sm btn-primary mb-1" title="{{ __('Sửa') }}"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('admin.tasks.destroy', $task) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Bạn có chắc chắn muốn xóa công việc này?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger mb-1" title="{{ __('Xóa') }}"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">{{ __('Không có công việc nào được tìm thấy.') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Phân trang --}}
            @if($tasks->hasPages())
            <div class="mt-3 d-flex justify-content-center">
                {{ $tasks->links() }} {{-- Mặc định dùng Bootstrap 4/5 styling --}}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Khởi tạo Select2 cho các dropdown filter
            $('.select2-filter').select2({
                theme: "bootstrap-5", // Hoặc "bootstrap"
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
                placeholder: $(this).data('placeholder'),
                allowClear: true // Cho phép xóa lựa chọn filter
            });
        });
    </script>
@endpush