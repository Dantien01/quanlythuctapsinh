{{-- resources/views/layouts/admin.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
{{-- CSRF Token --}}
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="description" content="Admin Panel for {{ config('app.name', 'Laravel') }}">
<meta name="author" content="">

<title>@yield('title', config('app.name', 'Laravel') . ' - Admin')</title>

{{-- CSS cho template sb-admin-2 (Vite) --}}
@vite(['resources/scss/admin/sb-admin-2.scss'])

{{-- (TÙY CHỌN) CSS chung của ứng dụng, nếu cần cho admin --}}
{{-- @vite(['resources/css/app.css']) --}}

{{-- Cho phép các trang con thêm CSS tùy chỉnh nếu cần --}}
@stack('styles')
</head>
<body id="page-top">
<!-- Page Wrapper -->
<div id="wrapper">

    {{-- Sidebar --}}
    @include('layouts.partials.admin-sidebar')

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

        <!-- Main Content -->
        <div id="content">

            {{-- Topbar (Nơi chứa nút thông báo) --}}
            @include('layouts.partials.admin-topbar')

            <!-- Begin Page Content -->
            <div class="container-fluid">
                @yield('content')
            </div>
            <!-- /.container-fluid -->

        </div>
        <!-- End of Main Content -->

        {{-- Footer --}}
        @include('layouts.partials.admin-footer')

    </div>
    <!-- End of Content Wrapper -->

</div>
<!-- End of Page Wrapper -->

{{-- Scroll to Top Button --}}
@include('layouts.partials.admin-scrolltop')

{{-- Logout Modal --}}
@include('layouts.partials.admin-logout-modal')


{{-- ========================================================================= --}}
{{--                       PHẦN SCRIPTS Ở CUỐI BODY                             --}}
{{-- ========================================================================= --}}

{{-- 1. JavaScript của template SB Admin 2 (NẾU CÓ và không bao gồm jQuery toàn cục) --}}
@vite(['resources/js/admin/sb-admin-2.js'])

{{-- 2. JavaScript CHÍNH CỦA ỨNG DỤNG BẠN (sẽ chứa jQuery và notification-handler) --}}
{{-- Đảm bảo file này được gọi SAU sb-admin-2.js nếu sb-admin-2.js cần jQuery, --}}
{{-- hoặc nếu sb-admin-2.js có jQuery riêng thì cần đảm bảo app.js không xung đột (app.js sẽ ghi đè $ nếu cần). --}}
{{-- Với giả định sb-admin-2.js không có jQuery toàn cục, thứ tự này là OK. --}}
@vite(['resources/js/app.js'])

{{-- (TÙY CHỌN) Alpine.js từ CDN, nếu bạn dùng và chưa có trong các file JS trên --}}
{{-- <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script> --}}

{{-- Cho phép các trang con thêm JS tùy chỉnh nếu cần --}}
@stack('scripts')
</body>
</html>