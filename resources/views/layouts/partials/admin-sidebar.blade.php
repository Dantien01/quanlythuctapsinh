{{-- resources/views/layouts/partials/admin-sidebar.blade.php --}}

{{-- Chỉ hiển thị sidebar nếu người dùng đã đăng nhập --}}
@auth
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand (Chung cho cả Admin và Sinh viên) -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center"
    href="{{ Auth::user()->hasRole('Admin') ? route('admin.dashboard') : route('dashboard') }}"
    style="padding-top: 10px; padding-bottom: 10px; height: auto;">
     <div class="sidebar-brand-icon">
         <img src="{{ asset('img/logo.png') }}"
              alt="Pizitech Logo"
              style="max-height: 55px; width: auto; object-fit: contain;">
     </div>
 </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    {{-- ========================================== --}}
    {{-- ========== MENU DÀNH CHO ADMIN ========== --}}
    {{-- ========================================== --}}
    @if(Auth::user()->hasRole('Admin'))

        <!-- Nav Item - Trang Chủ Admin -->
        <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.dashboard') }}">
                <i class="fas fa-fw fa-home"></i>
                <span>Trang Chủ</span></a>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider">

        <!-- Heading - Quản lý Hệ thống -->
        <div class="sidebar-heading">
            Quản lý Hệ thống
        </div>

        <!-- Nav Item - Quản lý Người dùng (Sinh viên) -->
        <li class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.users.index') }}">
                <i class="fas fa-fw fa-users"></i>
                <span>Quản lý Sinh viên</span>
            </a>
        </li>

         <!-- Nav Item - Quản lý Trường học -->
        <li class="nav-item {{ request()->routeIs('admin.schools.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.schools.index') }}">
                <i class="fas fa-fw fa-school"></i>
                <span>Quản lý Trường</span>
            </a>
        </li>

         <!-- Nav Item - Quản lý Chuyên ngành -->
         <li class="nav-item {{ request()->routeIs('admin.majors.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.majors.index') }}">
                <i class="fas fa-fw fa-graduation-cap"></i>
                <span>Quản lý Ngành</span>
            </a>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider">

        <!-- Heading - Quản lý Thực tập -->
        <div class="sidebar-heading">
            Quản lý Thực tập
        </div>

        <!-- Nav Item - Quản lý Lịch thực tập -->
        {{-- Giữ nguyên điều kiện active và show của menu cha như trong file gốc bạn gửi --}}
        <li class="nav-item {{ request()->routeIs('admin.schedules.*') ? 'active' : '' }}">
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSchedules"
                aria-expanded="{{ request()->routeIs('admin.schedules.*') ? 'true' : 'false' }}" aria-controls="collapseSchedules">
                <i class="fas fa-fw fa-calendar-alt"></i>
                <span>Quản lý Lịch</span>
            </a>
            <div id="collapseSchedules" class="collapse {{ request()->routeIs('admin.schedules.*') ? 'show' : '' }}" aria-labelledby="headingSchedules" data-bs-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Lịch thực tập:</h6>
                    <a class="collapse-item {{ request()->routeIs('admin.schedules.index') ? 'active' : '' }}" href="{{ route('admin.schedules.index') }}">Tất cả lịch</a>
                    <a class="collapse-item {{ request()->routeIs('admin.schedules.create') ? 'active' : '' }}" href="{{ route('admin.schedules.create') }}">Tạo lịch mới</a>
                    {{-- ========================================================================= --}}
                    {{-- PHẦN CẬP NHẬT - START: SỬA TÊN ROUTE CHO "YÊU CẦU CHỜ DUYỆT"          --}}
                    {{-- ========================================================================= --}}
                    {{-- Giữ nguyên điều kiện active cho item con như trong file gốc bạn gửi, chỉ sửa tên route --}}
                    <a class="collapse-item {{ request()->routeIs('admin.schedules.pending') ? 'active' : '' }}" href="{{ route('admin.schedules.pendingRequests') }}">Yêu cầu chờ duyệt</a>
                    {{-- ========================================================================= --}}
                    {{-- PHẦN CẬP NHẬT - END                                                       --}}
                    {{-- ========================================================================= --}}
                </div>
            </div>
        </li>

        <!-- Nav Item - Quản lý Nhật ký -->
        <li class="nav-item {{ request()->routeIs('admin.diaries.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.diaries.index') }}">
                <i class="fas fa-fw fa-book-open"></i>
                <span>Quản lý Nhật ký</span>
            </a>
        </li>

         <!-- Nav Item - Quản lý Điểm danh -->
        <li class="nav-item {{ request()->routeIs('admin.attendances.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.attendances.index') }}">
                <i class="fas fa-fw fa-user-check"></i>
                <span>Quản lý Điểm danh</span>
            </a>
        </li>

         <!-- Nav Item - Quản lý Nhận xét Sinh viên -->
        <li class="nav-item {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.reviews.index') }}">
                <i class="fas fa-fw fa-comments"></i>
                <span>Quản lý Nhận xét SV</span>
            </a>
        </li>

        <!-- Nav Item - Quản lý Công việc Thực tập -->
        <li class="nav-item {{ request()->routeIs('admin.tasks.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.tasks.index') }}">
                <i class="fas fa-fw fa-tasks"></i>
                <span>{{ __('Công việc Thực tập') }}</span>
            </a>
        </li>


    {{-- ============================================== --}}
    {{-- ========== MENU DÀNH CHO SINH VIÊN ========== --}}
    {{-- ============================================== --}}
    @elseif(Auth::user()->hasRole('SinhVien'))

        <!-- Nav Item - Trang Chủ Sinh viên -->
        <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="fas fa-fw fa-home"></i>
                <span>Trang Chủ</span></a>
        </li>

         <!-- Divider -->
         <hr class="sidebar-divider">

         <!-- Heading -->
         <div class="sidebar-heading">
             Thực tập
         </div>

         <!-- Nav Item - Lịch thực tập Sinh viên -->
         <li class="nav-item {{ request()->routeIs('student.schedule.index') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('student.schedule.index') }}">
                <i class="fas fa-fw fa-calendar-alt"></i>
                <span>Lịch thực tập</span>
            </a>
        </li>

         <!-- Nav Item - Nhật ký thực tập Sinh viên -->
         <li class="nav-item {{ request()->routeIs('student.diaries.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('student.diaries.index') }}">
                 <i class="fas fa-fw fa-book-open"></i>
                <span>Nhật ký thực tập</span>
            </a>
        </li>

        <!-- Nav Item - Công việc của tôi -->
        <li class="nav-item {{ request()->routeIs('student.tasks.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('student.tasks.index') }}">
                <i class="fas fa-fw fa-tasks"></i>
                <span>{{ __('Công việc của tôi') }}</span>
            </a>
        </li>

    @endif {{-- Kết thúc kiểm tra vai trò --}}


    {{-- Phần chung cho cả hai vai trò --}}
    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
@endauth {{-- Kết thúc @auth --}}