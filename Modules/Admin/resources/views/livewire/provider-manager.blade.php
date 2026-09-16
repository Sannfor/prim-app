<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Kelola Penyedia Layanan</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Data penyedia yang menaungi layanan premium pada katalog PRIM.
            </p>
        </div>

        <flux:button wire:click="create" variant="filled" icon="plus">Tambah Penyedia</flux:button>
    </div>

    @if ($providers->isEmpty())
        <div class="rounded-xl border border-dashed border-zinc-300 bg-white p-12 text-center dark:border-zinc-600 dark:bg-zinc-800">
            <flux:icon.building-office class="mx-auto size-8 text-zinc-400" />
            <p class="mt-3 font-medium">Belum ada penyedia</p>
            <p class="mt-1 text-sm text-zinc-500">Tambahkan penyedia sebelum mendaftarkan layanan.</p>
        </div>
    @else
        <div class="overflow-x-auto rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
            <table class="w-full text-sm">
                <thead class="border-b border-zinc-200 bg-zinc-50 text-left dark:border-zinc-700 dark:bg-zinc-900">
                    <tr>
                        <th class="p-3 font-medium">Penyedia</th>
                        <th class="p-3 font-medium">Situs Web</th>
                        <th class="p-3 font-medium">Layanan</th>
                        <th class="p-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($providers as $provider)
                        <tr wire:key="provider-{{ $provider->id }}" class="border-b border-zinc-100 last:border-0 dark:border-zinc-700">
                            <td class="p-3">
                                <div class="flex items-center gap-3">
                                    @if ($provider->logo_path)
                                        <img
                                            src="{{ Storage::disk('public')->url($provider->logo_path) }}"
                                            alt="Logo {{ $provider->name }}"
                                            class="size-9 rounded-lg object-cover"
                                        >
                                    @else
                                        <span class="flex size-9 items-center justify-center rounded-lg bg-indigo-50 text-xs font-bold text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">
                                            {{ Str::of($provider->name)->substr(0, 2)->upper() }}
                                        </span>
                                    @endif

                                    <div>
                                        <p class="font-medium">{{ $provider->name }}</p>
                                        <p class="font-mono text-xs text-zinc-500">{{ $provider->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3">
                                @if ($provider->website)
                                    <a href="{{ $provider->website }}" target="_blank" class="text-indigo-600 hover:underline">
                                        {{ Str::limit($provider->website, 32) }}
                                    </a>
                                @else
                                    <span class="text-zinc-400">—</span>
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
            <p class="mt-1 text-xs text-zinc-500">Format gambar, maksimal 2 MB.</p>
            @error('logo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </x-admin::form-modal>

    <x-admin::confirm-delete message="Penyedia yang masih memiliki layanan tidak dapat dihapus." />
</div>
