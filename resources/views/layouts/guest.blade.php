{{-- File: resources/views/layouts/guest.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    {{-- Import Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome cho icons (Đã thêm) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />

    {{-- Nhúng trực tiếp CSS vào đây cho đơn giản, hoặc link tới file CSS đã biên dịch nếu dùng Vite/Mix --}}
    <style>
        /* --- Paste toàn bộ CSS bạn đã cung cấp vào đây --- */
        *{
          margin: 0;
          padding: 0;
          box-sizing: border-box;
          font-family: 'Poppins', sans-serif;
        }
        html,body{
          display: grid; /* Sử dụng grid để căn giữa wrapper */
          height: 100%;
          width: 100%;
          place-items: center; /* Căn giữa theo cả chiều dọc và ngang */
          background: -webkit-linear-gradient(left, #003366,#004080,#0059b3, #0073e6);
          overflow: hidden; /* Tránh scroll không mong muốn */
        }
        ::selection{
          background: #1a75ff;
          color: #fff;
        }
        .wrapper{
          /* Bỏ overflow: hidden vì chúng ta không dùng hiệu ứng slide form */
          max-width: 390px;
          width: 100%; /* Đảm bảo wrapper chiếm không gian */
          background: #fff;
          padding: 30px;
          border-radius: 15px;
          box-shadow: 0px 15px 20px rgba(0,0,0,0.1);
        }
        .wrapper .title-text{
           /* Chỉ hiển thị 1 title nên không cần flex và width 200% */
        }
        .wrapper .title{
          /* Chỉ có 1 title nên không cần width 50% */
          font-size: 30px; /* Giảm nhẹ kích thước */
          font-weight: 600;
          text-align: center;
          /* Bỏ transition */
          margin-bottom: 20px; /* Thêm khoảng cách dưới title */
        }

        .wrapper .slide-controls{
          position: relative;
          display: flex;
          height: 50px;
          width: 100%;
          overflow: hidden;
          margin: 0 0 20px 0; /* Điều chỉnh margin */
          justify-content: space-between;
          border: 1px solid lightgrey;
          border-radius: 15px; /* Giữ bo tròn */
        }
        .slide-controls .slide{
          height: 100%;
          width: 50%; /* Mỗi tab chiếm 50% */
          color: #fff;
          font-size: 18px;
          font-weight: 500;
          text-align: center;
          line-height: 48px;
          cursor: pointer;
          z-index: 1;
          transition: color 0.6s ease; /* Chỉ cần transition màu chữ */
          text-decoration: none; /* Bỏ gạch chân link */
          display: flex; /* Căn giữa nội dung tab */
          align-items: center;
          justify-content: center;
        }
        .slide-controls a.slide.signup { /* Style cho tab Signup không active */
            color: #000;
        }
        .slide-controls a.slide.login { /* Style cho tab Login không active */
             color: #000;
        }

        .slide-controls .slider-tab{
          position: absolute;
          height: 100%;
          width: 50%; /* Chiếm 50% */
          top: 0; /* Căn lên trên */
          left: 0; /* Mặc định ở bên trái (Login) */
          z-index: 0;
          border-radius: 15px; /* Giữ bo tròn */
          background: -webkit-linear-gradient(left,#003366,#004080,#0059b3, #0073e6);
          transition: all 0.6s cubic-bezier(0.68,-0.55,0.265,1.55);
        }

        /* --- Điều chỉnh vị trí slider-tab dựa trên class của wrapper --- */
        .wrapper.login-active .slide-controls .slider-tab {
            left: 0;
        }
         .wrapper.login-active .slide-controls a.slide.signup { /* Signup không active */
             color: #000;
         }
         .wrapper.login-active .slide-controls .slide.login { /* Login active */
             color: #fff;
             cursor: default;
         }

        .wrapper.signup-active .slide-controls .slider-tab {
            left: 50%; /* Di chuyển sang phải */
        }
         .wrapper.signup-active .slide-controls a.slide.login { /* Login không active */
             color: #000;
         }
         .wrapper.signup-active .slide-controls .slide.signup { /* Signup active */
              color: #fff;
             cursor: default;
         }
        /* --- Hết phần điều chỉnh slider --- */


        /* Bỏ input radio vì không dùng */
        /* input[type="radio"]{ display: none; } */

        .wrapper .form-container{
          width: 100%;
           /* Bỏ overflow: hidden */
        }
        .form-container .form-inner{
           /* Chỉ có 1 form nên không cần flex và width 200% */
           /* Bỏ display: flex; */
           /* Bỏ width: 200%; */
        }
        .form-container .form-inner form{
           /* Chỉ có 1 form nên không cần width 50% và transition */
           /* Bỏ width: 50%; */
           /* Bỏ transition: all 0.6s cubic-bezier(0.68,-0.55,0.265,1.55); */
        }
        .form-inner form .field{
          height: 50px;
          width: 100%;
          margin-top: 20px;
        }
        .form-inner form .field input{
          height: 100%;
          width: 100%;
          outline: none;
          padding-left: 15px;
          border-radius: 15px; /* Giữ bo tròn */
          border: 1px solid lightgrey;
          border-bottom-width: 2px;
          font-size: 17px;
          transition: all 0.3s ease;
        }
        .form-inner form .field input:focus{
          border-color: #1a75ff;
        }
        .form-inner form .field input::placeholder{
          color: #999;
          transition: all 0.3s ease;
        }
        form .field input:focus::placeholder{
          color: #1a75ff;
        }
        .form-inner form .pass-link{
          margin-top: 10px; /* Tăng khoảng cách */
          text-align: left; /* Căn trái link quên MK */
        }
        .form-inner form .signup-link{ /* Đổi tên thành bottom-link chung */
          text-align: center;
          margin-top: 30px;
        }
        .form-inner form .pass-link a,
        .form-inner form .bottom-link a{ /* Đổi tên selector */
          color: #1a75ff;
          text-decoration: none;
        }
        .form-inner form .pass-link a:hover,
        .form-inner form .bottom-link a:hover{ /* Đổi tên selector */
          text-decoration: underline;
        }
        form .btn{
          height: 50px;
          width: 100%;
          border-radius: 15px; /* Giữ bo tròn */
          position: relative;
          overflow: hidden;
          margin-top: 20px; /* Thêm margin top cho nút */
        }
        form .btn .btn-layer{
          height: 100%;
          width: 300%;
          position: absolute;
          left: -100%;
          background: -webkit-linear-gradient(right,#003366,#004080,#0059b3, #0073e6);
          border-radius: 15px; /* Giữ bo tròn */
          transition: all 0.4s ease;;
        }
        form .btn:hover .btn-layer{
          left: 0;
        }
        form .btn input[type="submit"]{
          height: 100%;
          width: 100%;
          z-index: 1;
          position: relative;
          background: none;
          border: none;
          color: #fff;
          padding-left: 0;
          border-radius: 15px; /* Giữ bo tròn */
          font-size: 20px;
          font-weight: 500;
          cursor: pointer;
        }
        /* Thêm style cho validation error nếu cần */
        .form-inner form .field input.is-invalid {
            border-color: #dc3545; /* Màu đỏ cho lỗi */
        }
        .invalid-feedback {
            color: #dc3545;
            font-size: 0.875em;
            margin-top: .25rem;
            display: block; /* Hiển thị thông báo lỗi */
            text-align: left;
        }

        /* CSS cho icon ẩn/hiện mật khẩu (Đã thêm vào đây theo yêu cầu) */
        .field.password-field-wrapper {
            position: relative; /* Để định vị icon tuyệt đối bên trong */
            width: 100%;
        }

        .field.password-field-wrapper input[type="password"],
        .field.password-field-wrapper input[type="text"] { /* Áp dụng cho cả khi mật khẩu hiện */
            width: 100%;
            padding-right: 40px !important; /* Khoảng trống cho icon */
            box-sizing: border-box;
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 1px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            padding: 10px;
            display: flex;
            align-items: center;
            height: 100%;
            z-index: 2;
        }

        .toggle-password i {
            font-size: 16px;
        }
        /* Kết thúc CSS cho icon ẩn/hiện mật khẩu */

    </style>
    {{-- Có thể thêm @stack('styles') nếu cần CSS riêng cho từng view --}}
</head>
<body>
    {{-- Layout sẽ trực tiếp hiển thị nội dung từ @yield --}}
    @yield('content')

    {{-- JavaScript cho ẩn/hiện mật khẩu (Đã thêm vào đây theo yêu cầu) --}}
    <script>
    function togglePasswordVisibility(inputId, iconSpan) {
        const passwordInput = document.getElementById(inputId);
        const icon = iconSpan.querySelector('i'); // Lấy thẻ <i> bên trong <span>

        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = "password";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    // Không cần gán vào window nếu HTML và JS nằm trong cùng 1 file và script nằm sau HTML sử dụng nó
    // Tuy nhiên, nếu có trường hợp onclick được render trước khi script này chạy (ít khả năng với cách này),
    // hoặc để an toàn hơn nếu có thay đổi cấu trúc sau này, việc gán vào window vẫn có thể hữu ích.
    // window.togglePasswordVisibility = togglePasswordVisibility;
    </script>
     {{-- Có thể thêm @stack('scripts') nếu cần JS riêng cho từng view --}}
</body>
</html>