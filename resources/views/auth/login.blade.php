@extends('layouts.guest')

@section('content')
    {{-- Thêm class 'login-active' để CSS biết vị trí slider --}}
    <div class="wrapper login-active">
        <div class="title-text">
            <div class="title login">Đăng nhập</div>
            {{-- Không cần title Signup ở đây --}}
        </div>
        <div class="form-container">
            <div class="slide-controls">
                {{-- Tab Login (active, không phải link) --}}
                <span class="slide login">Đăng nhập</span>
                {{-- Tab Signup (link tới trang register) --}}
                <a href="{{ route('register') }}" class="slide signup">Đăng ký</a>
                {{-- Slider background --}}
                <div class="slider-tab"></div>
            </div>
            <div class="form-inner">
                {{-- Form Login của Laravel --}}
                <form action="{{ route('login') }}" method="POST" class="login">
                    @csrf

                     {{-- Session Status --}}
                    <x-auth-session-status class="mb-4 alert alert-success border-0 bg-success bg-opacity-10 text-success small" :status="session('status')" />

                     {{-- Generic Errors --}}
                    @if ($errors->any() && !$errors->has('email') && !$errors->has('password'))
                        <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger small mb-3" role="alert">
                            <ul class="mb-0 pl-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="field">
                        <input type="email" name="email" placeholder="Địa chỉ Email" required value="{{ old('email') }}" autocomplete="email" autofocus class="@error('email') is-invalid @enderror">
                         @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                         @enderror
                    </div>
                    <div class="field password-field-wrapper"> {{-- Wrapper cho input và icon --}}
                        <input type="password" id="login_password" name="password" placeholder="Mật khẩu" required autocomplete="current-password" class="@error('password') is-invalid @enderror">
                        <span class="toggle-password" onclick="togglePasswordVisibility('login_password', this)">
                            <i class="fas fa-eye"></i>
                        </span>
                         @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                         @enderror
                    </div>
                     @if (Route::has('password.request'))
                        <div class="pass-link">
                            <a href="{{ route('password.request') }}">Quên mật khẩu?</a>
                        </div>
                     @endif
                    <div class="field btn">
                        <div class="btn-layer"></div>
                        <input type="submit" value="Đăng nhập">
                    </div>
                    @if (Route::has('register'))
                        <div class="bottom-link">
                            Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký ngay</a>
                        </div>
                    @endif
                </form>
                {{-- Không cần form Signup ở đây --}}
            </div>
        </div>
    </div>
@endsection