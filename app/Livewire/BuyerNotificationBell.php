<?php

namespace App\Livewire;

use App\Models\BuyerNotification;
use App\Services\BuyerNotificationService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Lonceng notifikasi untuk pembeli.
 *
 * Menampilkan jumlah notifikasi yang belum dibaca beserta daftar terbarunya.
 * Notifikasi ditandai sudah dibaca ketika diklik, atau sekaligus lewat tombol
 * "Tandai semua dibaca".
 */
class BuyerNotificationBell extends Component
{
    /**
     * Jumlah notifikasi terbaru yang ditampilkan pada daftar jatuh.
     */
    private const JUMLAH_TAMPIL = 6;

    /**
     * Muat ulang daftar notifikasi.
     *
     * Dipanggil ulang ketika ada bagian lain aplikasi yang memicu notifikasi
     * baru, misalnya setelah pembayaran berhasil.
     */
    #[On('notifikasi-baru')]
    public function segarkan(): void
    {
        // Tidak ada keadaan yang perlu diubah; pemanggilan ini memicu render ulang.
    }

    /**
     * Tandai satu notifikasi sudah dibaca, lalu kembalikan tautan tujuannya.
     */
    public function buka(int $id): ?string
    {
        $notifikasi = BuyerNotification::query()
            ->where('user_id', auth()->id())
            ->whereKey($id)
            ->first();

        if ($notifikasi === null) {
            return null;
        }

        $notifikasi->markAsRead();

        return $notifikasi->url;
    }

    /**
     * Tandai seluruh notifikasi sudah dibaca.
     */
    public function tandaiSemua(BuyerNotificationService $notifications): void
    {
        $jumlah = $notifications->tandaiSemuaDibaca(auth()->user());

        \Flux\Flux::toast(
            variant: 'success',
            text: $jumlah > 0
                ? $jumlah.' notifikasi ditandai sudah dibaca.'
                : 'Tidak ada notifikasi yang belum dibaca.',
        );
    }

    public function render(): View
    {
        $daftar = BuyerNotification::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->take(self::JUMLAH_TAMPIL)
            ->get();

        return view('livewire.buyer-notification-bell', [
            'items' => $daftar,
            'unreadCount' => BuyerNotification::query()
                ->where('user_id', auth()->id())
                ->unread()
                ->count(),
        ]);
    }
}
