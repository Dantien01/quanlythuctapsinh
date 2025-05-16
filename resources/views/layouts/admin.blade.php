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

{{-- Tích hợp Vite để nạp CSS (bao gồm Bootstrap, FontAwesome, sb-admin-2.scss) và JS --}}
{{-- Đảm bảo các đường dẫn này khớp với phần 'input' trong vite.config.js --}}
@vite(['resources/scss/admin/sb-admin-2.scss', 'resources/js/admin/sb-admin-2.js'])

{{-- Cho phép các trang con thêm CSS tùy chỉnh nếu cần --}}
@stack('styles')
</head>
<body id="page-top">
<!-- Page Wrapper -->
<div id="wrapper">

    {{-- Sidebar: Nội dung sẽ nằm trong file partial --}}
    @include('layouts.partials.admin-sidebar')

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

        <!-- Main Content -->
        <div id="content">

            {{-- Topbar: Nội dung sẽ nằm trong file partial --}}
            @include('layouts.partials.admin-topbar')

            <!-- Begin Page Content -->
            <div class="container-fluid">

                {{-- Đây là nơi nội dung của các trang con sẽ được hiển thị --}}
                @yield('content')

            </div>
            <!-- /.container-fluid -->

        </div>
        <!-- End of Main Content -->

        {{-- Footer: Nội dung sẽ nằm trong file partial --}}
        @include('layouts.partials.admin-footer')

    </div>
    <!-- End of Content Wrapper -->

</div>
<!-- End of Page Wrapper -->

{{-- Scroll to Top Button: Nội dung sẽ nằm trong file partial --}}
@include('layouts.partials.admin-scrolltop')

{{-- Logout Modal: Nội dung sẽ nằm trong file partial --}}
@include('layouts.partials.admin-logout-modal')

{{-- Vite đã xử lý việc nạp JS, không cần các thẻ script tĩnh ở đây --}}

{{-- Cho phép các trang con thêm JS tùy chỉnh nếu cần --}}
@stack('scripts')
</body>
</html>