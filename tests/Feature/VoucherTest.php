<?php

use App\Enums\TransactionStatus;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherRedemption;
use App\Services\TransactionService;
use App\Services\VoucherService;
use Database\Seeders\CatalogSeeder;
use Livewire\Livewire;
use Modules\Transaction\Livewire\Checkout;

/*
|--------------------------------------------------------------------------
| Voucher: aturan pemakaian, potongan, dan pembatalan pesanan
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    $this->seed(CatalogSeeder::class);

    $this->user = User::factory()->create();
    $this->plan = Plan::query()->where('is_active', true)->firstOrFail();
    $this->vouchers = app(VoucherService::class);
});

test('voucher persen memotong sesuai nilai dan dibatasi maksimum', function () {
    $voucher = Voucher::factory()->percent(20, 50000)->create();

    // Harga tinggi: potongan terkena batas maksimum.
    $mahal = 567000;
    expect($this->vouchers->hitungPotongan($voucher, $mahal))->toBe(50000);

    // Harga rendah: potongan mengikuti persentase.
    expect($this->vouchers->hitungPotongan($voucher, 100000))->toBe(20000);
});

test('voucher nominal tetap memotong sebesar nilainya', function () {
    $voucher = Voucher::factory()->fixed(10000)->create();

    expect($this->vouchers->hitungPotongan($voucher, 95000))->toBe(10000);
});

test('potongan tidak pernah melebihi harga paket', function () {
    $voucher = Voucher::factory()->fixed(500000)->create();

    expect($this->vouchers->hitungPotongan($voucher, 25000))->toBe(25000);
});

test('kode voucher tidak peka huruf besar kecil', function () {
    Voucher::factory()->create(['code' => 'HEMAT20']);

    $hasil = $this->vouchers->periksa('hemat20', $this->user, $this->plan);

    expect($hasil['valid'])->toBeTrue()
        ->and($hasil['voucher']->code)->toBe('HEMAT20');
});

test('voucher tidak valid ditolak dengan alasan yang jelas', function () {
    $kasus = [
        ['TIDAKADA', 'tidak ditemukan'],
        ['KOSONG', 'tidak ditemukan'],
    ];

    foreach ($kasus as [$kode, $frasa]) {
        $hasil = $this->vouchers->periksa($kode, $this->user, $this->plan);

        expect($hasil['valid'])->toBeFalse()
            ->and($hasil['message'])->toContain($frasa);
    }

    // Kode kosong.
    expect($this->vouchers->periksa('', $this->user, $this->plan)['valid'])->toBeFalse()
        ->and($this->vouchers->periksa(null, $this->user, $this->plan)['valid'])->toBeFalse();
});

test('voucher tidak aktif, kedaluwarsa, dan kuota habis ditolak', function () {
    $nonaktif = Voucher::factory()->inactive()->create(['code' => 'NONAKTIF']);
    $kedaluwarsa = Voucher::factory()->expired()->create(['code' => 'KEDALUWARSA']);
    $habis = Voucher::factory()->quotaExhausted()->create(['code' => 'HABIS']);

    expect($this->vouchers->periksa('NONAKTIF', $this->user, $this->plan)['valid'])->toBeFalse()
        ->and($this->vouchers->periksa('KEDALUWARSA', $this->user, $this->plan)['valid'])->toBeFalse()
        ->and($this->vouchers->periksa('HABIS', $this->user, $this->plan)['valid'])->toBeFalse();

    expect($nonaktif->is_active)->toBeFalse()
        ->and($kedaluwarsa->isWithinPeriod())->toBeFalse()
        ->and($habis->hasQuotaLeft())->toBeFalse();
});

test('voucher menolak belanja di bawah minimum', function () {
    Voucher::factory()->fixed(50000)->create([
        'code' => 'BESAR',
        'min_purchase' => 500000,
    ]);

    $hasil = $this->vouchers->periksa('BESAR', $this->user, $this->plan);

    expect($hasil['valid'])->toBeFalse()
        ->and($hasil['message'])->toContain('minimal');
});

test('voucher yang sama tidak dapat dipakai dua kali oleh pembeli yang sama', function () {
    Voucher::factory()->fixed(10000)->create(['code' => 'SEKALI']);

    $pertama = app(TransactionService::class)->checkout($this->user, $this->plan, 'qris', 'SEKALI');

    expect($pertama->discount_amount)->toBe(10000);

    $hasil = $this->vouchers->periksa('SEKALI', $this->user, $this->plan);

    expect($hasil['valid'])->toBeFalse()
        ->and($hasil['message'])->toContain('sudah pernah');
});

test('memeriksa voucher tidak mengurangi kuota', function () {
    $voucher = Voucher::factory()->fixed(10000)->create([
        'code' => 'COBA',
        'usage_limit' => 5,
    ]);

    $this->vouchers->periksa('COBA', $this->user, $this->plan);
    $this->vouchers->periksa('COBA', $this->user, $this->plan);

    expect($voucher->fresh()->used_count)->toBe(0)
        ->and($voucher->fresh()->remainingQuota())->toBe(5);
});

test('checkout menerapkan voucher dan mencatat pemakaiannya', function () {
    $voucher = Voucher::factory()->fixed(25000)->create([
        'code' => 'MURAH',
        'usage_limit' => 10,
    ]);

    $transaksi = app(TransactionService::class)->checkout($this->user, $this->plan, 'qris', 'MURAH');

    expect($transaksi->subtotal_amount)->toBe((int) $this->plan->price)
        ->and($transaksi->discount_amount)->toBe(25000)
        ->and($transaksi->amount)->toBe((int) $this->plan->price - 25000)
        ->and($transaksi->voucher_id)->toBe($voucher->id)
        ->and($transaksi->hasDiscount())->toBeTrue();

    expect($voucher->fresh()->used_count)->toBe(1)
        ->and(VoucherRedemption::query()->where('transaction_id', $transaksi->id)->exists())->toBeTrue();
});

test('checkout tanpa voucher menyimpan potongan nol', function () {
    $transaksi = app(TransactionService::class)->checkout($this->user, $this->plan, 'qris');

    expect($transaksi->discount_amount)->toBe(0)
        ->and($transaksi->voucher_id)->toBeNull()
        ->and($transaksi->amount)->toBe((int) $this->plan->price)
        ->and($transaksi->hasDiscount())->toBeFalse();
});

test('checkout dengan kode tidak valid tetap berjalan tanpa potongan', function () {
    $transaksi = app(TransactionService::class)->checkout($this->user, $this->plan, 'qris', 'NGACO');

    expect($transaksi->discount_amount)->toBe(0)
        ->and($transaksi->amount)->toBe((int) $this->plan->price);
});

test('halaman checkout dapat menerapkan dan menghapus voucher', function () {
    Voucher::factory()->percent(20, 50000)->create(['code' => 'HEMAT20']);

    Livewire::actingAs($this->user)
        ->test(Checkout::class, ['plan' => $this->plan->id])
        ->set('voucherCode', 'HEMAT20')
        ->call('terapkanVoucher')
        ->assertSet('appliedVoucher', 'HEMAT20')
        ->assertSet('voucherValid', true)
        ->assertSee('Kamu hemat')
        ->call('hapusVoucher')
        ->assertSet('appliedVoucher', null)
        ->assertSet('appliedDiscount', 0);
});

test('halaman checkout menampilkan pesan ketika kode voucher salah', function () {
    Livewire::actingAs($this->user)
        ->test(Checkout::class, ['plan' => $this->plan->id])
        ->set('voucherCode', 'SALAH')
        ->call('terapkanVoucher')
        ->assertSet('voucherValid', false)
        ->assertSet('appliedVoucher', null)
        ->assertSee('tidak ditemukan');
});

test('checkout lewat halaman memesan dengan voucher menghasilkan nominal terpotong', function () {
    Voucher::factory()->fixed(15000)->create(['code' => 'OKE']);

    Livewire::actingAs($this->user)
        ->test(Checkout::class, ['plan' => $this->plan->id])
        ->set('voucherCode', 'OKE')
        ->call('terapkanVoucher')
        ->set('paymentMethod', 'qris')
        ->call('checkout')
        ->assertHasNoErrors();

    $transaksi = Transaction::query()->where('user_id', $this->user->id)->firstOrFail();

    expect($transaksi->amount)->toBe((int) $this->plan->price - 15000)
        ->and($transaksi->discount_amount)->toBe(15000);
});

test('membatalkan pesanan mengembalikan kuota voucher', function () {
    $voucher = Voucher::factory()->fixed(10000)->create([
        'code' => 'KEMBALI',
        'usage_limit' => 5,
    ]);

    $transaksi = app(TransactionService::class)->checkout($this->user, $this->plan, 'qris', 'KEMBALI');

    expect($voucher->fresh()->used_count)->toBe(1);

    $berhasil = app(TransactionService::class)->batalkan($transaksi);

    expect($berhasil)->toBeTrue()
        ->and($transaksi->fresh()->status)->toBe(TransactionStatus::Cancelled)
        ->and($voucher->fresh()->used_count)->toBe(0)
        ->and(VoucherRedemption::query()->where('transaction_id', $transaksi->id)->exists())->toBeFalse();
});

test('pesanan yang sudah lunas tidak dapat dibatalkan', function () {
    $transaksi = Transaction::factory()->paid()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
    ]);

    expect(app(TransactionService::class)->batalkan($transaksi))->toBeFalse()
        ->and($transaksi->fresh()->status)->toBe(TransactionStatus::Paid);
});

test('pembeli dapat membatalkan pesanannya dari halaman pesanan', function () {
    $transaksi = Transaction::factory()->pending()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
    ]);

    Livewire::actingAs($this->user)
        ->test('profile.orders')
        ->call('batalkan', $transaksi->id)
        ->assertHasNoErrors();

    expect($transaksi->fresh()->status)->toBe(TransactionStatus::Cancelled);
});

test('pembeli tidak dapat membatalkan pesanan milik orang lain', function () {
    $lain = User::factory()->create();

    $transaksi = Transaction::factory()->pending()->create([
        'user_id' => $lain->id,
        'plan_id' => $this->plan->id,
    ]);

    Livewire::actingAs($this->user)
        ->test('profile.orders')
        ->call('batalkan', $transaksi->id)
        ->assertForbidden();

    expect($transaksi->fresh()->status)->toBe(TransactionStatus::Pending);
});

test('struk menampilkan rincian potongan voucher', function () {
    $voucher = Voucher::factory()->fixed(10000)->create(['code' => 'STRUK10']);

    $transaksi = app(TransactionService::class)->checkout($this->user, $this->plan, 'qris', 'STRUK10');
    $transaksi->forceFill(['status' => TransactionStatus::Paid, 'paid_at' => now()])->save();

    $this->actingAs($this->user)
        ->get(route('transaction.receipt', $transaksi->order_code))
        ->assertOk()
        ->assertSee('STRUK10')
        ->assertSee('Subtotal')
        ->assertSee('10.000');

    expect($voucher->fresh()->used_count)->toBe(1);
});
