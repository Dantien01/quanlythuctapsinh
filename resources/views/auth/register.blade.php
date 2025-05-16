@extends('layouts.guest')

@section('content')
    {{-- Thêm class 'signup-active' để CSS biết vị trí slider --}}
    <div class="wrapper signup-active">
        <div class="title-text">
             {{-- Không cần title Login ở đây --}}
            <div class="title signup">Đăng ký</div>
        </div>
        <div class="form-container">
            <div class="slide-controls">
                 {{-- Tab Login (link tới trang login) --}}
                 <a href="{{ route('login') }}" class="slide login">Đăng nhập</a>
                 {{-- Tab Signup (active, không phải link) --}}
                 <span class="slide signup">Đăng ký</span>
                 {{-- Slider background --}}
                <div class="slider-tab"></div>
            </div>
            <div class="form-inner">
                 {{-- Không cần form Login ở đây --}}
                {{-- Form Signup của Laravel --}}
                <form action="{{ route('register') }}" method="POST" class="signup">
                    @csrf

                    {{-- Generic Errors --}}
                    @if ($errors->any())
                        <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger small mb-3" role="alert">
                            <ul class="mb-0 pl-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Name Field --}}
                    <div class="field">
                        <input type="text" name="name" placeholder="Họ và Tên" required value="{{ old('name') }}" autocomplete="name" autofocus class="@error('name') is-invalid @enderror">
                         @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                         @enderror
                    </div>

                     {{-- Phone Field --}}
                    <div class="field">
                        <input type="tel" name="phone_number" placeholder="Số điện thoại" required value="{{ old('phone_number') }}" autocomplete="tel" class="@error('phone_number') is-invalid @enderror">
                         @error('phone_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                         @enderror
                    </div>

                    <div class="field">
                        <input type="email" name="email" placeholder="Địa chỉ Email" required value="{{ old('email') }}" autocomplete="email" class="@error('email') is-invalid @enderror">
                         @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                         @enderror
                    </div>
                    <div class="field password-field-wrapper"> {{-- Wrapper cho input và icon --}}
                        <input type="password" id="register_password" name="password" placeholder="Mật khẩu" required autocomplete="new-password" class="@error('password') is-invalid @enderror">
                        <span class="toggle-password" onclick="togglePasswordVisibility('register_password', this)">
                            <i class="fas fa-eye"></i>
                        </span>
                         @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                         @enderror
                    </div>
                    <div class="field password-field-wrapper"> {{-- Wrapper cho input và icon --}}
                        <input type="password" id="register_password_confirmation" name="password_confirmation" placeholder="Xác nhận mật khẩu" required autocomplete="new-password">
                        <span class="toggle-password" onclick="togglePasswordVisibility('register_password_confirmation', this)">
                            <i class="fas fa-eye"></i>
                        </span>
                         {{-- Lỗi confirm thường hiển thị chung hoặc trong lỗi password --}}
                    </div>
                    <div class="field btn">
                        <div class="btn-layer"></div>
                        <input type="submit" value="Đăng ký">
                    </div>
                     @if (Route::has('login'))
                        <div class="bottom-link">
                             Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập ngay</a>
                        </div>
                     @endif
                </form>
            </div>
        </div>
    </div>
@endsection