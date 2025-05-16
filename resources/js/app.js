import './bootstrap';
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();
// ===== THÊM CODE JAVASCRIPT CHO ẨN/HIỆN MẬT KHẨU VÀO ĐÂY =====
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
// Gán hàm vào window để có thể gọi từ onclick trong Blade
window.togglePasswordVisibility = togglePasswordVisibility;
// ================================================================