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

{{-- ========================================================================= --}}
{{-- PHẦN CẬP NHẬT - START: CHỈ GIỮ LẠI CSS TRONG HEAD VỚI VITE             --}}
{{-- ========================================================================= --}}
@vite(['resources/scss/admin/sb-admin-2.scss']) {{-- Chỉ nạp CSS ở đây --}}
{{-- ========================================================================= --}}
{{-- PHẦN CẬP NHẬT - END                                                       --}}
{{-- ========================================================================= --}}

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

            {{-- Topbar --}}
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
{{-- PHẦN CẬP NHẬT - START: CHUYỂN VITE CHO JS XUỐNG CUỐI BODY, TRƯỚC STACK  --}}
{{-- ========================================================================= --}}
@vite(['resources/js/admin/sb-admin-2.js']) {{-- Nạp JS chính (chứa jQuery) ở đây --}}
{{-- ========================================================================= --}}
{{-- PHẦN CẬP NHẬT - END                                                       --}}
{{-- ========================================================================= --}}

{{-- Cho phép các trang con thêm JS tùy chỉnh nếu cần --}}
@stack('scripts') {{-- Script của trang con sẽ chạy sau khi JS chính đã được gọi để nạp --}}
</body>
</html>