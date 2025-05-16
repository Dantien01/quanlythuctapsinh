{{-- resources/views/profile/edit.blade.php --}}
@extends('layouts.admin') {{-- HOẶC layouts.app nếu muốn giao diện khác --}}

@section('title', 'Hồ sơ cá nhân')

@section('content')
    {{-- Page Heading --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Hồ sơ cá nhân</h1>
         {{-- Có thể thêm nút quay lại Dashboard SV --}}
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-primary shadow-sm text-white">
            <i class="fas fa-arrow-left fa-sm text-white"></i> Quay lại Dashboard
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8"> {{-- Cột chính --}}

            {{-- Card cho Thông tin Hồ sơ --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin Hồ sơ Sinh viên</h6>
                </div>
                <div class="card-body">
                    {{-- Include partial form cập nhật thông tin SINH VIÊN --}}
                    {{-- Quan trọng: Truyền cả $schools và $majors từ Controller vào đây --}}
                    @include('profile.partials.update-student-profile-information-form', ['schools' => $schools ?? collect(), 'majors' => $majors ?? collect()])
                </div>
            </div>

            {{-- Card cho Cập nhật Mật khẩu --}}
            <div class="card shadow mb-4">
                 <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Cập nhật Mật khẩu</h6>
                </div>
                <div class="card-body">
                     {{-- Include partial form cập nhật mật khẩu (dùng chung) --}}
                    @include('profile.partials.update-password-form')
                </div>
            </div>

        </div>

        <div class="col-lg-4"> {{-- Cột phụ --}}
             {{-- Card cho Xóa Tài khoản --}}
             <div class="card shadow mb-4">
                 <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">Xóa Tài khoản</h6>
                </div>
                <div class="card-body">
                    {{-- Include partial xóa tài khoản (dùng chung) --}}
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
@endsection