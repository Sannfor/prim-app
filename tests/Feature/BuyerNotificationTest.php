<?php

use App\Enums\BuyerNotificationType;
use App\Enums\SubscriptionStatus;
use App\Enums\TransactionStatus;
use App\Models\BuyerNotification;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Services\BuyerNotificationService;
use App\Services\TransactionService;
use Database\Seeders\CatalogSeeder;
use Livewire\Livewire;

/*
|--------------------------------------------------------------------------
| Notifikasi dalam aplikasi untuk pembeli
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    $this->seed(CatalogSeeder::class);

    $this->pelanggan = User::factory()->create();
    $this->paket = Plan::query()->where('is_active', true)->firstOrFail();
});

test('pembayaran berhasil menerbitkan notifikasi kepada pembeli', function () {
    $transaksi = Transaction::factory()->pending()->create([
        'user_id' => $this->pelanggan->id,
        'plan_id' => $this->paket->id,
        'payment_method' => 'qris',
    ]);

    app(TransactionService::class)->pay($transaksi, $this->pelanggan, 'qris', true);

    $notifikasi = BuyerNotification::query()->where('user_id', $this->pelanggan->id)->get();

    expect($notifikasi)->toHaveCount(1)
        ->and($notifikasi->first()->type)->toBe(BuyerNotificationType::OrderPaid)
        ->and($notifikasi->first()->isUnread())->toBeTrue()
        ->and($notifikasi->first()->transaction_id)->toBe($transaksi->id)
        ->and($notifikasi->first()->url)->toContain($transaksi->order_code);
});

test('pembayaran gagal tidak menerbitkan notifikasi', function () {
    $transaksi = Transaction::factory()->pending()->create([
        'user_id' => $this->pelanggan->id,
        'plan_id' => $this->paket->id,
        'payment_method' => 'qris',
    ]);

    app(TransactionService::class)->pay($transaksi, $this->pelanggan, 'qris', false);

    expect(BuyerNotification::query()->count())->toBe(0);
});

test('pembatalan pesanan menerbitkan notifikasi', function () {
    $transaksi = Transaction::factory()->pending()->create([
        'user_id' => $this->pelanggan->id,
        'plan_id' => $this->paket->id,
    ]);

    app(TransactionService::class)->batalkan($transaksi);

    $notifikasi = BuyerNotification::query()->first();

    expect($notifikasi)->not->toBeNull()
        ->and($notifikasi->type)->toBe(BuyerNotificationType::OrderCancelled);
});

test('pesanan kedaluwarsa menerbitkan notifikasi', function () {
    Transaction::factory()->pending()->create([
        'user_id' => $this->pelanggan->id,
        'plan_id' => $this->paket->id,
        'expires_at' => now()->subHour(),
    ]);

    app(TransactionService::class)->expireOverdueTransactions();

    $notifikasi = BuyerNotification::query()->first();

    expect($notifikasi)->not->toBeNull()
        ->and($notifikasi->type)->toBe(BuyerNotificationType::OrderExpired);
});

test('kode login yang dibagikan pengelola memberi tahu pembeli', function () {
    $transaksi = Transaction::factory()->paid()->create([
        'user_id' => $this->pelanggan->id,
        'plan_id' => $this->paket->id,
    ]);

    app(BuyerNotificationService::class)->kodeLoginSiap($transaksi, 1);

    $notifikasi = BuyerNotification::query()->first();

    expect($notifikasi->type)->toBe(BuyerNotificationType::LoginCodeReady)
        ->and($notifikasi->body)->toContain('kode login');
});

test('notifikasi dapat ditandai dibaca satu per satu dan sekaligus', function () {
    $layanan = app(BuyerNotificationService::class);

    $a = $layanan->kirim($this->pelanggan, BuyerNotificationType::OrderPaid, body: 'Pertama');
    $b = $layanan->kirim($this->pelanggan, BuyerNotificationType::OrderExpired, body: 'Kedua');

    expect(BuyerNotification::query()->unread()->count())->toBe(2);

    $a->markAsRead();

    expect($a->fresh()->isUnread())->toBeFalse()
        ->and($b->fresh()->isUnread())->toBeTrue()
        ->and(BuyerNotification::query()->unread()->count())->toBe(1);

    $jumlah = $layanan->tandaiSemuaDibaca($this->pelanggan);

    expect($jumlah)->toBe(1)
        ->and(BuyerNotification::query()->unread()->count())->toBe(0);
});

test('notifikasi lama yang sudah dibaca dibersihkan', function () {
    $layanan = app(BuyerNotificationService::class);

    $lama = $layanan->kirim($this->pelanggan, BuyerNotificationType::OrderPaid, body: 'Lama');
    $lama->forceFill(['read_at' => now()->subDays(60)])->save();

    $baru = $layanan->kirim($this->pelanggan, BuyerNotificationType::OrderPaid, body: 'Baru');

    $terhapus = $layanan->bersihkan(hari: 30);

    expect($terhapus)->toBe(1)
        ->and(BuyerNotification::query()->whereKey($lama->id)->exists())->toBeFalse()
        ->and(BuyerNotification::query()->whereKey($baru->id)->exists())->toBeTrue();
});

test('lonceng notifikasi hanya menampilkan notifikasi milik pengguna yang masuk', function () {
    $lain = User::factory()->create();

    BuyerNotification::create([
        'user_id' => $this->pelanggan->id,
        'type' => BuyerNotificationType::OrderPaid,
        'title' => 'Milik saya',
    ]);

    BuyerNotification::create([
        'user_id' => $lain->id,
        'type' => BuyerNotificationType::OrderPaid,
        'title' => 'Milik orang lain',
    ]);

    Livewire::actingAs($this->pelanggan)
        ->test(App\Livewire\BuyerNotificationBell::class)
        ->assertSee('Milik saya')
        ->assertDontSee('Milik orang lain')
        ->assertViewHas('unreadCount', 1);
});

test('membuka notifikasi menandainya dibaca dan mengembalikan tautan', function () {
    $notifikasi = BuyerNotification::create([
        'user_id' => $this->pelanggan->id,
        'type' => BuyerNotificationType::OrderPaid,
        'title' => 'Pembayaran diterima',
        'url' => route('profile.orders'),
    ]);

    Livewire::actingAs($this->pelanggan)
        ->test(App\Livewire\BuyerNotificationBell::class)
        ->call('buka', $notifikasi->id);

    expect($notifikasi->fresh()->isUnread())->toBeFalse();
});

test('pembeli tidak dapat membuka notifikasi milik orang lain', function () {
    $lain = User::factory()->create();

    $notifikasi = BuyerNotification::create([
        'user_id' => $lain->id,
        'type' => BuyerNotificationType::OrderPaid,
        'title' => 'Milik orang lain',
    ]);

    Livewire::actingAs($this->pelanggan)
        ->test(App\Livewire\BuyerNotificationBell::class)
        ->call('buka', $notifikasi->id);

    expect($notifikasi->fresh()->isUnread())->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| Pengembalian dana oleh pengelola
|--------------------------------------------------------------------------
*/

test('pengembalian dana penuh menandai pesanan dan menghentikan langganan', function () {
    $transaksi = Transaction::factory()->paid()->create([
        'user_id' => $this->pelanggan->id,
        'plan_id' => $this->paket->id,
        'amount' => 95000,
    ]);

    $langganan = Subscription::factory()->create([
        'user_id' => $this->pelanggan->id,
        'plan_id' => $this->paket->id,
        'transaction_id' => $transaksi->id,
        'status' => SubscriptionStatus::Active,
        'started_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);

    $hasil = app(App\Services\RefundService::class)->kembalikan($transaksi, 95000, 'Layanan tidak dapat diproses');

    expect($hasil['berhasil'])->toBeTrue()
        ->and($hasil['nominal'])->toBe(95000);

    $transaksi->refresh();

    expect($transaksi->isRefunded())->toBeTrue()
        ->and($transaksi->refund_amount)->toBe(95000)
        ->and($transaksi->refund_reason)->toBe('Layanan tidak dapat diproses')
        ->and($transaksi->status)->toBe(TransactionStatus::Paid)
        ->and($langganan->fresh()->status)->toBe(SubscriptionStatus::Cancelled);
});

test('pengembalian dana sebagian diperbolehkan', function () {
    $transaksi = Transaction::factory()->paid()->create([
        'user_id' => $this->pelanggan->id,
        'plan_id' => $this->paket->id,
        'amount' => 100000,
    ]);

    $hasil = app(App\Services\RefundService::class)->kembalikan($transaksi, 40000, 'Kompensasi sebagian');

    expect($hasil['berhasil'])->toBeTrue()
        ->and($transaksi->fresh()->refund_amount)->toBe(40000);
});

test('nominal pengembalian dibatasi total pesanan', function () {
    $transaksi = Transaction::factory()->paid()->create([
        'user_id' => $this->pelanggan->id,
        'plan_id' => $this->paket->id,
        'amount' => 50000,
    ]);

    $hasil = app(App\Services\RefundService::class)->kembalikan($transaksi, 999999, 'Terlalu besar');

    expect($hasil['berhasil'])->toBeTrue()
        ->and($transaksi->fresh()->refund_amount)->toBe(50000);
});

test('pesanan yang belum lunas tidak dapat dikembalikan dananya', function () {
    $transaksi = Transaction::factory()->pending()->create([
        'user_id' => $this->pelanggan->id,
        'plan_id' => $this->paket->id,
    ]);

    $hasil = app(App\Services\RefundService::class)->kembalikan($transaksi, null, 'Belum bayar');

    expect($hasil['berhasil'])->toBeFalse()
        ->and($transaksi->fresh()->isRefunded())->toBeFalse();
});

test('pesanan tidak dapat dikembalikan dananya dua kali', function () {
    $transaksi = Transaction::factory()->paid()->create([
        'user_id' => $this->pelanggan->id,
        'plan_id' => $this->paket->id,
        'refunded_at' => now()->subDay(),
        'refund_amount' => 10000,
    ]);

    $hasil = app(App\Services\RefundService::class)->kembalikan($transaksi, 10000, 'Ulangi');

    expect($hasil['berhasil'])->toBeFalse()
        ->and($hasil['pesan'])->toContain('sudah pernah');
});

test('pengembalian dana memberi tahu pembeli', function () {
    $transaksi = Transaction::factory()->paid()->create([
        'user_id' => $this->pelanggan->id,
        'plan_id' => $this->paket->id,
        'amount' => 75000,
    ]);

    app(App\Services\RefundService::class)->kembalikan($transaksi, 75000, 'Kesalahan sistem');

    $notifikasi = BuyerNotification::query()->first();

    expect($notifikasi)->not->toBeNull()
        ->and($notifikasi->type)->toBe(BuyerNotificationType::OrderRefunded)
        ->and($notifikasi->body)->toContain('75.000')
        ->and($notifikasi->body)->toContain('Kesalahan sistem');
});

test('administrator dapat memproses pengembalian dana dari panel', function () {
    $admin = User::factory()->admin()->create();

    $transaksi = Transaction::factory()->paid()->create([
        'user_id' => $this->pelanggan->id,
        'plan_id' => $this->paket->id,
        'amount' => 60000,
    ]);

    Livewire::actingAs($admin)
        ->test(Modules\Admin\Livewire\TransactionManager::class)
        ->call('bukaFormRefund', $transaksi->id)
        ->assertSet('refundTransaksiId', $transaksi->id)
        ->set('refundNominal', '60000')
        ->set('refundAlasan', 'Layanan tidak tersedia')
        ->call('prosesRefund')
        ->assertHasNoErrors()
        ->assertSet('refundTransaksiId', null);

    $transaksi->refresh();

    expect($transaksi->isRefunded())->toBeTrue()
        ->and($transaksi->refund_amount)->toBe(60000)
        ->and($transaksi->refunded_by)->toBe($admin->id);
});

test('pengembalian dana melebihi total pesanan ditolak di panel', function () {
    $admin = User::factory()->admin()->create();

    $transaksi = Transaction::factory()->paid()->create([
        'user_id' => $this->pelanggan->id,
        'plan_id' => $this->paket->id,
        'amount' => 30000,
    ]);

    Livewire::actingAs($admin)
        ->test(Modules\Admin\Livewire\TransactionManager::class)
        ->call('bukaFormRefund', $transaksi->id)
        ->set('refundNominal', '999999')
        ->set('refundAlasan', 'Terlalu besar')
        ->call('prosesRefund')
        ->assertHasErrors(['refundNominal']);

    expect($transaksi->fresh()->isRefunded())->toBeFalse();
});

test('administrator dapat membagikan kode login dan pembeli diberi tahu', function () {
    $admin = User::factory()->admin()->create();

    $transaksi = Transaction::factory()->paid()->create([
        'user_id' => $this->pelanggan->id,
        'plan_id' => $this->paket->id,
    ]);

    Livewire::actingAs($admin)
        ->test(Modules\Admin\Livewire\TransactionManager::class)
        ->call('bukaFormKredensial', $transaksi->id)
        ->assertSet('kredensialTransaksiId', $transaksi->id)
        ->set('kredensialLogin', 'prim.netflix01@mail.com')
        ->set('kredensialSandi', 'Rahasia123')
        ->set('kredensialProfil', 'Profil 1')
        ->set('kredensialCatatan', 'Jangan ubah kata sandi')
        ->call('simpanKredensial')
        ->assertHasNoErrors()
        ->assertSet('kredensialTransaksiId', null);

    $kredensial = $transaksi->serviceCredentials()->first();

    expect($kredensial)->not->toBeNull()
        ->and($kredensial->login_code)->toBe('prim.netflix01@mail.com')
        ->and($kredensial->password_code)->toBe('Rahasia123')
        ->and($kredensial->delivered_at)->not->toBeNull();

    expect(BuyerNotification::query()
        ->where('user_id', $this->pelanggan->id)
        ->where('type', BuyerNotificationType::LoginCodeReady->value)
        ->exists())->toBeTrue();
});

test('kode login tidak dapat dibagikan untuk pesanan yang belum lunas', function () {
    $admin = User::factory()->admin()->create();

    $transaksi = Transaction::factory()->pending()->create([
        'user_id' => $this->pelanggan->id,
        'plan_id' => $this->paket->id,
    ]);

    Livewire::actingAs($admin)
        ->test(Modules\Admin\Livewire\TransactionManager::class)
        ->call('bukaFormKredensial', $transaksi->id)
        ->assertSet('kredensialTransaksiId', null);

    expect($transaksi->serviceCredentials()->count())->toBe(0);
});
