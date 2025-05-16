{{-- resources/views/admin/users/show.blade.php --}}
@extends('layouts.admin')

@php
    // Đặt use Carbon ở đây nếu bạn có sử dụng Carbon::parse() cho các trường ngày tháng khác trong file này
    // use Carbon\Carbon;
@endphp

@section('title', __('Chi tiết Sinh viên') . ': ' . $user->name) {{-- Sử dụng __() --}}

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('Chi tiết Sinh viên') }}: {{ $user->name }}</h1> {{-- Sử dụng __() --}}
        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm"></i> {{ __('Quay lại danh sách Users') }} {{-- Sử dụng __() --}}
        </a>
    </div>

    @include('partials.alerts')

    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Thông tin Cá nhân') }}</h6> {{-- Sử dụng __() --}}
                </div>
                <div class="card-body">
                    <p><strong>{{ __('Tên') }}:</strong> {{ $user->name }}</p>
                    <p><strong>{{ __('MSSV') }}:</strong> {{ $user->mssv ?? __('N/A') }}</p>
                    <p><strong>{{ __('Email') }}:</strong> {{ $user->email }}</p>
                    <p><strong>{{ __('Số điện thoại') }}:</strong> {{ $user->phone_number ?? __('N/A') }}</p>
                    <p><strong>{{ __('Trường') }}:</strong> {{ $user->school->name ?? __('N/A') }}</p>
                    <p><strong>{{ __('Chuyên ngành') }}:</strong> {{ $user->major->name ?? __('N/A') }}</p>
                    {{-- ========================================================== --}}
                    {{-- ===== BẮT ĐẦU PHẦN CẬP NHẬT TRẠNG THÁI HỒ SƠ ===== --}}
                    {{-- ========================================================== --}}
                    <p><strong>{{ __('Trạng thái hồ sơ') }}:</strong>
                        <span @class([
                            'badge',
                            'p-2', // Thêm padding cho badge nếu muốn
                            'bg-warning text-dark' => $user->profile_status === 'pending',
                            'bg-success' => $user->profile_status === 'approved',
                            'bg-danger' => $user->profile_status === 'rejected',
                            'bg-secondary' => !in_array($user->profile_status, ['pending', 'approved', 'rejected'])
                        ])>
                            {{ match($user->profile_status) {
                                'pending' => __('Chờ duyệt'),
                                'approved' => __('Đã duyệt'),
                                'rejected' => __('Bị từ chối'),
                                default => ucfirst(str_replace('_', ' ', $user->profile_status ?? __('Chưa nộp')))
                            } }}
                        </span>
                    </p>
                    {{-- ========================================================== --}}
                    {{-- ===== KẾT THÚC PHẦN CẬP NHẬT TRẠNG THÁI HỒ SƠ ===== --}}
                    {{-- ========================================================== --}}

                    {{-- Ví dụ thêm hiển thị lý do từ chối nếu có --}}
                    @if($user->profile_status === 'rejected' && $user->rejection_reason)
                        <p class="mt-2"><strong>{{ __('Lý do từ chối') }}:</strong> <span class="text-danger">{{ $user->rejection_reason }}</span></p>
                    @endif
                    {{-- Thêm các thông tin khác nếu cần --}}
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Thông tin Thực tập & Chuyên cần') }}</h6> {{-- Sử dụng __() --}}
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h5>
                            {{ __('Tỷ lệ chuyên cần') }}: {{-- Sử dụng __() --}}
                            @if(!is_null($user->attendance_rate))
                                <span class="badge bg-{{ $user->attendance_rate >= 80 ? 'success' : ($user->attendance_rate >= 50 ? 'warning' : 'danger') }} p-2">
                                    {{ number_format($user->attendance_rate, 1) }}%
                                </span>
                            @else
                                <span class="badge bg-secondary p-2">{{ __('N/A (Chưa có dữ liệu)') }}</span> {{-- Sử dụng __() --}}
                            @endif
                        </h5>
                    </div>
                    <hr>
                    <p><em>({{ __('Khu vực này có thể hiển thị thêm chi tiết về lịch sử điểm danh, công việc, nhật ký...') }})</em></p> {{-- Sử dụng __() --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection