{{-- resources/views/profile/partials/delete-user-form.blade.php --}}
<section class="space-y-6"> {{-- Có thể bỏ space-y --}}
    <header>
        <h2 class="text-lg font-weight-medium text-dark">Xóa Tài khoản</h2>
        <p class="mt-1 small text-muted">
            Khi tài khoản của bạn bị xóa, tất cả tài nguyên và dữ liệu của nó sẽ bị xóa vĩnh viễn. Trước khi xóa tài khoản, vui lòng tải xuống mọi dữ liệu hoặc thông tin bạn muốn giữ lại.
        </p>
    </header>

    {{-- Nút mở Bootstrap Modal --}}
    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmUserDeletionModal">
         Xóa Tài khoản
    </button>

    {{-- Bootstrap Modal --}}
    <div class="modal fade" id="confirmUserDeletionModal" tabindex="-1" aria-labelledby="confirmUserDeletionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                 <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')

                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmUserDeletionModalLabel">Bạn có chắc chắn muốn xóa tài khoản?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted">
                            Khi tài khoản của bạn bị xóa, tất cả tài nguyên và dữ liệu của nó sẽ bị xóa vĩnh viễn. Vui lòng nhập mật khẩu của bạn để xác nhận bạn muốn xóa tài khoản vĩnh viễn.
                        </p>

                        <div class="mt-3">
                            <label for="password_delete" class="form-label sr-only">Mật khẩu</label> {{-- sr-only ẩn label nhưng vẫn tốt cho accessibility --}}
                            <input
                                id="password_delete"
                                name="password"
                                type="password"
                                class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                                placeholder="Mật khẩu"
                                required
                            />
                             @error('password', 'userDeletion') {{-- Sử dụng error bag 'userDeletion' --}}
                                <div class="invalid-feedback mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-danger">Xóa Tài khoản</button>
                    </div>
                 </form>
            </div>
        </div>
    </div>
</section>