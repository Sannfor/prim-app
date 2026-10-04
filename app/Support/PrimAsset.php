<?php

namespace App\Support;

use Illuminate\Support\Facades\Vite;
use Throwable;

/**
 * Penunjuk lokasi aset gambar desain PRIM.
 *
 * Sebagian aset dirujuk lewat nama berkas dinamis (mis. logo merek per slug
 * layanan) sehingga tidak dapat didaftarkan sebagai entry di Vite dan
 * disajikan langsung dari public/images. Aset yang jumlahnya tetap dan selalu
 * dipakai dibundel lewat Vite agar ikut ter-cache dengan hash.
 */
class PrimAsset
{
    /**
     * URL logo merek layanan berdasarkan slug, atau null bila berkasnya tidak ada.
     */
    public static function brandLogo(?string $slug): ?string
    {
        if (blank($slug)) {
            return null;
        }

        $relative = 'images/brands/'.$slug.'.png';

        return file_exists(public_path($relative)) ? asset($relative) : null;
    }

    /**
     * URL aset halaman pada resources/images/figma (dibundel lewat Vite),
     * atau null bila berkasnya tidak tersedia.
     */
    public static function figma(string $name): ?string
    {
        $resource = 'resources/images/figma/'.$name.'.png';

        if (! file_exists(base_path($resource))) {
            return null;
        }

        try {
            return Vite::asset($resource);
        } catch (Throwable) {
            // Mode pengembangan tanpa manifest: sajikan langsung bila tersalin.
            $public = 'images/figma/'.$name.'.png';

            return file_exists(public_path($public)) ? asset($public) : null;
        }
    }
}
