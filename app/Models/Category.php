<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Kategori layanan premium, misalnya Hiburan, Produktivitas, atau Edukasi.
 */
class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
    ];

    protected static function booted(): void
    {
        static::creating(function (Category $category) {
            if (blank($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Seluruh layanan dalam kategori ini.
     *
     * @return HasMany<Service, $this>
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Hanya kategori yang memiliki minimal satu layanan aktif.
     *
     * @param  Builder<Category>  $query
     */
    public function scopeWithActiveServices(Builder $query): void
    {
        $query->whereHas('services', fn (Builder $q) => $q->where('is_active', true));
    }
}
