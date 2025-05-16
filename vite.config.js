// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', // Giữ lại cho frontend/student nếu cần
                'resources/js/app.js',   // Giữ lại cho frontend/student nếu cần

                // === THÊM TÀI SẢN CHO ADMIN ===
                'resources/scss/admin/sb-admin-2.scss', // File SCSS chính của SB Admin 2
                'resources/js/admin/sb-admin-2.js'      // File JS chính của SB Admin 2
                // ==============================
            ],
            refresh: true,
        }),
    ],
    // (Tùy chọn) Thêm alias cho jQuery nếu cần bởi các plugin cũ
    // resolve: {
    //     alias: {
    //         '$': 'jQuery'
    //     }
    // }
});