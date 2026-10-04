<?php

use App\Models\ServiceCredential;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

/**
 * Kode login akun layanan.
 *
 * Mengikuti tab "Kode Login" pada frame "Profil - click kode login" di desain:
 * daftar kredensial akun untuk setiap pesanan yang sudah berhasil. Nilai
 * sensitif disembunyikan sampai pengguna memilih untuk menampilkannya.
 */
new #[Layout('layouts::app')] #[Title('Kode Login')] class extends Component {
    /**
     * Id kredensial yang sedang ditampilkan nilainya.
     *
     * @var list<int>
     */
    public array $revealed = [];

    /**
     * Tampilkan atau sembunyikan nilai kredensial.
     */
    public function toggle(int $credentialId): void
    {
        if (in_array($credentialId, $this->revealed, true)) {
            $this->revealed = array_values(array_diff($this->revealed, [$credentialId]));

            return;
        }

        $this->revealed[] = $credentialId;
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $credentials = ServiceCredential::query()
            ->with(['transaction.plan.service'])
            ->where('user_id', auth()->id())
            ->latest('delivered_at')
            ->get();

        return view('livewire.profile.login-code', [
            'credentials' => $credentials,
        ]);
    }
}; ?>

<div class="mx-auto w-full max-w-[1728px] px-6 py-10 lg:px-12">
    <div class="grid gap-8 lg:grid-cols-[300px_minmax(0,1fr)]">
        {{-- Panel akun --}}
        <aside class="lg:sticky lg:top-8 lg:self-start">
            <div class="prim-card p-6">
                <div class="flex items-center gap-3">
                    <span class="flex size-12 shrink-0 items-center justify-center rounded-full bg-brand text-sm font-medium text-white">
                        {{ auth()->user()->initials() }}
                    </span>
                    <div class="min-w-0">
                        <p class="truncate font-display text-base font-semibold text-ink-strong">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-muted">{{ auth()->user()->email }}</p>
                    </div>
                </div>

                <nav class="mt-6 space-y-1 border-t border-line-soft pt-4">
                    <a href="{{ route('profile.orders') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-ink transition hover:bg-canvas" wire:navigate>
                        <flux:icon.receipt-percent class="size-5" />
                        Pesanan
                    </a>

                    <a href="{{ route('profile.login-code') }}" class="flex items-center gap-3 rounded-lg bg-brand-soft px-3 py-2.5 text-sm font-medium text-brand" wire:navigate>
                        <flux:icon.key class="size-5" />
                        Kode Login
                    </a>

                    <a href="{{ route('subscription.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-ink transition hover:bg-canvas" wire:navigate>
                        <flux:icon.arrow-path-rounded-square class="size-5" />
                        Langganan
                    </a>

                    <a href="{{ route('settings.profile') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-ink transition hover:bg-canvas" wire:navigate>
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

        {{-- Daftar kredensial --}}
        <section class="min-w-0">
            <h1 class="font-display text-[28px] font-bold text-ink-strong sm:text-[35px]">Kode Login</h1>
            <p class="mt-2 text-sm text-muted">
                Kredensial akun untuk pesanan yang sudah selesai. Jangan bagikan kode ini kepada siapa pun.
            </p>

            <div class="mt-6 space-y-4">
                @forelse ($credentials as $credential)
                    @php $isRevealed = in_array($credential->id, $revealed, true); @endphp

                    <article wire:key="cred-{{ $credential->id }}" class="prim-card p-5">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <x-service-logo :service="$credential->transaction->plan->service" class="h-12 w-20 shrink-0" />

                                <div>
                                    <p class="font-display text-base font-semibold text-ink-strong">
                                        {{ $credential->transaction->plan->service->name }}
                                    </p>
                                    <p class="text-xs text-muted">
                                        {{ $credential->label }}
                                    </p>
                                </div>
                            </div>

                            <button
                                type="button"
                                wire:click="toggle({{ $credential->id }})"
                                class="prim-btn-ghost h-9 text-sm"
                            >
                                {{ $isRevealed ? 'Sembunyikan' : 'Tampilkan' }}
                            </button>
                        </div>

                        <dl class="mt-5 grid gap-4 border-t border-line-soft pt-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs tracking-wide text-muted uppercase">Email / Kode</dt>
                                <dd class="mt-1 font-mono text-sm text-ink-strong">
                                    {{ $isRevealed ? $credential->login_code : '••••••••••••' }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-xs tracking-wide text-muted uppercase">Kata Sandi</dt>
                                <dd class="mt-1 font-mono text-sm text-ink-strong">
                                    {{ $isRevealed ? ($credential->password_code ?: '—') : '••••••••' }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-xs tracking-wide text-muted uppercase">Profil</dt>
                                <dd class="mt-1 text-sm text-ink">{{ $credential->profile_name ?: '—' }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs tracking-wide text-muted uppercase">PIN</dt>
                                <dd class="mt-1 font-mono text-sm text-ink">
                                    {{ $isRevealed ? ($credential->pin_code ?: '—') : '••••' }}
                                </dd>
                            </div>
                        </dl>

                        @if ($credential->notes)
                            <p class="mt-4 rounded-lg bg-canvas px-3.5 py-2.5 text-xs leading-relaxed text-muted">
                                {{ $credential->notes }}
                            </p>
                        @endif

                        <p class="mt-3 text-xs text-muted">
                            Dikirim {{ $credential->delivered_at?->translatedFormat('d M Y, H:i') ?? '—' }}
                            · Pesanan
                            <a href="{{ route('transaction.show', $credential->transaction->order_code) }}" class="text-brand hover:underline" wire:navigate>
                                {{ $credential->transaction->order_code }}
                            </a>
                        </p>
                    </article>
                @empty
                    <div class="prim-card p-12 text-center">
                        <flux:icon.key class="mx-auto size-9 text-muted-2" />
                        <p class="mt-3 font-medium text-ink-strong">Belum ada kode login</p>
                        <p class="mt-1 text-sm text-muted">
                            Kredensial akun akan muncul di sini setelah pesananmu selesai diproses.
                        </p>
                        <a href="{{ route('catalog.index') }}" class="prim-btn mt-5" wire:navigate>Jelajahi Katalog</a>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
