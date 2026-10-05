<div class="space-y-7">
    <div>
        <h1 class="font-display text-2xl font-medium text-ink-strong">Manajemen Pengguna</h1>
        <p class="mt-1 text-sm text-muted">Cari pengguna, lihat aktivitasnya, dan atur peran serta statusnya.</p>
    </div>

    {{-- Ringkasan pengguna --}}
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
        <div class="w-full max-w-xs">
            <input
                type="search"
                wire:model.live.debounce.400ms="search"
                placeholder="Cari ID, nama, atau email…"
                class="prim-input h-10 text-sm"
            >
        </div>

        <select wire:model.live="roleFilter" class="prim-input h-10 max-w-40 text-sm">
            <option value="">Semua peran</option>
            @foreach ($roles as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>

        <select wire:model.live="statusFilter" class="prim-input h-10 max-w-40 text-sm">
            <option value="">Semua status</option>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>

        <div wire:loading class="text-xs text-muted">Memuat…</div>
    </div>

    {{-- Tabel pengguna --}}
    <div class="overflow-x-auto rounded-xl bg-white shadow-brand-xs">
        <table class="w-full min-w-[1060px] text-sm">
            <thead class="prim-table-head bg-canvas">
                <tr>
                    <th class="px-4 py-3">Pengguna</th>
                    <th class="px-4 py-3">No. Telepon</th>
                    <th class="px-4 py-3">Role</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Tgl. Daftar</th>
                    <th class="px-4 py-3">Pesanan</th>
                    <th class="px-4 py-3">Terakhir Login</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr wire:key="user-{{ $user->id }}" class="border-b border-line-soft last:border-0 hover:bg-canvas/60">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-brand-soft text-xs font-medium text-brand">
                                    {{ $user->initials() }}
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-ink-strong">
                                        {{ $user->name }}
                                        @if ($user->id === auth()->id())
                                            <span class="text-xs text-muted">(Anda)</span>
                                        @endif
                                    </p>
                                    <p class="truncate text-xs text-muted">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-xs text-ink whitespace-nowrap">{{ $user->phone ?: '—' }}</td>
                        <td class="px-4 py-3">
                            {{--
                                Pemilih peran memakai dropdown Flux, bukan select bawaan.
                                Select bawaan dirender oleh sistem operasi sehingga
                                daftar pilihannya terpotong oleh area gulir tabel dan
                                sebagian pilihan tidak terlihat.
                            --}}
                            <flux:dropdown position="bottom" align="start">
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-lg border border-line-soft bg-white px-2.5 py-1.5 text-xs text-ink transition hover:border-brand hover:text-brand"
                                >
                                    {{ $user->role->label() }}
                                    <flux:icon.chevron-down class="size-3.5 text-muted" />
                                </button>

                                <flux:menu>
                                    @foreach ($roles as $value => $label)
                                        <flux:menu.item
                                            as="button"
                                            type="button"
                                            wire:key="role-{{ $user->id }}-{{ $value }}"
                                            wire:click="changeRole({{ $user->id }}, '{{ $value }}')"
                                            icon="{{ $user->role->value === $value ? 'check' : '' }}"
                                        >
                                            {{ $label }}
                                        </flux:menu.item>
                                    @endforeach
                                </flux:menu>
                            </flux:dropdown>
                        </td>
                        <td class="px-4 py-3">
                            {{-- Pemilih status, alasan yang sama seperti pemilih peran. --}}
                            <flux:dropdown position="bottom" align="start">
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-lg border border-line-soft bg-white px-2.5 py-1.5 text-xs text-ink transition hover:border-brand hover:text-brand"
                                >
                                    <span @class([
                                        'size-2 rounded-full',
                                        'bg-status-done-fg' => $user->statusEnum()->value === 'aktif',
                                        'bg-status-cancel-fg' => $user->statusEnum()->value === 'non_aktif',
                                        'bg-status-wait-fg' => $user->statusEnum()->value === 'suspend',
                                    ])></span>
                                    {{ $user->statusEnum()->label() }}
                                    <flux:icon.chevron-down class="size-3.5 text-muted" />
                                </button>

                                <flux:menu>
                                    @foreach ($statuses as $value => $label)
                                        <flux:menu.item
                                            as="button"
                                            type="button"
                                            wire:key="status-{{ $user->id }}-{{ $value }}"
                                            wire:click="changeStatus({{ $user->id }}, '{{ $value }}')"
                                            icon="{{ $user->statusEnum()->value === $value ? 'check' : '' }}"
                                        >
                                            {{ $label }}
                                        </flux:menu.item>
                                    @endforeach
                                </flux:menu>
                            </flux:dropdown>
                        </td>
                        <td class="px-4 py-3 text-xs text-muted whitespace-nowrap">
                            {{ $user->created_at->translatedFormat('d M Y') }}
                        </td>
                        <td class="px-4 py-3 text-ink">{{ $user->transactions_count }}</td>
                        <td class="px-4 py-3 text-xs text-muted whitespace-nowrap">
                            {{ $user->last_login_at?->diffForHumans() ?? 'Belum pernah' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-muted">Tidak ada pengguna yang cocok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $users->links() }}</div>

    <p class="text-xs text-muted">
        Peran <strong>Administrator</strong> dapat mengakses seluruh panel pengelola,
        <strong>Penyedia Layanan</strong> mewakili pihak pemasok katalog, dan
        <strong>Pelanggan</strong> hanya dapat berbelanja serta melihat langganannya.
        Akun berstatus <strong>Suspend</strong> atau <strong>Non-aktif</strong> tidak dapat masuk.
    </p>
</div>
