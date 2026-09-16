<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

/**
 * Layanan digital premium yang ditawarkan pada katalog PRIM.
 */
class Service extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceFactory> */
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'category_id',
        'name',
        'slug',
        'tagline',
        'description',
        'logo_path',
        'website',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Service $service) {
            if (blank($service->slug)) {
                $service->slug = Str::slug($service->name);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Provider pemilik layanan ini.
     *
     * @return BelongsTo<Provider, $this>
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * Kategori layanan ini.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Paket langganan yang tersedia untuk layanan ini.
     *
     * @return HasMany<Plan, $this>
     */
    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class);
    }

    /**
     * Ulasan pengguna terhadap layanan ini.
     *
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Seluruh transaksi atas paket layanan ini.
     *
     * @return HasManyThrough<Transaction, Plan, $this>
     */
    public function transactions(): HasManyThrough
    {
        return $this->hasManyThrough(Transaction::class, Plan::class);
    }

    /**
     * Hanya layanan yang berstatus aktif.
     *
     * @param  Builder<Service>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Layanan aktif beserta provider, paket aktif, dan ulasannya.
     *
     * @param  Builder<Service>  $query
     */
    public function scopeWithCatalogRelations(Builder $query): void
    {
        $query->with([
            'provider',
            'category',
            'reviews',
            'plans' => fn ($q) => $q->where('is_active', true)->orderBy('price'),
        ]);
    }

    /**
     * Harga paket aktif termurah pada layanan ini.
     */
    public function lowestPrice(): ?int
    {
        return $this->plans->where('is_active', true)->min('price');
    }

    /**
     * Rata-rata nilai ulasan, dibulatkan satu desimal.
     */
    public function averageRating(): ?float
    {
        $average = $this->reviews->avg('rating');

        return $average === null ? null : round((float) $average, 1);
    }
}
