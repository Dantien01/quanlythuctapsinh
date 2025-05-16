{{-- resources/views/admin/majors/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Chỉnh sửa Chuyên ngành: ' . $major->name)

@section('content')

    {{-- Page Heading --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Chỉnh sửa Chuyên ngành</h1>
         {{-- Nút quay lại danh sách --}}
         <a href="{{ route('admin.majors.index') }}" class="btn btn-sm btn-primary shadow-sm text-white">
            <i class="fas fa-arrow-left fa-sm text-white"></i> Quay lại Danh sách
        </a>
    </div>

    {{-- Card chứa form --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Thông tin: {{ $major->name }}</h6>
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

            <form action="{{ route('admin.majors.update', $major) }}" method="POST">
                @csrf
                @method('PUT')

                 {{-- Chọn Trường --}}
                 <div class="mb-3">
                    <label for="school_id" class="form-label">Thuộc Trường <span class="text-danger">*</span></label>
                    <select name="school_id" id="school_id" class="form-select @error('school_id') is-invalid @enderror" required>
                        <option value="">-- Chọn trường --</option>
                        @isset($schools)
                            @foreach ($schools as $school)
                                {{-- Chọn sẵn trường hiện tại của chuyên ngành --}}
                                <option value="{{ $school->id }}" {{ old('school_id', $major->school_id) == $school->id ? 'selected' : '' }}>
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
                    <input type="text" id="name" name="name" value="{{ old('name', $major->name) }}" required
                           class="form-control @error('name') is-invalid @enderror">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Nút Submit và Hủy --}}
                <div class="mt-4">
                    <button type="submit" class="btn btn-warning btn-icon-split">
                        <span class="icon text-white">
                            <i class="fas fa-save"></i>
                        </span>
                        <span class="text">Cập nhật</span>
                    </button>
                    <a href="{{ route('admin.majors.index') }}" class="btn btn-danger ml-2 text-white">Hủy</a>
                </div>

            </form>
        </div> {{-- End card-body --}}
    </div> {{-- End card --}}

@endsection