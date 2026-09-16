<?php

namespace App\Models;

use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Number;

/**
 * Paket langganan pada sebuah layanan premium.
 *
 * Contoh: paket "Basic 1 Bulan" atau "Premium 12 Bulan" untuk satu layanan.
 */
class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory;

    protected $fillable = [
        'service_id',
        'name',
        'price',
        'duration_days',
        'max_devices',
        'description',
        'features',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'duration_days' => 'integer',
            'max_devices' => 'integer',
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Layanan pemilik paket ini.
     *
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Transaksi yang memakai paket ini.
     *
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Langganan yang memakai paket ini.
     *
     * @return HasMany<Subscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Hanya paket yang berstatus aktif.
     *
     * @param  Builder<Plan>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Harga dalam format rupiah, misalnya "Rp75.000".
     */
    public function formattedPrice(): string
    {
        return 'Rp'.Number::format($this->price, locale: 'id');
    }

    /**
     * Harga per bulan sebagai pembanding antar paket.
     */
    public function monthlyPrice(): ?int
    {
        if ($this->duration_days <= 0) {
            return null;
        }

        return (int) round($this->price / ($this->duration_days / 30));
    }

    /**
     * Label durasi yang mudah dibaca, misalnya "1 Bulan" atau "12 Bulan".
     *
     * Durasi tahunan (365 hari) tetap ditampilkan dalam satuan bulan karena
     * itu yang lazim dipakai pada katalog layanan berlangganan.
     */
    public function durationLabel(): string
    {
        if ($this->duration_days >= 360) {
            return round($this->duration_days / 30).' Bulan';
        }

        if ($this->duration_days % 30 === 0) {
            return intdiv($this->duration_days, 30).' Bulan';
        }

        if ($this->duration_days % 7 === 0) {
            return intdiv($this->duration_days, 7).' Minggu';
        }

        return $this->duration_days.' Hari';
    }
}
