// resources/js/app.js

import $ from 'jquery';
window.$ = window.jQuery = $;
console.log('app.js: jQuery gán vào window');

import './bootstrap';
console.log('app.js: bootstrap.js đã được import');

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();
console.log('app.js: Alpine.js đã được khởi tạo');

// ===== CODE JAVASCRIPT CHO ẨN/HIỆN MẬT KHẨU =====
function togglePasswordVisibility(inputId, iconSpan) {
    const passwordInput = document.getElementById(inputId);
    const icon = iconSpan.querySelector('i');

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
window.togglePasswordVisibility = togglePasswordVisibility;
console.log('app.js: togglePasswordVisibility đã được gán vào window');
// ===================================================

// ===== IMPORT FILE XỬ LÝ THÔNG BÁO Ở CUỐI CÙNG =====
import './notification-handler.js'; // File này chứa logic jQuery cho thông báo
console.log('app.js: notification-handler.js đã được import');