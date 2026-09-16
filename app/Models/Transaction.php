<?php

namespace App\Models;

use App\Casts\TransactionStatusCast;
use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
    /** @use HasFactory<\Database\Factories\TransactionFactory> */
    use HasFactory;

    /**
     * Masa berlaku pesanan sebelum dianggap kedaluwarsa.
     */
    public const PAYMENT_WINDOW_HOURS = 24;

    protected $fillable = [
        'order_code',
        'user_id',
        'plan_id',
        'amount',
        'status',
        'payment_method',
        'paid_at',
        'expires_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'status' => TransactionStatusCast::class,
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
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
     * Langganan yang lahir dari transaksi ini (jika sudah dibayar).
     *
     * @return HasOne<Subscription, $this>
     */
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
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
}
