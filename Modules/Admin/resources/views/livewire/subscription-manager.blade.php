<div class="space-y-7">
    <div>
        <h1 class="font-display text-2xl font-medium text-ink-strong">Manajemen Langganan</h1>
        <p class="mt-1 text-sm text-muted">
            Pantau masa aktif seluruh langganan pelanggan beserta perpanjangan otomatisnya.
        </p>
    </div>

    {{-- Kartu ringkasan --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($cards as $card)
            <div class="rounded-xl bg-canvas p-5">
                <p class="text-sm text-muted">{{ $card['label'] }}</p>
                <p class="mt-1.5 font-display text-xl font-medium text-ink-strong">{{ $card['value'] }}</p>
                <p class="mt-1 text-xs text-muted">{{ $card['hint'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Penyaring --}}
    <div class="flex flex-wrap items-center gap-3">
        <input
            type="search"
            wire:model.live.debounce.400ms="search"
            placeholder="Cari nama, email, atau layanan…"
            class="prim-input h-10 max-w-72 text-sm"
        >

        <select wire:model.live="status" class="prim-input h-10 max-w-56 text-sm">
            <option value="">Semua kondisi</option>
            <option value="aktif">Sedang aktif</option>
            <option value="akan-berakhir">Akan berakhir (7 hari)</option>
            <option value="berakhir">Sudah berakhir</option>
        </select>

        <div wire:loading class="text-xs text-muted">Memuat…</div>
    </div>

    {{-- Tabel langganan --}}
    <div class="overflow-x-auto rounded-xl bg-white shadow-brand-xs">
        <table class="w-full min-w-[860px] text-sm">
            <thead class="prim-table-head bg-canvas">
                <tr>
                    <th class="px-4 py-3">Pelanggan</th>
                    <th class="px-4 py-3">Layanan</th>
                    <th class="px-4 py-3">Paket</th>
                    <th class="px-4 py-3">Mulai</th>
                    <th class="px-4 py-3">Berakhir</th>
                    <th class="px-4 py-3">Sisa</th>
                    <th class="px-4 py-3">Perpanjangan</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($subscriptions as $langganan)
                    <tr wire:key="langganan-{{ $langganan->id }}" class="border-b border-line-soft last:border-0 hover:bg-canvas/60">
                        <td class="px-4 py-3">
                            <p class="text-ink">{{ $langganan->user->name }}</p>
                            <p class="text-xs text-muted">{{ $langganan->user->email }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-ink">{{ $langganan->plan->service->name }}</p>
                            <p class="text-xs text-muted">{{ $langganan->plan->service->provider->name }}</p>
                        </td>
                        <td class="px-4 py-3 text-xs text-ink">{{ $langganan->plan->groupLabel() }}</td>
                        <td class="px-4 py-3 text-xs text-muted whitespace-nowrap">
                            {{ $langganan->started_at?->translatedFormat('d M Y') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-xs text-muted whitespace-nowrap">
                            {{ $langganan->ends_at?->translatedFormat('d M Y') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if ($langganan->isActive())
                                <span @class([
                                    'text-sm font-medium',
                                    'text-status-wait-fg' => $langganan->daysRemaining() <= 7,
                                    'text-ink' => $langganan->daysRemaining() > 7,
                                ])>
                                    {{ $langganan->daysRemaining() }} hari
                                </span>
                            @else
                                <span class="text-sm text-muted">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs">
                            @if ($langganan->auto_renew)
                                <span class="prim-badge bg-status-done-bg text-status-done-fg">Aktif</span>
                            @else
                                <span class="prim-badge bg-canvas text-muted">Mati</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($langganan->isActive())
                                <span class="prim-badge bg-status-done-bg text-status-done-fg">Aktif</span>
                            @else
                                <span class="prim-badge bg-status-cancel-bg text-status-cancel-fg">Berakhir</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-muted">Belum ada data langganan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $subscriptions->links() }}</div>
</div>
