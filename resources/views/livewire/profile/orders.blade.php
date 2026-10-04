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
 * Mengikuti frame "Profil - pesanan" pada desain: judul "Pesanan", daftar tab
 * status yang dapat diperluas, kolom pencarian pesanan, dan daftar pesanan
 * milik pengguna yang sedang masuk.
 */
new #[Layout('layouts::app')] #[Title('Pesanan Saya')] class extends Component {
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
    $user = auth()->user();

    $tabs = [
        ['key' => '', 'label' => 'Semua'],
        ['key' => 'aktif', 'label' => 'Aktif'],
        ['key' => 'follow_up', 'label' => 'Ditindak Lanjuti'],
        ['key' => 'proof_renewal', 'label' => 'Pembaharuan Bukti'],
        ['key' => 'pending', 'label' => 'Menunggu Pembayaran'],
        ['key' => 'dibatalkan', 'label' => 'Dibatalkan'],
        ['key' => 'processed', 'label' => 'Sedang Proses'],
        ['key' => 'grace', 'label' => 'Masa Tenggang'],
        ['key' => 'proof_revision', 'label' => 'Revisi Bukti'],
    ];
@endphp

<div class="mx-auto w-full max-w-[1728px] px-6 py-10 lg:px-12">
    <div class="grid gap-8 lg:grid-cols-[300px_minmax(0,1fr)]">
        {{-- Panel akun --}}
        <aside class="lg:sticky lg:top-8 lg:self-start">
            <div class="prim-card p-6">
                <div class="flex items-center gap-3">
                    <span class="flex size-12 shrink-0 items-center justify-center rounded-full bg-brand text-sm font-medium text-white">
                        {{ $user->initials() }}
                    </span>
                    <div class="min-w-0">
                        <p class="truncate font-display text-base font-semibold text-ink-strong">{{ $user->name }}</p>
                        <p class="truncate text-xs text-muted">{{ $user->email }}</p>
                    </div>
                </div>

                <dl class="mt-5 space-y-2 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted">No. HP</dt>
                        <dd class="truncate text-ink">{{ $user->phone ?: '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted">Kode Login</dt>
                        <dd class="text-ink">{{ $credentialCount }} akun</dd>
                    </div>
                </dl>

                <nav class="mt-6 space-y-1 border-t border-line-soft pt-4">
                    <a
                        href="{{ route('profile.orders') }}"
                        @class([
                            'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition',
                            'bg-brand-soft text-brand' => request()->routeIs('profile.orders'),
                            'text-ink hover:bg-canvas' => ! request()->routeIs('profile.orders'),
                        ])
                        wire:navigate
                    >
                        <flux:icon.receipt-percent class="size-5" />
                        Pesanan
                    </a>

                    <a
                        href="{{ route('profile.login-code') }}"
                        @class([
                            'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition',
                            'bg-brand-soft text-brand' => request()->routeIs('profile.login-code'),
                            'text-ink hover:bg-canvas' => ! request()->routeIs('profile.login-code'),
                        ])
                        wire:navigate
                    >
                        <flux:icon.key class="size-5" />
                        Kode Login
                    </a>

                    <a
                        href="{{ route('subscription.index') }}"
                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-ink transition hover:bg-canvas"
                        wire:navigate
                    >
                        <flux:icon.arrow-path-rounded-square class="size-5" />
                        Langganan
                    </a>

                    <a
                        href="{{ route('settings.profile') }}"
                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-ink transition hover:bg-canvas"
                        wire:navigate
                    >
                        <flux:icon.cog-6-tooth class="size-5" />
                        Pengaturan
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-status-cancel-fg transition hover:bg-status-cancel-bg">
                            <flux:icon.arrow-right-start-on-rectangle class="size-5" />
                            Keluar
                        </button>
                    </form>
                </nav>
            </div>
        </aside>

        {{-- Daftar pesanan --}}
        <section class="min-w-0">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <h1 class="font-display text-[28px] font-bold text-ink-strong sm:text-[35px]">Pesanan</h1>

                <div class="w-full max-w-xs">
                    <input
                        type="search"
                        wire:model.live.debounce.400ms="search"
                        placeholder="Cari Pesanan"
                        class="prim-input h-10 text-sm"
                    >
                </div>
            </div>

            {{-- Tab status --}}
            <div class="mt-6 flex flex-wrap gap-1.5">
                @foreach ($tabs as $tab)
                    <button
                        type="button"
                        wire:key="tab-{{ $tab['key'] ?: 'semua' }}"
                        wire:click="$set('status', '{{ $tab['key'] }}')"
                        @class([
                            'rounded-full px-3.5 py-1.5 text-sm transition',
                            'bg-brand text-white' => $status === $tab['key'],
                            'bg-canvas text-ink hover:bg-brand-soft' => $status !== $tab['key'],
                        ])
                    >
                        {{ $tab['label'] }}({{ $counts[$tab['key']] ?? 0 }})
                    </button>
                @endforeach
            </div>

            {{-- Daftar --}}
            <div class="mt-6 space-y-4">
                @forelse ($transactions as $transaction)
                    <article wire:key="order-{{ $transaction->id }}" class="prim-card p-5">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <x-service-logo :service="$transaction->plan->service" class="h-12 w-20 shrink-0" />

                                <div>
                                    <p class="font-display text-base font-semibold text-ink-strong">
                                        {{ $transaction->plan->service->name }}
                                    </p>
                                    <p class="text-xs text-muted">
                                        {{ $transaction->plan->groupLabel() }} ·
                                        <span class="font-mono">{{ $transaction->order_code }}</span>
                                    </p>
                                </div>
                            </div>

                            <div class="text-right">
                                <x-status-badge :status="$transaction->status" />
                                <p class="mt-2 text-sm font-medium text-ink-strong">{{ $transaction->formattedAmount() }}</p>
                                <p class="text-xs text-muted">{{ $transaction->created_at->translatedFormat('d M Y') }}</p>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-3 border-t border-line-soft pt-4">
                            <a
                                href="{{ route('transaction.show', $transaction->order_code) }}"
                                class="prim-btn-ghost h-9 text-sm"
                                wire:navigate
                            >
                                Lihat Rincian
                            </a>

                            @if ($transaction->serviceCredentials->isNotEmpty())
                                <a href="{{ route('profile.login-code') }}" class="prim-btn h-9 text-sm" wire:navigate>
                                    Buka Kode Login
                                </a>
                            @elseif ($transaction->isPayable())
                                <span class="text-xs text-status-wait-fg">
                                    Selesaikan pembayaran sebelum
                                    {{ $transaction->expires_at?->translatedFormat('d M Y, H:i') }}
                                </span>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="prim-card p-12 text-center">
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
        </section>
    </div>
</div>
