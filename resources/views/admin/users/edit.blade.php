{{-- resources/views/admin/users/edit.blade.php --}}
@extends('layouts.admin') {{-- Kế thừa từ layout admin mới --}}

@section('title', 'Chỉnh sửa Người Dùng: ' . $user->name)

@section('content')

    {{-- Page Heading --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Chỉnh sửa Người Dùng: <span class="text-primary">{{ $user->name }}</span></h1>
         {{-- Nút quay lại danh sách --}}
         <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-primary shadow-sm text-white">
            <i class="fas fa-arrow-left fa-sm text-white"></i> Quay lại Danh sách
        </a>
    </div>

    {{-- Card chứa form --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Thông tin Người dùng</h6>
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

            <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Sử dụng Bootstrap grid --}}
                <div class="row">
                    {{-- Cột Tên --}}
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Tên <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name"
                               class="form-control @error('name') is-invalid @enderror">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Cột Email --}}
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="email"
                               class="form-control @error('email') is-invalid @enderror">
                         @error('email')
                             <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                 <div class="row">
                    {{-- Cột MSSV --}}
                    <div class="col-md-6 mb-3">
                        <label for="mssv" class="form-label">Mã Sinh Viên (MSSV)</label>
                        <input type="text" id="mssv" name="mssv" value="{{ old('mssv', $user->mssv) }}"
                               class="form-control @error('mssv') is-invalid @enderror">
                         @error('mssv')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Cột Số điện thoại --}}
                    <div class="col-md-6 mb-3">
                        <label for="phone_number" class="form-label">Số điện thoại</label>
                        <input type="tel" id="phone_number" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}" autocomplete="tel"
                               class="form-control @error('phone_number') is-invalid @enderror">
                         @error('phone_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                 <div class="row">
                     {{-- Cột Chọn Trường --}}
                     @if(isset($schools) && $schools->count() > 0)
                        <div class="col-md-6 mb-3">
                            <label for="school_id" class="form-label">Trường</label>
                            <select id="school_id" name="school_id" class="form-select @error('school_id') is-invalid @enderror">
                                <option value="">-- Chọn trường --</option>
                                @foreach ($schools as $school)
                                    <option value="{{ $school->id }}" {{ old('school_id', $user->school_id) == $school->id ? 'selected' : '' }}>
                                        {{ $school->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('school_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                     {{-- Cột Chọn Chuyên ngành --}}
                     @if(isset($majors) && $majors->count() > 0)
                        <div class="col-md-6 mb-3">
                            <label for="major_id" class="form-label">Chuyên ngành</label>
                            {{-- TODO: Cân nhắc lọc động ngành theo trường --}}
                            <select id="major_id" name="major_id" class="form-select @error('major_id') is-invalid @enderror">
                                <option value="">-- Chọn ngành --</option>
                                @foreach ($majors as $major)
                                    <option value="{{ $major->id }}" {{ old('major_id', $user->major_id) == $major->id ? 'selected' : '' }}>
                                        {{ $major->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('major_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                 </div>


                <div class="row">
                    {{-- Cột Chọn Vai trò --}}
                    @if(isset($roles) && $roles->count() > 0)
                        <div class="col-md-6 mb-3">
                            <label for="role_id" class="form-label">Vai trò <span class="text-danger">*</span></label>
                            <select id="role_id" name="role_id" required class="form-select @error('role_id') is-invalid @enderror">
                                <option value="">-- Chọn vai trò --</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    {{-- Cột Trạng thái hồ sơ --}}
                     <div class="col-md-6 mb-3">
                        <label for="profile_status" class="form-label">Trạng thái Hồ sơ <span class="text-danger">*</span></label>
                        <select id="profile_status" name="profile_status" required class="form-select @error('profile_status') is-invalid @enderror">
                            <option value="pending" {{ old('profile_status', $user->profile_status) == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                            <option value="approved" {{ old('profile_status', $user->profile_status) == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                            <option value="rejected" {{ old('profile_status', $user->profile_status) == 'rejected' ? 'selected' : '' }}>Bị từ chối</option>
                            {{-- Thêm các trạng thái khác nếu có --}}
                        </select>
                        @error('profile_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                 <hr class="my-4"> {{-- Đường kẻ phân cách --}}

                {{-- Phần Mật khẩu mới --}}
                 <h6 class="mb-3 font-weight-bold">Đổi Mật khẩu (Để trống nếu không đổi)</h6>
                 <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Mật khẩu mới</label>
                        <input type="password" id="password" name="password" autocomplete="new-password"
                               class="form-control @error('password') is-invalid @enderror">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label">Xác nhận mật khẩu mới</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password"
                               class="form-control">
                        {{-- Lỗi xác nhận thường hiển thị ở trường password --}}
                    </div>
                </div>


                {{-- Nút bấm --}}
                <div class="mt-4">
                    <button type="submit" class="btn btn-warning btn-icon-split">
                        <span class="icon text-white">
                            <i class="fas fa-save"></i>
                        </span>
                        <span class="text">Lưu thay đổi</span>
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-danger ml-2 text-white">Hủy</a>
                </div>
            </form>
        </div> {{-- End card-body --}}
    </div> {{-- End card --}}

@endsection