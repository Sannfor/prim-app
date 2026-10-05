<?php

namespace App\Models;

use App\Enums\BuyerNotificationType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Notifikasi dalam aplikasi untuk pembeli.
 */
class BuyerNotification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'url',
        'transaction_id',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => BuyerNotificationType::class,
            'read_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Transaction, $this>
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Apakah notifikasi belum dibaca.
     */
    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * Tandai notifikasi sudah dibaca.
     */
    public function markAsRead(): void
    {
        if ($this->isUnread()) {
            $this->forceFill(['read_at' => now()])->save();
        }
    }

    /**
     * Notifikasi yang belum dibaca.
     *
     * @param  Builder<BuyerNotification>  $query
     */
    public function scopeUnread(Builder $query): void
    {
        $query->whereNull('read_at');
    }
}
