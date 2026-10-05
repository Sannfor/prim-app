<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Catatan satu kali pemakaian voucher.
 *
 * Dipakai untuk menegakkan batas pemakaian per pembeli dan untuk menelusuri
 * siapa saja yang memakai sebuah voucher.
 */
class VoucherRedemption extends Model
{
    protected $fillable = [
        'voucher_id',
        'user_id',
        'transaction_id',
        'discount_amount',
    ];

    protected function casts(): array
    {
        return [
            'discount_amount' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Voucher, $this>
     */
    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
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
}
