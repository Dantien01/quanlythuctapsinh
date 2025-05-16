{{-- resources/views/admin/users/index.blade.php --}}
@extends('layouts.admin')

@section('title', __('Quản lý Sinh viên')) {{-- Đã dùng __() --}}

@section('content')

    {{-- Page Heading --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('Quản lý Sinh viên') }}</h1> {{-- Đã dùng __() --}}
        {{-- <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> {{ __('Thêm Sinh viên') }}
        </a> --}}
    </div>

    {{-- Hiển thị thông báo --}}
     @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
     @endif
     @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
     @endif


    {{-- Bộ lọc --}}
    <div class="mb-3">
        <span>{{ __('Lọc theo trạng thái hồ sơ:') }}</span>
        <a href="{{ route('admin.users.index') }}" class="btn btn-sm {{ !request('profile_status') ? 'btn-primary' : 'btn-outline-secondary' }} mx-1">{{ __('Tất cả SV') }}</a>
        <a href="{{ route('admin.users.index', ['profile_status' => 'pending']) }}" class="btn btn-sm {{ request('profile_status') == 'pending' ? 'btn-primary' : 'btn-outline-secondary' }} mx-1">{{ __('Chờ duyệt') }}</a>
        <a href="{{ route('admin.users.index', ['profile_status' => 'approved']) }}" class="btn btn-sm {{ request('profile_status') == 'approved' ? 'btn-primary' : 'btn-outline-secondary' }} mx-1">{{ __('Đã duyệt') }}</a>
        <a href="{{ route('admin.users.index', ['profile_status' => 'rejected']) }}" class="btn btn-sm {{ request('profile_status') == 'rejected' ? 'btn-primary' : 'btn-outline-secondary' }} mx-1">{{ __('Bị từ chối') }}</a>
    </div>


    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('Danh sách Sinh viên') }}</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableUsers" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>{{ __('ID') }}</th>
                            <th>{{ __('Tên') }}</th>
                            <th>{{ __('Email') }}</th>
                            <th>{{ __('Vai trò') }}</th>
                            <th>{{ __('Trạng thái HS') }}</th>
                            <th>{{ __('Ngày tạo') }}</th>
                            <th class="text-center">{{ __('Tùy chọn') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>
                                    {{ $user->name }} <br>
                                    @if($user->mssv)
                                        <small class="text-muted">{{ $user->mssv }}</small>
                                    @endif
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    {{ $user->role->name ?? __('N/A') }}
                                </td>
                                <td>
                                     <span @class([
                                        'badge',
                                        'p-2', // Thêm padding cho badge to hơn
                                        'bg-warning text-dark' => $user->profile_status === 'pending',
                                        'bg-success' => $user->profile_status === 'approved',
                                        'bg-danger' => $user->profile_status === 'rejected',
                                        'bg-secondary' => !in_array($user->profile_status, ['pending', 'approved', 'rejected'])
                                    ])>
                                        {{-- Sử dụng hàm __() cho các text --}}
                                        {{ match($user->profile_status) {
                                            'pending' => __('Chờ duyệt'),
                                            'approved' => __('Đã duyệt'),
                                            'rejected' => __('Bị từ chối'),
                                            default => ucfirst(str_replace('_', ' ', $user->profile_status ?? __('Chưa nộp')))
                                        } }}
                                    </span>
                                </td>
                                <td>{{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : __('N/A') }}</td>
                                <td class="text-center">
                                    {{-- Nút Xem chi tiết --}}
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-info btn-sm me-1" title="{{ __('Xem chi tiết') }}">
                                        <i class="fas fa-eye fa-sm"></i>
                                    </a>

                                    @if($user->profile_status === 'pending')
                                        <form action="{{ route('admin.users.approve', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Duyệt hồ sơ cho sinh viên :name?', ['name' => $user->name]) }}');">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-success btn-sm me-1" title="{{ __('Duyệt hồ sơ') }}">
                                                <i class="fas fa-check fa-sm"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.users.reject', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Từ chối hồ sơ của sinh viên :name?', ['name' => $user->name]) }}');">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-warning btn-sm me-1" title="{{ __('Từ chối hồ sơ') }}">
                                                 <i class="fas fa-times fa-sm"></i>
                                             </button>
                                        </form>
                                    @endif

                                    {{-- Nút Sửa thông tin (có thể thêm điều kiện nếu cần) --}}
                                    {{-- @if($user->profile_status !== 'pending') --}}
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-sm me-1" title="{{ __('Sửa thông tin') }}"> {{-- Đổi màu thành primary --}}
                                        <i class="fas fa-edit fa-sm"></i>
                                    </a>
                                    {{-- @endif --}}

                                    {{-- Nút Xóa --}}
                                    {{-- Thêm điều kiện không cho xóa chính mình và có thể cả các admin khác tùy quyền --}}
                                    @if(Auth::check() && Auth::id() !== $user->id && (!$user->role || $user->role->name !== 'SuperAdmin')) {{-- Điều kiện ví dụ, cần có relationship role và phương thức hasRole hoặc kiểm tra role->name --}}
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Bạn chắc chắn muốn xóa sinh viên :name? Thao tác này không thể hoàn tác!', ['name' => $user->name]) }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="{{ __('Xóa sinh viên') }}">
                                                <i class="fas fa-trash fa-sm"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">{{ __('Không có sinh viên nào.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 d-flex justify-content-center">
                 @if($users instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    {{ $users->appends(request()->query())->links() }}
                 @endif
            </div>

        </div>
    </div>

@endsection