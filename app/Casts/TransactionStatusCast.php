<?php

namespace App\Casts;

use App\Enums\TransactionStatus;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Cast kolom `status` transaksi dengan nilai cadangan yang aman.
 *
 * Sama seperti RoleCast: menjaga properti selalu berupa enum sehingga kode
 * tampilan tidak perlu melakukan pengecekan null berulang kali.
 *
 * @implements CastsAttributes<TransactionStatus, TransactionStatus|string>
 */
class TransactionStatusCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): TransactionStatus
    {
        if ($value instanceof TransactionStatus) {
            return $value;
        }

        if (is_string($value)) {
            return TransactionStatus::tryFrom($value) ?? TransactionStatus::Pending;
        }

        return TransactionStatus::Pending;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value instanceof TransactionStatus) {
            return $value->value;
        }

        if (is_string($value) && TransactionStatus::tryFrom($value) !== null) {
            return $value;
        }

        return $value === null ? null : TransactionStatus::Pending->value;
    }
}
