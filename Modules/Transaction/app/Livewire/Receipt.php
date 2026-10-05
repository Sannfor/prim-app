<?php

namespace Modules\Transaction\Livewire;

use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Struk digital sebuah pesanan.
 *
 * Halaman ini dirancang untuk dicetak (atau disimpan sebagai PDF melalui dialog
 * cetak peramban) sehingga pembeli memiliki bukti pembayaran yang sah. Hanya
 * pemilik pesanan dan administrator yang boleh membukanya.
 */
#[Layout('layouts::account')]
#[Title('Struk Digital')]
class Receipt extends Component
{
    public Transaction $order;

    public function mount(Transaction $order): void
    {
        abort_unless(
            $order->user_id === auth()->id() || auth()->user()?->isAdmin(),
            403,
            'Struk ini bukan milik Anda.'
        );

        $this->order = $order->load(['user', 'plan.service.provider', 'subscription', 'voucher']);
    }

    public function render(): View
    {
        return view('transaction::livewire.receipt', [
            'order' => $this->order,
            'nomorStruk' => 'STR-'.$this->order->created_at->format('Ymd').'-'.str_pad((string) $this->order->id, 5, '0', STR_PAD_LEFT),
        ]);
    }
}
