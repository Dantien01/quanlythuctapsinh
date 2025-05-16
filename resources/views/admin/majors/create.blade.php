{{-- resources/views/admin/majors/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Thêm Chuyên ngành Mới')

@section('content')

    {{-- Page Heading --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Thêm Chuyên ngành Mới</h1>
         {{-- Nút quay lại danh sách --}}
         <a href="{{ route('admin.majors.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại Danh sách
        </a>
    </div>

    {{-- Card chứa form --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Thông tin Chuyên ngành</h6>
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

            <form action="{{ route('admin.majors.store') }}" method="POST">
                @csrf

                {{-- Chọn Trường --}}
                 <div class="mb-3">
                    <label for="school_id" class="form-label">Thuộc Trường <span class="text-danger">*</span></label>
                    <select name="school_id" id="school_id" class="form-select @error('school_id') is-invalid @enderror" required>
                        <option value="">-- Chọn trường --</option>
                        {{-- Giả sử biến $schools được truyền từ controller --}}
                        @isset($schools)
                            @foreach ($schools as $school)
                                <option value="{{ $school->id }}" {{ old('school_id') == $school->id ? 'selected' : '' }}>
                                    {{ $school->name }}
                                </option>
                            @endforeach
                        @endisset
                    </select>
                    @error('school_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Tên Chuyên ngành --}}
                <div class="mb-3">
                    <label for="name" class="form-label">Tên Chuyên ngành <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           class="form-control @error('name') is-invalid @enderror">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Nút Submit và Hủy --}}
                <div class="mt-4">
                    <button type="submit" class="btn btn-success btn-icon-split">
                        <span class="icon text-white-50">
                            <i class="fas fa-check"></i>
                        </span>
                        <span class="text">Lưu</span>
                    </button>
                    <a href="{{ route('admin.majors.index') }}" class="btn btn-secondary ml-2">Hủy</a>
                </div>

            </form>
        </div> {{-- End card-body --}}
    </div> {{-- End card --}}

@endsection