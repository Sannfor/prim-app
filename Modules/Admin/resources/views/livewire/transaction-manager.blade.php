<div class="space-y-7">
    <div>
        <h1 class="font-display text-2xl font-medium text-ink-strong">Manajemen Pesanan</h1>
        <p class="mt-1 text-sm text-muted">
            Pantau seluruh pesanan dan verifikasi pembayaran secara manual bila diperlukan.
        </p>
    </div>

    {{-- Ringkasan --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($summaryCards as $card)
            <div class="rounded-xl bg-canvas p-5">
                <p class="text-sm text-muted">{{ $card['label'] }}</p>
                <p class="mt-1.5 font-display text-xl font-medium text-ink-strong">{{ $card['value'] }}</p>
                <p class="mt-1 text-xs text-muted">{{ $card['hint'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Penyaring --}}
    <div class="flex flex-wrap items-center gap-3">
        <div class="flex flex-wrap gap-1.5">
            <button
                type="button"
                wire:click="$set('status', '')"
                @class([
                    'rounded-full px-4 py-1.5 text-sm transition',
                    'bg-brand text-white' => $status === '',
                    'bg-canvas text-ink hover:bg-brand-soft' => $status !== '',
                ])
            >Semua</button>

            @foreach (['paid' => 'Selesai', 'processed' => 'Diproses', 'pending' => 'Menunggu', 'cancelled' => 'Dibatalkan'] as $value => $label)
                <button
                    type="button"
                    wire:click="$set('status', '{{ $value }}')"
                    @class([
                        'rounded-full px-4 py-1.5 text-sm transition',
                        'bg-brand text-white' => $status === $value,
                        'bg-canvas text-ink hover:bg-brand-soft' => $status !== $value,
                    ])
                >{{ $label }}</button>
            @endforeach
        </div>

        <div class="ml-auto w-full max-w-xs">
            <input
                type="search"
                wire:model.live.debounce.400ms="search"
                placeholder="Cari ID atau nama pelanggan…"
                class="prim-input h-10 text-sm"
            >
        </div>
    </div>

    {{-- Formulir pembagian kode login --}}
    @if ($kredensialTransaksiId)
        <section class="rounded-xl bg-white p-6 shadow-brand-xs" wire:key="form-kredensial">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="font-display text-base font-semibold text-ink-strong">Bagikan Kode Login</h2>
                    <p class="mt-1 text-xs text-muted">
                        Kredensial ini akan muncul pada halaman Kode Login pembeli, dan pembeli
                        menerima pemberitahuan begitu disimpan.
                    </p>
                </div>

                <button type="button" wire:click="tutupFormKredensial" class="prim-btn-ghost h-9 text-sm">Tutup</button>
            </div>

            <p class="mt-4 rounded-lg bg-canvas px-3.5 py-2.5 text-sm text-ink">
                Paket: <strong>{{ $kredensialLabel }}</strong>
            </p>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="kredensialLogin" class="prim-label">Kode Login / Email Akun</label>
                    <input id="kredensialLogin" type="text" wire:model="kredensialLogin" class="prim-input font-mono"
                        placeholder="mis. prim.netflix01@mail.com">
                    @error('kredensialLogin') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="kredensialSandi" class="prim-label">Kata Sandi</label>
                    <input id="kredensialSandi" type="text" wire:model="kredensialSandi" class="prim-input font-mono"
                        placeholder="opsional">
                    @error('kredensialSandi') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="kredensialProfil" class="prim-label">Nama Profil</label>
                    <input id="kredensialProfil" type="text" wire:model="kredensialProfil" class="prim-input"
                        placeholder="opsional">
                    @error('kredensialProfil') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="kredensialPin" class="prim-label">PIN</label>
                    <input id="kredensialPin" type="text" wire:model="kredensialPin" class="prim-input font-mono"
                        placeholder="opsional">
                    @error('kredensialPin') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="kredensialCatatan" class="prim-label">Catatan untuk Pembeli</label>
                    <input id="kredensialCatatan" type="text" wire:model="kredensialCatatan" class="prim-input"
                        placeholder="mis. jangan mengubah kata sandi dan profil">
                    @error('kredensialCatatan') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-line-soft pt-5">
                <button type="button" wire:click="simpanKredensial" class="prim-btn px-7" wire:loading.attr="disabled">
                    <flux:icon.key class="size-4" wire:loading.remove />
                    <span wire:loading.remove>Simpan &amp; Beri Tahu Pembeli</span>
                    <span wire:loading>Menyimpan…</span>
                </button>

                <button type="button" wire:click="tutupFormKredensial" class="prim-btn-ghost h-10">Batal</button>
            </div>
        </section>
    @endif

    {{-- Formulir pengembalian dana --}}
    @if ($refundTransaksiId)
        <section class="rounded-xl border border-status-cancel-bg bg-status-cancel-bg/40 p-6" wire:key="form-refund">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="font-display text-base font-semibold text-status-cancel-fg">Kembalikan Dana</h2>
                    <p class="mt-1 text-xs text-muted">
                        Pengembalian dana tidak menghapus pesanan. Statusnya tetap Selesai dengan
                        penanda dana sudah dikembalikan, dan langganan pembeli dihentikan.
                    </p>
                </div>

                <button type="button" wire:click="tutupFormRefund" class="prim-btn-ghost h-9 text-sm">Tutup</button>
            </div>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="refundNominal" class="prim-label">Nominal Pengembalian (Rp)</label>
                    <input id="refundNominal" type="number" wire:model="refundNominal" class="prim-input" min="1">
                    <p class="mt-1.5 text-xs text-muted">
                        Boleh sebagian, tetapi tidak melebihi total pesanan.
                    </p>
                    @error('refundNominal') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="refundAlasan" class="prim-label">Alasan Pengembalian</label>
                    <input id="refundAlasan" type="text" wire:model="refundAlasan" class="prim-input"
                        placeholder="mis. layanan tidak dapat diproses">
                    <p class="mt-1.5 text-xs text-muted">Alasan ikut dikirimkan kepada pembeli.</p>
                    @error('refundAlasan') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-line-soft pt-5">
                <button
                    type="button"
                    wire:click="prosesRefund"
                    class="inline-flex items-center gap-2 rounded-full bg-status-cancel-fg px-6 py-2.5 text-sm font-medium text-white transition hover:opacity-90"
                    wire:loading.attr="disabled"
                >
                    <flux:icon.banknotes class="size-4" wire:loading.remove />
                    <span wire:loading.remove>Proses Pengembalian</span>
                    <span wire:loading>Memproses…</span>
                </button>

                <button type="button" wire:click="tutupFormRefund" class="prim-btn-ghost h-10">Batal</button>
            </div>
        </section>
    @endif

    {{-- Tabel pesanan --}}
    <div class="overflow-x-auto rounded-xl bg-white shadow-brand-xs">
        <table class="w-full min-w-[900px] text-sm">
            <thead class="prim-table-head bg-canvas">
                <tr>
                    <th class="px-4 py-3">ID Pesanan</th>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Pelanggan</th>
                    <th class="px-4 py-3">Produk</th>
                    <th class="px-4 py-3">Metode Pembayaran</th>
                    <th class="px-4 py-3">Total</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $transaction)
                    <tr wire:key="trx-{{ $transaction->id }}" class="border-b border-line-soft last:border-0 hover:bg-canvas/60">
                        <td class="px-4 py-3 font-mono text-xs text-ink">
                            {{ $transaction->order_code }}
                        </td>
                        <td class="px-4 py-3 text-xs text-muted whitespace-nowrap">
                            {{ $transaction->created_at->translatedFormat('d M Y') }}
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-ink">{{ $transaction->user->name }}</p>
                            <p class="text-xs text-muted">{{ $transaction->user->email }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-ink">{{ $transaction->plan->service->name }}</p>
                            <p class="text-xs text-muted">{{ $transaction->plan->groupLabel() }}</p>
                        </td>
                        <td class="px-4 py-3 text-xs text-ink">{{ $transaction->paymentLabel() }}</td>
                        <td class="px-4 py-3 font-medium text-ink whitespace-nowrap">{{ $transaction->formattedAmount() }}</td>
                        <td class="px-4 py-3">
                            <x-status-badge :status="$transaction->status" />

                            @if ($transaction->isRefunded())
                                <p class="mt-1 flex items-center gap-1 text-[11px] font-medium text-status-cancel-fg">
                                    <flux:icon.banknotes class="size-3" />
                                    Dana dikembalikan {{ $transaction->formattedRefund() }}
                                </p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-1">
                                @if ($transaction->status->isSuccessful())
                                    {{-- Pesanan lunas: bagikan kode login atau kembalikan dana --}}
                                    <button
                                        type="button"
                                        wire:click="bukaFormKredensial({{ $transaction->id }})"
                                        class="rounded-md p-1.5 text-brand hover:bg-brand-soft"
                                        title="Bagikan kode login"
                                    >
                                        <flux:icon.key class="size-4" />
                                    </button>

                                    @unless ($transaction->isRefunded())
                                        <button
                                            type="button"
                                            wire:click="bukaFormRefund({{ $transaction->id }})"
                                            class="rounded-md p-1.5 text-status-cancel-fg hover:bg-status-cancel-bg"
                                            title="Kembalikan dana"
                                        >
                                            <flux:icon.banknotes class="size-4" />
                                        </button>
                                    @endunless
                                @else
                                    <button
                                        type="button"
                                        wire:click="markAsPaid({{ $transaction->id }})"
                                        class="rounded-md p-1.5 text-status-done-fg hover:bg-status-done-bg"
                                        title="Tandai selesai"
                                    >
                                        <flux:icon.check-circle class="size-4" />
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="markAsFailed({{ $transaction->id }})"
                                        class="rounded-md p-1.5 text-status-cancel-fg hover:bg-status-cancel-bg"
                                        title="Tandai gagal"
                                    >
                                        <flux:icon.x-circle class="size-4" />
                                    </button>
                                @endif

                                <a
                                    href="{{ route('transaction.show', $transaction->order_code) }}"
                                    class="rounded-md p-1.5 text-muted hover:bg-canvas hover:text-brand"
                                    title="Lihat rincian"
                                    wire:navigate
                                >
                                    <flux:icon.eye class="size-4" />
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-muted">Tidak ada pesanan yang cocok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $transactions->links() }}</div>
</div>
