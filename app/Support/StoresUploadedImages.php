<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Penanganan unggahan gambar (logo layanan/penyedia) pada disk publik.
 *
 * Berkas disimpan di storage/app/public sehingga dapat diakses melalui
 * public/storage setelah menjalankan `php artisan storage:link`.
 */
trait StoresUploadedImages
{
    /**
     * Simpan berkas unggahan dan kembalikan path relatifnya pada disk publik.
     *
     * @param  string  $directory  Sub-folder, mis. 'services' atau 'providers'.
     * @param  string|null  $previousPath  Path lama yang akan dihapus bila ada.
     */
    protected function storeImage(UploadedFile $file, string $directory, ?string $previousPath = null): string
    {
        $path = $file->store($directory, 'public');

        if ($previousPath !== null) {
            $this->deleteImage($previousPath);
        }

        return $path;
    }

    /**
     * Hapus berkas gambar dari disk publik bila ada.
     */
    protected function deleteImage(?string $path): void
    {
        if ($path !== null && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
