<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-medium text-ink-strong">Kelola Penyedia Layanan</h1>
            <p class="mt-1 text-sm text-muted">
                Data penyedia yang menaungi layanan premium pada katalog PRIM.
            </p>
        </div>

        <flux:button wire:click="create" variant="filled" icon="plus">Tambah Penyedia</flux:button>
    </div>

    @if ($providers->isEmpty())
        <div class="rounded-xl border border-dashed border-line bg-white p-12 text-center">
            <flux:icon.building-office class="mx-auto size-8 text-muted-2" />
            <p class="mt-3 font-medium">Belum ada penyedia</p>
            <p class="mt-1 text-sm text-muted">Tambahkan penyedia sebelum mendaftarkan layanan.</p>
        </div>
    @else
        <div class="overflow-x-auto rounded-xl bg-white shadow-brand-xs">
            <table class="w-full text-sm">
                <thead class="border-b border-line-soft bg-canvas text-left">
                    <tr>
                        <th class="p-3 font-medium">Penyedia</th>
                        <th class="p-3 font-medium">Situs Web</th>
                        <th class="p-3 font-medium">Layanan</th>
                        <th class="p-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($providers as $provider)
                        <tr wire:key="provider-{{ $provider->id }}" class="border-b border-line-soft last:border-0">
                            <td class="p-3">
                                <div class="flex items-center gap-3">
                                    @if ($provider->logo_path)
                                        <img
                                            src="{{ Storage::disk('public')->url($provider->logo_path) }}"
                                            alt="Logo {{ $provider->name }}"
                                            class="size-9 rounded-lg object-cover"
                                        >
                                    @else
                                        <span class="flex size-9 items-center justify-center rounded-lg bg-brand-soft text-xs font-bold text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">
                                            {{ Str::of($provider->name)->substr(0, 2)->upper() }}
                                        </span>
                                    @endif

                                    <div>
                                        <p class="font-medium">{{ $provider->name }}</p>
                                        <p class="font-mono text-xs text-muted">{{ $provider->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3">
                                @if ($provider->website)
                                    <a href="{{ $provider->website }}" target="_blank" class="text-indigo-600 hover:underline">
                                        {{ Str::limit($provider->website, 32) }}
                                    </a>
                                @else
                                    <span class="text-muted-2">—</span>
                                @endif
                            </td>
                            <td class="p-3">{{ $provider->services_count }}</td>
                            <td class="p-3">
                                <div class="flex justify-end gap-1">
                                    <flux:button wire:click="edit({{ $provider->id }})" variant="ghost" size="sm" icon="pencil" />
                                    <flux:button wire:click="confirmDelete({{ $provider->id }})" variant="ghost" size="sm" icon="trash" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <x-admin::form-modal :title="$editingId ? 'Sunting Penyedia' : 'Tambah Penyedia'">
        <flux:input wire:model="name" label="Nama Penyedia" placeholder="Contoh: Nusantara Stream" required />
        <flux:input wire:model="website" type="url" label="Situs Web" placeholder="https://contoh.id" />
        <flux:textarea wire:model="description" label="Deskripsi" rows="3" />

        <div>
            <flux:input wire:model="logo" type="file" accept="image/*" label="Logo (opsional)" />
            <p class="mt-1 text-xs text-muted">Format gambar, maksimal 2 MB.</p>
            @error('logo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </x-admin::form-modal>

    <x-admin::confirm-delete message="Penyedia yang masih memiliki layanan tidak dapat dihapus." />
</div>
