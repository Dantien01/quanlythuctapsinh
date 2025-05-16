{{-- resources/views/layouts/student.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    {{-- ... (meta tags, title, vite/css) ... --}}
    @vite(['resources/scss/student/student-theme.scss', 'resources/js/student/student-app.js']) {{-- Ví dụ: file CSS/JS riêng --}}
    @stack('styles')
</head>
<body id="page-top-student">
    <div id="wrapper">
        @include('layouts.partials.student-sidebar') {{-- Sidebar riêng cho sinh viên --}}
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                @include('layouts.partials.student-topbar') {{-- Topbar riêng cho sinh viên --}}
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
            @include('layouts.partials.student-footer') {{-- Footer riêng --}}
        </div>
    </div>
    @include('layouts.partials.admin-scrolltop') {{-- Có thể dùng chung --}}
    @include('layouts.partials.admin-logout-modal') {{-- Có thể dùng chung --}}
    @stack('scripts')
</body>
</html>