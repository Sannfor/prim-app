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
new #[Layout('layouts::account')] #[Title('Kode Login')] class extends Component {
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

<div class="min-w-0">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-2xl font-bold text-ink-strong">Kode Login</h1>
            <p class="mt-1 text-sm text-muted">
                Kredensial akun untuk pesanan yang sudah selesai. Jangan bagikan kode ini kepada siapa pun.
            </p>
        </div>

        <span class="rounded-full bg-status-warn-bg px-3.5 py-1.5 text-xs font-medium text-status-wait-fg">
            {{ $credentials->count() }} akun tersedia
        </span>
    </div>

    <div class="mt-5 space-y-4">
        @forelse ($credentials as $credential)
            @php $isRevealed = in_array($credential->id, $revealed, true); @endphp

            <article wire:key="cred-{{ $credential->id }}" class="rounded-xl bg-white p-5 shadow-brand-xs">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <x-service-logo :service="$credential->transaction->plan->service" class="h-12 w-20 shrink-0" />

                        <div class="min-w-0">
                            <p class="font-display text-base font-semibold text-ink-strong">
                                {{ $credential->transaction->plan->service->name }}
                            </p>
                            <p class="truncate text-xs text-muted">{{ $credential->label }}</p>
                        </div>
                    </div>

                    <button type="button" wire:click="toggle({{ $credential->id }})" class="prim-btn-ghost h-9 text-sm">
                        <flux:icon :name="$isRevealed ? 'eye-slash' : 'eye'" class="size-4" />
                        {{ $isRevealed ? 'Sembunyikan' : 'Tampilkan' }}
                    </button>
                </div>

                <dl class="mt-4 grid gap-4 border-t border-line-soft pt-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <dt class="text-xs tracking-wide text-muted uppercase">Email / Kode</dt>
                        <dd class="mt-1 font-mono text-sm break-all text-ink-strong">
                            {{ $isRevealed ? $credential->login_code : '••••••••••••' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs tracking-wide text-muted uppercase">Kata Sandi</dt>
                        <dd class="mt-1 font-mono text-sm break-all text-ink-strong">
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
                    Dikirim {{ $credential->delivered_at?->translatedFormat('d M Y, H:i') ?? '—' }} ·
                    Pesanan
                    <a href="{{ route('transaction.show', $credential->transaction->order_code) }}" class="text-brand hover:underline" wire:navigate>
                        {{ $credential->transaction->order_code }}
                    </a>
                </p>
            </article>
        @empty
            <div class="rounded-xl bg-white p-12 text-center shadow-brand-xs">
                <flux:icon.key class="mx-auto size-9 text-muted-2" />
                <p class="mt-3 font-medium text-ink-strong">Belum ada kode login</p>
                <p class="mt-1 text-sm text-muted">
                    Kredensial akun akan muncul di sini setelah pesananmu selesai diproses.
                </p>
                <a href="{{ route('catalog.index') }}" class="prim-btn mt-5" wire:navigate>Jelajahi Katalog</a>
            </div>
        @endforelse
    </div>
</div>
