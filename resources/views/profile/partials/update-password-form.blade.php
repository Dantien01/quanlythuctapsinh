{{-- resources/views/profile/partials/update-password-form.blade.php --}}
<section>
    <header>
        <h2 class="text-lg font-weight-medium text-dark">Cập nhật Mật khẩu</h2>
        <p class="mt-1 small text-muted">
            Đảm bảo tài khoản của bạn đang sử dụng mật khẩu dài, ngẫu nhiên để giữ an toàn.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-4 space-y-4"> {{-- Bỏ space-y, dùng mb-3 --}}
        @csrf
        @method('put')

        {{-- Mật khẩu hiện tại --}}
        <div class="mb-3">
            <label for="update_password_current_password" class="form-label">Mật khẩu hiện tại</label>
            <input id="update_password_current_password" name="current_password" type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" autocomplete="current-password" />
            @error('current_password', 'updatePassword') {{-- Sử dụng error bag 'updatePassword' --}}
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Mật khẩu mới --}}
        <div class="mb-3">
            <label for="update_password_password" class="form-label">Mật khẩu mới</label>
            <input id="update_password_password" name="password" type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" autocomplete="new-password" />
            @error('password', 'updatePassword')
                 <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Xác nhận mật khẩu mới --}}
        <div class="mb-3">
            <label for="update_password_password_confirmation" class="form-label">Xác nhận mật khẩu mới</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" autocomplete="new-password" />
             @error('password_confirmation', 'updatePassword')
                 <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex align-items-center gap-4">
            <button type="submit" class="btn btn-sm btn-primary shadow-sm text-white">Lưu</button>

            @if (session('status') === 'password-updated')
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