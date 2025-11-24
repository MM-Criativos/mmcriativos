import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // 🌐 Arquivos do SITE
                'resources/css/site.css',
                'resources/js/site.js',

                // 🛠️ Arquivos do ADMIN
                'resources/css/admin.css',
                'resources/js/admin.js',

                // (opcional) app.css/app.js caso queira usar
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});
