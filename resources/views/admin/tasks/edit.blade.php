{{-- resources/views/admin/tasks/edit.blade.php --}}
@extends('layouts.admin') {{-- Thay 'layouts.admin' bằng tên file layout admin của bạn --}}

@section('title', __('Chỉnh sửa Công việc'))

@push('styles')
    {{-- CSS cho Select2 nếu cần --}}
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title'): <span class="text-primary font-weight-normal">{{ $task->title }}</span></h1>
    </div>

    @include('partials.alerts')

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('Thông tin Công việc') }}</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.tasks.update', $task) }}">
                @method('PUT') {{-- Phương thức PUT cho update --}}
                @include('admin.tasks._form') {{-- Include partial form --}}
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.select2-interns').select2({
                theme: "bootstrap-5",
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
                placeholder: $(this).data('placeholder'),
            });
        });
    </script>
@endpush