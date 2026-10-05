<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Voucher atau kode promo.
 *
 * Potongan dihitung oleh VoucherService; model ini hanya menyimpan aturan dan
 * menyediakan pemeriksaan masa berlaku serta sisa kuota.
 */
class Voucher extends Model
{
    /** @use HasFactory<\Database\Factories\VoucherFactory> */
    use HasFactory;

    public const TYPE_PERCENT = 'percent';

    public const TYPE_FIXED = 'fixed';

    protected $fillable = [
        'code',
        'description',
        'type',
        'value',
        'max_discount',
        'min_purchase',
        'usage_limit',
        'usage_limit_per_user',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => 'string',
            'value' => 'integer',
            'max_discount' => 'integer',
            'min_purchase' => 'integer',
            'usage_limit' => 'integer',
            'usage_limit_per_user' => 'integer',
            'used_count' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Kode voucher selalu disimpan dalam huruf besar agar pencocokan konsisten.
     */
    public function setCodeAttribute(string $value): void
    {
        $this->attributes['code'] = strtoupper(trim($value));
    }

    /**
     * @return HasMany<VoucherRedemption, $this>
     */
    public function redemptions(): HasMany
    {
        return $this->hasMany(VoucherRedemption::class);
    }

    /**
     * Apakah voucher sedang dalam masa berlaku.
     */
    public function isWithinPeriod(): bool
    {
        $sekarang = Carbon::now();

        if ($this->starts_at !== null && $sekarang->lessThan($this->starts_at)) {
            return false;
        }

        if ($this->expires_at !== null && $sekarang->greaterThan($this->expires_at)) {
            return false;
        }

        return true;
    }

    /**
     * Apakah kuota pemakaian keseluruhan masih tersedia.
     */
    public function hasQuotaLeft(): bool
    {
        return $this->usage_limit === null || $this->used_count < $this->usage_limit;
    }

    /**
     * Sisa kuota pemakaian, null bila tidak dibatasi.
     */
    public function remainingQuota(): ?int
    {
        return $this->usage_limit === null
            ? null
            : max(0, $this->usage_limit - $this->used_count);
    }

    /**
     * Tampilan nilai potongan untuk keperluan antarmuka.
     */
    public function valueLabel(): string
    {
        if ($this->type === self::TYPE_FIXED) {
            return 'Rp'.number_format($this->value, 0, ',', '.');
        }

        return $this->value.'%';
    }

    /**
     * Keterangan singkat syarat pemakaian.
     */
    public function requirementLabel(): string
    {
        $bagian = [];

        if ($this->min_purchase > 0) {
            $bagian[] = 'min. belanja Rp'.number_format($this->min_purchase, 0, ',', '.');
        }

        if ($this->max_discount !== null && $this->type === self::TYPE_PERCENT) {
            $bagian[] = 'maks. potongan Rp'.number_format($this->max_discount, 0, ',', '.');
        }

        if ($this->expires_at !== null) {
            $bagian[] = 'berlaku sampai '.$this->expires_at->translatedFormat('d M Y');
        }

        return implode(' · ', $bagian);
    }
}
