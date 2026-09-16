<?php

namespace App\Models;

use App\Casts\SubscriptionStatusCast;
use App\Enums\SubscriptionStatus;
use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Masa aktif langganan pengguna atas sebuah paket layanan premium.
 *
 * Satu transaksi yang berhasil melahirkan tepat satu langganan.
 */
class Subscription extends Model
{
    /** @use HasFactory<SubscriptionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'transaction_id',
        'status',
        'started_at',
        'ends_at',
        'auto_renew',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatusCast::class,
            'started_at' => 'datetime',
            'ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'auto_renew' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /**
     * Pemilik langganan.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Paket yang dilanggan.
     *
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Transaksi sumber langganan ini.
     *
     * @return BelongsTo<Transaction, $this>
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Hanya langganan yang benar-benar masih aktif.
     *
     * @param  Builder<Subscription>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', SubscriptionStatus::Active->value)
            ->where('ends_at', '>', now());
    }

    /**
     * Langganan yang sudah lewat masa aktifnya.
     *
     * @param  Builder<Subscription>  $query
     */
    public function scopeExpired(Builder $query): void
    {
        $query->where('ends_at', '<=', now());
    }

    /**
     * Apakah langganan masih berlaku saat ini.
     */
    public function isActive(): bool
    {
        return $this->status === SubscriptionStatus::Active
            && $this->ends_at !== null
            && $this->ends_at->isFuture();
    }

    /**
     * Apakah masa aktif tinggal sedikit lagi (ambang 7 hari).
     */
    public function isExpiringSoon(): bool
    {
        return $this->isActive() && $this->daysRemaining() <= 7;
    }

    /**
     * Sisa hari masa aktif; 0 bila sudah lewat.
     */
    public function daysRemaining(): int
    {
        if ($this->ends_at === null || $this->ends_at->isPast()) {
            return 0;
        }

        return (int) ceil(now()->floatDiffInDays($this->ends_at));
    }

    /**
     * Persentase masa aktif yang sudah terpakai (0-100).
     */
    public function progressPercentage(): int
    {
        if ($this->started_at === null || $this->ends_at === null) {
            return 0;
        }

        $total = $this->started_at->diffInSeconds($this->ends_at);

        if ($total <= 0) {
            return 100;
        }

        $elapsed = $this->started_at->diffInSeconds(now());
        $percentage = ($elapsed / $total) * 100;

        return (int) max(0, min(100, round($percentage)));
    }
}
