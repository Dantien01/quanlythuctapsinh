{{-- resources/views/admin/schools/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Chỉnh sửa Trường học: ' . $school->name)

@section('content')

    {{-- Page Heading --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Chỉnh sửa Trường học</h1>
         {{-- Nút quay lại danh sách --}}
         <a href="{{ route('admin.schools.index') }}" class="btn btn-sm btn-primary shadow-sm text-white">
            <i class="fas fa-arrow-left fa-sm text-white"></i> Quay lại Danh sách
        </a>
    </div>

    {{-- Card chứa form --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Thông tin Trường: {{ $school->name }}</h6>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.schools.update', $school) }}" method="POST">
                @csrf
                @method('PUT') {{-- Giả mạo phương thức PUT --}}

                {{-- Tên Trường --}}
                <div class="mb-3">
                    <label for="name" class="form-label">Tên Trường <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $school->name) }}" required
                           class="form-control @error('name') is-invalid @enderror">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Địa chỉ --}}
                <div class="mb-3">
                    <label for="address" class="form-label">Địa chỉ</label>
                    <input type="text" id="address" name="address" value="{{ old('address', $school->address) }}"
                           class="form-control @error('address') is-invalid @enderror">
                     @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Nút Submit và Hủy --}}
                <div class="mt-4">
                    <button type="submit" class="btn btn-warning btn-icon-split"> {{-- Màu vàng cho cập nhật --}}
                        <span class="icon text-white">
                            <i class="fas fa-save"></i>
                        </span>
                        <span class="text">Cập nhật</span>
                    </button>
                    <a href="{{ route('admin.schools.index') }}" class="btn btn-danger ml-2 text-white">Hủy</a>
                </div>

            </form>
        </div> {{-- End card-body --}}
    </div> {{-- End card --}}

@endsection