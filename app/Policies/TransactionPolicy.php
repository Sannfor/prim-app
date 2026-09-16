<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

/**
 * Otorisasi transaksi: pengguna hanya boleh mengakses transaksinya sendiri,
 * sedangkan administrator platform boleh melihat seluruh transaksi.
 */
class TransactionPolicy
{
    /**
     * Apakah pengguna boleh melihat daftar transaksi (selalu miliknya sendiri).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Apakah pengguna boleh melihat satu transaksi.
     */
    public function view(User $user, Transaction $transaction): bool
    {
        return $user->isAdmin() || $transaction->user_id === $user->id;
    }

    /**
     * Apakah pengguna boleh membuat transaksi.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Apakah pengguna boleh membayar transaksi ini.
     */
    public function pay(User $user, Transaction $transaction): bool
    {
        return $transaction->user_id === $user->id && $transaction->isPayable();
    }

    /**
     * Apakah pengguna boleh mengubah status transaksi secara manual.
     */
    public function update(User $user, Transaction $transaction): bool
    {
        return $user->isAdmin();
    }

    /**
     * Apakah pengguna boleh menghapus transaksi.
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        return $user->isAdmin();
    }
}
