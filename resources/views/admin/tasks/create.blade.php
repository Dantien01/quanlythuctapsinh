{{-- resources/views/admin/tasks/create.blade.php --}}
@extends('layouts.admin') {{-- Thay 'layouts.admin' bằng tên file layout admin của bạn --}}

@section('title', __('Giao Công việc Mới'))

{{-- Push CSS cho Select2 nếu dùng --}}
@push('styles')
    {{-- Đã thêm CDN vào layout, nếu muốn CSS riêng cho trang này thì đặt ở đây --}}
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
    </div>

    @include('partials.alerts') {{-- Hiển thị thông báo lỗi validate nếu có --}}

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('Thông tin Công việc') }}</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.tasks.store') }}">
                @include('admin.tasks._form') {{-- Include partial form --}}
            </form>
        </div>
    </div>
</div>
@endsection

{{-- Push JS cho Select2 nếu dùng --}}
@push('scripts')
    <script>
        $(document).ready(function() {
            // Khởi tạo Select2 cho dropdown chọn sinh viên
            $('.select2-interns').select2({
                theme: "bootstrap-5", // Hoặc "bootstrap" nếu dùng BS4
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
                placeholder: $(this).data('placeholder'),
                // allowClear: true // Nếu muốn có nút X để xóa lựa chọn
            });
        });
    </script>
@endpush