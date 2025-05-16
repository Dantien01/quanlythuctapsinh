{{-- resources/views/profile/partials/update-profile-information-form.blade.php --}}
<section>
    <header>
        <h2 class="text-lg font-weight-medium text-dark">Thông tin Hồ sơ cá nhân</h2>
        <p class="mt-1 small text-muted">Cập nhật thông tin hồ sơ và địa chỉ email của tài khoản.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>

    {{-- Quan trọng: Thêm enctype="multipart/form-data" cho phép upload file --}}
    <form method="post" action="{{ route('profile.update') }}" class="mt-4" enctype="multipart/form-data">
        @csrf
        @method('patch')

         {{-- Phần hiển thị và chọn ảnh đại diện --}}
         <div class="mb-3">
            <label for="photo" class="form-label">Ảnh đại diện</label>
            <div class="mt-2 d-flex align-items-center gap-3"> {{-- Dùng Flexbox để xếp ảnh và input --}}
                <!-- Ảnh đại diện hiện tại -->
                {{-- Sử dụng accessor profile_photo_url đã thêm vào User model --}}
                <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" class="rounded-circle" width="80" height="80" style="object-fit: cover;">
                 <!-- Input chọn ảnh mới -->
                <div>
                    <input class="form-control form-control-sm @error('photo') is-invalid @enderror" type="file" name="photo" id="photo" accept="image/png, image/jpeg, image/jpg">
                     <small class="text-muted d-block mt-1">Chọn ảnh mới JPG, PNG (Tối đa 2MB).</small>
                     @error('photo')
                        <div class="invalid-feedback d-block">{{ $message }}</div> {{-- Hiển thị lỗi nếu có --}}
                    @enderror
                </div>
            </div>
        </div>

        {{-- Tên --}}
        <div class="mb-3">
            <label for="name" class="form-label">Tên <span class="text-danger">*</span></label>
            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Email --}}
        <div class="mb-3">
            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username" />
            @error('email')
                 <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            {{-- Phần xác thực email (giữ nguyên) --}}
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="small mt-2 text-muted">
                        Địa chỉ email của bạn chưa được xác thực.
                        <button form="send-verification" class="btn btn-link p-0 m-0 align-baseline text-decoration-underline small">
                            Nhấp vào đây để gửi lại email xác thực.
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 small text-success">
                            Một liên kết xác thực mới đã được gửi đến địa chỉ email của bạn.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        {{-- === THÊM TRƯỜNG SỐ ĐIỆN THOẠI === --}}
        <div class="mb-3">
             <label for="phone_number" class="form-label">Số điện thoại</label>
             <input type="tel" id="phone_number" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}" autocomplete="tel"
                    class="form-control @error('phone_number') is-invalid @enderror">
             @error('phone_number')
                 <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        {{-- ================================ --}}


        {{-- Các trường không cần thiết cho Admin đã được xóa --}}


        <div class="d-flex align-items-center gap-4 mt-4"> {{-- Dùng d-flex và gap của Bootstrap 5 --}}
            <button type="submit" class="btn btn-primary">Lưu</button>

            @if (session('status') === 'profile-updated')
                {{-- Đoạn mã Alpine.js này sẽ vẫn hoạt động nếu Alpine được nạp toàn cục --}}
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="small text-muted m-0"
                >Đã lưu.</p>
            @endif
        </div>
    </form>
</section>