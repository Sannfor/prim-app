<?php

use App\Models\Transaction;
use App\Models\User;
use App\Models\Voucher;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\VoucherSeeder;
use Livewire\Livewire;
use Modules\Admin\Livewire\VoucherManager;

/*
|--------------------------------------------------------------------------
| Pengelolaan voucher oleh administrator
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    $this->seed(CatalogSeeder::class);

    $this->admin = User::factory()->admin()->create();
    $this->pelanggan = User::factory()->create();
});

test('halaman kelola voucher memerlukan peran administrator', function () {
    $this->get(route('admin.vouchers.index'))->assertRedirect(route('login'));

    $this->actingAs($this->pelanggan)
        ->get(route('admin.vouchers.index'))
        ->assertForbidden();

    $this->actingAs($this->admin)
        ->get(route('admin.vouchers.index'))
        ->assertOk()
        ->assertSee('Kelola Voucher');
});

test('administrator dapat menambah voucher baru', function () {
    Livewire::actingAs($this->admin)
        ->test(VoucherManager::class)
        ->call('buat')
        ->assertSet('showForm', true)
        ->set('code', 'baru20')
        ->set('description', 'Potongan uji coba')
        ->set('type', Voucher::TYPE_PERCENT)
        ->set('value', 20)
        ->set('maxDiscount', 25000)
        ->set('minPurchase', 50000)
        ->set('usageLimit', 10)
        ->set('usageLimitPerUser', 2)
        ->call('simpan')
        ->assertHasNoErrors()
        ->assertSet('showForm', false);

    $voucher = Voucher::query()->where('code', 'BARU20')->first();

    expect($voucher)->not->toBeNull()
        ->and($voucher->type)->toBe(Voucher::TYPE_PERCENT)
        ->and($voucher->value)->toBe(20)
        ->and($voucher->max_discount)->toBe(25000)
        ->and($voucher->min_purchase)->toBe(50000)
        ->and($voucher->used_count)->toBe(0)
        ->and($voucher->is_active)->toBeTrue();
});

test('kode voucher duplikat ditolak', function () {
    Voucher::factory()->create(['code' => 'KEMBAR']);

    Livewire::actingAs($this->admin)
        ->test(VoucherManager::class)
        ->call('buat')
        ->set('code', 'KEMBAR')
        ->set('value', 10)
        ->call('simpan')
        ->assertHasErrors(['code']);

    expect(Voucher::query()->where('code', 'KEMBAR')->count())->toBe(1);
});

test('kode voucher dengan karakter tidak sah ditolak', function () {
    Livewire::actingAs($this->admin)
        ->test(VoucherManager::class)
        ->call('buat')
        ->set('code', 'HEMAT 20%')
        ->set('value', 10)
        ->call('simpan')
        ->assertHasErrors(['code']);
});

test('potongan persen di luar rentang ditolak', function () {
    Livewire::actingAs($this->admin)
        ->test(VoucherManager::class)
        ->call('buat')
        ->set('code', 'TERLALU')
        ->set('type', Voucher::TYPE_PERCENT)
        ->set('value', 150)
        ->call('simpan')
        ->assertHasErrors(['value']);
});

test('administrator dapat mengubah voucher yang ada', function () {
    $voucher = Voucher::factory()->create([
        'code' => 'UBAH',
        'type' => Voucher::TYPE_FIXED,
        'value' => 10000,
    ]);

    Livewire::actingAs($this->admin)
        ->test(VoucherManager::class)
        ->call('ubah', $voucher->id)
        ->assertSet('showForm', true)
        ->assertSet('code', 'UBAH')
        ->set('value', 30000)
        ->call('simpan')
        ->assertHasNoErrors();

    expect($voucher->fresh()->value)->toBe(30000);
});

test('administrator dapat mengaktifkan dan menonaktifkan voucher', function () {
    $voucher = Voucher::factory()->create(['code' => 'TOMBOL', 'is_active' => true]);

    Livewire::actingAs($this->admin)
        ->test(VoucherManager::class)
        ->call('ubahStatus', $voucher->id);

    expect($voucher->fresh()->is_active)->toBeFalse();

    Livewire::actingAs($this->admin)
        ->test(VoucherManager::class)
        ->call('ubahStatus', $voucher->id);

    expect($voucher->fresh()->is_active)->toBeTrue();
});

test('voucher yang belum dipakai dapat dihapus', function () {
    $voucher = Voucher::factory()->create(['code' => 'HAPUS', 'used_count' => 0]);

    Livewire::actingAs($this->admin)
        ->test(VoucherManager::class)
        ->call('hapus', $voucher->id);

    expect(Voucher::query()->where('code', 'HAPUS')->exists())->toBeFalse();
});

test('voucher yang sudah dipakai dinonaktifkan alih-alih dihapus', function () {
    $voucher = Voucher::factory()->create(['code' => 'TERPAKAI', 'used_count' => 3]);

    Livewire::actingAs($this->admin)
        ->test(VoucherManager::class)
        ->call('hapus', $voucher->id);

    // Riwayat pemakaian pada transaksi lama harus tetap utuh.
    expect(Voucher::query()->where('code', 'TERPAKAI')->exists())->toBeTrue()
        ->and($voucher->fresh()->is_active)->toBeFalse();
});

test('halaman kelola voucher dapat disaring dan dicari', function () {
    Voucher::factory()->create(['code' => 'AKTIFSATU', 'is_active' => true]);
    Voucher::factory()->create(['code' => 'MATISATU', 'is_active' => false]);

    Livewire::actingAs($this->admin)
        ->test(VoucherManager::class)
        ->set('statusFilter', 'aktif')
        ->assertSee('AKTIFSATU')
        ->assertDontSee('MATISATU')
        ->set('statusFilter', 'nonaktif')
        ->assertSee('MATISATU')
        ->assertDontSee('AKTIFSATU')
        ->set('statusFilter', 'semua')
        ->set('search', 'AKTIFSATU')
        ->assertSee('AKTIFSATU')
        ->assertDontSee('MATISATU');
});

test('voucher contoh dari seeder dapat langsung dipakai', function () {
    $this->seed(VoucherSeeder::class);

    $paket = App\Models\Plan::query()->where('is_active', true)->firstOrFail();
    $layanan = app(App\Services\VoucherService::class);

    expect(Voucher::query()->count())->toBeGreaterThanOrEqual(4);

    $hasil = $layanan->periksa('HEMAT20', $this->pelanggan, $paket);

    expect($hasil['valid'])->toBeTrue()
        ->and($hasil['discount'])->toBeGreaterThan(0);
});

/*
|--------------------------------------------------------------------------
| Batas waktu pembayaran
|--------------------------------------------------------------------------
*/

test('pesanan yang melewati batas waktu tidak dapat dibayar', function () {
    $paket = App\Models\Plan::query()->where('is_active', true)->firstOrFail();

    $transaksi = Transaction::factory()->pending()->create([
        'user_id' => $this->pelanggan->id,
        'plan_id' => $paket->id,
        'expires_at' => now()->subHour(),
    ]);

    expect($transaksi->hasExpired())->toBeTrue()
        ->and($transaksi->isPayable())->toBeFalse()
        ->and($transaksi->remainingPaymentTime())->toBeNull();
});

test('sisa waktu pembayaran terhitung untuk pesanan yang masih berlaku', function () {
    $paket = App\Models\Plan::query()->where('is_active', true)->firstOrFail();

    $transaksi = Transaction::factory()->pending()->create([
        'user_id' => $this->pelanggan->id,
        'plan_id' => $paket->id,
        'expires_at' => now()->addHours(6),
    ]);

    expect($transaksi->hasExpired())->toBeFalse()
        ->and($transaksi->isPayable())->toBeTrue()
        ->and($transaksi->remainingPaymentHours())->toBe(5)
        ->and($transaksi->remainingPaymentTime())->toBeString();
});

test('perintah penandaan kedaluwarsa mengubah status pesanan yang lewat waktu', function () {
    $paket = App\Models\Plan::query()->where('is_active', true)->firstOrFail();

    $lewat = Transaction::factory()->pending()->create([
        'user_id' => $this->pelanggan->id,
        'plan_id' => $paket->id,
        'expires_at' => now()->subHour(),
    ]);

    $masihBerlaku = Transaction::factory()->pending()->create([
        'user_id' => $this->pelanggan->id,
        'plan_id' => $paket->id,
        'expires_at' => now()->addHours(6),
    ]);

    $this->artisan('prim:expire-transactions')->assertSuccessful();

    expect($lewat->fresh()->status)->toBe(App\Enums\TransactionStatus::Expired)
        ->and($masihBerlaku->fresh()->status)->toBe(App\Enums\TransactionStatus::Pending);
});

test('pesanan yang kedaluwarsa tidak dapat dibatalkan lagi', function () {
    $paket = App\Models\Plan::query()->where('is_active', true)->firstOrFail();

    $transaksi = Transaction::factory()->pending()->create([
        'user_id' => $this->pelanggan->id,
        'plan_id' => $paket->id,
        'expires_at' => now()->subHour(),
    ]);

    expect(app(App\Services\TransactionService::class)->batalkan($transaksi))->toBeFalse();
});
