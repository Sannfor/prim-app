<?php

namespace Modules\Admin\Livewire;

use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Pengaturan notifikasi administrator.
 *
 * Mengikuti frame "ADMIN - Pengaturan notifikasi": delapan saklar yang dibagi
 * menjadi kelompok Notifikasi Email dan Notifikasi Sistem.
 */
#[Layout('layouts::admin')]
#[Title('Pengaturan Notifikasi')]
class SettingsNotification extends Component
{
    /**
     * Daftar saklar notifikasi yang tersedia.
     *
     * @var array<string, array{group: string, label: string, description: string}>
     */
    public const OPTIONS = [
        'daily_report' => [
            'group' => 'email',
            'label' => 'Laporan Harian',
            'description' => 'Terima ringkasan aktivitas harian toko langsung di email.',
        ],
        'new_order_email' => [
            'group' => 'email',
            'label' => 'Pesanan Baru',
            'description' => 'Dapatkan notifikasi instan setiap kali pelanggan membuat pesanan baru.',
        ],
        'system_update' => [
            'group' => 'email',
            'label' => 'Pembaruan Sistem',
            'description' => 'Notifikasi mengenai pemeliharaan terjadwal dan fitur baru aplikasi.',
        ],
        'password_change' => [
            'group' => 'email',
            'label' => 'Perubahan Kata Sandi',
            'description' => 'Notifikasi segera setelah ada upaya perubahan kata sandi pada akun.',
        ],
        'new_order_popup' => [
            'group' => 'sistem',
            'label' => 'Pop-up Pesanan',
            'description' => 'Tampilkan jendela pop-up di layar saat ada pesanan masuk.',
        ],
        'new_device_login' => [
            'group' => 'sistem',
            'label' => 'Login Perangkat Baru',
            'description' => 'Kirim peringatan jika akun Anda diakses dari perangkat yang tidak dikenal.',
        ],
        'user_message' => [
            'group' => 'sistem',
            'label' => 'Pesan Pengguna',
            'description' => 'Notifikasi ketika pelanggan mengirim pesan melalui fitur bantuan.',
        ],
        'low_stock' => [
            'group' => 'sistem',
            'label' => 'Peringatan Stok Rendah',
            'description' => 'Peringatan sistem saat stok produk mencapai batas minimum.',
        ],
    ];

    /** @var array<string, bool> */
    public array $preferences = [];

    public function mount(): void
    {
        $this->preferences = Auth::user()->notificationPreferences();
    }

    /**
     * Simpan preferensi notifikasi ke kolom settings milik pengguna.
     */
    public function save(): void
    {
        $user = Auth::user();

        $settings = (array) $user->settings;
        $settings['notifications'] = $this->preferences;

        $user->settings = $settings;
        $user->save();

        Flux::toast(variant: 'success', text: 'Preferensi notifikasi tersimpan.');
    }

    public function render(): View
    {
        $grouped = ['email' => [], 'sistem' => []];

        foreach (self::OPTIONS as $key => $option) {
            $grouped[$option['group']][$key] = $option;
        }

        return view('admin::livewire.settings-notification', [
            'grouped' => $grouped,
        ]);
    }
}
