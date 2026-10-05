<div class="space-y-5">
    {{-- Kepala halaman --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-2xl font-bold text-ink-strong">Kelola Voucher</h1>
            <p class="mt-1 text-sm text-muted">Atur kode promo beserta syarat dan kuotanya.</p>
        </div>

        <button type="button" wire:click="buat" class="prim-btn h-10">
            <flux:icon.plus class="size-4" />
            Tambah Voucher
        </button>
    </div>

    {{-- Ringkasan --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl bg-white p-5 shadow-brand-xs">
            <p class="text-xs tracking-wide text-muted uppercase">Total Voucher</p>
            <p class="mt-1.5 font-display text-2xl font-bold text-ink-strong">{{ $ringkasan['total'] }}</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-brand-xs">
            <p class="text-xs tracking-wide text-muted uppercase">Voucher Aktif</p>
            <p class="mt-1.5 font-display text-2xl font-bold text-status-done-fg">{{ $ringkasan['aktif'] }}</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-brand-xs">
            <p class="text-xs tracking-wide text-muted uppercase">Total Pemakaian</p>
            <p class="mt-1.5 font-display text-2xl font-bold text-ink-strong">{{ $ringkasan['pemakaian'] }}×</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-brand-xs">
            <p class="text-xs tracking-wide text-muted uppercase">Total Potongan Diberikan</p>
            <p class="mt-1.5 font-display text-xl font-bold text-brand">
                Rp{{ number_format($ringkasan['totalPotongan'], 0, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- Formulir voucher --}}
    @if ($showForm)
        <section class="rounded-xl bg-white p-6 shadow-brand-xs" wire:key="formulir-voucher">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="font-display text-base font-semibold text-ink-strong">
                        {{ $editingId ? 'Ubah Voucher' : 'Voucher Baru' }}
                    </h2>
                    <p class="mt-1 text-xs text-muted">
                        Kode voucher tidak peka huruf besar kecil dan otomatis disimpan dalam huruf besar.
                    </p>
                </div>

                <button type="button" wire:click="tutupForm" class="prim-btn-ghost h-9 text-sm">Tutup</button>
            </div>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="kode" class="prim-label">Kode Voucher</label>
                    <input id="kode" type="text" wire:model="code" class="prim-input font-mono uppercase" placeholder="HEMAT20">
                    @error('code') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="deskripsi" class="prim-label">Keterangan</label>
                    <input id="deskripsi" type="text" wire:model="description" class="prim-input" placeholder="Potongan 20% untuk semua layanan">
                    @error('description') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="tipe" class="prim-label">Jenis Potongan</label>
                    <select id="tipe" wire:model.live="type" class="prim-input">
                        <option value="percent">Persentase (%)</option>
                        <option value="fixed">Nominal tetap (Rp)</option>
                    </select>
                </div>

                <div>
                    <label for="nilai" class="prim-label">
                        {{ $type === 'percent' ? 'Besar Potongan (%)' : 'Besar Potongan (Rp)' }}
                    </label>
                    <input id="nilai" type="number" wire:model="value" class="prim-input" min="1">
                    @error('value') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
                </div>

                @if ($type === 'percent')
                    <div>
                        <label for="maks" class="prim-label">Maksimum Potongan (Rp)</label>
                        <input id="maks" type="number" wire:model="maxDiscount" class="prim-input" placeholder="50000">
                        <p class="mt-1.5 text-xs text-muted">Kosongkan bila potongan tidak dibatasi.</p>
                        @error('maxDiscount') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div>
                    <label for="minimum" class="prim-label">Minimum Belanja (Rp)</label>
                    <input id="minimum" type="number" wire:model="minPurchase" class="prim-input" min="0">
                    @error('minPurchase') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="kuota" class="prim-label">Batas Kuota Keseluruhan</label>
                    <input id="kuota" type="number" wire:model="usageLimit" class="prim-input" placeholder="100">
                    <p class="mt-1.5 text-xs text-muted">Kosongkan bila kuota tidak dibatasi.</p>
                    @error('usageLimit') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="peruser" class="prim-label">Batas Pemakaian per Pengguna</label>
                    <input id="peruser" type="number" wire:model="usageLimitPerUser" class="prim-input" min="1">
                    @error('usageLimitPerUser') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="berakhir" class="prim-label">Berlaku Sampai</label>
                    <input id="berakhir" type="date" wire:model="expiresAt" class="prim-input">
                    <p class="mt-1.5 text-xs text-muted">Kosongkan bila voucher berlaku selamanya.</p>
                    @error('expiresAt') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-end">
                    <label class="flex cursor-pointer items-center gap-3">
                        <input type="checkbox" wire:model="isActive" class="size-4 accent-[#534AB7]">
                        <span class="text-sm text-ink">Aktifkan voucher ini</span>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-line-soft pt-5">
                <button type="button" wire:click="simpan" class="prim-btn px-7" wire:loading.attr="disabled">
                    <span wire:loading.remove>{{ $editingId ? 'Simpan Perubahan' : 'Simpan Voucher' }}</span>
                    <span wire:loading>Menyimpan…</span>
                </button>

                <button type="button" wire:click="tutupForm" class="prim-btn-ghost h-10">Batal</button>
            </div>
        </section>
    @endif

    {{-- Penyaring --}}
    <div class="flex flex-wrap items-center gap-3">
        <div class="min-w-[220px] flex-1">
            <input type="search" wire:model.live.debounce.400ms="search" placeholder="Cari kode atau keterangan…" class="prim-input h-10 text-sm">
        </div>

        <div class="flex flex-wrap gap-1.5">
            @foreach (['semua' => 'Semua', 'aktif' => 'Aktif', 'nonaktif' => 'Nonaktif', 'habis' => 'Kuota Habis'] as $nilai => $label)
                <button
                    type="button"
                    wire:key="saring-{{ $nilai }}"
                    wire:click="$set('statusFilter', '{{ $nilai }}')"
                    @class([
                        'rounded-full px-3.5 py-1.5 text-sm transition',
                        'bg-brand text-white' => $statusFilter === $nilai,
                        'bg-white text-ink hover:bg-brand-soft' => $statusFilter !== $nilai,
                    ])
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Daftar voucher --}}
    <section class="overflow-hidden rounded-xl bg-white shadow-brand-xs">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-sm">
                <thead class="prim-table-head bg-canvas">
                    <tr>
                        <th class="px-6 py-3">Kode</th>
                        <th class="px-6 py-3">Potongan</th>
                        <th class="px-6 py-3">Syarat</th>
                        <th class="px-6 py-3">Kuota</th>
                        <th class="px-6 py-3">Masa Berlaku</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($vouchers as $voucher)
                        <tr wire:key="voucher-{{ $voucher->id }}" class="border-b border-line-soft last:border-0 hover:bg-canvas/60">
                            <td class="px-6 py-3">
                                <p class="font-mono font-semibold text-ink-strong">{{ $voucher->code }}</p>
                                @if ($voucher->description)
                                    <p class="mt-0.5 text-xs text-muted">{{ $voucher->description }}</p>
                                @endif
                            </td>

                            <td class="px-6 py-3">
                                <span class="prim-badge bg-brand-soft text-brand">{{ $voucher->valueLabel() }}</span>
                                @if ($voucher->max_discount)
                                    <p class="mt-1 text-xs text-muted">
                                        maks. Rp{{ number_format($voucher->max_discount, 0, ',', '.') }}
                                    </p>
                                @endif
                            </td>

                            <td class="px-6 py-3 text-xs text-muted">
                                @if ($voucher->min_purchase > 0)
                                    min. Rp{{ number_format($voucher->min_purchase, 0, ',', '.') }}
                                @else
                                    tanpa minimum
                                @endif
                                <br>
                                {{ $voucher->usage_limit_per_user }}× per pengguna
                            </td>

                            <td class="px-6 py-3">
                                @php $sisa = $voucher->remainingQuota(); @endphp

                                @if ($sisa === null)
                                    <span class="text-xs text-muted">tak terbatas</span>
                                @else
                                    <span @class([
                                        'text-sm font-medium',
                                        'text-status-cancel-fg' => $sisa === 0,
                                        'text-status-wait-fg' => $sisa > 0 && $sisa <= 10,
                                        'text-ink' => $sisa > 10,
                                    ])>{{ $sisa }}</span>
                                    <span class="text-xs text-muted">/ {{ $voucher->usage_limit }}</span>
                                @endif

                                <p class="mt-0.5 text-xs text-muted">dipakai {{ $voucher->used_count }}×</p>
                            </td>

                            <td class="px-6 py-3 text-xs text-muted">
                                {{ $voucher->expires_at?->translatedFormat('d M Y') ?? 'selamanya' }}
                                @if ($voucher->expires_at && ! $voucher->isWithinPeriod())
                                    <p class="mt-0.5 text-status-cancel-fg">sudah berakhir</p>
                                @endif
                            </td>

                            <td class="px-6 py-3">
                                @if ($voucher->is_active)
                                    <span class="prim-badge bg-status-done-bg text-status-done-fg">Aktif</span>
                                @else
                                    <span class="prim-badge bg-status-cancel-bg text-status-cancel-fg">Nonaktif</span>
                                @endif

                                @if ($sisa === 0)
                                    <p class="mt-1 text-[11px] text-status-cancel-fg">kuota habis</p>
                                @endif
                            </td>

                            <td class="px-6 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" wire:click="ubah({{ $voucher->id }})" class="prim-btn-ghost h-8 px-3 text-xs">
                                        Ubah
                                    </button>

                                    <button type="button" wire:click="ubahStatus({{ $voucher->id }})" class="prim-btn-ghost h-8 px-3 text-xs">
                                        {{ $voucher->is_active ? 'Matikan' : 'Aktifkan' }}
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="hapus({{ $voucher->id }})"
                                        wire:confirm="Hapus voucher {{ $voucher->code }}? Voucher yang pernah dipakai hanya akan dinonaktifkan."
                                        class="inline-flex h-8 items-center rounded-full px-3 text-xs text-status-cancel-fg transition hover:bg-status-cancel-bg"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <flux:icon.ticket class="mx-auto size-8 text-muted-2" />
                                <p class="mt-3 font-medium text-ink-strong">Belum ada voucher</p>
                                <p class="mt-1 text-sm text-muted">Tambahkan kode promo agar pembeli mendapat potongan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($vouchers->hasPages())
            <div class="border-t border-line-soft px-6 py-4">{{ $vouchers->links() }}</div>
        @endif
    </section>
</div>
