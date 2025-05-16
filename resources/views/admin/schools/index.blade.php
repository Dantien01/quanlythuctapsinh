{{-- resources/views/admin/schools/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Quản lý Trường học')

@section('content')

    {{-- Page Heading --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quản lý Trường học</h1>
        {{-- Nút Thêm Trường Mới --}}
        <a href="{{ route('admin.schools.create') }}" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Thêm Trường Mới
        </a>
    </div>

    {{-- Hiển thị thông báo --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Card chứa bảng danh sách trường --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách Trường học</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableSchools" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th style="width: 5%;">ID</th> {{-- Đặt độ rộng cột ID nhỏ hơn --}}
                            <th>Tên Trường</th>
                            <th>Địa chỉ</th>
                            <th class="text-center" style="width: 15%;">Tùy chọn</th> {{-- Đặt độ rộng cột Tùy chọn --}}
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Giả sử biến $schools được truyền từ controller --}}
                        @forelse ($schools as $school)
                            <tr>
                                <td>{{ $school->id }}</td>
                                <td>{{ $school->name }}</td>
                                <td>{{ $school->address ?? '--' }}</td> {{-- Hiển thị địa chỉ nếu có --}}
                                <td class="text-center">
                                    {{-- Nút Sửa --}}
                                    <a href="{{ route('admin.schools.edit', $school) }}" class="btn btn-warning btn-sm me-1" title="Sửa">
                                        <i class="fas fa-edit fa-sm"></i>
                                    </a>
                                    {{-- Nút Xóa --}}
                                    <form action="{{ route('admin.schools.destroy', $school) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn chắc chắn muốn xóa trường này? Các chuyên ngành và sinh viên liên quan có thể bị ảnh hưởng!');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Xóa">
                                            <i class="fas fa-trash fa-sm"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Chưa có trường học nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div> {{-- End table-responsive --}}

            {{-- Phân trang --}}
            <div class="mt-4 d-flex justify-content-center">
                 @if($schools instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    {{ $schools->links() }}
                 @endif
            </div>

        </div> {{-- End card-body --}}
    </div> {{-- End card --}}

@endsection