<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherRedemption;

/**
 * Aturan pemakaian voucher.
 *
 * Seluruh pemeriksaan dikumpulkan di sini agar halaman konfirmasi pemesanan dan
 * pembuatan transaksi memakai aturan yang sama. Potongan dihitung dari harga
 * paket, lalu dibatasi agar tidak melebihi harga itu sendiri.
 */
class VoucherService
{
    /**
     * Hasil pemeriksaan voucher.
     *
     * @param  bool  $valid  Apakah voucher dapat dipakai.
     * @param  string  $message  Penjelasan untuk ditampilkan ke pengguna.
     * @param  Voucher|null  $voucher  Voucher yang cocok, bila ada.
     * @param  int  $discount  Potongan dalam rupiah, bila valid.
     */
    public function periksa(?string $code, User $user, Plan $plan): array
    {
        $kode = strtoupper(trim((string) $code));

        if ($kode === '') {
            return $this->hasil(false, 'Masukkan kode voucher terlebih dahulu.');
        }

        $voucher = Voucher::query()->where('code', $kode)->first();

        if ($voucher === null) {
            return $this->hasil(false, 'Kode voucher "'.$kode.'" tidak ditemukan.');
        }

        if (! $voucher->is_active) {
            return $this->hasil(false, 'Voucher ini sudah tidak aktif.');
        }

        if (! $voucher->isWithinPeriod()) {
            return $this->hasil(false, 'Voucher ini sedang tidak berlaku.', $voucher);
        }

        if (! $voucher->hasQuotaLeft()) {
            return $this->hasil(false, 'Kuota voucher ini sudah habis.', $voucher);
        }

        $terpakai = $this->pemakaianOleh($voucher, $user);

        if ($terpakai >= $voucher->usage_limit_per_user) {
            return $this->hasil(false, 'Kamu sudah pernah memakai voucher ini.', $voucher);
        }

        if ($voucher->min_purchase > 0 && $plan->price < $voucher->min_purchase) {
            return $this->hasil(
                false,
                'Voucher ini memerlukan belanja minimal Rp'.number_format($voucher->min_purchase, 0, ',', '.').'.',
                $voucher,
            );
        }

        $potongan = $this->hitungPotongan($voucher, $plan->price);

        if ($potongan <= 0) {
            return $this->hasil(false, 'Voucher ini tidak menghasilkan potongan untuk paket ini.', $voucher);
        }

        return $this->hasil(
            true,
            'Voucher berhasil dipakai. Kamu hemat Rp'.number_format($potongan, 0, ',', '.').'.',
            $voucher,
            $potongan,
        );
    }

    /**
     * Hitung potongan sebuah voucher terhadap sebuah harga.
     */
    public function hitungPotongan(Voucher $voucher, int $harga): int
    {
        $potongan = $voucher->type === Voucher::TYPE_FIXED
            ? $voucher->value
            : (int) floor($harga * $voucher->value / 100);

        if ($voucher->max_discount !== null) {
            $potongan = min($potongan, $voucher->max_discount);
        }

        // Potongan tidak boleh melebihi harga, agar total tidak pernah negatif.
        return max(0, min($potongan, $harga));
    }

    /**
     * Catat pemakaian voucher dan tambah penghitungnya.
     *
     * Dipanggil saat transaksi dibuat, bukan saat voucher diperiksa, supaya
     * kuota tidak berkurang hanya karena pengguna mencoba kodenya.
     */
    public function catatPemakaian(Voucher $voucher, User $user, int $transactionId, int $discount): void
    {
        VoucherRedemption::create([
            'voucher_id' => $voucher->id,
            'user_id' => $user->id,
            'transaction_id' => $transactionId,
            'discount_amount' => $discount,
        ]);

        $voucher->increment('used_count');
    }

    /**
     * Lepas pemakaian voucher, misalnya ketika pesanan dibatalkan.
     */
    public function lepasPemakaian(?int $transactionId): void
    {
        if ($transactionId === null) {
            return;
        }

        $catatan = VoucherRedemption::query()->where('transaction_id', $transactionId)->get();

        foreach ($catatan as $baris) {
            $baris->voucher?->decrement('used_count');
            $baris->delete();
        }
    }

    /**
     * Berapa kali pengguna ini sudah memakai voucher tersebut.
     */
    private function pemakaianOleh(Voucher $voucher, User $user): int
    {
        return VoucherRedemption::query()
            ->where('voucher_id', $voucher->id)
            ->where('user_id', $user->id)
            ->count();
    }

    /**
     * Susun hasil pemeriksaan.
     *
     * @return array{valid: bool, message: string, voucher: Voucher|null, discount: int}
     */
    private function hasil(bool $valid, string $message, ?Voucher $voucher = null, int $discount = 0): array
    {
        return [
            'valid' => $valid,
            'message' => $message,
            'voucher' => $voucher,
            'discount' => $discount,
        ];
    }
}
