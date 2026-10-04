import { existsSync, readdirSync } from 'node:fs';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

/*
 | Aset gambar desain PRIM pada resources/images/figma didaftarkan sebagai entry
 | agar ikut dibundel Vite dan dirujuk lewat manifest dengan nama ber-hash.
 | Logo merek per layanan tetap disajikan dari public/images/brands karena
 | namanya mengikuti slug layanan (dinamis), sehingga tidak dapat dijadikan entry.
 */
const figmaDir = 'resources/images/figma';

const figmaImages = existsSync(figmaDir)
    ? readdirSync(figmaDir)
        .filter((file) => file.endsWith('.png'))
        .map((file) => `${figmaDir}/${file}`)
    : [];

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', ...figmaImages],
            refresh: [
                'resources/views/**/*',
                'Modules/*/resources/views/**/*',
            ],
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
    },
});
