<?php

namespace App\Models;

use Database\Factories\ProviderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Penyedia layanan premium yang katalognya ditampilkan pada PRIM.
 */
class Provider extends Model
{
    /** @use HasFactory<ProviderFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'logo_path',
        'website',
        'description',
    ];

    /**
     * Atribut yang otomatis diisi dari nama provider.
     */
    protected static function booted(): void
    {
        static::creating(function (Provider $provider) {
            if (blank($provider->slug)) {
                $provider->slug = Str::slug($provider->name);
            }
        });
    }

    /**
     * Kunci rute memakai slug agar URL lebih terbaca.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Seluruh layanan yang disediakan provider ini.
     *
     * @return HasMany<Service, $this>
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Hanya provider yang memiliki minimal satu layanan aktif.
     *
     * @param  Builder<Provider>  $query
     */
    public function scopeWithActiveServices(Builder $query): void
    {
        $query->whereHas('services', fn (Builder $q) => $q->where('is_active', true));
    }
}
