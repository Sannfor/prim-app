<?php

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

/**
 * Pusat pesanan pengguna.
 *
 * Menampilkan pesanan milik pengguna yang sedang masuk beserta status, rincian
 * paket, tombol pembayaran, dan penanda ketersediaan kode login.
 */
new #[Layout('layouts::account')] #[Title('Pesanan Saya')] class extends Component {
    use WithPagination;

    #[Url(as: 'status', history: true)]
    public string $status = '';

    #[Url(as: 'q', history: true)]
    public string $search = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['status', 'search'], true)) {
            $this->resetPage();
        }
    }

    /**
     * Jumlah pesanan per status, dipakai pada label tab.
     *
     * @return array<string, int>
     */
    public function counts(): array
    {
        $base = Transaction::query()->where('user_id', auth()->id());

        $counts = [
            'semua' => (clone $base)->count(),
            'aktif' => (clone $base)->whereIn('status', [
                TransactionStatus::Paid->value,
                TransactionStatus::Accepted->value,
            ])->count(),
            'dibatalkan' => (clone $base)->where('status', TransactionStatus::Cancelled->value)->count(),
        ];

        foreach (TransactionStatus::customerTabs() as $status) {
            $counts[$status->value] = (clone $base)->where('status', $status->value)->count();
        }

        return $counts;
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $transactions = Transaction::query()
            ->with(['plan.service', 'serviceCredentials'])
            ->where('user_id', auth()->id())
            ->when($this->status === 'dibatalkan', fn ($q) => $q->where('status', TransactionStatus::Cancelled->value))
            ->when($this->status === 'aktif', fn ($q) => $q->whereIn('status', [
                TransactionStatus::Paid->value,
                TransactionStatus::Accepted->value,
            ]))
            ->when(
                $this->status !== '' && ! in_array($this->status, ['aktif', 'dibatalkan'], true),
                fn ($q) => $q->where('status', $this->status)
            )
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';

                $query->where(fn ($q) => $q
                    ->where('order_code', 'like', $term)
                    ->orWhereHas('plan.service', fn ($s) => $s->where('name', 'like', $term)));
            })
            ->latest()
            ->paginate(8);

        return view('livewire.profile.orders', [
            'transactions' => $transactions,
            'counts' => $this->counts(),
            'credentialCount' => auth()->user()->serviceCredentials()->count(),
        ]);
    }
}; ?>

@php
    $tabs = [
        ['key' => '', 'label' => 'Semua'],
        ['key' => 'aktif', 'label' => 'Aktif'],
        ['key' => 'pending', 'label' => 'Menunggu Pembayaran'],
        ['key' => 'processed', 'label' => 'Diproses'],
        ['key' => 'follow_up', 'label' => 'Ditindak Lanjuti'],
        ['key' => 'proof_renewal', 'label' => 'Pembaharuan Bukti'],
        ['key' => 'proof_revision', 'label' => 'Revisi Bukti'],
        ['key' => 'grace', 'label' => 'Masa Tenggang'],
        ['key' => 'dibatalkan', 'label' => 'Dibatalkan'],
    ];
@endphp

<div class="min-w-0">
    {{-- Kepala halaman --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-2xl font-bold text-ink-strong">Pesanan Saya</h1>
            <p class="mt-1 text-sm text-muted">
                Seluruh pesananmu beserta status, rincian paket, dan kredensial akunnya.
            </p>
        </div>

        <div class="w-full max-w-xs">
            <input
                type="search"
                wire:model.live.debounce.400ms="search"
                placeholder="Cari pesanan atau layanan…"
                class="prim-input h-10 text-sm"
            >
        </div>
    </div>

    {{-- Tab status --}}
    <div class="mt-5 flex flex-wrap gap-1.5">
        @foreach ($tabs as $tab)
            <button
                type="button"
                wire:key="tab-{{ $tab['key'] ?: 'semua' }}"
                wire:click="$set('status', '{{ $tab['key'] }}')"
                @class([
                    'rounded-full px-3.5 py-1.5 text-sm transition',
                    'bg-brand text-white' => $status === $tab['key'],
                    'bg-white text-ink hover:bg-brand-soft' => $status !== $tab['key'],
                ])
            >
                {{ $tab['label'] }} ({{ $counts[$tab['key']] ?? $counts[$tab['key'] === '' ? 'semua' : $tab['key']] ?? 0 }})
            </button>
        @endforeach
    </div>

    {{-- Daftar pesanan --}}
    <div class="mt-5 space-y-4">
        @forelse ($transactions as $transaction)
            @php $credential = $transaction->serviceCredentials->first(); @endphp

            <article wire:key="order-{{ $transaction->id }}" class="rounded-xl bg-white p-5 shadow-brand-xs">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <x-service-logo :service="$transaction->plan->service" class="h-12 w-20 shrink-0" />

                        <div class="min-w-0">
                            <p class="font-display text-base font-semibold text-ink-strong">
                                {{ $transaction->plan->service->name }}
                            </p>
                            <p class="text-xs text-muted">
                                {{ $transaction->plan->groupLabel() }} &middot;
                                <span class="font-mono">{{ $transaction->order_code }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="text-right">
                        <x-status-badge :status="$transaction->status" />
                        <p class="mt-2 text-sm font-medium text-ink-strong">{{ $transaction->formattedAmount() }}</p>
                        <p class="text-xs text-muted">{{ $transaction->created_at->translatedFormat('d M Y, H:i') }}</p>
                    </div>
                </div>

                {{-- Rincian singkat pesanan --}}
                <dl class="mt-4 grid gap-3 border-t border-line-soft pt-4 text-xs sm:grid-cols-3">
                    <div>
                        <dt class="text-muted">Metode pembayaran</dt>
                        <dd class="mt-0.5 text-ink">{{ $transaction->paymentLabel() }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted">Masa aktif</dt>
                        <dd class="mt-0.5 text-ink">{{ $transaction->plan->durationLabel() }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted">Perangkat</dt>
                        <dd class="mt-0.5 text-ink">{{ $transaction->plan->max_devices }} perangkat</dd>
                    </div>
                </dl>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    @if ($transaction->isPayable())
                        <a
                            href="{{ route('transaction.show', $transaction->order_code) }}"
                            class="prim-btn h-9 text-sm"
                            wire:navigate
                        >
                            <flux:icon.credit-card class="size-4" />
                            Bayar Sekarang
                        </a>
                    @endif

                    <a
                        href="{{ route('transaction.show', $transaction->order_code) }}"
                        class="prim-btn-ghost h-9 text-sm"
                        wire:navigate
                    >
                        Lihat Rincian
                    </a>

                    @if ($credential)
                        <a href="{{ route('profile.login-code') }}" class="text-sm font-medium text-brand hover:underline" wire:navigate>
                            Kode Login Tersedia
                        </a>
                    @endif

                    @if ($transaction->isPayable())
                        <span class="text-xs text-status-wait-fg">
                            Bayar sebelum {{ $transaction->expires_at?->translatedFormat('d M Y, H:i') }}
                        </span>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-xl bg-white p-12 text-center shadow-brand-xs">
                <flux:icon.receipt-percent class="mx-auto size-9 text-muted-2" />
                <p class="mt-3 font-medium text-ink-strong">Belum ada pesanan</p>
                <p class="mt-1 text-sm text-muted">
                    Pesanan yang kamu buat akan muncul di sini beserta statusnya.
                </p>
                <a href="{{ route('catalog.index') }}" class="prim-btn mt-5" wire:navigate>Jelajahi Katalog</a>
            </div>
        @endforelse
    </div>

    <div class="mt-8">{{ $transactions->links() }}</div>
</div>
