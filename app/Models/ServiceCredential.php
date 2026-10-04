<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Kredensial akun layanan yang dibagikan setelah pembayaran berhasil.
 *
 * Hanya pemilik transaksi yang boleh melihat datanya; hal ini ditegakkan pada
 * komponen Livewire yang menampilkannya, bukan pada model.
 */
class ServiceCredential extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceCredentialFactory> */
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'user_id',
        'label',
        'login_code',
        'password_code',
        'profile_name',
        'pin_code',
        'notes',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'delivered_at' => 'datetime',
        ];
    }

    /**
     * Transaksi sumber kredensial ini.
     *
     * @return BelongsTo<Transaction, $this>
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Pembeli yang berhak memakai kredensial ini.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
