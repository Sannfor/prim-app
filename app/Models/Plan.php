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
        'variant_group',
        'price',
        'compare_at_price',
        'discount_percent',
        'duration_days',
        'periods_label',
        'max_devices',
        'description',
        'features',
        'is_active',
        'is_preorder',
        'stock',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'compare_at_price' => 'integer',
            'discount_percent' => 'integer',
            'duration_days' => 'integer',
            'max_devices' => 'integer',
            'features' => 'array',
            'is_active' => 'boolean',
            'is_preorder' => 'boolean',
            'stock' => 'integer',
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

    /**
     * Nama grup varian paket, mis. "Bulanan" atau "1 Perangkat".
     *
     * Bila kolom varian belum diisi, dipakai nama paket agar tampilan katalog
     * tetap masuk akal.
     */
    public function groupLabel(): string
    {
        return $this->variant_group ?: $this->name;
    }

    /**
     * Daftar periode tagihan yang dapat dipilih, mis. "1, 2, 3, 6 bln".
     */
    public function periodsLabel(): string
    {
        if (filled($this->periods_label)) {
            return $this->periods_label;
        }

        $months = max(1, (int) round($this->duration_days / 30));

        return $months.' bln';
    }

    /**
     * Apakah paket ini sedang memakai pita diskon.
     */
    public function hasDiscount(): bool
    {
        return $this->discount_percent > 0;
    }

    /**
     * Teks pita diskon, mis. "Diskon 10%".
     */
    public function discountLabel(): string
    {
        return 'Diskon '.$this->discount_percent.'%';
    }

    /**
     * Harga sebelum diskon yang ditampilkan sebagai harga coret.
     */
    public function compareAtPrice(): int
    {
        if ($this->compare_at_price !== null) {
            return $this->compare_at_price;
        }

        if (! $this->hasDiscount()) {
            return $this->price;
        }

        return (int) round($this->price / (1 - $this->discount_percent / 100));
    }

    /**
     * Harga coret dalam format rupiah.
     */
    public function formattedCompareAtPrice(): string
    {
        return 'Rp'.Number::format($this->compareAtPrice(), locale: 'id');
    }

    /**
     * Apakah stok paket ini habis.
     */
    public function isOutOfStock(): bool
    {
        return $this->stock <= 0;
    }

    /**
     * Label stok untuk kolom pada tabel admin.
     */
    public function stockLabel(): string
    {
        return $this->isOutOfStock() ? 'Habis' : (string) $this->stock;
    }
}
