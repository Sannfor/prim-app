<?php

use App\Enums\SubscriptionStatus;
use App\Enums\TransactionStatus;
use App\Models\Plan;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Payment\PaymentGateway;
use App\Services\Payment\PaymentResult;
use Database\Seeders\CatalogSeeder;
use Livewire\Livewire;
use Modules\Transaction\Livewire\Checkout;
use Modules\Transaction\Livewire\TransactionDetail;

/*
|--------------------------------------------------------------------------
| Alur pembayaran mode simulasi, dari checkout sampai struk
|--------------------------------------------------------------------------
|
| Pengujian ini memastikan seluruh alur berjalan tanpa akun Midtrans, sehingga
| aplikasi tetap dapat didemonstrasikan ketika MIDTRANS_SERVER_KEY belum diisi.
|
*/

beforeEach(function () {
    $this->seed(CatalogSeeder::class);

    $this->user = User::factory()->create();
    $this->plan = Plan::query()->where('is_active', true)->firstOrFail();
});

test('seluruh alur pembayaran simulasi berjalan dari checkout sampai struk', function () {
    // 1. Pengguna memesan paket.
    Livewire::actingAs($this->user)
        ->test(Checkout::class, ['plan' => $this->plan->id])
        ->set('paymentMethod', 'qris')
        ->call('checkout')
        ->assertHasNoErrors();

    $transaksi = Transaction::query()->where('user_id', $this->user->id)->firstOrFail();

    expect($transaksi->status)->toBe(TransactionStatus::Pending)
        ->and($transaksi->paid_at)->toBeNull()
        ->and($transaksi->isPayable())->toBeTrue();

    // 2. Pengguna menyelesaikan pembayaran pada mode simulasi.
    Livewire::actingAs($this->user)
        ->test(TransactionDetail::class, ['order' => $transaksi->order_code])
        ->call('pay', true)
        ->assertRedirect(route('transaction.receipt', $transaksi->order_code));

    $transaksi->refresh();

    expect($transaksi->status)->toBe(TransactionStatus::Paid)
        ->and($transaksi->paid_at)->not->toBeNull()
        ->and($transaksi->payment_reference)->not->toBeNull()
        ->and($transaksi->payment_url)->toBeNull();

    // 3. Langganan aktif otomatis dengan masa aktif sesuai durasi paket.
    $langganan = Subscription::query()->where('transaction_id', $transaksi->id)->firstOrFail();

    expect($langganan->status)->toBe(SubscriptionStatus::Active)
        ->and($langganan->isActive())->toBeTrue()
        ->and((int) $langganan->started_at->diffInDays($langganan->ends_at))
        ->toBe($this->plan->duration_days);

    // 4. Struk dapat dibuka pemiliknya dan memuat data penting.
    $this->actingAs($this->user)
        ->get(route('transaction.receipt', $transaksi->order_code))
        ->assertOk()
        ->assertSee('STRUK DIGITAL')
        ->assertSee($transaksi->order_code)
        ->assertSee($this->plan->service->name)
        ->assertSee($this->user->name);
});

test('pembayaran simulasi yang gagal tidak mengaktifkan langganan', function () {
    Livewire::actingAs($this->user)
        ->test(Checkout::class, ['plan' => $this->plan->id])
        ->set('paymentMethod', 'qris')
        ->call('checkout');

    $transaksi = Transaction::query()->where('user_id', $this->user->id)->firstOrFail();

    Livewire::actingAs($this->user)
        ->test(TransactionDetail::class, ['order' => $transaksi->order_code])
        ->call('pay', false);

    $transaksi->refresh();

    expect($transaksi->status)->toBe(TransactionStatus::Failed)
        ->and($transaksi->paid_at)->toBeNull()
        ->and(Subscription::query()->where('transaction_id', $transaksi->id)->exists())->toBeFalse();
});

test('struk hanya dapat dibuka oleh pemilik pesanan atau administrator', function () {
    $lain = User::factory()->create();

    $transaksi = Transaction::factory()->paid()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
    ]);

    // Pemilik pesanan.
    $this->actingAs($this->user)
        ->get(route('transaction.receipt', $transaksi->order_code))
        ->assertOk();

    // Pengguna lain ditolak.
    $this->actingAs($lain)
        ->get(route('transaction.receipt', $transaksi->order_code))
        ->assertForbidden();
});

test('gateway bawaan bekerja dalam mode simulasi tanpa kunci Midtrans', function () {
    $gateway = app(PaymentGateway::class);

    expect($gateway->requiresRedirect())->toBeFalse()
        ->and($gateway->methods())->not->toBeEmpty()
        ->and($gateway->label('qris'))->toBeString();
});

test('transaksi yang sudah lunas tidak dapat dibayar dua kali', function () {
    $transaksi = Transaction::factory()->paid()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
    ]);

    expect($transaksi->fresh()->isPayable())->toBeFalse();

    Livewire::actingAs($this->user)
        ->test(TransactionDetail::class, ['order' => $transaksi->order_code])
        ->call('pay', true);

    // Jumlah langganan tidak bertambah karena pembayaran ditolak lebih awal.
    expect(Subscription::query()->where('transaction_id', $transaksi->id)->count())->toBeLessThanOrEqual(1)
        ->and($transaksi->fresh()->status)->toBe(TransactionStatus::Paid);
});

test('notifikasi Midtrans ditolak bila signature tidak cocok', function () {
    config()->set('services.midtrans.server_key', 'SB-Mid-server-uji');

    $transaksi = Transaction::factory()->pending()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'payment_reference' => 'MT-PRIM-UJI',
    ]);

    $this->postJson(route('midtrans.notification'), [
        'order_id' => 'MT-PRIM-UJI',
        'status_code' => '200',
        'gross_amount' => (string) $transaksi->amount,
        'transaction_status' => 'settlement',
        'fraud_status' => 'accept',
        'signature_key' => 'signature-palsu',
    ])->assertForbidden();

    expect($transaksi->fresh()->status)->toBe(TransactionStatus::Pending);
});

test('notifikasi Midtrans dengan signature benar menandai pesanan lunas', function () {
    $serverKey = 'SB-Mid-server-uji';
    config()->set('services.midtrans.server_key', $serverKey);

    $transaksi = Transaction::factory()->pending()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'payment_reference' => 'MT-PRIM-UJI2',
    ]);

    $signature = hash('sha512', 'MT-PRIM-UJI2'.'200'.(string) $transaksi->amount.$serverKey);

    $this->postJson(route('midtrans.notification'), [
        'order_id' => 'MT-PRIM-UJI2',
        'status_code' => '200',
        'gross_amount' => (string) $transaksi->amount,
        'transaction_status' => 'settlement',
        'fraud_status' => 'accept',
        'signature_key' => $signature,
    ])->assertOk();

    $transaksi->refresh();

    expect($transaksi->status)->toBe(TransactionStatus::Paid)
        ->and($transaksi->paid_at)->not->toBeNull()
        ->and(Subscription::query()->where('transaction_id', $transaksi->id)->exists())->toBeTrue();
});

test('gateway meminta pengalihan halaman hanya ketika kunci Midtrans diisi', function () {
    // Mode simulasi: tanpa kunci.
    $simulasi = new App\Services\Payment\MidtransGateway(serverKey: null, mode: 'simulation');
    expect($simulasi->requiresRedirect())->toBeFalse()
        ->and($simulasi->name())->toBe('Simulasi PRIM Pay');

    // Mode Snap: kunci terisi.
    $snap = new App\Services\Payment\MidtransGateway(serverKey: 'SB-Mid-server-uji', mode: 'snap');
    expect($snap->requiresRedirect())->toBeTrue()
        ->and($snap->name())->toBe('Midtrans');

    // Mode Snap tanpa kunci: tetap jatuh ke simulasi, bukan gagal.
    $tanpaKunci = new App\Services\Payment\MidtransGateway(serverKey: null, mode: 'snap');
    expect($tanpaKunci->requiresRedirect())->toBeFalse();

    // Hasil simulasi selalu berhasil atau gagal secara langsung.
    $hasil = $simulasi->charge(
        Transaction::factory()->pending()->create(['user_id' => $this->user->id, 'plan_id' => $this->plan->id]),
        $this->user,
        'qris',
        true,
    );

    expect($hasil)->toBeInstanceOf(PaymentResult::class)
        ->and($hasil->successful)->toBeTrue()
        ->and($hasil->requiresAction)->toBeFalse();
});
