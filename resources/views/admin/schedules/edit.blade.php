{{-- resources/views/admin/schedules/edit.blade.php --}}
@extends('layouts.admin') {{-- Kế thừa từ layout admin mới --}}

@section('title', 'Chỉnh Sửa Lịch Thực Tập')

@section('content')

    {{-- Page Heading --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Chỉnh Sửa Lịch Thực Tập</h1>
         {{-- Nút quay lại danh sách --}}
         <a href="{{ route('admin.schedules.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại Danh sách
        </a>
    </div>

    {{-- Card chứa form --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
             {{-- Hiển thị tiêu đề lịch đang sửa --}}
            <h6 class="m-0 font-weight-bold text-primary">Chỉnh sửa: {{ $schedule->title }}</h6>
        </div>
        <div class="card-body">
             {{-- Hiển thị lỗi validation nếu có --}}
             @if ($errors->any())
                 <div class="alert alert-danger">
                     <ul class="mb-0">
                         @foreach ($errors->all() as $error)
                             <li>{{ $error }}</li>
                         @endforeach
                     </ul>
                 </div>
             @endif

            {{-- Form trỏ đến route update, sử dụng method PUT --}}
            <form action="{{ route('admin.schedules.update', $schedule) }}" method="POST">
                @csrf {{-- CSRF Token --}}
                @method('PUT') {{-- Giả mạo phương thức PUT --}}

                {{-- Include form dùng chung, truyền các biến cần thiết --}}
                {{-- Đảm bảo controller đã truyền $schedule và $students --}}
                @include('admin.schedules._form', ['schedule' => $schedule, 'students' => $students])

                {{-- Nút Submit và Hủy --}}
                <div class="mt-4">
                    <button type="submit" class="btn btn-warning btn-icon-split"> {{-- Màu vàng cho cập nhật --}}
                        <span class="icon text-white-50">
                            <i class="fas fa-save"></i>
                        </span>
                        <span class="text">Cập nhật</span>
                    </button>
                    <a href="{{ route('admin.schedules.index') }}" class="btn btn-secondary ml-2">Hủy</a>
                </div>

            </form>
        </div> {{-- End card-body --}}
    </div> {{-- End card --}}

@endsection