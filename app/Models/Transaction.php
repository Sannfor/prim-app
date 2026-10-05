<?php

namespace App\Models;

use App\Casts\TransactionStatusCast;
use App\Enums\TransactionStatus;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

/**
 * Transaksi pembelian paket langganan oleh pengguna.
 *
 * Pada versi ini pembayaran masih berupa simulasi internal sehingga status
 * transaksi diubah oleh sistem, bukan oleh callback payment gateway.
 */
class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    /**
     * Masa berlaku pesanan sebelum dianggap kedaluwarsa.
     */
    public const PAYMENT_WINDOW_HOURS = 24;

    protected $fillable = [
        'order_code',
        'user_id',
        'plan_id',
        'voucher_id',
        'subtotal_amount',
        'discount_amount',
        'amount',
        'status',
        'payment_method',
        'payment_method_label',
        'payment_reference',
        'payment_token',
        'payment_url',
        'paid_at',
        'paid_confirmed_at',
        'expires_at',
        'notes',
        'refunded_at',
        'refund_amount',
        'refund_reason',
        'refunded_by',
    ];

    protected function casts(): array
    {
        return [
            'subtotal_amount' => 'integer',
            'discount_amount' => 'integer',
            'amount' => 'integer',
            'status' => TransactionStatusCast::class,
            'paid_at' => 'datetime',
            'paid_confirmed_at' => 'datetime',
            'expires_at' => 'datetime',
            'refunded_at' => 'datetime',
            'refund_amount' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Transaction $transaction) {
            if (blank($transaction->order_code)) {
                $transaction->order_code = self::generateOrderCode();
            }

            if (blank($transaction->expires_at)) {
                $transaction->expires_at = now()->addHours(self::PAYMENT_WINDOW_HOURS);
            }
        });
    }

    /**
     * Buat kode pesanan unik berformat PRIM-YYYYMMDD-XXXXXX.
     */
    public static function generateOrderCode(): string
    {
        do {
            $code = 'PRIM-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (self::where('order_code', $code)->exists());

        return $code;
    }

    /**
     * Kunci rute memakai kode pesanan agar tidak mudah ditebak.
     */
    public function getRouteKeyName(): string
    {
        return 'order_code';
    }

    /**
     * Pengguna pembeli.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Paket langganan yang dibeli.
     *
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Voucher yang dipakai pada pesanan ini, bila ada.
     *
     * @return BelongsTo<Voucher, $this>
     */
    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    /**
     * Harga paket sebelum potongan voucher.
     *
     * Transaksi lama yang belum memiliki subtotal_amount memakai nilai amount,
     * sehingga rincian tetap benar tanpa perlu mengisi ulang data lama.
     */
    public function subtotal(): int
    {
        return $this->subtotal_amount > 0 ? $this->subtotal_amount : (int) $this->amount;
    }

    /**
     * Potongan dalam rupiah.
     */
    public function discount(): int
    {
        return (int) $this->discount_amount;
    }

    /**
     * Apakah pesanan ini memakai voucher.
     */
    public function hasDiscount(): bool
    {
        return $this->discount() > 0;
    }

    /**
     * Apakah dana pesanan ini sudah dikembalikan.
     */
    public function isRefunded(): bool
    {
        return $this->refunded_at !== null;
    }

    /**
     * Nominal yang dikembalikan dalam format rupiah.
     */
    public function formattedRefund(): string
    {
        return 'Rp'.Number::format((int) $this->refund_amount, locale: 'id');
    }

    /**
     * Langganan yang lahir dari transaksi ini (jika sudah dibayar).
     *
     * @return HasOne<Subscription, $this>
     */
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    /**
     * Kredensial akun layanan yang dibagikan untuk pesanan ini.
     *
     * @return HasMany<ServiceCredential, $this>
     */
    public function serviceCredentials(): HasMany
    {
        return $this->hasMany(ServiceCredential::class);
    }

    /**
     * Nama kanal pembayaran yang ditampilkan pada tabel pesanan.
     *
     * Desain menampilkan label seperti "E-Wallet (Dana)" atau "Bank Transfer".
     */
    public function paymentLabel(): string
    {
        return $this->payment_method_label ?: ($this->payment_method ?: 'Belum dipilih');
    }

    /**
     * Hanya transaksi dengan status tertentu.
     *
     * @param  Builder<Transaction>  $query
     */
    public function scopeStatus(Builder $query, TransactionStatus $status): void
    {
        $query->where('status', $status->value);
    }

    /**
     * Transaksi yang masih menunggu pembayaran dan belum lewat batas waktu.
     *
     * @param  Builder<Transaction>  $query
     */
    public function scopeAwaitingPayment(Builder $query): void
    {
        $query->where('status', TransactionStatus::Pending->value)
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    /**
     * Nominal dalam format rupiah.
     */
    public function formattedAmount(): string
    {
        return 'Rp'.Number::format($this->amount, locale: 'id');
    }

    /**
     * Apakah pesanan sudah melewati batas waktu pembayaran.
     */
    public function hasExpired(): bool
    {
        return $this->status === TransactionStatus::Pending
            && $this->expires_at !== null
            && $this->expires_at->isPast();
    }

    /**
     * Apakah transaksi masih dapat dibayar.
     */
    public function isPayable(): bool
    {
        return $this->status === TransactionStatus::Pending && ! $this->hasExpired();
    }

    /**
     * Sisa waktu pembayaran dalam bentuk yang mudah dibaca, mis. "5 jam 12 menit".
     *
     * Mengembalikan null bila batas waktu belum ditetapkan atau sudah lewat.
     * Dipakai untuk menampilkan hitung mundur pada kartu pesanan.
     */
    public function remainingPaymentTime(): ?string
    {
        if ($this->expires_at === null || $this->hasExpired()) {
            return null;
        }

        return $this->expires_at->diffForHumans(now(), [
            'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE,
            'parts' => 2,
            'short' => false,
        ]);
    }

    /**
     * Sisa waktu pembayaran dalam jam, dibulatkan ke bawah.
     */
    public function remainingPaymentHours(): ?int
    {
        if ($this->expires_at === null || $this->hasExpired()) {
            return null;
        }

        return (int) floor(now()->diffInHours($this->expires_at));
    }
}
