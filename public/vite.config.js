import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    // THÊM KHỐI NÀY
    build: {
        // Đảm bảo output của Vite được đặt trong thư mục ".vite"
        outDir: 'public/.vite',
    }
    // KẾT THÚC KHỐI NÀY
});
