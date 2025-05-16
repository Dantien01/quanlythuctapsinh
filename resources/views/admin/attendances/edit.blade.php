{{-- resources/views/admin/attendances/edit.blade.php --}}
@extends('layouts.admin')

{{-- Lấy tên sinh viên và ngày để hiển thị tiêu đề --}}
@php
    $studentName = $attendance->user->name ?? 'N/A';
    $attendanceDate = $attendance->attendance_date ? \Carbon\Carbon::parse($attendance->attendance_date)->format('d/m/Y') : ($attendance->check_in_time ? $attendance->check_in_time->format('d/m/Y') : 'N/A');
@endphp

@section('title', "Sửa Điểm danh - $studentName - $attendanceDate")

@section('content')

    {{-- Page Heading --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Sửa Điểm danh cho sinh viên: <span class="text-primary">{{ $studentName }}</span></h1>
        <a href="{{ route('admin.attendances.index', ['student_id' => $attendance->user_id, 'attendance_date' => $attendance->attendance_date ? $attendance->attendance_date->format('Y-m-d') : ($attendance->check_in_time ? $attendance->check_in_time->format('Y-m-d') : '')]) }}" class="btn btn-sm btn-primary shadow-sm text-white">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại Danh sách
        </a>
    </div>

    {{-- Card chứa form --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Chi tiết Điểm danh Ngày: {{ $attendanceDate }}</h6>
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

            <form action="{{ route('admin.attendances.update', $attendance) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Hiển thị thông tin không cho sửa --}}
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Giờ Check-in:</label>
                    <div class="col-sm-9">
                        <input type="text" readonly class="form-control-plaintext" value="{{ $attendance->check_in_time ? $attendance->check_in_time->format('H:i:s') : '--' }}">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Giờ Check-out:</label>
                    <div class="col-sm-9">
                         <input type="text" readonly class="form-control-plaintext" value="{{ $attendance->check_out_time ? $attendance->check_out_time->format('H:i:s') : '--' }}">
                    </div>
                </div>

                <hr> {{-- Đường kẻ ngang phân tách --}}

                {{-- Các trường cho phép sửa --}}
                <div class="row mb-3 align-items-center"> {{-- Thêm align-items-center --}}
                    <label for="status" class="col-sm-3 col-form-label">Trạng thái <span class="text-danger">*</span></label>
                    <div class="col-sm-5"> {{-- Giảm độ rộng cột --}}
                        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                            {{-- Lấy giá trị cũ hoặc giá trị hiện tại --}}
                            @php $currentStatus = old('status', $attendance->status); @endphp
                            <option value="present" {{ $currentStatus == 'present' ? 'selected' : '' }}>Có mặt (Present)</option>
                            <option value="on_time" {{ $currentStatus == 'on_time' ? 'selected' : '' }}>Đúng giờ (On Time)</option>
                            <option value="late" {{ $currentStatus == 'late' ? 'selected' : '' }}>Đi trễ (Late)</option>
                            <option value="absent" {{ $currentStatus == 'absent' ? 'selected' : '' }}>Vắng mặt (Absent)</option>
                            <option value="excused" {{ $currentStatus == 'excused' ? 'selected' : '' }}>Có phép (Excused)</option>
                            {{-- Thêm các trạng thái khác nếu cần --}}
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="notes" class="col-sm-3 col-form-label">Ghi chú:</label>
                    <div class="col-sm-9">
                        <textarea name="notes" id="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $attendance->notes) }}</textarea>
                         @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>


                {{-- Nút Submit và Hủy --}}
                <div class="mt-4">
                    <button type="submit" class="btn btn-warning btn-icon-split">
                        <span class="icon text-white">
                            <i class="fas fa-save"></i>
                        </span>
                        <span class="text">Lưu thay đổi</span>
                    </button>
                     {{-- Nút Hủy quay lại trang danh sách điểm danh --}}
                    <a href="{{ route('admin.attendances.index', ['student_id' => $attendance->user_id, 'attendance_date' => $attendance->attendance_date ? $attendance->attendance_date->format('Y-m-d') : ($attendance->check_in_time ? $attendance->check_in_time->format('Y-m-d') : '')]) }}" class="btn btn-secondary ml-2">Hủy</a>
                </div>

            </form>
        </div> {{-- End card-body --}}
    </div> {{-- End card --}}

@endsection