{{-- resources/views/admin/majors/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Quản lý Chuyên ngành')

@section('content')

    {{-- Page Heading --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quản lý Chuyên ngành</h1>
        {{-- Nút Thêm Chuyên ngành Mới --}}
        <a href="{{ route('admin.majors.create') }}" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Thêm Chuyên ngành Mới
        </a>
    </div>

    {{-- Hiển thị thông báo --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Card chứa bảng danh sách chuyên ngành --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách Chuyên ngành</h6>
            {{-- Có thể thêm bộ lọc theo trường ở đây nếu cần --}}
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableMajors" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th style="width: 5%;">ID</th>
                            <th>Tên Chuyên ngành</th>
                            <th>Thuộc Trường</th>
                            <th class="text-center" style="width: 15%;">Tùy chọn</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Giả sử biến $majors được truyền từ controller --}}
                        @forelse ($majors as $major)
                            <tr>
                                <td>{{ $major->id }}</td>
                                <td>{{ $major->name }}</td>
                                <td>
                                    {{-- Giả sử có relationship 'school' --}}
                                    {{ $major->school->name ?? 'N/A' }}
                                </td>
                                <td class="text-center">
                                    {{-- Nút Sửa --}}
                                    <a href="{{ route('admin.majors.edit', $major) }}" class="btn btn-warning btn-sm me-1" title="Sửa">
                                        <i class="fas fa-edit fa-sm"></i>
                                    </a>
                                    {{-- Nút Xóa --}}
                                    <form action="{{ route('admin.majors.destroy', $major) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn chắc chắn muốn xóa chuyên ngành này?');">
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
                                <td colspan="4" class="text-center text-muted py-4">Chưa có chuyên ngành nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div> {{-- End table-responsive --}}

            {{-- Phân trang --}}
            <div class="mt-4 d-flex justify-content-center">
                 @if($majors instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    {{ $majors->links() }}
                 @endif
            </div>

        </div> {{-- End card-body --}}
    </div> {{-- End card --}}

@endsection