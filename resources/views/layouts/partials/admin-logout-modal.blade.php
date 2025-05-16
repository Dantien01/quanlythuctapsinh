{{-- resources/views/layouts/partials/admin-logout-modal.blade.php --}}
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Sẵn sàng rời đi?</h5>
                 {{-- Sử dụng data-bs-dismiss cho Bootstrap 5 --}}
                <button class="close" type="button" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Chọn "Đăng xuất" bên dưới nếu bạn đã sẵn sàng kết thúc phiên làm việc hiện tại.</div>
            <div class="modal-footer">
                {{-- Sử dụng data-bs-dismiss cho Bootstrap 5 --}}
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Hủy</button>

                {{-- Form Logout an toàn với CSRF --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">Đăng xuất</button>
                </form>
            </div>
        </div>
    </div>
</div>